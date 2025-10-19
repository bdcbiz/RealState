<?php

namespace App\Exports;

use App\Models\UnitsAvailability;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UnitsAvailabilityExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return UnitsAvailability::all();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Unit Name',
            'Project',
            'Usage Type',
            'BUA',
            'Garden Area',
            'Roof Area',
            'Floor',
            'No. of Bedrooms',
            'Nominal Price',
            'Created At',
            'Updated At',
        ];
    }

    public function map($unitsAvailability): array
    {
        return [
            $unitsAvailability->id,
            $unitsAvailability->unit_name,
            $unitsAvailability->project,
            $unitsAvailability->usage_type,
            $unitsAvailability->bua,
            $unitsAvailability->garden_area,
            $unitsAvailability->roof_area,
            $unitsAvailability->floor,
            $unitsAvailability->no__of_bedrooms,
            $unitsAvailability->nominal_price,
            $unitsAvailability->created_at,
            $unitsAvailability->updated_at,
        ];
    }
}
