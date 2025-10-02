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
        Schema::table('units', function (Blueprint $table) {
            // Remove redundant compound_name (use relationship instead)
            $table->dropColumn('compound_name');

            // Add indexes for performance
            $table->index('compound_id');
            $table->index('usage_type');
            $table->index('unit_type');
            $table->index('floor_number');
            $table->index('number_of_beds');
            $table->index(['compound_id', 'building_name']); // Composite index

            // Add soft deletes
            $table->softDeletes();
        });

        Schema::table('compounds', function (Blueprint $table) {
            // Add indexes
            $table->index('project');
            $table->index('planned_delivery_date');
            $table->index('actual_delivery_date');

            // Add soft deletes
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->string('compound_name')->nullable();
            $table->dropIndex(['compound_id']);
            $table->dropIndex(['usage_type']);
            $table->dropIndex(['unit_type']);
            $table->dropIndex(['floor_number']);
            $table->dropIndex(['number_of_beds']);
            $table->dropIndex(['compound_id', 'building_name']);
            $table->dropSoftDeletes();
        });

        Schema::table('compounds', function (Blueprint $table) {
            $table->dropIndex(['project']);
            $table->dropIndex(['planned_delivery_date']);
            $table->dropIndex(['actual_delivery_date']);
            $table->dropSoftDeletes();
        });
    }
};
