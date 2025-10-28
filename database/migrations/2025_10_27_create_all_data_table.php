<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('all_data', function (Blueprint $table) {
            $table->id();
            
            // Unit Information
            $table->string('unit_code')->nullable();
            $table->string('unit_name')->nullable();
            $table->string('unit_type')->nullable();
            $table->string('usage_type')->nullable();
            $table->string('category')->nullable();
            $table->integer('floor')->nullable();
            $table->string('view')->nullable();
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->integer('living_rooms')->nullable();
            
            // Areas
            $table->decimal('built_up_area', 10, 2)->nullable();
            $table->decimal('land_area', 10, 2)->nullable();
            $table->decimal('garden_area', 10, 2)->nullable();
            $table->decimal('roof_area', 10, 2)->nullable();
            $table->decimal('terrace_area', 10, 2)->nullable();
            $table->decimal('basement_area', 10, 2)->nullable();
            $table->decimal('garage_area', 10, 2)->nullable();
            $table->decimal('total_area', 10, 2)->nullable();
            
            // Pricing
            $table->decimal('normal_price', 15, 2)->nullable();
            $table->decimal('cash_price', 15, 2)->nullable();
            $table->decimal('price_per_meter', 15, 2)->nullable();
            $table->decimal('down_payment', 15, 2)->nullable();
            $table->decimal('monthly_installment', 15, 2)->nullable();
            $table->integer('over_years')->nullable();
            
            // Finishing
            $table->string('finishing_type')->nullable();
            $table->string('finishing_specs')->nullable();
            $table->decimal('finishing_price', 15, 2)->nullable();
            
            // Status & Dates
            $table->string('status')->nullable();
            $table->string('availability')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_available')->default(true);
            $table->date('delivery_date')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->date('planned_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();
            $table->decimal('completion_progress', 5, 2)->nullable();
            
            // Unit Details
            $table->string('model')->nullable();
            $table->string('phase')->nullable();
            $table->string('building_number')->nullable();
            $table->string('unit_number')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->json('features')->nullable();
            $table->json('amenities')->nullable();
            $table->json('unit_images')->nullable();
            $table->string('floor_plan_image')->nullable();
            
            // Compound/Project Information
            $table->string('project_name')->nullable();
            $table->string('project_name_ar')->nullable();
            $table->string('compound_location')->nullable();
            $table->string('compound_city')->nullable();
            $table->string('compound_area')->nullable();
            $table->text('compound_description')->nullable();
            $table->text('compound_description_ar')->nullable();
            $table->decimal('compound_latitude', 10, 8)->nullable();
            $table->decimal('compound_longitude', 11, 8)->nullable();
            $table->string('master_plan_image')->nullable();
            $table->json('compound_images')->nullable();
            
            // Company Information
            $table->string('company_name')->nullable();
            $table->string('company_name_ar')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_website')->nullable();
            $table->text('company_address')->nullable();
            
            // Sales Info
            $table->unsignedBigInteger('sales_id')->nullable();
            $table->unsignedBigInteger('buyer_id')->nullable();
            $table->decimal('discount', 10, 2)->nullable();
            $table->decimal('total_price_after_discount', 15, 2)->nullable();
            
            $table->timestamps();
            
            // Indexes for faster searching
            $table->index('unit_code');
            $table->index('project_name');
            $table->index('company_name');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('all_data');
    }
};
