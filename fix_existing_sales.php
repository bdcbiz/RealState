<?php
/**
 * Fix Existing Sales - Populate old_price and new_price
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
echo "║         Fix Existing Sales Prices                             ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

$sales = \App\Models\Sale::whereNull('old_price')
    ->orWhereNull('new_price')
    ->get();

echo "Found {$sales->count()} sales with missing prices.\n\n";

if ($sales->count() === 0) {
    echo "✅ All sales already have prices set!\n\n";
    exit(0);
}

$updated = 0;
$skipped = 0;

foreach ($sales as $sale) {
    echo "Processing Sale #{$sale->id} - {$sale->sale_name}...\n";

    $oldPrice = $sale->old_price;
    $newPrice = $sale->new_price;
    $needsUpdate = false;

    // If old_price is missing and it's a unit sale
    if (!$oldPrice && $sale->sale_type === 'unit' && $sale->unit_id) {
        $unit = \App\Models\Unit::find($sale->unit_id);
        if ($unit) {
            $unitPrice = $unit->normal_price ?? $unit->unit_total_with_finish_price;
            if ($unitPrice) {
                $oldPrice = $unitPrice;
                $needsUpdate = true;
                echo "  → Found unit price: " . number_format($unitPrice, 2) . " EGP\n";
            } else {
                echo "  ⚠️  Unit has no price - cannot calculate\n";
            }
        }
    }

    // Calculate new_price if we have old_price and discount
    if ($oldPrice && $sale->discount_percentage && !$newPrice) {
        $newPrice = $oldPrice - ($oldPrice * $sale->discount_percentage / 100);
        $needsUpdate = true;
        echo "  → Calculated new price: " . number_format($newPrice, 2) . " EGP ({$sale->discount_percentage}% discount)\n";
    }

    // Update if needed
    if ($needsUpdate) {
        $sale->old_price = $oldPrice;
        $sale->new_price = $newPrice;
        $sale->saveQuietly(); // Save without triggering events
        echo "  ✅ Updated!\n";
        $updated++;
    } else {
        echo "  ⊘ Skipped (no price data available)\n";
        $skipped++;
    }

    echo "\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
echo "📊 Summary:\n";
echo "  ✅ Updated: $updated\n";
echo "  ⊘ Skipped: $skipped\n";
echo "  Total Processed: " . ($updated + $skipped) . "\n\n";

if ($updated > 0) {
    echo "╔═══════════════════════════════════════════════════════════════╗\n";
    echo "║  ✅ Existing sales have been updated successfully!            ║\n";
    echo "║  New sales will auto-calculate prices from now on.            ║\n";
    echo "╚═══════════════════════════════════════════════════════════════╝\n\n";
}
