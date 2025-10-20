<?php

namespace App\Observers;

use App\Models\Unit;
use App\Services\FCMNotificationService;
use Illuminate\Support\Facades\Log;

class UnitObserver
{
    protected $fcmService;

    public function __construct()
    {
        try {
            $this->fcmService = new FCMNotificationService();
        } catch (\Exception $e) {
            Log::warning('FCM Service not initialized in UnitObserver: ' . $e->getMessage());
            $this->fcmService = null;
        }
    }

    /**
     * Handle the Unit "created" event.
     *
     * @param  \App\Models\Unit  $unit
     * @return void
     */
    public function created(Unit $unit)
    {
        if (!$this->fcmService) {
            return;
        }

        try {
            $compoundName = $unit->compound->project ?? $unit->compound->name ?? 'Unknown Compound';
            $unitName = $unit->unit_name ?? $unit->unit_code ?? 'New Unit';

            $title = "New Unit Available!";
            $body = "A new unit '{$unitName}' has been added in {$compoundName}";

            $data = [
                'type' => 'new_unit',
                'unit_id' => (string)$unit->id,
                'unit_name' => $unitName,
                'compound_id' => (string)$unit->compound_id,
                'compound_name' => $compoundName,
                'price' => (string)($unit->normal_price ?? '0'),
            ];

            // Send to all buyers
            $this->fcmService->sendToUsersByRole('buyer', $title, $body, $data);

            Log::info("Notification sent for new unit: {$unitName}");
        } catch (\Exception $e) {
            Log::error('Failed to send notification for new unit: ' . $e->getMessage());
        }

        // Update company statistics
        $this->updateCompanyStatistics($unit);
    }

    /**
     * Handle the Unit "updated" event.
     *
     * @param  \App\Models\Unit  $unit
     * @return void
     */
    public function updated(Unit $unit)
    {
        if (!$this->fcmService) {
            return;
        }

        // Log all changes for debugging
        $changes = $unit->getDirty();
        if (!empty($changes)) {
            Log::info("Unit {$unit->id} updated. Changed fields: " . implode(', ', array_keys($changes)));
        }

        // Check if the unit was just sold
        if ($unit->isDirty('is_sold') && $unit->is_sold) {
            try {
                $compoundName = $unit->compound->project ?? $unit->compound->name ?? 'Unknown Compound';
                $unitName = $unit->unit_name ?? $unit->unit_code ?? 'Unit';

                $title = "Unit Sold!";
                $body = "Unit '{$unitName}' in {$compoundName} has been sold";

                $data = [
                    'type' => 'unit_sold',
                    'unit_id' => (string)$unit->id,
                    'unit_name' => $unitName,
                    'compound_id' => (string)$unit->compound_id,
                    'compound_name' => $compoundName,
                ];

                // Send to all users
                $this->fcmService->sendToAllUsers($title, $body, $data);

                Log::info("Notification sent for sold unit: {$unitName}");
                return; // Don't send other notifications if unit is sold
            } catch (\Exception $e) {
                Log::error('Failed to send notification for sold unit: ' . $e->getMessage());
            }
        }

        // Check if price was reduced
        if ($unit->isDirty('normal_price') && $unit->getOriginal('normal_price') > $unit->normal_price) {
            try {
                $compoundName = $unit->compound->project ?? $unit->compound->name ?? 'Unknown Compound';
                $unitName = $unit->unit_name ?? $unit->unit_code ?? 'Unit';
                $oldPrice = $unit->getOriginal('normal_price');
                $newPrice = $unit->normal_price;
                $discount = (($oldPrice - $newPrice) / $oldPrice) * 100;

                $title = "Price Drop Alert!";
                $body = "Unit '{$unitName}' in {$compoundName} price reduced by " . number_format($discount, 1) . "%";

                $data = [
                    'type' => 'price_drop',
                    'unit_id' => (string)$unit->id,
                    'unit_name' => $unitName,
                    'compound_id' => (string)$unit->compound_id,
                    'compound_name' => $compoundName,
                    'old_price' => (string)$oldPrice,
                    'new_price' => (string)$newPrice,
                    'discount_percentage' => (string)round($discount, 1),
                ];

                // Send to all buyers
                $this->fcmService->sendToUsersByRole('buyer', $title, $body, $data);

                Log::info("Notification sent for price drop: {$unitName}");
                return; // Don't send generic update notification if price drop sent
            } catch (\Exception $e) {
                Log::error('Failed to send notification for price drop: ' . $e->getMessage());
            }
        }

        // Check if price was increased
        if ($unit->isDirty('normal_price') && $unit->getOriginal('normal_price') < $unit->normal_price) {
            try {
                $compoundName = $unit->compound->project ?? $unit->compound->name ?? 'Unknown Compound';
                $unitName = $unit->unit_name ?? $unit->unit_code ?? 'Unit';
                $oldPrice = $unit->getOriginal('normal_price');
                $newPrice = $unit->normal_price;

                $title = "Unit Price Updated";
                $body = "Unit '{$unitName}' in {$compoundName} price changed from " . number_format($oldPrice) . " to " . number_format($newPrice);

                $data = [
                    'type' => 'price_update',
                    'unit_id' => (string)$unit->id,
                    'unit_name' => $unitName,
                    'compound_id' => (string)$unit->compound_id,
                    'compound_name' => $compoundName,
                    'old_price' => (string)$oldPrice,
                    'new_price' => (string)$newPrice,
                ];

                // Send to all buyers
                $this->fcmService->sendToUsersByRole('buyer', $title, $body, $data);

                Log::info("Notification sent for price update: {$unitName}");
                return;
            } catch (\Exception $e) {
                Log::error('Failed to send notification for price update: ' . $e->getMessage());
            }
        }

        // Send notification for ANY other significant changes
        $significantFields = ['unit_name', 'unit_type', 'number_of_beds', 'status', 'available', 'base_price', 'total_price'];
        $hasSignificantChange = false;
        $changedFields = [];

        foreach ($significantFields as $field) {
            if ($unit->isDirty($field)) {
                $hasSignificantChange = true;
                $changedFields[] = $field;
            }
        }

        if ($hasSignificantChange) {
            try {
                $compoundName = $unit->compound->project ?? $unit->compound->name ?? 'Unknown Compound';
                $unitName = $unit->unit_name ?? $unit->unit_code ?? 'Unit';

                $title = "🔔 Unit Information Updated";
                $body = "Unit '{$unitName}' in {$compoundName} has been updated";

                $data = [
                    'type' => 'unit_updated',
                    'unit_id' => (string)$unit->id,
                    'unit_name' => $unitName,
                    'compound_id' => (string)$unit->compound_id,
                    'compound_name' => $compoundName,
                    'updated_fields' => implode(', ', $changedFields),
                ];

                // Send to all buyers
                $this->fcmService->sendToUsersByRole('buyer', $title, $body, $data);

                Log::info("Notification sent for unit update: {$unitName} (changed: " . implode(', ', $changedFields) . ")");
            } catch (\Exception $e) {
                Log::error('Failed to send notification for unit update: ' . $e->getMessage());
            }
        }

        // Update company statistics if is_sold status changed or compound changed
        if ($unit->isDirty('is_sold') || $unit->isDirty('compound_id')) {
            if ($unit->isDirty('compound_id')) {
                // Update old compound's company
                $oldCompound = \App\Models\Compound::find($unit->getOriginal('compound_id'));
                if ($oldCompound && $oldCompound->company) {
                    $oldCompound->company->updateStatistics();
                }
            }
            // Update current compound's company
            $this->updateCompanyStatistics($unit);
        }
    }

    /**
     * Handle the Unit "deleted" event.
     */
    public function deleted(Unit $unit)
    {
        $this->updateCompanyStatistics($unit);
    }

    /**
     * Update the company statistics
     */
    protected function updateCompanyStatistics(Unit $unit)
    {
        if ($unit->compound && $unit->compound->company) {
            $unit->compound->company->updateStatistics();
        }
    }
}
