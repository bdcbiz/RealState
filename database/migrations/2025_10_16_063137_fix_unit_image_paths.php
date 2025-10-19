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
        // Fix unit image paths
        \DB::table('units')->whereNotNull('images')->get()->each(function ($unit) {
            $images = json_decode($unit->images, true);

            if (is_array($images)) {
                $cleanedImages = array_map(function($img) {
                    // Remove full URLs and extract just the path
                    $img = str_replace('http://192.168.1.33/larvel2/storage/app/public/', '', $img);
                    $img = str_replace('http://192.168.1.33/larvel2/storage/', '', $img);
                    $img = str_replace('http://127.0.0.1:8001/storage/', '', $img);

                    // Remove duplicate prefixes
                    $img = preg_replace('#^company-images/compound-images/#', 'compound-images/', $img);
                    $img = preg_replace('#^unit-images/unit-images/#', 'unit-images/', $img);

                    // Remove escaped slashes
                    $img = str_replace('\\/', '/', $img);

                    return $img;
                }, $images);

                \DB::table('units')
                    ->where('id', $unit->id)
                    ->update(['images' => json_encode($cleanedImages)]);
            }
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
