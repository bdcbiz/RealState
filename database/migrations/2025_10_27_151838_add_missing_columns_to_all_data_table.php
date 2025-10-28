<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('all_data', function (Blueprint $table) {
            // Missing Unit columns
            $table->boolean('available')->default(true)->after('unit_code');
            $table->string('unit_name_ar')->nullable()->after('unit_name');
            $table->string('unit_name_en')->nullable()->after('unit_name_ar');
            $table->string('building_name')->nullable()->after('unit_name_en');
            $table->string('code')->nullable()->after('unit_code'); // Different from unit_code
            $table->string('compound_name')->nullable()->after('code');
            $table->string('usage_type_ar')->nullable()->after('usage_type');
            $table->string('usage_type_en')->nullable()->after('usage_type_ar');
            $table->string('unit_type_ar')->nullable()->after('unit_type');
            $table->string('unit_type_en')->nullable()->after('unit_type_ar');

            // Missing Area columns from Units
            $table->decimal('uncovered_basement', 10, 2)->nullable()->after('basement_area');
            $table->decimal('penthouse', 10, 2)->nullable()->after('uncovered_basement');
            $table->decimal('semi_covered_roof_area', 10, 2)->nullable()->after('roof_area');
            $table->decimal('pergola_area', 10, 2)->nullable()->after('garage_area');
            $table->decimal('storage_area', 10, 2)->nullable()->after('pergola_area');
            $table->decimal('extra_built_up_area', 10, 2)->nullable()->after('built_up_area');

            // Missing Unit pricing/info columns
            $table->string('stage_number')->nullable()->after('unit_number');
            $table->decimal('total_pricing', 15, 2)->nullable()->after('normal_price');
            $table->decimal('total_finish_pricing', 15, 2)->nullable()->after('finishing_price');
            $table->decimal('unit_total_with_finish_price', 15, 2)->nullable()->after('total_finish_pricing');
            $table->boolean('is_sold')->default(false)->after('is_available');
            $table->string('status_ar')->nullable()->after('status');
            $table->string('status_en')->nullable()->after('status_ar');

            // Unit images (from units.images column)
            $table->json('unit_images_json')->nullable()->after('unit_images');

            // Soft delete tracking
            $table->timestamp('unit_deleted_at')->nullable()->after('updated_at');

            // Missing Compound columns
            $table->unsignedBigInteger('current_sale_id')->nullable()->after('project_name');
            $table->unsignedBigInteger('sales_person_id')->nullable()->after('current_sale_id');
            $table->string('project_en')->nullable()->after('project_name_ar');
            $table->text('location_ar')->nullable()->after('compound_location');
            $table->text('location_en')->nullable()->after('location_ar');
            $table->text('location_url')->nullable()->after('location_en');
            $table->decimal('compound_built_up_area', 10, 2)->nullable()->after('compound_area');
            $table->decimal('compound_built_area', 10, 2)->nullable()->after('compound_built_up_area');
            $table->integer('how_many_floors')->nullable()->after('compound_built_area');
            $table->decimal('compound_land_area', 10, 2)->nullable()->after('how_many_floors');
            $table->text('compound_finish_specs')->nullable()->after('compound_description_ar');
            $table->boolean('club')->default(false)->after('compound_finish_specs');
            $table->integer('total_units')->nullable()->after('club');
            $table->timestamp('compound_deleted_at')->nullable()->after('total_units');

            // Compound dates (separate from unit-level dates)
            $table->date('compound_planned_delivery_date')->nullable()->after('compound_deleted_at');
            $table->date('compound_actual_delivery_date')->nullable()->after('compound_planned_delivery_date');
            $table->decimal('compound_completion_progress', 5, 2)->nullable()->after('compound_actual_delivery_date');
            $table->date('compound_delivered_at')->nullable()->after('compound_completion_progress');
            $table->string('compound_status')->nullable()->after('compound_delivered_at');
            $table->boolean('compound_is_sold')->default(false)->after('compound_status');

            // Missing Company columns
            $table->string('company_name_en')->nullable()->after('company_name_ar');
            $table->string('company_logo')->nullable()->after('company_name_en');
            $table->json('company_developer_areas')->nullable()->after('company_logo');
            $table->json('company_sales_ids')->nullable()->after('company_developer_areas');
            $table->integer('company_number_of_compounds')->nullable()->after('company_sales_ids');
            $table->integer('company_number_of_available_units')->nullable()->after('company_number_of_compounds');

            // Add index for new searchable columns
            $table->index('building_name');
            $table->index('compound_name');
        });
    }

    public function down(): void
    {
        Schema::table('all_data', function (Blueprint $table) {
            $table->dropColumn([
                // Unit columns
                'available', 'unit_name_ar', 'unit_name_en', 'building_name', 'code', 'compound_name',
                'usage_type_ar', 'usage_type_en', 'unit_type_ar', 'unit_type_en',
                'uncovered_basement', 'penthouse', 'semi_covered_roof_area', 'pergola_area', 'storage_area', 'extra_built_up_area',
                'stage_number', 'total_pricing', 'total_finish_pricing', 'unit_total_with_finish_price',
                'is_sold', 'status_ar', 'status_en', 'unit_images_json', 'unit_deleted_at',

                // Compound columns
                'current_sale_id', 'sales_person_id', 'project_en', 'location_ar', 'location_en', 'location_url',
                'compound_built_up_area', 'compound_built_area', 'compound_land_area', 'how_many_floors', 'compound_finish_specs', 'club', 'total_units',
                'compound_deleted_at', 'compound_planned_delivery_date', 'compound_actual_delivery_date',
                'compound_completion_progress', 'compound_delivered_at', 'compound_status', 'compound_is_sold',

                // Company columns
                'company_name_en', 'company_logo', 'company_developer_areas', 'company_sales_ids',
                'company_number_of_compounds', 'company_number_of_available_units',
            ]);
        });
    }
};
