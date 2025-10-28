<?php

/**
 * ============================================================================
 * Units Import Script - PHP Version
 * ============================================================================
 *
 * Imports units from Excel to database
 *
 * Usage: php import_units.php
 * ============================================================================
 */

// Configuration
$dbHost = 'localhost';
$dbName = 'real_state';
$dbUser = 'laravel';
$dbPass = 'laravel123';

// Excel file path
$excelFile = '/var/www/realestate/units_data.xlsx';

// Project name to compound ID mapping
$projectMapping = [
    'Club Views' => 678,
    'Elan' => 571,
    'ELAN' => 571,
    'esse residence' => 572,
    'Esse Residence' => 572,
    'Origami' => 577,
    'ORIGAMI' => 577,
    'Rai' => 719,
    'RAI' => 719,
    'Rai Valleys' => 575,
    'Rai Views' => 574,
    'RAI VIEWS' => 574,
    'Sheya Residence' => 573,
    'Sheya residence' => 573,
    'Talala' => 796,
    'TALALA' => 796,
    'The Butterfly' => 601,
    'Zahw Assuit' => 1362,
];

echo "\n";
echo str_repeat('=', 80) . "\n";
echo "Units Import Script - PHP Version\n";
echo str_repeat('=', 80) . "\n\n";

// Check if file exists
if (!file_exists($excelFile)) {
    die("[ERROR] Excel file not found: $excelFile\n");
}

// Require Composer autoload for PHPSpreadsheet
require_once '/var/www/realestate/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Database connection
try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "[OK] Connected to database\n\n";
} catch (PDOException $e) {
    die("[ERROR] Database connection failed: " . $e->getMessage() . "\n");
}

// Load Excel file
echo "[1/4] Loading Excel file...\n";
try {
    $spreadsheet = IOFactory::load($excelFile);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();

    // Remove header row
    $header = array_shift($rows);

    $totalRows = count($rows);
    echo "[OK] Loaded $totalRows units from Excel\n\n";
} catch (Exception $e) {
    die("[ERROR] Failed to read Excel: " . $e->getMessage() . "\n");
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
    usage_type,
    usage_type_en,
    usage_type_ar,
    built_up_area,
    garden_area,
    roof_area,
    floor_number,
    number_of_beds,
    normal_price,
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
    :usage_type,
    :usage_type_en,
    :usage_type_ar,
    :built_up_area,
    :garden_area,
    :roof_area,
    :floor_number,
    :number_of_beds,
    :normal_price,
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

// Helper function to parse floor
function parseFloor($floorStr) {
    if (empty($floorStr)) return null;

    $floorStr = strtoupper(trim($floorStr));

    $floorMap = [
        'G' => 0,
        'GF' => 0,
        'GROUND' => 0,
        'V' => -1,  // Villa/Variable
        'P' => -2,  // Penthouse
        'R' => -3,  // Roof
    ];

    if (isset($floorMap[$floorStr])) {
        return $floorMap[$floorStr];
    }

    if (is_numeric($floorStr)) {
        return (int)$floorStr;
    }

    return null;
}

// Helper function to clean numeric value
function cleanNumeric($value) {
    if (empty($value)) return null;
    if (is_numeric($value)) return $value;

    // Remove currency symbols, spaces, and commas
    $cleaned = preg_replace('/[^0-9.]/', '', $value);

    return is_numeric($cleaned) ? (float)$cleaned : null;
}

// Import units
echo "\n[4/4] Importing units...\n";
echo str_repeat('=', 80) . "\n\n";

$startTime = time();

foreach ($rows as $rowIndex => $row) {
    $stats['total']++;

    try {
        // Extract data from row
        // Expected columns: Unit Name, Project, Usage Type, BUA, Garden Area, Roof Area, Floor, No. of Bedrooms, Nominal Price
        $unitName = $row[0] ?? '';
        $project = $row[1] ?? '';
        $usageType = $row[2] ?? '';
        $bua = cleanNumeric($row[3] ?? null);
        $gardenArea = cleanNumeric($row[4] ?? null);
        $roofArea = cleanNumeric($row[5] ?? null);
        $floor = $row[6] ?? '';
        $bedrooms = $row[7] ?? null;
        $price = cleanNumeric($row[8] ?? null);

        // Get compound ID
        $compoundId = $projectMapping[trim($project)] ?? null;

        if (!$compoundId) {
            $stats['skipped']++;
            $stats['errors'][] = "Row " . ($rowIndex + 2) . ": Project '$project' not found in mapping";
            continue;
        }

        // Parse floor
        $floorNumber = parseFloor($floor);

        // Execute insert
        $stmt->execute([
            'compound_id' => $compoundId,
            'unit_name' => $unitName,
            'unit_name_en' => $unitName,
            'unit_name_ar' => $unitName,
            'compound_name' => $project,
            'usage_type' => $usageType,
            'usage_type_en' => $usageType,
            'usage_type_ar' => $usageType,
            'built_up_area' => $bua,
            'garden_area' => $gardenArea,
            'roof_area' => $roofArea,
            'floor_number' => $floorNumber,
            'number_of_beds' => $bedrooms,
            'normal_price' => $price,
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
        $stats['errors'][] = "Row " . ($rowIndex + 2) . ": " . $e->getMessage();

        if ($stats['failed'] <= 10) {
            echo "[ERROR] Row " . ($rowIndex + 2) . ": " . $e->getMessage() . "\n";
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
