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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compound_id')->nullable()->constrained()->onDelete('cascade');

            // Unit Information from Units Availability Report
            $table->string('unit_name')->nullable();
            $table->string('compound_name')->nullable();
            $table->string('building_name')->nullable();
            $table->string('unit_number')->nullable();
            $table->string('code')->nullable()->unique();
            $table->string('usage_type')->nullable();
            $table->decimal('garden_area', 10, 2)->nullable();
            $table->decimal('roof_area', 10, 2)->nullable();
            $table->integer('floor_number')->nullable();
            $table->integer('number_of_beds')->nullable();
            $table->decimal('normal_price', 15, 2)->nullable();

            // From Sales Availability Report
            $table->string('stage_number')->nullable();
            $table->string('unit_type')->nullable();
            $table->string('unit_code')->nullable();
            $table->decimal('total_pricing', 15, 2)->nullable();
            $table->decimal('total_finish_pricing', 15, 2)->nullable();
            $table->decimal('unit_total_with_finish_price', 15, 2)->nullable();
            $table->decimal('basement_area', 10, 2)->nullable();
            $table->decimal('uncovered_basement', 10, 2)->nullable();
            $table->decimal('penthouse', 10, 2)->nullable();
            $table->decimal('semi_covered_roof_area', 10, 2)->nullable();
            $table->decimal('garage_area', 10, 2)->nullable();
            $table->decimal('pergola_area', 10, 2)->nullable();
            $table->decimal('storage_area', 10, 2)->nullable();
            $table->decimal('extra_built_up_area', 10, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
