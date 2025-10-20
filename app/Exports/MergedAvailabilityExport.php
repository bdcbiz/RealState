<?php

namespace App\Exports;

use App\Models\SalesAvailability;
use App\Models\UnitsAvailability;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Illuminate\Support\Facades\DB;

class MergedAvailabilityExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading, WithCustomCsvSettings
{
    public function chunkSize(): int
    {
        return 500; // Process 500 records at a time to reduce memory usage
    }

    public function getCsvSettings(): array
    {
        return [
            'use_bom' => true,  // Add UTF-8 BOM for Excel compatibility
            'output_encoding' => 'UTF-8',
        ];
    }
    public function query()
    {
        // Use a subquery with UNION to merge both tables, then order by source and id
        return DB::query()
            ->fromSub(function ($query) {
                $query->from('sales_availability')
                    ->selectRaw('
                        "Sales" as source,
                        id,
                        project,
                        stage,
                        category,
                        unit_type,
                        unit_code,
                        NULL as unit_name,
                        NULL as usage_type,
                        NULL as floor,
                        NULL as bedrooms,
                        grand_total,
                        total_finishing_price,
                        unit_total_with_finishing_price,
                        NULL as nominal_price,
                        planned_delivery_date,
                        actual_delivery_date,
                        completion_progress,
                        land_area,
                        built_area,
                        basement_area,
                        uncovered_basement_area,
                        penthouse_area,
                        semi_covered_roof_area,
                        roof_area,
                        garden_outdoor_area as garden_area,
                        garage_area,
                        pergola_area,
                        storage_area,
                        extra_builtup_area as bua,
                        finishing_specs,
                        club,
                        created_at,
                        updated_at
                    ')
                    ->unionAll(
                        DB::table('units_availability')
                            ->selectRaw('
                                "Units" as source,
                                id,
                                project,
                                NULL as stage,
                                NULL as category,
                                NULL as unit_type,
                                NULL as unit_code,
                                unit_name,
                                usage_type,
                                floor,
                                no__of_bedrooms as bedrooms,
                                NULL as grand_total,
                                NULL as total_finishing_price,
                                NULL as unit_total_with_finishing_price,
                                nominal_price,
                                NULL as planned_delivery_date,
                                NULL as actual_delivery_date,
                                NULL as completion_progress,
                                NULL as land_area,
                                NULL as built_area,
                                NULL as basement_area,
                                NULL as uncovered_basement_area,
                                NULL as penthouse_area,
                                NULL as semi_covered_roof_area,
                                roof_area,
                                garden_area,
                                NULL as garage_area,
                                NULL as pergola_area,
                                NULL as storage_area,
                                bua,
                                NULL as finishing_specs,
                                NULL as club,
                                created_at,
                                updated_at
                            ')
                    );
            }, 'merged_data')
            ->orderBy('source')
            ->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'Source',
            'ID',
            'Project',
            'Stage',
            'Category',
            'Unit Type',
            'Unit Code',
            'Grand Total',
            'Total Finishing Price',
            'Unit Total with Finishing',
            'Planned Delivery Date',
            'Actual Delivery Date',
            'Completion Progress',
            'Land Area',
            'Built Area',
            'Basement Area',
            'Uncovered Basement Area',
            'Penthouse Area',
            'Semi Covered Roof Area',
            'Garage Area',
            'Pergola Area',
            'Storage Area',
            'Finishing Specs',
            'Club',
            'Unit Name',
            'Usage Type',
            'Floor',
            'Bedrooms',
            'Nominal Price',
            'BUA',
            'Garden Area',
            'Roof Area',
            'Created At',
            'Updated At',
        ];
    }

    public function map($availability): array
    {
        return [
            $availability->source,
            $availability->id,
            $availability->project,
            $availability->stage,
            $availability->category,
            $availability->unit_type,
            $availability->unit_code,
            $this->cleanValue($availability->grand_total),
            $this->cleanValue($availability->total_finishing_price),
            $this->cleanValue($availability->unit_total_with_finishing_price),
            $availability->planned_delivery_date,
            $availability->actual_delivery_date,
            $availability->completion_progress,
            $this->cleanValue($availability->land_area),
            $this->cleanValue($availability->built_area),
            $this->cleanValue($availability->basement_area),
            $this->cleanValue($availability->uncovered_basement_area),
            $this->cleanValue($availability->penthouse_area),
            $this->cleanValue($availability->semi_covered_roof_area),
            $this->cleanValue($availability->garage_area),
            $this->cleanValue($availability->pergola_area),
            $this->cleanValue($availability->storage_area),
            $availability->finishing_specs,
            $availability->club,
            $availability->unit_name,
            $availability->usage_type,
            $availability->floor,
            $availability->bedrooms,
            $this->cleanValue($availability->nominal_price),
            $this->cleanValue($availability->bua),
            $this->cleanValue($availability->garden_area),
            $this->cleanValue($availability->roof_area),
            $availability->created_at,
            $availability->updated_at,
        ];
    }

    /**
     * Clean numeric values by removing currency symbols and special characters
     */
    private function cleanValue($value)
    {
        if (empty($value)) {
            return $value;
        }

        // Convert to string and ensure UTF-8
        $value = (string) $value;

        // Remove common currency symbols and Arabic text
        $value = str_replace(['ج.م.‏', 'EGP', 'LE', '​'], '', $value); // Remove Arabic pounds symbol
        $value = preg_replace('/[\x{0600}-\x{06FF}]/u', '', $value); // Remove all Arabic characters
        $value = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $value); // Remove zero-width spaces

        // Keep only numbers, commas, dots, and minus sign
        $cleaned = preg_replace('/[^\d.,\-]/', '', $value);

        // Trim whitespace
        $cleaned = trim($cleaned);

        return $cleaned;
    }
}
