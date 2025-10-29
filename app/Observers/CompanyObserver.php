<?php

namespace App\Observers;

use App\Models\Company;
use App\Services\FCMNotificationService;
use Illuminate\Support\Facades\Log;

class CompanyObserver
{
    protected $fcmService;

    public function __construct()
    {
        try {
            $this->fcmService = new FCMNotificationService();
        } catch (\Exception $e) {
            Log::warning('FCM Service not initialized in CompanyObserver: ' . $e->getMessage());
            $this->fcmService = null;
        }
    }

    /**
     * Handle the Company "created" event.
     *
     * @param  \App\Models\Company  $company
     * @return void
     */
    public function created(Company $company)
    {
        if (!$this->fcmService) {
            return;
        }

        try {
            $companyName = $company->name ?? 'New Company';
            $location = $company->location ?? '';
            $totalCompounds = $company->total_compounds ?? 0;

            $title = "New Developer Added!";
            $body = "{$companyName}" . ($location ? " - {$location}" : "");
            if ($totalCompounds > 0) {
                $body .= " with {$totalCompounds} compounds";
            }

            $data = [
                'type' => 'new_company',
                'company_id' => (string)$company->id,
                'company_name' => $companyName,
                'location' => $location,
                'total_compounds' => (string)$totalCompounds,
            ];

            // Send to all buyers and agents
            $this->fcmService->sendToUsersByRole('buyer', $title, $body, $data);
            $this->fcmService->sendToUsersByRole('agent', $title, $body, $data);

            Log::info("Notification sent for new company: {$companyName}");
        } catch (\Exception $e) {
            Log::error('Failed to send notification for new company: ' . $e->getMessage());
        }
    }

    /**
     * Handle the Company "updated" event.
     *
     * @param  \App\Models\Company  $company
     * @return void
     */
    public function updated(Company $company)
    {
        if (!$this->fcmService) {
            return;
        }

        // Notify if company was just activated or featured
        if ($company->isDirty('is_active') && $company->is_active) {
            try {
                $companyName = $company->name ?? 'Company';

                $title = "Developer Now Active!";
                $body = "{$companyName} is now available";

                $data = [
                    'type' => 'company_activated',
                    'company_id' => (string)$company->id,
                    'company_name' => $companyName,
                ];

                $this->fcmService->sendToUsersByRole('buyer', $title, $body, $data);

                Log::info("Notification sent for activated company: {$companyName}");
            } catch (\Exception $e) {
                Log::error('Failed to send notification for activated company: ' . $e->getMessage());
            }
        }
    }
}
