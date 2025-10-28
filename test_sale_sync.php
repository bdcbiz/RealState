<?php
/**
 * Test Sale-Compound Auto Sync
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
echo "║         Test Sale-Compound Auto Sync                         ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

// Check existing data first
echo "1️⃣ Checking existing synced sales:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$compounds = \App\Models\Compound::whereNotNull('current_sale_id')
    ->select('id', 'project', 'current_sale_id', 'sales_person_id')
    ->get();

if ($compounds->count() > 0) {
    foreach ($compounds as $compound) {
        echo "Compound #{$compound->id} - {$compound->project}\n";
        echo "  current_sale_id: {$compound->current_sale_id}\n";
        echo "  sales_person_id: " . ($compound->sales_person_id ?? 'NULL') . "\n\n";
    }
} else {
    echo "❌ No compounds with sales found!\n\n";
}

// Test creating a new sale
echo "\n2️⃣ Testing auto-sync by creating a test sale:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Find a unit with a compound
$unit = \App\Models\Unit::whereNotNull('compound_id')->first();

if (!$unit) {
    echo "❌ No units found to test with!\n";
    exit(1);
}

echo "Using Unit: {$unit->unit_code} (Compound ID: {$unit->compound_id})\n\n";

// Create a test sale
$sale = new \App\Models\Sale();
$sale->company_id = 2; // Larz Developments
$sale->sale_type = 'unit';
$sale->unit_id = $unit->id;
$sale->sale_name = 'TEST SALE - ' . date('H:i:s');
$sale->description = 'Auto-generated test sale';
$sale->discount_percentage = 15;
$sale->old_price = 5000000;
$sale->new_price = 4250000;
$sale->start_date = now();
$sale->end_date = now()->addDays(30);
$sale->is_active = true;

echo "Creating test sale...\n";
$sale->save();
echo "✅ Sale created with ID: {$sale->id}\n\n";

// Check if compound was updated
echo "Checking if compound was auto-updated...\n";
$compound = \App\Models\Compound::find($unit->compound_id);

echo "\nCompound #{$compound->id} - {$compound->project}:\n";
echo "  current_sale_id: " . ($compound->current_sale_id ?? 'NULL') . "\n";
echo "  sales_person_id: " . ($compound->sales_person_id ?? 'NULL') . "\n\n";

if ($compound->current_sale_id == $sale->id) {
    echo "✅ SUCCESS! Compound was automatically updated!\n";
    echo "   The bidirectional sync is working!\n\n";
} else {
    echo "❌ FAILED! Compound was NOT updated.\n";
    echo "   Expected current_sale_id: {$sale->id}\n";
    echo "   Actual current_sale_id: " . ($compound->current_sale_id ?? 'NULL') . "\n\n";
    echo "   This means the model event is not firing.\n\n";
}

// Test deletion
echo "\n3️⃣ Testing auto-sync on deletion:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Deleting test sale...\n";
$sale->delete();
echo "✅ Sale deleted\n\n";

// Refresh compound
$compound->refresh();

echo "Checking if compound was cleared...\n";
echo "Compound #{$compound->id} - {$compound->project}:\n";
echo "  current_sale_id: " . ($compound->current_sale_id ?? 'NULL') . "\n";
echo "  sales_person_id: " . ($compound->sales_person_id ?? 'NULL') . "\n\n";

if ($compound->current_sale_id === null) {
    echo "✅ SUCCESS! Compound was automatically cleared!\n";
    echo "   Delete sync is working!\n\n";
} else {
    echo "⚠️  Compound still has current_sale_id: {$compound->current_sale_id}\n";
    echo "   This might be expected if there are other sales for this compound.\n\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
echo "🎯 Test Complete!\n\n";
