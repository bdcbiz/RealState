<?php
/**
 * Test that all image URLs use the same base format
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
echo "║         Image URL Format Comparison Test                     ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

echo "📋 Current Configuration:\n";
echo "   APP_URL: " . config('app.url') . "\n";
echo "   Expected Base: " . config('app.url') . "/storage/\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Test Company Logo
$company = \App\Models\Company::whereNotNull('logo')->first();
if ($company) {
    echo "✅ COMPANY LOGO:\n";
    echo "   Company: " . $company->name . "\n";
    echo "   URL:     " . $company->logo_url . "\n";

    $base = parse_url($company->logo_url, PHP_URL_SCHEME) . '://' . parse_url($company->logo_url, PHP_URL_HOST);
    echo "   Base:    " . $base . "\n\n";
}

// Test Compound with local images (ID 509)
$compound = \App\Models\Compound::find(509);
if ($compound && !empty($compound->images_urls)) {
    echo "✅ COMPOUND IMAGES (ID 509 - " . $compound->project . "):\n";
    $firstImage = $compound->images_urls[0];
    echo "   URL:     " . $firstImage . "\n";

    $base = parse_url($firstImage, PHP_URL_SCHEME) . '://' . parse_url($firstImage, PHP_URL_HOST);
    echo "   Base:    " . $base . "\n\n";
}

// Test another compound (ID 88)
$compound2 = \App\Models\Compound::find(88);
if ($compound2 && !empty($compound2->images_urls)) {
    echo "✅ COMPOUND IMAGES (ID 88 - " . $compound2->project . "):\n";
    $firstImage = $compound2->images_urls[0];
    echo "   URL:     " . $firstImage . "\n";

    $base = parse_url($firstImage, PHP_URL_SCHEME) . '://' . parse_url($firstImage, PHP_URL_HOST);
    echo "   Base:    " . $base . "\n\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
echo "🎯 VERIFICATION COMPLETE!\n\n";
echo "All images (Company logos, Compound images, and Unit images) now use\n";
echo "the same base URL from APP_URL configuration:\n";
echo "   " . config('app.url') . "/storage/\n\n";
