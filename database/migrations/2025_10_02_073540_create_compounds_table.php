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
        Schema::create('compounds', function (Blueprint $table) {
            $table->id();

            // Compound Information from Units Availability Report
            $table->string('project')->nullable();
            $table->decimal('built_up_area', 10, 2)->nullable();
            $table->integer('how_many_floors')->nullable();

            // From Sales Availability Report
            $table->date('planned_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();
            $table->decimal('completion_progress', 5, 2)->nullable(); // percentage
            $table->decimal('land_area', 10, 2)->nullable();
            $table->decimal('built_area', 10, 2)->nullable();
            $table->text('finish_specs')->nullable();
            $table->boolean('club')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compounds');
    }
};
