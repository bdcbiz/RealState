<?php

namespace App\Observers;

use App\Models\Compound;
use App\Services\FCMNotificationService;
use Illuminate\Support\Facades\Log;

class CompoundObserver
{
    protected $fcmService;

    public function __construct()
    {
        try {
            $this->fcmService = new FCMNotificationService();
        } catch (\Exception $e) {
            Log::warning('FCM Service not initialized in CompoundObserver: ' . $e->getMessage());
            $this->fcmService = null;
        }
    }

    /**
     * Handle the Compound "created" event.
     *
     * @param  \App\Models\Compound  $compound
     * @return void
     */
    public function created(Compound $compound)
    {
        if (!$this->fcmService) {
            return;
        }

        try {
            $compoundName = $compound->project ?? $compound->name ?? 'New Compound';
            $companyName = $compound->company->name ?? 'Unknown Company';
            $location = $compound->location ?? '';

            $title = "New Compound Available!";
            $body = "'{$compoundName}' by {$companyName}" . ($location ? " in {$location}" : "") . " is now available";

            $data = [
                'type' => 'new_compound',
                'compound_id' => (string)$compound->id,
                'compound_name' => $compoundName,
                'company_id' => (string)$compound->company_id,
                'company_name' => $companyName,
                'location' => $location,
            ];

            // Send to all buyers and agents
            $this->fcmService->sendToUsersByRole('buyer', $title, $body, $data);
            $this->fcmService->sendToUsersByRole('agent', $title, $body, $data);

            Log::info("Notification sent for new compound: {$compoundName}");
        } catch (\Exception $e) {
            Log::error('Failed to send notification for new compound: ' . $e->getMessage());
        }

        // Update company statistics
        $this->updateCompanyStatistics($compound);
    }

    /**
     * Handle the Compound "updated" event.
     */
    public function updated(Compound $compound)
    {
        // Update company statistics if company changed
        if ($compound->isDirty('company_id')) {
            // Update old company
            if ($compound->getOriginal('company_id')) {
                $oldCompany = \App\Models\Company::find($compound->getOriginal('company_id'));
                if ($oldCompany) {
                    $oldCompany->updateStatistics();
                }
            }
            // Update new company
            $this->updateCompanyStatistics($compound);
        }
    }

    /**
     * Handle the Compound "deleted" event.
     */
    public function deleted(Compound $compound)
    {
        $this->updateCompanyStatistics($compound);
    }

    /**
     * Update the company statistics
     */
    protected function updateCompanyStatistics(Compound $compound)
    {
        if ($compound->company) {
            $compound->company->updateStatistics();
        }
    }
}
