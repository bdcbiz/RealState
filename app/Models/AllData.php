<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllData extends Model
{
    use HasFactory;

    protected $table = 'all_data';

    protected $fillable = [
        // Unit Information
        'unit_code', 'unit_name', 'unit_name_ar', 'unit_name_en', 'building_name',
        'code', 'compound_name', 'unit_type', 'unit_type_ar', 'unit_type_en',
        'usage_type', 'usage_type_ar', 'usage_type_en', 'category',
        'floor', 'view', 'bedrooms', 'bathrooms', 'living_rooms', 'available',

        // Areas
        'built_up_area', 'extra_built_up_area', 'land_area', 'garden_area', 'roof_area',
        'semi_covered_roof_area', 'terrace_area', 'basement_area', 'uncovered_basement',
        'penthouse', 'garage_area', 'pergola_area', 'storage_area', 'total_area',

        // Pricing
        'normal_price', 'total_pricing', 'cash_price', 'price_per_meter', 'down_payment',
        'monthly_installment', 'over_years',

        // Finishing
        'finishing_type', 'finishing_specs', 'finishing_price', 'total_finish_pricing',
        'unit_total_with_finish_price',

        // Status & Dates
        'status', 'status_ar', 'status_en', 'availability', 'is_featured', 'is_available', 'is_sold',
        'delivery_date', 'delivered_at', 'planned_delivery_date',
        'actual_delivery_date', 'completion_progress',

        // Unit Details
        'model', 'phase', 'stage_number', 'building_number', 'unit_number',
        'description', 'description_ar', 'features', 'amenities',
        'unit_images', 'unit_images_json', 'floor_plan_image', 'unit_deleted_at',

        // Compound/Project Information
        'project_name', 'project_name_ar', 'project_en', 'compound_location',
        'location_ar', 'location_en', 'location_url',
        'compound_city', 'compound_area', 'compound_built_up_area', 'compound_built_area',
        'compound_land_area', 'how_many_floors', 'compound_description',
        'compound_description_ar', 'compound_latitude', 'compound_longitude',
        'master_plan_image', 'compound_images', 'compound_finish_specs',
        'club', 'total_units',
        'current_sale_id', 'sales_person_id',
        'compound_planned_delivery_date', 'compound_actual_delivery_date',
        'compound_completion_progress', 'compound_delivered_at',
        'compound_status', 'compound_is_sold', 'compound_deleted_at',

        // Company Information
        'company_name', 'company_name_ar', 'company_name_en', 'company_logo',
        'company_email', 'company_phone', 'company_website', 'company_address',
        'company_developer_areas', 'company_sales_ids', 'company_sales_person_ids',
        'company_number_of_compounds', 'company_number_of_available_units',

        // Sales Info
        'sales_id', 'buyer_id', 'discount', 'total_price_after_discount',

        // Additional Fields
        'agent_name',
    ];

    protected $casts = [
        // JSON fields
        'features' => 'array',
        'amenities' => 'array',
        'unit_images' => 'array',
        'unit_images_json' => 'array',
        'compound_images' => 'array',
        'company_developer_areas' => 'array',
        'company_sales_ids' => 'array',
        'company_sales_person_ids' => 'array',

        // Boolean fields
        'is_featured' => 'boolean',
        'is_available' => 'boolean',
        'is_sold' => 'boolean',
        'available' => 'boolean',
        'club' => 'boolean',
        'compound_is_sold' => 'boolean',

        // Date/DateTime fields
        'delivered_at' => 'datetime',
        'unit_deleted_at' => 'datetime',
        'compound_deleted_at' => 'datetime',
        'delivery_date' => 'date',
        'planned_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'compound_planned_delivery_date' => 'date',
        'compound_actual_delivery_date' => 'date',
        'compound_delivered_at' => 'date',
    ];
}
