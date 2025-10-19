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
        // Add multilingual fields to companies table
        Schema::table('companies', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('name');
            $table->string('name_en')->nullable()->after('name_ar');
        });

        // Add multilingual fields to compounds table
        Schema::table('compounds', function (Blueprint $table) {
            $table->string('project_ar')->nullable()->after('project');
            $table->string('project_en')->nullable()->after('project_ar');
            $table->text('location_ar')->nullable()->after('location');
            $table->text('location_en')->nullable()->after('location_ar');
        });

        // Add multilingual fields to units table
        Schema::table('units', function (Blueprint $table) {
            $table->string('unit_name_ar')->nullable()->after('unit_name');
            $table->string('unit_name_en')->nullable()->after('unit_name_ar');
            $table->string('unit_type_ar')->nullable()->after('unit_type');
            $table->string('unit_type_en')->nullable()->after('unit_type_ar');
            $table->string('usage_type_ar')->nullable()->after('usage_type');
            $table->string('usage_type_en')->nullable()->after('usage_type_ar');
            $table->string('status_ar')->nullable()->after('status');
            $table->string('status_en')->nullable()->after('status_ar');
        });

        // Copy existing data to English fields (assuming current data is English)
        DB::statement('UPDATE companies SET name_en = name WHERE name_en IS NULL');
        DB::statement('UPDATE compounds SET project_en = project WHERE project_en IS NULL');
        DB::statement('UPDATE compounds SET location_en = location WHERE location_en IS NULL');
        DB::statement('UPDATE units SET unit_name_en = unit_name WHERE unit_name_en IS NULL');
        DB::statement('UPDATE units SET unit_type_en = unit_type WHERE unit_type_en IS NULL');
        DB::statement('UPDATE units SET usage_type_en = usage_type WHERE usage_type_en IS NULL');
        DB::statement('UPDATE units SET status_en = status WHERE status_en IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_en']);
        });

        Schema::table('compounds', function (Blueprint $table) {
            $table->dropColumn(['project_ar', 'project_en', 'location_ar', 'location_en']);
        });

        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn(['unit_name_ar', 'unit_name_en', 'unit_type_ar', 'unit_type_en', 'usage_type_ar', 'usage_type_en', 'status_ar', 'status_en']);
        });
    }
};
