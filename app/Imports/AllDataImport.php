<?php

namespace App\Imports;

use App\Models\AllData;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Carbon\Carbon;

class AllDataImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    protected $importedCount = 0;
    protected $errors = [];

    /**
     * Transform each row into an AllData model
     */
    public function model(array $row)
    {
        try {
            // Skip completely empty rows
            if (empty(array_filter($row))) {
                return null;
            }

            $this->importedCount++;

            return new AllData([
                // Unit Information
                'unit_code' => $row['unit_code'] ?? null,
                'unit_name' => $row['unit_name'] ?? null,
                'unit_type' => $row['unit_type'] ?? null,
                'usage_type' => $row['usage_type'] ?? null,
                'category' => $row['category'] ?? null,
                'floor' => $row['floor'] ?? null,
                'view' => $row['view'] ?? null,
                'bedrooms' => $row['bedrooms'] ?? 0,
                'bathrooms' => $row['bathrooms'] ?? 0,
                'living_rooms' => $row['living_rooms'] ?? null,

                // Areas
                'built_up_area' => $row['built_up_area'] ?? $row['builtup_area'] ?? null,
                'land_area' => $row['land_area'] ?? null,
                'garden_area' => $row['garden_area'] ?? null,
                'roof_area' => $row['roof_area'] ?? null,
                'terrace_area' => $row['terrace_area'] ?? null,
                'basement_area' => $row['basement_area'] ?? null,
                'garage_area' => $row['garage_area'] ?? null,
                'pergola_area' => $row['pergola_area'] ?? null,
                'storage_area' => $row['storage_area'] ?? null,
                'total_area' => $row['total_area'] ?? null,

                // Pricing
                'normal_price' => $row['normal_price'] ?? null,
                'cash_price' => $row['cash_price'] ?? null,
                'price_per_meter' => $row['price_per_meter'] ?? null,
                'down_payment' => $row['down_payment'] ?? null,
                'monthly_installment' => $row['monthly_installment'] ?? null,
                'over_years' => $row['over_years'] ?? null,

                // Finishing
                'finishing_type' => $row['finishing_type'] ?? null,
                'finishing_specs' => $row['finishing_specs'] ?? null,
                'finishing_price' => $row['finishing_price'] ?? null,

                // Status & Availability
                'status' => $row['status'] ?? null,
                'availability' => $row['availability'] ?? null,
                'is_featured' => $this->parseBoolean($row['is_featured'] ?? null),
                'is_available' => $this->parseBoolean($row['is_available'] ?? null),
                'is_sold' => $this->parseBoolean($row['is_sold'] ?? false),

                // Dates
                'delivery_date' => $this->parseDate($row['delivery_date'] ?? null),
                'delivered_at' => $this->parseDate($row['delivered_at'] ?? null),
                'planned_delivery_date' => $this->parseDate($row['planned_delivery_date'] ?? null),
                'actual_delivery_date' => $this->parseDate($row['actual_delivery_date'] ?? null),
                'completion_progress' => $row['completion_progress'] ?? null,

                // Unit Details
                'model' => $row['model'] ?? null,
                'phase' => $row['phase'] ?? null,
                'building_number' => $row['building_number'] ?? null,
                'unit_number' => $row['unit_number'] ?? null,
                'description' => $row['description'] ?? null,
                'description_ar' => $row['description_arabic'] ?? $row['description_ar'] ?? null,
                'features' => $this->parseJson($row['features'] ?? null),
                'amenities' => $this->parseJson($row['amenities'] ?? null),
                'unit_images' => $this->parseJson($row['unit_images'] ?? null),
                'floor_plan_image' => $row['floor_plan_image'] ?? null,

                // Project/Compound Information
                'project_name' => $row['project_name'] ?? null,
                'project_name_ar' => $row['project_name_arabic'] ?? $row['project_name_ar'] ?? null,
                'compound_location' => $row['compound_location'] ?? null,
                'compound_city' => $row['compound_city'] ?? null,
                'compound_area' => $row['compound_area'] ?? null,
                'compound_description' => $row['compound_description'] ?? null,
                'compound_description_ar' => $row['compound_description_arabic'] ?? $row['compound_description_ar'] ?? null,
                'compound_latitude' => $row['compound_latitude'] ?? null,
                'compound_longitude' => $row['compound_longitude'] ?? null,
                'master_plan_image' => $row['master_plan_image'] ?? null,
                'compound_images' => $this->parseJson($row['compound_images'] ?? null),

                // Company Information
                'company_name' => $row['company_name'] ?? null,
                'company_name_ar' => $row['company_name_arabic'] ?? $row['company_name_ar'] ?? null,
                'company_email' => $row['company_email'] ?? null,
                'company_phone' => $row['company_phone'] ?? null,
                'company_website' => $row['company_website'] ?? null,
                'company_address' => $row['company_address'] ?? null,

                // Sales Information
                'sales_id' => $row['sales_id'] ?? null,
                'buyer_id' => $row['buyer_id'] ?? null,
                'discount' => $row['discount'] ?? null,
                'total_price_after_discount' => $row['total_price_after_discount'] ?? null,
            ]);

        } catch (\Exception $e) {
            \Log::error("Import error for row: " . json_encode($row) . " - " . $e->getMessage());
            $this->errors[] = $e->getMessage();
            return null;
        }
    }

    /**
     * Parse boolean values from various formats
     */
    private function parseBoolean($value)
    {
        if (is_bool($value)) {
            return $value;
        }

        $value = strtolower(trim($value ?? ''));
        return in_array($value, ['1', 'true', 'yes', 'y', 'on']);
    }

    /**
     * Parse date from various formats
     */
    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            // Handle Excel date numbers
            if (is_numeric($value)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
            }

            // Try parsing as string
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse JSON from string
     */
    private function parseJson($value)
    {
        if (empty($value)) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        try {
            $decoded = json_decode($value, true);
            return $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Batch size for inserts
     */
    public function batchSize(): int
    {
        return 500;
    }

    /**
     * Chunk size for reading
     */
    public function chunkSize(): int
    {
        return 500;
    }

    /**
     * Get import statistics
     */
    public function getStats()
    {
        return [
            'total' => $this->importedCount,
            'errors' => count($this->errors),
        ];
    }

    /**
     * Get any errors that occurred
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
