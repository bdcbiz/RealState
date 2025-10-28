<?php
/**
 * Sync Existing Sales to Compounds
 *
 * Updates compounds with their current sale and sales person information
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
echo "║         Sync Existing Sales to Compounds                     ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

$sales = \App\Models\Sale::all();

echo "Found {$sales->count()} sales to process.\n\n";

$synced = 0;
$skipped = 0;

foreach ($sales as $sale) {
    echo "Processing Sale #{$sale->id} - {$sale->sale_name}...\n";

    $compoundId = null;

    // Get compound ID
    if ($sale->sale_type === 'compound' && $sale->compound_id) {
        $compoundId = $sale->compound_id;
        echo "  → Type: Compound Sale\n";
        echo "  → Compound ID: {$compoundId}\n";
    } elseif ($sale->sale_type === 'unit' && $sale->unit_id) {
        $unit = \App\Models\Unit::find($sale->unit_id);
        if ($unit && $unit->compound_id) {
            $compoundId = $unit->compound_id;
            echo "  → Type: Unit Sale\n";
            echo "  → Unit ID: {$sale->unit_id}\n";
            echo "  → Compound ID: {$compoundId}\n";
        } else {
            echo "  ⚠️  Unit not found or has no compound\n";
        }
    }

    if ($compoundId) {
        $compound = \App\Models\Compound::find($compoundId);
        if ($compound) {
            $compound->current_sale_id = $sale->id;
            $compound->sales_person_id = $sale->sales_person_id;
            $compound->saveQuietly();

            echo "  ✅ Compound '{$compound->project}' updated!\n";
            echo "     - current_sale_id: {$sale->id}\n";
            echo "     - sales_person_id: " . ($sale->sales_person_id ?? 'NULL') . "\n";
            $synced++;
        } else {
            echo "  ⚠️  Compound not found\n";
            $skipped++;
        }
    } else {
        echo "  ⊘ No compound to update\n";
        $skipped++;
    }

    echo "\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
echo "📊 Summary:\n";
echo "  ✅ Synced: $synced\n";
echo "  ⊘ Skipped: $skipped\n";
echo "  Total: " . ($synced + $skipped) . "\n\n";

if ($synced > 0) {
    echo "╔═══════════════════════════════════════════════════════════════╗\n";
    echo "║  ✅ Existing sales synced to compounds successfully!          ║\n";
    echo "║  Future sales will auto-sync when created/updated.            ║\n";
    echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

    // Show verification
    echo "🔍 Verification - Compounds with sales:\n\n";
    $compoundsWithSales = \App\Models\Compound::whereNotNull('current_sale_id')
        ->with(['currentSale', 'salesPerson'])
        ->take(5)
        ->get();

    foreach ($compoundsWithSales as $compound) {
        echo "  Compound: {$compound->project}\n";
        echo "  Sale: " . ($compound->currentSale ? $compound->currentSale->sale_name : 'None') . "\n";
        echo "  Sales Person: " . ($compound->salesPerson ? $compound->salesPerson->name : 'None') . "\n";
        echo "\n";
    }
}
