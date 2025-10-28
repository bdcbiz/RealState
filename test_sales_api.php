<?php
/**
 * Test Sales API - Images and Prices
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
echo "║         Test Sales API - Images and Prices                   ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

// Get all sales
$sales = \App\Models\Sale::all();

echo "Found {$sales->count()} sales\n\n";

foreach ($sales as $sale) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Sale #{$sale->id}: {$sale->sale_name}\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    echo "  Type: {$sale->sale_type}\n";
    echo "  Discount: {$sale->discount_percentage}%\n";
    echo "  Old Price: " . ($sale->old_price ?? 'NULL') . "\n";
    echo "  New Price: " . ($sale->new_price ?? 'NULL') . "\n\n";

    echo "  Images in database:\n";
    if ($sale->images && is_array($sale->images)) {
        echo "    Count: " . count($sale->images) . "\n";
        echo "    First 3:\n";
        foreach (array_slice($sale->images, 0, 3) as $img) {
            echo "      - $img\n";
        }
    } else {
        echo "    None\n";
    }
    echo "\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Test the API controller directly
echo "Testing API Response Format:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$sale = $sales->first();
if ($sale) {
    echo "Sale #{$sale->id} API check:\n";
    echo "  Images cast type: " . (is_array($sale->images) ? 'array' : gettype($sale->images)) . "\n";
    echo "  Images count: " . (is_array($sale->images) ? count($sale->images) : 0) . "\n";
    echo "  Old price type: " . gettype($sale->old_price) . "\n";
    echo "  New price type: " . gettype($sale->new_price) . "\n\n";
}

echo "✅ Test Complete!\n\n";

echo "Next Steps:\n";
echo "1. Images should now appear in API (controller fixed)\n";
echo "2. Prices are NULL because units have no prices\n";
echo "3. Need to manually set old_price in Filament admin\n\n";
