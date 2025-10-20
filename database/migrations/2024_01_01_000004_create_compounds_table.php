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
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('project')->nullable()->index();
            $table->string('project_ar')->nullable();
            $table->string('project_en')->nullable();
            $table->string('location')->nullable();
            $table->text('location_ar')->nullable();
            $table->text('location_en')->nullable();
            $table->text('location_url')->nullable();
            $table->json('images')->nullable(); // Array of image paths
            $table->decimal('built_up_area', 10, 2)->nullable();
            $table->integer('how_many_floors')->nullable();
            $table->date('planned_delivery_date')->nullable()->index();
            $table->date('actual_delivery_date')->nullable()->index();
            $table->decimal('completion_progress', 5, 2)->nullable(); // Percentage 0-100
            $table->decimal('land_area', 10, 2)->nullable();
            $table->decimal('built_area', 10, 2)->nullable();
            $table->text('finish_specs')->nullable();
            $table->boolean('club')->default(false);
            $table->boolean('is_sold')->default(false);
            $table->enum('status', ['inhabited', 'in_progress', 'delivered'])->default('in_progress');
            $table->date('delivered_at')->nullable();
            $table->integer('total_units')->default(0);
            $table->timestamps();
            $table->softDeletes();
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
