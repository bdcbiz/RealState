<?php
/**
 * Sync Sale Images
 *
 * Populates the images field in existing sales from their units/compounds
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
echo "║         Sync Sale Images from Units/Compounds                ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

$sales = \App\Models\Sale::all();

echo "Found {$sales->count()} sales to process.\n\n";

$synced = 0;
$skipped = 0;
$noImages = 0;

foreach ($sales as $sale) {
    echo "Processing Sale #{$sale->id} - {$sale->sale_name}...\n";
    echo "  Type: {$sale->sale_type}\n";

    if ($sale->images) {
        echo "  ⊘ Already has images - skipped\n\n";
        $skipped++;
        continue;
    }

    $images = null;

    // Get images based on sale type
    if ($sale->sale_type === 'unit' && $sale->unit_id) {
        $unit = \App\Models\Unit::find($sale->unit_id);

        if ($unit) {
            echo "  Unit: {$unit->unit_code}\n";

            if ($unit->images && is_array($unit->images) && count($unit->images) > 0) {
                $images = $unit->images;
                echo "  → Found " . count($images) . " images from unit\n";
            } else {
                echo "  → Unit has no images, checking compound...\n";

                if ($unit->compound_id) {
                    $compound = \App\Models\Compound::find($unit->compound_id);
                    if ($compound && $compound->images && is_array($compound->images) && count($compound->images) > 0) {
                        $images = $compound->images;
                        echo "  → Found " . count($images) . " images from compound '{$compound->project}'\n";
                    }
                }
            }
        } else {
            echo "  ⚠️  Unit not found\n";
        }
    } elseif ($sale->sale_type === 'compound' && $sale->compound_id) {
        $compound = \App\Models\Compound::find($sale->compound_id);

        if ($compound) {
            echo "  Compound: {$compound->project}\n";

            if ($compound->images && is_array($compound->images) && count($compound->images) > 0) {
                $images = $compound->images;
                echo "  → Found " . count($images) . " images from compound\n";
            }
        } else {
            echo "  ⚠️  Compound not found\n";
        }
    }

    // Update sale with images
    if ($images) {
        $sale->images = $images;
        $sale->saveQuietly();
        echo "  ✅ Sale updated with images!\n";
        $synced++;
    } else {
        echo "  ⊘ No images available\n";
        $noImages++;
    }

    echo "\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
echo "📊 Summary:\n";
echo "  ✅ Synced with images: $synced\n";
echo "  ⊘ Already had images: $skipped\n";
echo "  ⊘ No images available: $noImages\n";
echo "  Total: " . ($synced + $skipped + $noImages) . "\n\n";

if ($synced > 0) {
    echo "╔═══════════════════════════════════════════════════════════════╗\n";
    echo "║  ✅ Sales synced with images successfully!                    ║\n";
    echo "║  Future sales will auto-populate images when created.        ║\n";
    echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

    // Show verification
    echo "🔍 Verification - Sales with images:\n\n";
    $salesWithImages = \App\Models\Sale::whereNotNull('images')
        ->take(3)
        ->get();

    foreach ($salesWithImages as $sale) {
        echo "  Sale #{$sale->id}: {$sale->sale_name}\n";
        if (is_array($sale->images)) {
            echo "  → Has " . count($sale->images) . " image(s)\n";
            if (count($sale->images) > 0) {
                echo "  → First image: " . $sale->images[0] . "\n";
            }
        }
        echo "\n";
    }
}
