<?php

namespace App\Observers;

use App\Models\Sale;
use App\Services\FCMNotificationService;
use Illuminate\Support\Facades\Log;

class SaleObserver
{
    protected $fcmService;

    public function __construct()
    {
        try {
            $this->fcmService = new FCMNotificationService();
        } catch (\Exception $e) {
            Log::warning('FCM Service not initialized in SaleObserver: ' . $e->getMessage());
            $this->fcmService = null;
        }
    }

    /**
     * Handle the Sale "created" event.
     *
     * @param  \App\Models\Sale  $sale
     * @return void
     */
    public function created(Sale $sale)
    {
        if (!$this->fcmService) {
            return;
        }

        try {
            $saleName = $sale->sale_name ?? 'New Sale';
            $discountPercentage = $sale->discount_percentage ?? 0;
            $companyName = $sale->company->name ?? 'Unknown Company';

            // Determine item name
            $itemName = '';
            if ($sale->sale_type === 'unit' && $sale->unit) {
                $itemName = $sale->unit->unit_name ?? $sale->unit->unit_code ?? '';
            } elseif ($sale->sale_type === 'compound' && $sale->compound) {
                $itemName = $sale->compound->project ?? $sale->compound->name ?? '';
            }

            $title = "New Sale Alert!";
            $body = $saleName;
            if ($discountPercentage > 0) {
                $body .= " - Save up to {$discountPercentage}%";
            }
            if ($itemName) {
                $body .= " on {$itemName}";
            }

            $data = [
                'type' => 'new_sale',
                'sale_id' => (string)$sale->id,
                'sale_name' => $saleName,
                'sale_type' => $sale->sale_type,
                'discount_percentage' => (string)$discountPercentage,
                'old_price' => (string)($sale->old_price ?? '0'),
                'new_price' => (string)($sale->new_price ?? '0'),
                'company_id' => (string)$sale->company_id,
                'company_name' => $companyName,
                'start_date' => $sale->start_date ? $sale->start_date->format('Y-m-d') : '',
                'end_date' => $sale->end_date ? $sale->end_date->format('Y-m-d') : '',
            ];

            if ($sale->unit_id) {
                $data['unit_id'] = (string)$sale->unit_id;
                $data['item_name'] = $itemName;
            }

            if ($sale->compound_id) {
                $data['compound_id'] = (string)$sale->compound_id;
                $data['item_name'] = $itemName;
            }

            // Send to all buyers
            $this->fcmService->sendToUsersByRole('buyer', $title, $body, $data);

            Log::info("Notification sent for new sale: {$saleName}");
        } catch (\Exception $e) {
            Log::error('Failed to send notification for new sale: ' . $e->getMessage());
        }
    }

    /**
     * Handle the Sale "updated" event.
     *
     * @param  \App\Models\Sale  $sale
     * @return void
     */
    public function updated(Sale $sale)
    {
        if (!$this->fcmService) {
            return;
        }

        // If sale was just activated
        if ($sale->isDirty('is_active') && $sale->is_active) {
            try {
                $saleName = $sale->sale_name ?? 'Sale';
                $discountPercentage = $sale->discount_percentage ?? 0;

                $title = "Sale Now Active!";
                $body = "{$saleName} is now live!" . ($discountPercentage > 0 ? " Save up to {$discountPercentage}%" : "");

                $data = [
                    'type' => 'sale_activated',
                    'sale_id' => (string)$sale->id,
                    'sale_name' => $saleName,
                    'discount_percentage' => (string)$discountPercentage,
                    'new_price' => (string)($sale->new_price ?? '0'),
                ];

                $this->fcmService->sendToUsersByRole('buyer', $title, $body, $data);

                Log::info("Notification sent for activated sale: {$saleName}");
            } catch (\Exception $e) {
                Log::error('Failed to send notification for activated sale: ' . $e->getMessage());
            }
        }

        // If discount was increased
        if ($sale->isDirty('discount_percentage') &&
            $sale->discount_percentage > $sale->getOriginal('discount_percentage')) {
            try {
                $saleName = $sale->sale_name ?? 'Sale';
                $oldDiscount = $sale->getOriginal('discount_percentage');
                $newDiscount = $sale->discount_percentage;

                $title = "Discount Increased!";
                $body = "{$saleName} - Discount increased from {$oldDiscount}% to {$newDiscount}%";

                $data = [
                    'type' => 'discount_increased',
                    'sale_id' => (string)$sale->id,
                    'sale_name' => $saleName,
                    'old_discount' => (string)$oldDiscount,
                    'new_discount' => (string)$newDiscount,
                ];

                $this->fcmService->sendToUsersByRole('buyer', $title, $body, $data);

                Log::info("Notification sent for discount increase: {$saleName}");
            } catch (\Exception $e) {
                Log::error('Failed to send notification for discount increase: ' . $e->getMessage());
            }
        }
    }
}
