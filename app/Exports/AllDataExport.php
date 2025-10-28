<?php

namespace App\Exports;

use App\Models\AllData;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AllDataExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading, WithCustomCsvSettings, ShouldAutoSize
{
    public function chunkSize(): int
    {
        return 1000;
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
        return AllData::query()->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Unit Code',
            'Unit Name',
            'Unit Type',
            'Usage Type',
            'Category',
            'Floor',
            'View',
            'Bedrooms',
            'Bathrooms',
            'Living Rooms',

            // Areas
            'Built-Up Area',
            'Land Area',
            'Garden Area',
            'Roof Area',
            'Terrace Area',
            'Basement Area',
            'Garage Area',
            'Total Area',

            // Pricing
            'Normal Price',
            'Cash Price',
            'Price Per Meter',
            'Down Payment',
            'Monthly Installment',
            'Over Years',

            // Finishing
            'Finishing Type',
            'Finishing Specs',
            'Finishing Price',

            // Status & Dates
            'Status',
            'Availability',
            'Is Featured',
            'Is Available',
            'Delivery Date',
            'Delivered At',
            'Planned Delivery Date',
            'Actual Delivery Date',
            'Completion Progress',

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

            // Project Info
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

            // Company Info
            'Company Name',
            'Company Name (Arabic)',
            'Company Email',
            'Company Phone',
            'Company Website',
            'Company Address',

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

    public function map($row): array
    {
        return [
            $row->id,
            $row->unit_code,
            $row->unit_name,
            $row->unit_type,
            $row->usage_type,
            $row->category,
            $row->floor,
            $row->view,
            $row->bedrooms,
            $row->bathrooms,
            $row->living_rooms,

            // Areas
            $row->built_up_area,
            $row->land_area,
            $row->garden_area,
            $row->roof_area,
            $row->terrace_area,
            $row->basement_area,
            $row->garage_area,
            $row->total_area,

            // Pricing
            $row->normal_price,
            $row->cash_price,
            $row->price_per_meter,
            $row->down_payment,
            $row->monthly_installment,
            $row->over_years,

            // Finishing
            $row->finishing_type,
            $row->finishing_specs,
            $row->finishing_price,

            // Status & Dates
            $row->status,
            $row->availability,
            $row->is_featured ? 'Yes' : 'No',
            $row->is_available ? 'Yes' : 'No',
            $row->delivery_date,
            $row->delivered_at,
            $row->planned_delivery_date,
            $row->actual_delivery_date,
            $row->completion_progress,

            // Unit Details
            $row->model,
            $row->phase,
            $row->building_number,
            $row->unit_number,
            $row->description,
            $row->description_ar,
            is_array($row->features) ? json_encode($row->features, JSON_UNESCAPED_UNICODE) : $row->features,
            is_array($row->amenities) ? json_encode($row->amenities, JSON_UNESCAPED_UNICODE) : $row->amenities,
            is_array($row->unit_images) ? json_encode($row->unit_images, JSON_UNESCAPED_UNICODE) : $row->unit_images,
            $row->floor_plan_image,

            // Project Info
            $row->project_name,
            $row->project_name_ar,
            $row->compound_location,
            $row->compound_city,
            $row->compound_area,
            $row->compound_description,
            $row->compound_description_ar,
            $row->compound_latitude,
            $row->compound_longitude,
            $row->master_plan_image,
            is_array($row->compound_images) ? json_encode($row->compound_images, JSON_UNESCAPED_UNICODE) : $row->compound_images,

            // Company Info
            $row->company_name,
            $row->company_name_ar,
            $row->company_email,
            $row->company_phone,
            $row->company_website,
            $row->company_address,

            // Sales Info
            $row->sales_id,
            $row->buyer_id,
            $row->discount,
            $row->total_price_after_discount,

            // Timestamps
            $row->created_at,
            $row->updated_at,
        ];
    }
}
