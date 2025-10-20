<?php

namespace App\Exports;

use App\Models\SalesAvailability;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesAvailabilityExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return SalesAvailability::all();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Project',
            'Stage',
            'Category',
            'Unit Type',
            'Unit Code',
            'Grand Total',
            'Total Finishing Price',
            'Unit Total with Finishing Price',
            'Planned Delivery Date',
            'Actual Delivery Date',
            'Completion Progress',
            'Land Area',
            'Built Area',
            'Basement Area',
            'Uncovered Basement Area',
            'Penthouse Area',
            'Semi Covered Roof Area',
            'Roof Area',
            'Garden/Outdoor Area',
            'Garage Area',
            'Pergola Area',
            'Storage Area',
            'Extra Built-up Area (BUA)',
            'Finishing Specs',
            'Club',
            'Created At',
            'Updated At',
        ];
    }

    public function map($salesAvailability): array
    {
        return [
            $salesAvailability->id,
            $salesAvailability->project,
            $salesAvailability->stage,
            $salesAvailability->category,
            $salesAvailability->unit_type,
            $salesAvailability->unit_code,
            $salesAvailability->grand_total,
            $salesAvailability->total_finishing_price,
            $salesAvailability->unit_total_with_finishing_price,
            $salesAvailability->planned_delivery_date,
            $salesAvailability->actual_delivery_date,
            $salesAvailability->completion_progress,
            $salesAvailability->land_area,
            $salesAvailability->built_area,
            $salesAvailability->basement_area,
            $salesAvailability->uncovered_basement_area,
            $salesAvailability->penthouse_area,
            $salesAvailability->semi_covered_roof_area,
            $salesAvailability->roof_area,
            $salesAvailability->garden_outdoor_area,
            $salesAvailability->garage_area,
            $salesAvailability->pergola_area,
            $salesAvailability->storage_area,
            $salesAvailability->extra_builtup_area,
            $salesAvailability->finishing_specs,
            $salesAvailability->club,
            $salesAvailability->created_at,
            $salesAvailability->updated_at,
        ];
    }
}
