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
        // Fix company logo paths
        // Remove the incorrect "company-images/" prefix if it exists
        // and remove the old URL structure
        \DB::table('companies')->whereNotNull('logo')->get()->each(function ($company) {
            $logo = $company->logo;

            // Remove full URLs and extract just the path
            $logo = str_replace('http://192.168.1.33/larvel2/storage/app/public/', '', $logo);
            $logo = str_replace('http://192.168.1.33/larvel2/storage/', '', $logo);
            $logo = str_replace('http://127.0.0.1:8001/storage/', '', $logo);

            // Remove duplicate "company-images/" prefix
            $logo = preg_replace('#^company-images/company-images/#', 'company-images/', $logo);
            $logo = preg_replace('#^company-images/compound-images/#', 'compound-images/', $logo);

            // Remove escaped slashes
            $logo = str_replace('\\/', '/', $logo);

            \DB::table('companies')
                ->where('id', $company->id)
                ->update(['logo' => $logo]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse
    }
};
