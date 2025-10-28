<?php

/**
 * ============================================================================
 * Sales Availability Units Import Script
 * ============================================================================
 *
 * Imports units from Sales Availability CSV to database
 *
 * Usage: php import_sales_units.php
 * ============================================================================
 */

// Configuration
$dbHost = 'localhost';
$dbName = 'real_state';
$dbUser = 'laravel';
$dbPass = 'laravel123';

// CSV file path on server
$csvFile = '/var/www/realestate/sales_availability.csv';

// Project name to compound ID mapping
$projectMapping = [
    'Badya' => 34,
    'Hacienda Blue' => 709,
    'Hacienda West' => 30,
    'Hacienda Waters' => 588,
    'Palm Hills Jirian' => 1363,
    'Palm Hills New Cairo' => 35,
    'PX' => 518,
    'PHNC Commercial' => 1364,
    'Hacienda Heneish' => 543,
    'Palm Hills Alexandria' => 203,
    'Capital Gardens' => 183,
    'Hacienda Bay' => 104,
    'Bamboo III' => 1365,
    'Palm Parks' => 127,
    'The Crown' => 88,
];

echo "\n";
echo str_repeat('=', 80) . "\n";
echo "Sales Availability Units Import Script\n";
echo str_repeat('=', 80) . "\n\n";

// Check if file exists
if (!file_exists($csvFile)) {
    die("[ERROR] CSV file not found: $csvFile\n");
}

// Database connection
try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "[OK] Connected to database\n\n";
} catch (PDOException $e) {
    die("[ERROR] Database connection failed: " . $e->getMessage() . "\n");
}

// Load CSV file
echo "[1/4] Loading CSV file...\n";
$rows = [];
if (($handle = fopen($csvFile, "r")) !== FALSE) {
    $rowIndex = 0;
    $headerRow = null;

    while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
        // Skip first 4 rows (title and empty rows)
        if ($rowIndex < 4) {
            $rowIndex++;
            continue;
        }

        // Row 4 is the header
        if ($rowIndex == 4) {
            $headerRow = $data;
            $rowIndex++;
            continue;
        }

        // Data rows
        $rows[] = $data;
        $rowIndex++;
    }
    fclose($handle);

    $totalRows = count($rows);
    echo "[OK] Loaded $totalRows units from CSV\n";
    echo "[OK] Headers found: " . count($headerRow) . " columns\n\n";
} else {
    die("[ERROR] Failed to open CSV file\n");
}

// Check existing units count
echo "[2/4] Checking existing units...\n";
$stmt = $pdo->query("SELECT COUNT(*) FROM units");
$existingCount = $stmt->fetchColumn();
echo "[INFO] Found $existingCount existing units in database\n\n";

// Prepare insert statement
echo "[3/4] Preparing to import units...\n";
$insertSql = "INSERT INTO units (
    compound_id,
    unit_name,
    unit_name_en,
    unit_name_ar,
    compound_name,
    unit_code,
    usage_type,
    usage_type_en,
    usage_type_ar,
    built_up_area,
    land_area,
    garden_area,
    roof_area,
    basement_area,
    semi_covered_roof_area,
    garage_area,
    pergola_area,
    storage_area,
    extra_built_up_area,
    normal_price,
    planned_delivery_date,
    actual_delivery_date,
    completion_progress,
    available,
    is_sold,
    status,
    created_at,
    updated_at
) VALUES (
    :compound_id,
    :unit_name,
    :unit_name_en,
    :unit_name_ar,
    :compound_name,
    :unit_code,
    :usage_type,
    :usage_type_en,
    :usage_type_ar,
    :built_up_area,
    :land_area,
    :garden_area,
    :roof_area,
    :basement_area,
    :semi_covered_roof_area,
    :garage_area,
    :pergola_area,
    :storage_area,
    :extra_built_up_area,
    :normal_price,
    :planned_delivery_date,
    :actual_delivery_date,
    :completion_progress,
    :available,
    :is_sold,
    :status,
    NOW(),
    NOW()
)";

$stmt = $pdo->prepare($insertSql);

// Statistics
$stats = [
    'total' => 0,
    'success' => 0,
    'failed' => 0,
    'skipped' => 0,
    'errors' => []
];

// Helper function to clean numeric value
function cleanNumeric($value) {
    if (empty($value)) return null;
    if (is_numeric($value)) return $value;

    // Remove currency symbols, spaces, and commas
    $cleaned = preg_replace('/[^0-9.]/', '', $value);

    return is_numeric($cleaned) ? (float)$cleaned : null;
}

// Helper function to parse date
function parseDate($dateStr) {
    if (empty($dateStr)) return null;

    $timestamp = strtotime($dateStr);
    if ($timestamp === false) return null;

    return date('Y-m-d', $timestamp);
}

// Map CSV columns (based on header row)
// Expected columns:
// 0: Project
// 1: Stage
// 2: Category
// 3: Unit Type
// 4: Unit Code
// 5: Grand Total (Pricing Structure)
// 6: Total Finishing Price
// 7: Unit Total with Finishing Price
// 8: Planned Delivery Date
// 9: Actual Delivery Date
// 10: Completion Progress
// 11: Land Area
// 12: Built Area
// 13: Basement Area
// 14: Uncovered Basement Area
// 15: Penthouse Area
// 16: Semi Covered Roof Area
// 17: Roof Area
// 18: Garden / Outdoor Area
// 19: Garage Area
// 20: Pergola Area
// 21: Storage Area
// 22: Extra BuiltUp Area
// 23: Finishing Specs
// 24: Club

// Import units
echo "\n[4/4] Importing units...\n";
echo str_repeat('=', 80) . "\n\n";

$startTime = time();

foreach ($rows as $rowIndex => $row) {
    $stats['total']++;

    try {
        // Extract data from row
        $project = trim($row[0] ?? '');
        $unitCode = trim($row[4] ?? '');
        $unitType = trim($row[3] ?? '');
        $category = trim($row[2] ?? '');

        // Skip empty rows
        if (empty($project) || empty($unitCode)) {
            $stats['skipped']++;
            continue;
        }

        // Get compound ID
        $compoundId = $projectMapping[$project] ?? null;

        if (!$compoundId) {
            $stats['skipped']++;
            $stats['errors'][] = "Row " . ($rowIndex + 6) . ": Project '$project' not found in mapping";
            continue;
        }

        // Check if unit already exists
        $checkStmt = $pdo->prepare("SELECT id FROM units WHERE unit_code = ? LIMIT 1");
        $checkStmt->execute([$unitCode]);
        if ($checkStmt->fetch()) {
            $stats['skipped']++;
            continue; // Already exists
        }

        // Extract numeric values from CSV columns
        $price = cleanNumeric($row[5] ?? null); // Grand Total (Pricing Structure)
        $landArea = cleanNumeric($row[11] ?? null); // Land Area
        $builtArea = cleanNumeric($row[12] ?? null); // Built Area
        $basementArea = cleanNumeric($row[13] ?? null); // Basement Area
        $semiCoveredRoofArea = cleanNumeric($row[16] ?? null); // Semi Covered Roof Area
        $roofArea = cleanNumeric($row[17] ?? null); // Roof Area
        $gardenArea = cleanNumeric($row[18] ?? null); // Garden / Outdoor Area
        $garageArea = cleanNumeric($row[19] ?? null); // Garage Area
        $pergolaArea = cleanNumeric($row[20] ?? null); // Pergola Area
        $storageArea = cleanNumeric($row[21] ?? null); // Storage Area
        $extraBuiltUpArea = cleanNumeric($row[22] ?? null); // Extra BuiltUp Area
        $completionProgress = cleanNumeric($row[10] ?? null); // Completion Progress

        // Extract dates
        $plannedDeliveryDate = parseDate($row[8] ?? null);
        $actualDeliveryDate = parseDate($row[9] ?? null);

        // Unit name from category or unit code
        $unitName = !empty($category) ? $category : $unitCode;

        // Execute insert
        $stmt->execute([
            'compound_id' => $compoundId,
            'unit_name' => $unitName,
            'unit_name_en' => $unitName,
            'unit_name_ar' => $unitName,
            'compound_name' => $project,
            'unit_code' => $unitCode,
            'usage_type' => $unitType,
            'usage_type_en' => $unitType,
            'usage_type_ar' => $unitType,
            'built_up_area' => $builtArea,
            'land_area' => $landArea,
            'garden_area' => $gardenArea,
            'roof_area' => $roofArea,
            'basement_area' => $basementArea,
            'semi_covered_roof_area' => $semiCoveredRoofArea,
            'garage_area' => $garageArea,
            'pergola_area' => $pergolaArea,
            'storage_area' => $storageArea,
            'extra_built_up_area' => $extraBuiltUpArea,
            'normal_price' => $price,
            'planned_delivery_date' => $plannedDeliveryDate,
            'actual_delivery_date' => $actualDeliveryDate,
            'completion_progress' => $completionProgress,
            'available' => 1,
            'is_sold' => 0,
            'status' => 'in_progress'
        ]);

        $stats['success']++;

        // Progress indicator
        if ($stats['total'] % 100 == 0) {
            $elapsed = time() - $startTime;
            $rate = $elapsed > 0 ? $stats['total'] / $elapsed : 0;
            $remaining = $rate > 0 ? ($totalRows - $stats['total']) / $rate : 0;

            $progress = ($stats['total'] / $totalRows) * 100;
            echo sprintf(
                "[PROGRESS] %d/%d (%.1f%%) - %d success, %d skipped, %d failed | Rate: %.1f units/sec | ETA: %s\n",
                $stats['total'],
                $totalRows,
                $progress,
                $stats['success'],
                $stats['skipped'],
                $stats['failed'],
                $rate,
                formatSeconds($remaining)
            );
        }

    } catch (Exception $e) {
        $stats['failed']++;
        $stats['errors'][] = "Row " . ($rowIndex + 6) . ": " . $e->getMessage();

        if ($stats['failed'] <= 10) {
            echo "[ERROR] Row " . ($rowIndex + 6) . ": " . $e->getMessage() . "\n";
        }
    }
}

// Final statistics
$totalTime = time() - $startTime;

echo "\n";
echo str_repeat('=', 80) . "\n";
echo "Import Completed!\n";
echo str_repeat('=', 80) . "\n";
echo "Statistics:\n";
echo str_repeat('-', 80) . "\n";
echo "  Total rows processed:    {$stats['total']}\n";
echo "  [OK] Successfully imported: {$stats['success']}\n";
echo "  [SKIP] Skipped:             {$stats['skipped']}\n";
echo "  [FAIL] Failed:              {$stats['failed']}\n";
echo "  Time elapsed:            " . formatSeconds($totalTime) . "\n";
echo "  Average rate:            " . number_format($stats['total'] / max($totalTime, 1), 2) . " units/sec\n";
echo str_repeat('-', 80) . "\n";

if (!empty($stats['errors']) && count($stats['errors']) <= 20) {
    echo "\nErrors:\n";
    foreach (array_slice($stats['errors'], 0, 20) as $error) {
        echo "  - $error\n";
    }
}

echo "\n[SUCCESS] Import process completed!\n\n";

// Helper function
function formatSeconds($seconds) {
    $seconds = (int)$seconds;
    if ($seconds < 60) return $seconds . 's';
    if ($seconds < 3600) return floor($seconds / 60) . 'm ' . ($seconds % 60) . 's';
    return floor($seconds / 3600) . 'h ' . floor(($seconds % 3600) / 60) . 'm';
}
