<?php

namespace App\Console\Commands;

use App\Models\AllData;
use App\Models\Unit;
use Illuminate\Console\Command;

class PopulateAllData extends Command
{
    protected $signature = 'populate:all-data {--fresh}';
    protected $description = 'Populate all_data table with data from units, compounds, and companies';

    public function handle()
    {
        if ($this->option('fresh')) {
            $this->info('Truncating all_data table...');
            AllData::truncate();
        }

        $this->info('Starting to populate all_data table...');
        
        $units = Unit::with(['compound.company', 'compound.company.sales'])->get();
        $bar = $this->output->createProgressBar($units->count());
        $bar->start();

        $inserted = 0;

        foreach ($units as $unit) {
            try {
                $compound = $unit->compound;
                $company = $compound?->company;

                // Get sale ID and salesperson for this unit from sales table
                $sale = \DB::table('sales')->where('unit_id', $unit->id)->first();
                $unitSaleId = $sale?->id;
                $salesPersonId = $sale?->sales_person_id ?? $compound?->sales_person_id;

                // Get all sale IDs for this company
                $companySaleIds = $company ? \DB::table('sales')
                    ->where('company_id', $company->id)
                    ->pluck('id')
                    ->toArray() : [];

                // Get all salespeople (users with role='sales') for this company
                $companySalesPeople = $company ? \DB::table('users')
                    ->where('company_id', $company->id)
                    ->where('role', 'sales')
                    ->pluck('id')
                    ->toArray() : [];

                AllData::create([
                    // Unit Information (from units table)
                    'unit_code' => $unit->unit_code,
                    'unit_name' => $unit->unit_name,
                    'unit_name_ar' => $unit->unit_name_ar,
                    'unit_name_en' => $unit->unit_name_en,
                    'building_name' => $unit->building_name,
                    'code' => $unit->code,
                    'compound_name' => $unit->compound_name,
                    'unit_type' => $unit->unit_type,
                    'unit_type_ar' => $unit->unit_type_ar,
                    'unit_type_en' => $unit->unit_type_en,
                    'usage_type' => $unit->usage_type,
                    'usage_type_ar' => $unit->usage_type_ar,
                    'usage_type_en' => $unit->usage_type_en,
                    'category' => $unit->category,
                    'floor' => $unit->floor_number,
                    'view' => $unit->view,
                    'bedrooms' => $unit->number_of_beds,
                    'bathrooms' => $unit->bathrooms,
                    'living_rooms' => $unit->living_rooms,
                    'available' => $unit->available,

                    // Areas (from units table)
                    'built_up_area' => $unit->built_up_area,
                    'extra_built_up_area' => $unit->extra_built_up_area,
                    'land_area' => $unit->land_area,
                    'garden_area' => $unit->garden_area,
                    'roof_area' => $unit->roof_area,
                    'semi_covered_roof_area' => $unit->semi_covered_roof_area,
                    'terrace_area' => $unit->terrace_area,
                    'basement_area' => $unit->basement_area,
                    'uncovered_basement' => $unit->uncovered_basement,
                    'penthouse' => $unit->penthouse,
                    'garage_area' => $unit->garage_area,
                    'pergola_area' => $unit->pergola_area,
                    'storage_area' => $unit->storage_area,
                    'total_area' => $unit->total_area,

                    // Pricing (from units table)
                    'normal_price' => $unit->normal_price,
                    'total_pricing' => $unit->total_pricing,
                    'cash_price' => $unit->cash_price,
                    'price_per_meter' => $unit->price_per_meter,
                    'down_payment' => $unit->down_payment,
                    'monthly_installment' => $unit->monthly_installment,
                    'over_years' => $unit->over_years,

                    // Finishing (from units table)
                    'finishing_type' => $unit->finishing_type,
                    'finishing_specs' => $unit->finishing_specs,
                    'finishing_price' => $unit->finishing_price,
                    'total_finish_pricing' => $unit->total_finish_pricing,
                    'unit_total_with_finish_price' => $unit->unit_total_with_finish_price,

                    // Status & Dates (from units table)
                    'status' => $unit->status,
                    'status_ar' => $unit->status_ar,
                    'status_en' => $unit->status_en,
                    'availability' => $unit->availability,
                    'is_featured' => $unit->is_featured ?? false,
                    'is_available' => $unit->is_available ?? true,
                    'is_sold' => $unit->is_sold,
                    'delivery_date' => $unit->delivery_date,
                    'delivered_at' => $unit->delivered_at,
                    'planned_delivery_date' => $unit->planned_delivery_date,
                    'actual_delivery_date' => $unit->actual_delivery_date,
                    'completion_progress' => $unit->completion_progress,

                    // Unit Details (from units table)
                    'model' => $unit->model,
                    'phase' => $unit->phase,
                    'stage_number' => $unit->stage_number,
                    'building_number' => $unit->building_number,
                    'unit_number' => $unit->unit_number,
                    'description' => $unit->description,
                    'description_ar' => $unit->description_ar,
                    'features' => $unit->features,
                    'amenities' => $unit->amenities,
                    'unit_images' => $unit->images,
                    'unit_images_json' => $unit->images,
                    'floor_plan_image' => $unit->floor_plan_image,
                    'unit_deleted_at' => $unit->deleted_at,

                    // Compound/Project Information (from compounds table)
                    'project_name' => $compound?->project,
                    'project_name_ar' => $compound?->project_ar,
                    'project_en' => $compound?->project_en,
                    'compound_location' => $compound?->location,
                    'location_ar' => $compound?->location_ar,
                    'location_en' => $compound?->location_en,
                    'location_url' => $compound?->location_url,
                    'compound_city' => $compound?->location, // Using location as city
                    'compound_area' => $compound?->location,
                    'compound_built_up_area' => $compound?->built_up_area,
                    'compound_built_area' => $compound?->built_area,
                    'compound_land_area' => $compound?->land_area,
                    'how_many_floors' => $compound?->how_many_floors,
                    'compound_description' => $compound?->description,
                    'compound_description_ar' => $compound?->description_ar,
                    'compound_latitude' => $compound?->latitude,
                    'compound_longitude' => $compound?->longitude,
                    'master_plan_image' => $compound?->master_plan,
                    'compound_images' => $compound?->images,
                    'compound_finish_specs' => $compound?->finish_specs,
                    'club' => $compound?->club ?? false,
                    'total_units' => $compound?->total_units,
                    'current_sale_id' => $compound?->current_sale_id,
                    'sales_person_id' => $salesPersonId,
                    'compound_planned_delivery_date' => $compound?->planned_delivery_date,
                    'compound_actual_delivery_date' => $compound?->actual_delivery_date,
                    'compound_completion_progress' => $compound?->completion_progress,
                    'compound_delivered_at' => $compound?->delivered_at,
                    'compound_status' => $compound?->status,
                    'compound_is_sold' => $compound?->is_sold,
                    'compound_deleted_at' => $compound?->deleted_at,

                    // Company Information (from companies table)
                    'company_name' => $company?->name,
                    'company_name_ar' => $company?->name_ar,
                    'company_name_en' => $company?->name_en,
                    'company_logo' => $company?->logo,
                    'company_email' => $company?->email,
                    'company_phone' => $company?->phone,
                    'company_website' => $company?->website,
                    'company_address' => $company?->headquarters,
                    'company_developer_areas' => $company?->developer_areas,
                    'company_sales_ids' => !empty($companySaleIds) ? $companySaleIds : null,
                    'company_sales_person_ids' => !empty($companySalesPeople) ? $companySalesPeople : null,
                    'company_number_of_compounds' => $company?->number_of_compounds,
                    'company_number_of_available_units' => $company?->number_of_available_units,

                    // Sales Info (from sales table + units table)
                    'sales_id' => $unitSaleId,
                    'buyer_id' => $unit->buyer_id,
                    'discount' => $unit->discount,
                    'total_price_after_discount' => $unit->total_price_after_discount,
                ]);

                $inserted++;
            } catch (\Exception $e) {
                $this->error("Error inserting unit {$unit->id}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("✓ Successfully inserted {$inserted} records into all_data table");
    }
}
