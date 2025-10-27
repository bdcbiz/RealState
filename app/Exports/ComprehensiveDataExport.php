<?php

namespace App\Exports;

use App\Models\Unit;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ComprehensiveDataExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading, WithCustomCsvSettings, ShouldAutoSize
{
    public function chunkSize(): int
    {
        return 500;
    }

    public function getCsvSettings(): array
    {
        return [
            'use_bom' => true,
            'output_encoding' => 'UTF-8',
        ];
    }

    public function query()
    {
        return Unit::query()
            ->with(['compound', 'company', 'finishSpec'])
            ->orderBy('compound_id')
            ->orderBy('id');
    }

    public function headings(): array
    {
        return [
            // Unit Basic Info
            'Unit ID',
            'Unit Code',
            'Unit Name',
            
            // Company Info
            'Company ID',
            'Company Name',
            'Company Name (Arabic)',
            'Company Email',
            'Company Phone',
            'Company Website',
            'Company Address',
            
            // Compound/Project Info
            'Compound ID',
            'Project Name',
            'Project Name (Arabic)',
            'Compound Location',
            'Compound City',
            'Compound Area',
            'Compound Description',
            'Compound Description (Arabic)',
            'Compound Latitude',
            'Compound Longitude',
            'Master Plan Image',
            'Compound Images',
            
            // Unit Type & Details
            'Unit Type',
            'Usage Type',
            'Category',
            'Floor',
            'View',
            'Number of Bedrooms',
            'Number of Bathrooms',
            'Number of Living Rooms',
            
            // Unit Areas
            'Built-Up Area (m²)',
            'Land Area (m²)',
            'Garden Area (m²)',
            'Roof Area (m²)',
            'Terrace Area (m²)',
            'Basement Area (m²)',
            'Garage Area (m²)',
            'Total Area (m²)',
            
            // Unit Pricing
            'Normal Price (EGP)',
            'Cash Price (EGP)',
            'Price Per Meter (EGP)',
            'Down Payment',
            'Monthly Installment',
            'Over Years',
            
            // Unit Finishing
            'Finishing Type',
            'Finishing Specs',
            'Finishing Price',
            
            // Unit Status & Dates
            'Status',
            'Availability',
            'Is Featured',
            'Is Available',
            'Delivery Date',
            'Delivered At',
            'Planned Delivery Date',
            'Actual Delivery Date',
            'Completion Progress (%)',
            
            // Unit Details
            'Model',
            'Phase',
            'Building Number',
            'Unit Number',
            'Description',
            'Description (Arabic)',
            'Features',
            'Amenities',
            'Unit Images',
            'Floor Plan Image',
            
            // Sales Info
            'Sales ID',
            'Buyer ID',
            'Discount',
            'Total Price After Discount',
            
            // Timestamps
            'Created At',
            'Updated At',
        ];
    }

    public function map($unit): array
    {
        $compound = $unit->compound;
        $company = $unit->company;
        $finishSpec = $unit->finishSpec;

        return [
            // Unit Basic Info
            $unit->id,
            $unit->unit_code,
            $unit->unit_name,
            
            // Company Info
            $company?->id,
            $company?->name,
            $company?->name_ar,
            $company?->email,
            $company?->phone,
            $company?->website,
            $company?->address,
            
            // Compound/Project Info
            $compound?->id,
            $compound?->project,
            $compound?->name_ar,
            $compound?->location,
            $compound?->city,
            $compound?->area,
            $compound?->description,
            $compound?->description_ar,
            $compound?->latitude,
            $compound?->longitude,
            $compound?->master_plan_image,
            is_array($compound?->images) ? implode(', ', $compound->images) : $compound?->images,
            
            // Unit Type & Details
            $unit->unit_type,
            $unit->usage_type,
            $unit->category,
            $unit->floor,
            $unit->view,
            $unit->bedrooms,
            $unit->bathrooms,
            $unit->living_rooms,
            
            // Unit Areas
            $unit->built_up_area,
            $unit->land_area,
            $unit->garden_area,
            $unit->roof_area,
            $unit->terrace_area,
            $unit->basement_area,
            $unit->garage_area,
            $unit->total_area,
            
            // Unit Pricing
            $unit->normal_price,
            $unit->cash_price,
            $unit->price_per_meter,
            $unit->down_payment,
            $unit->monthly_installment,
            $unit->over_years,
            
            // Unit Finishing
            $unit->finishing_type,
            $finishSpec?->name,
            $unit->finishing_price,
            
            // Unit Status & Dates
            $unit->status,
            $unit->availability,
            $unit->is_featured ? 'Yes' : 'No',
            $unit->is_available ? 'Yes' : 'No',
            $unit->delivery_date,
            $unit->delivered_at,
            $unit->planned_delivery_date,
            $unit->actual_delivery_date,
            $unit->completion_progress,
            
            // Unit Details
            $unit->model,
            $unit->phase,
            $unit->building_number,
            $unit->unit_number,
            $unit->description,
            $unit->description_ar,
            is_array($unit->features) ? implode(', ', $unit->features) : $unit->features,
            is_array($unit->amenities) ? implode(', ', $unit->amenities) : $unit->amenities,
            is_array($unit->images) ? implode(', ', $unit->images) : $unit->images,
            $unit->floor_plan_image,
            
            // Sales Info
            $unit->sales_id,
            $unit->buyer_id,
            $unit->discount,
            $unit->total_price_after_discount,
            
            // Timestamps
            $unit->created_at,
            $unit->updated_at,
        ];
    }
}
