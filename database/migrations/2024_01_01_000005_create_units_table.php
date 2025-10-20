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
            $table->foreignId('buyer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('sales_id')->nullable()->constrained('users')->onDelete('set null');

            // Basic Information
            $table->boolean('available')->default(true);
            $table->string('unit_name')->nullable();
            $table->string('unit_name_ar')->nullable();
            $table->string('unit_name_en')->nullable();
            $table->string('building_name')->nullable();
            $table->string('unit_number')->nullable();
            $table->string('code')->nullable()->unique();
            $table->string('compound_name')->nullable(); // Denormalized for performance

            // Usage and Type
            $table->string('usage_type')->nullable();
            $table->string('usage_type_ar')->nullable();
            $table->string('usage_type_en')->nullable();
            $table->string('unit_type')->nullable();
            $table->string('unit_type_ar')->nullable();
            $table->string('unit_type_en')->nullable();
            $table->string('unit_code')->nullable();

            // Areas
            $table->decimal('garden_area', 10, 2)->nullable();
            $table->decimal('roof_area', 10, 2)->nullable();
            $table->decimal('basement_area', 10, 2)->nullable();
            $table->decimal('uncovered_basement', 10, 2)->nullable();
            $table->decimal('penthouse', 10, 2)->nullable();
            $table->decimal('semi_covered_roof_area', 10, 2)->nullable();
            $table->decimal('garage_area', 10, 2)->nullable();
            $table->decimal('pergola_area', 10, 2)->nullable();
            $table->decimal('storage_area', 10, 2)->nullable();
            $table->decimal('extra_built_up_area', 10, 2)->nullable();

            // Unit Details
            $table->integer('floor_number')->nullable();
            $table->integer('number_of_beds')->nullable();
            $table->string('stage_number')->nullable();

            // Pricing
            $table->decimal('normal_price', 15, 2)->nullable();
            $table->decimal('total_pricing', 15, 2)->nullable();
            $table->decimal('total_finish_pricing', 15, 2)->nullable();
            $table->decimal('unit_total_with_finish_price', 15, 2)->nullable();

            // Status
            $table->boolean('is_sold')->default(false);
            $table->enum('status', ['inhabited', 'in_progress', 'delivered'])->default('in_progress');
            $table->string('status_ar')->nullable();
            $table->string('status_en')->nullable();
            $table->date('delivered_at')->nullable();

            // Images
            $table->json('images')->nullable(); // Array of image paths

            $table->timestamps();
            $table->softDeletes();
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
