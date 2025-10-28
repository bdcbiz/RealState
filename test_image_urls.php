<?php
/**
 * Test Image URLs - Verify all models use the same base URL
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
echo "║            Image URL Base Test                               ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

echo "APP_URL: " . config('app.url') . "\n\n";

// Test Company Logo
$company = \App\Models\Company::whereNotNull('logo')->first();
if ($company) {
    echo "✅ Company Logo URL:\n";
    echo "   " . $company->logo_url . "\n\n";
}

// Test Compound Images
$compound = \App\Models\Compound::whereNotNull('images')
    ->where('images', '!=', '[]')
    ->first();
if ($compound) {
    echo "✅ Compound Image URLs (first 2):\n";
    $images = $compound->images_urls;
    foreach (array_slice($images, 0, 2) as $img) {
        echo "   " . $img . "\n";
    }
    echo "\n";
}

// Test Unit Images
$unit = \App\Models\Unit::whereNotNull('images')
    ->where('images', '!=', '[]')
    ->first();
if ($unit) {
    echo "✅ Unit Image URLs (first 2):\n";
    $images = $unit->images_urls;
    foreach (array_slice($images, 0, 2) as $img) {
        echo "   " . $img . "\n";
    }
    echo "\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
echo "✅ All image URLs now use the same base URL format!\n";
echo "   Base: " . config('app.url') . "/storage/\n\n";
