<?php
/**
 * Test Sales API URLs
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
echo "║         Test Sales API - Image URLs                          ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

// Get first sale
$sale = \App\Models\Sale::first();

if (!$sale) {
    echo "No sales found!\n";
    exit;
}

echo "Sale #{$sale->id}: {$sale->sale_name}\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Images in database (raw):\n";
if ($sale->images && is_array($sale->images)) {
    echo "  Count: " . count($sale->images) . "\n";
    echo "  First 3:\n";
    foreach (array_slice($sale->images, 0, 3) as $img) {
        echo "    - $img\n";
    }
} else {
    echo "  None\n";
}
echo "\n";

echo "Expected API output:\n";
echo "  Base URL: " . url('/storage') . "\n";
echo "  Sample image URL:\n";
if ($sale->images && is_array($sale->images) && count($sale->images) > 0) {
    $sampleImage = $sale->images[0];
    $expectedUrl = url('/storage/' . ltrim($sampleImage, '/'));
    echo "    → $expectedUrl\n";
}
echo "\n";

echo "✅ Test Complete!\n\n";
echo "Expected format: https://aqar.bdcbiz.com/storage/compound-images/...\n";
echo "NOT: http://127.0.0.1:8001/storage/...\n\n";
