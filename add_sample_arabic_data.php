<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Company;
use App\Models\Compound;
use App\Models\Unit;

echo "Adding sample Arabic translations...\n";
echo "=====================================\n\n";

// Add Arabic translations to first company
$company = Company::first();
if ($company) {
    $company->update([
        'name_ar' => 'شركة التطوير العقاري',  // Real Estate Development Company
        'name_en' => $company->name
    ]);
    echo "✓ Updated company: {$company->name}\n";
    echo "  - English: {$company->name_en}\n";
    echo "  - Arabic: {$company->name_ar}\n\n";
}

// Add Arabic translations to first compound
$compound = Compound::first();
if ($compound) {
    $compound->update([
        'project_ar' => 'مشروع الكومباوند السكني',  // Residential Compound Project
        'project_en' => $compound->project,
        'location_ar' => 'القاهرة، مصر',  // Cairo, Egypt
        'location_en' => $compound->location
    ]);
    echo "✓ Updated compound: {$compound->project}\n";
    echo "  - English: {$compound->project_en}\n";
    echo "  - Arabic: {$compound->project_ar}\n";
    echo "  - Location (EN): {$compound->location_en}\n";
    echo "  - Location (AR): {$compound->location_ar}\n\n";
}

// Add Arabic translations to first unit
$unit = Unit::first();
if ($unit && $unit->unit_name) {
    $unit->update([
        'unit_name_ar' => 'شقة فاخرة',  // Luxury Apartment
        'unit_name_en' => $unit->unit_name,
        'unit_type_ar' => 'شقة',  // Apartment
        'unit_type_en' => $unit->unit_type ?? 'Apartment',
        'usage_type_ar' => 'سكني',  // Residential
        'usage_type_en' => $unit->usage_type ?? 'Residential',
        'status_ar' => 'متاح',  // Available
        'status_en' => $unit->status ?? 'Available'
    ]);
    echo "✓ Updated unit: {$unit->unit_name}\n";
    echo "  - Name (EN): {$unit->unit_name_en}\n";
    echo "  - Name (AR): {$unit->unit_name_ar}\n";
    echo "  - Type (EN): {$unit->unit_type_en}\n";
    echo "  - Type (AR): {$unit->unit_type_ar}\n\n";
}

echo "=====================================\n";
echo "Sample translations added successfully!\n";
echo "\nNow test in Postman:\n";
echo "- GET http://127.0.0.1:8001/api/companies\n";
echo "- GET http://127.0.0.1:8001/api/compounds\n";
echo "- GET http://127.0.0.1:8001/api/units\n";
