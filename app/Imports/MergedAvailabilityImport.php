<?php

namespace App\Imports;

use App\Models\SalesAvailability;
use App\Models\UnitsAvailability;
use App\Services\FCMNotificationService;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;

class MergedAvailabilityImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected $importedCount = 0;
    protected $salesCount = 0;
    protected $unitsCount = 0;
    protected $fcmService;

    /**
     * Constructor - Initialize FCM Service
     */
    public function __construct()
    {
        try {
            $this->fcmService = new FCMNotificationService();
        } catch (\Exception $e) {
            \Log::warning('FCM Service not initialized in Excel Import: ' . $e->getMessage());
            $this->fcmService = null;
        }
    }

    /**
     * Process entire collection at once
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Auto-detect source if not provided
            $source = $this->detectSource($row);

            if ($source === 'sales') {
                // Insert into sales_availability table - ALL 28 COLUMNS
                SalesAvailability::create([
                    'project' => $row['project'] ?? null,
                    'stage' => $row['stage'] ?? null,
                    'category' => $row['category'] ?? null,
                    'unit_type' => $row['unit_type'] ?? null,
                    'unit_code' => $row['unit_code'] ?? null,
                    'grand_total' => $row['grand_total'] ?? $row['grand_tot'] ?? null,
                    'total_finishing_price' => $row['total_finishing_price'] ?? $row['total_finis'] ?? null,
                    'unit_total_with_finishing_price' => $row['unit_total_with_finishing'] ?? $row['unit_total_with_finishing_price'] ?? null,
                    'planned_delivery_date' => $row['planned_delivery_date'] ?? $row['planned_d'] ?? null,
                    'actual_delivery_date' => $row['actual_delivery_date'] ?? $row['actual_del'] ?? null,
                    'completion_progress' => $row['completion_progress'] ?? $row['completion'] ?? null,
                    'land_area' => $row['land_area'] ?? null,
                    'built_area' => $row['built_area'] ?? null,
                    'basement_area' => $row['basement_area'] ?? null,
                    'uncovered_basement_area' => $row['uncovered_basement_area'] ?? $row['uncovered'] ?? null,
                    'penthouse_area' => $row['penthouse_area'] ?? $row['penthouse'] ?? null,
                    'semi_covered_roof_area' => $row['semi_covered_roof_area'] ?? $row['semi_cov'] ?? null,
                    'roof_area' => $row['roof_area'] ?? null,
                    'garden_outdoor_area' => $row['garden_area'] ?? null,
                    'garage_area' => $row['garage_area'] ?? $row['garage_ar'] ?? null,
                    'pergola_area' => $row['pergola_area'] ?? $row['pergola_ar'] ?? null,
                    'storage_area' => $row['storage_area'] ?? $row['storage_ar'] ?? null,
                    'extra_builtup_area' => $row['bua'] ?? null,
                    'finishing_specs' => $row['finishing_specs'] ?? $row['finishing'] ?? null,
                    'club' => $row['club'] ?? null,
                ]);

                $this->salesCount++;
                $this->importedCount++;

            } elseif ($source === 'units') {
                // Insert into units_availability table - ALL 12 COLUMNS
                UnitsAvailability::create([
                    'unit_name' => $row['unit_name'] ?? $row['unit_namu'] ?? null,
                    'project' => $row['project'] ?? null,
                    'usage_type' => $row['usage_type'] ?? $row['usage_typ'] ?? null,
                    'bua' => $row['bua'] ?? null,
                    'garden_area' => $row['garden_area'] ?? $row['garden_ar'] ?? null,
                    'roof_area' => $row['roof_area'] ?? $row['roof_ar'] ?? null,
                    'floor' => $row['floor'] ?? null,
                    'no__of_bedrooms' => $row['bedrooms'] ?? null,
                    'nominal_price' => $row['nominal_price'] ?? $row['nominal_p'] ?? null,
                ]);

                $this->unitsCount++;
                $this->importedCount++;
            }
        }

        // Send FCM notifications after import completes
        $this->sendNotifications();
    }

    /**
     * Auto-detect source based on which columns have data
     */
    private function detectSource($row): string
    {
        // If source column exists and is explicitly set, use it
        $explicitSource = strtolower($row['source'] ?? '');
        if (in_array($explicitSource, ['sales', 'units'])) {
            return $explicitSource;
        }

        // Sales-specific fields (if any of these exist, it's likely Sales)
        $salesIndicators = [
            'grand_total', 'grand_tot', 'unit_code', 'stage', 'category',
            'total_finishing_price', 'total_finis', 'land_area',
            'finishing_specs', 'finishing', 'club'
        ];

        // Units-specific fields (if any of these exist, it's likely Units)
        $unitsIndicators = [
            'unit_name', 'unit_namu', 'usage_type', 'usage_typ',
            'floor', 'bedrooms', 'nominal_price', 'nominal_p'
        ];

        $salesScore = 0;
        $unitsScore = 0;

        // Count how many sales-specific fields have data
        foreach ($salesIndicators as $field) {
            if (!empty($row[$field])) {
                $salesScore++;
            }
        }

        // Count how many units-specific fields have data
        foreach ($unitsIndicators as $field) {
            if (!empty($row[$field])) {
                $unitsScore++;
            }
        }

        // Return the type with more indicators
        return $unitsScore > $salesScore ? 'units' : 'sales';
    }

    /**
     * Validation rules for each row
     */
    public function rules(): array
    {
        return [
            'project' => 'required|string',
            // Source is no longer required - it will be auto-detected
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'project.required' => 'Project name is required',
        ];
    }

    /**
     * Get import statistics
     */
    public function getStats()
    {
        return [
            'total' => $this->importedCount,
            'sales' => $this->salesCount,
            'units' => $this->unitsCount,
        ];
    }

    /**
     * Send FCM notifications to buyers after import
     */
    protected function sendNotifications()
    {
        // Skip if FCM service is not available
        if (!$this->fcmService) {
            \Log::info("Excel Import: FCM service not available, skipping notifications");
            return;
        }

        try {
            // Send notification for new units
            if ($this->unitsCount > 0) {
                $this->fcmService->sendToUsersByRole(
                    'buyer',
                    'New Units Available!',
                    "{$this->unitsCount} new units have been added via Excel import. Check them out now!",
                    [
                        'type' => 'bulk_units_import',
                        'count' => (string)$this->unitsCount,
                        'timestamp' => now()->toISOString(),
                    ]
                );
            }

            // Send notification for new sales
            if ($this->salesCount > 0) {
                $this->fcmService->sendToUsersByRole(
                    'buyer',
                    'New Sales Available!',
                    "{$this->salesCount} new sales/promotions have been added via Excel import. Don't miss out!",
                    [
                        'type' => 'bulk_sales_import',
                        'count' => (string)$this->salesCount,
                        'timestamp' => now()->toISOString(),
                    ]
                );
            }

            // Send combined notification if both were imported
            if ($this->unitsCount > 0 && $this->salesCount > 0) {
                $this->fcmService->sendToUsersByRole(
                    'buyer',
                    'New Properties & Sales!',
                    "{$this->unitsCount} units and {$this->salesCount} sales have been imported. Browse the latest offerings now!",
                    [
                        'type' => 'bulk_import',
                        'units_count' => (string)$this->unitsCount,
                        'sales_count' => (string)$this->salesCount,
                        'total_count' => (string)$this->importedCount,
                        'timestamp' => now()->toISOString(),
                    ]
                );
            }

            \Log::info("Excel Import: Sent FCM notifications - {$this->unitsCount} units, {$this->salesCount} sales");

        } catch (\Exception $e) {
            // Log error but don't fail the import
            \Log::error("Excel Import: Failed to send FCM notifications - " . $e->getMessage());
        }
    }
}
