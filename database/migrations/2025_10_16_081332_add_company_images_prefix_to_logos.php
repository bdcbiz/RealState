<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'company-images/' prefix to logos that are just filenames
        // Only update logos that don't already start with 'company-images/'
        DB::statement("
            UPDATE companies
            SET logo = CONCAT('company-images/', logo)
            WHERE logo IS NOT NULL
            AND logo != ''
            AND logo NOT LIKE 'company-images/%'
            AND logo NOT LIKE 'http://%'
            AND logo NOT LIKE 'https://%'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'company-images/' prefix
        DB::statement("
            UPDATE companies
            SET logo = REPLACE(logo, 'company-images/', '')
            WHERE logo LIKE 'company-images/%'
        ");
    }
};
