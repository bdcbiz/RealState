<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('sales_person_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('sale_type', ['unit', 'compound'])->index();
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('compound_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('sale_name');
            $table->text('description')->nullable();
            $table->decimal('discount_percentage', 5, 2);
            $table->decimal('old_price', 15, 2);
            $table->decimal('new_price', 15, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // Sales Availability table (legacy, consider merging with sales in the future)
        Schema::create('sales_availability', function (Blueprint $table) {
            $table->id();
            $table->string('project')->nullable()->index();
            $table->string('location')->nullable();
            $table->string('location_url')->nullable();
            $table->string('images')->nullable();
            $table->decimal('land_area', 10, 2)->nullable();
            $table->decimal('built_up_area', 10, 2)->nullable();
            $table->integer('how_many_floors')->nullable();
            $table->date('delivery_date')->nullable();
            $table->decimal('completion_progress', 5, 2)->nullable();
            $table->timestamps();
        });

        // Units Availability table (legacy, consider removing in favor of units.available field)
        Schema::create('units_availability', function (Blueprint $table) {
            $table->id();
            $table->string('unit_name')->nullable();
            $table->string('building_name')->nullable();
            $table->integer('floor_number')->nullable();
            $table->string('unit_type')->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units_availability');
        Schema::dropIfExists('sales_availability');
        Schema::dropIfExists('sales');
    }
};
