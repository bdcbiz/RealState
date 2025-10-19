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
        // Fix company logo paths - remove wrong prefixes and URL structures
        // The logo should just be stored as: company-images/filename.ext

        // Remove full URLs if they exist
        DB::statement("
            UPDATE companies
            SET logo = REGEXP_REPLACE(logo, '^https?://[^/]+/.*/', '')
            WHERE logo LIKE 'http://%' OR logo LIKE 'https://%'
        ");

        // Remove 'storage/app/public/' prefix if it exists
        DB::statement("
            UPDATE companies
            SET logo = REPLACE(logo, 'storage/app/public/', '')
            WHERE logo LIKE '%storage/app/public/%'
        ");

        // Remove 'app/public/' prefix if it exists
        DB::statement("
            UPDATE companies
            SET logo = REPLACE(logo, 'app/public/', '')
            WHERE logo LIKE '%app/public/%'
        ");

        // Remove 'public/' prefix if it exists
        DB::statement("
            UPDATE companies
            SET logo = REPLACE(logo, 'public/', '')
            WHERE logo LIKE '%public/%'
        ");

        // Fix paths that start with compound-images instead of company-images
        DB::statement("
            UPDATE companies
            SET logo = REPLACE(logo, 'compound-images/', 'company-images/')
            WHERE logo LIKE 'compound-images/%'
        ");

        // Remove any escaped slashes
        DB::statement("
            UPDATE companies
            SET logo = REPLACE(logo, '\\\/', '/')
            WHERE logo LIKE '%\\\\/%'
        ");

        // Remove duplicate 'company-images/company-images/' paths
        DB::statement("
            UPDATE companies
            SET logo = REPLACE(logo, 'company-images/company-images/', 'company-images/')
            WHERE logo LIKE '%company-images/company-images/%'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed as we're just cleaning data
    }
};
