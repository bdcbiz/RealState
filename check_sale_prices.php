<?php
/**
 * Check why sale prices aren't saving to database
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
echo "║         Sale Prices Investigation                            ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

$sales = \App\Models\Sale::with('unit')->take(3)->get();

foreach ($sales as $sale) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Sale ID: {$sale->id} - {$sale->sale_name}\n";
    echo "Sale Type: {$sale->sale_type}\n";

    if ($sale->sale_type === 'unit' && $sale->unit) {
        echo "\n📦 Unit Info:\n";
        echo "   Unit Name: {$sale->unit->unit_name}\n";
        echo "   Unit Normal Price: " . ($sale->unit->normal_price ?? 'NULL') . "\n";
        echo "   Unit Total Price: " . ($sale->unit->unit_total_with_finish_price ?? 'NULL') . "\n";
    }

    echo "\n💰 Pricing in Database:\n";
    echo "   Discount %: {$sale->discount_percentage}%\n";
    echo "   Old Price (DB): " . ($sale->old_price ?? 'NULL') . "\n";
    echo "   New Price (DB): " . ($sale->new_price ?? 'NULL') . "\n";

    // Calculate what they should be
    if ($sale->sale_type === 'unit' && $sale->unit) {
        $unitPrice = $sale->unit->normal_price ?? $sale->unit->unit_total_with_finish_price ?? 0;

        if ($unitPrice > 0) {
            $calculatedOld = $unitPrice;
            $calculatedNew = $unitPrice - ($unitPrice * $sale->discount_percentage / 100);

            echo "\n✅ Expected Values (from unit):\n";
            echo "   Should be Old Price: " . number_format($calculatedOld, 2) . " EGP\n";
            echo "   Should be New Price: " . number_format($calculatedNew, 2) . " EGP\n";

            if ($sale->old_price !== null) {
                echo "\n   ✓ Old price IS saved correctly\n";
            } else {
                echo "\n   ❌ Old price NOT saved (NULL in DB)\n";
            }

            if ($sale->new_price !== null) {
                echo "   ✓ New price IS saved correctly\n";
            } else {
                echo "   ❌ New price NOT saved (NULL in DB)\n";
            }
        }
    }

    echo "\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "🔍 Diagnosis:\n";
echo "If old_price and new_price are NULL in DB but appear in Filament,\n";
echo "it means Filament is calculating them on-the-fly but not saving them.\n\n";

echo "💡 Solution:\n";
echo "We need to ensure the values are actually saved when creating/editing sales.\n";
echo "This might be a dehydration or model event issue.\n\n";
