<?php

/**
 * ============================================================================
 * Master Plan Migration Script
 * ============================================================================
 *
 * This script extracts master plan images from the images JSON array
 * and moves them to the dedicated master_plan field
 *
 * Usage: php migrate_master_plans.php
 * ============================================================================
 */

// Database configuration
$dbHost = 'localhost';
$dbName = 'real_state';
$dbUser = 'laravel';
$dbPass = 'laravel123';

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════════════════╗\n";
echo "║                   Master Plan Migration Script                           ║\n";
echo "╚═══════════════════════════════════════════════════════════════════════════╝\n\n";

// Database connection
try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to database\n";
} catch(PDOException $e) {
    die("✗ Database connection failed: " . $e->getMessage() . "\n");
}

// Get all compounds with images
echo "✓ Fetching compounds with images...\n";
$stmt = $pdo->query("SELECT id, images FROM compounds WHERE images IS NOT NULL");
$compounds = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total = count($compounds);

echo "✓ Found $total compounds with images\n\n";

$stats = [
    'processed' => 0,
    'migrated' => 0,
    'no_master_plan' => 0,
    'errors' => 0
];

echo str_repeat('═', 80) . "\n\n";

// Process each compound
foreach ($compounds as $index => $compound) {
    $stats['processed']++;
    $current = $index + 1;
    $percent = round(($current / $total) * 100);

    echo "[$current/$total] [$percent%] Processing Compound ID: {$compound['id']}\n";

    try {
        // Decode images array
        $images = json_decode($compound['images'], true);

        if (!is_array($images) || empty($images)) {
            echo "  ⊘ No images found\n\n";
            $stats['no_master_plan']++;
            continue;
        }

        // Find master plan
        $masterPlan = null;
        $remainingImages = [];

        foreach ($images as $imagePath) {
            // Check if this is a master plan
            if (stripos($imagePath, 'masterplan') !== false) {
                $masterPlan = $imagePath;
                echo "  → Found master plan: " . basename($imagePath) . "\n";
            } else {
                $remainingImages[] = $imagePath;
            }
        }

        // Update database if master plan was found
        if ($masterPlan !== null) {
            echo "  → Migrating master plan to separate field...\n";

            $updateStmt = $pdo->prepare("
                UPDATE compounds
                SET master_plan = :master_plan,
                    images = :images,
                    updated_at = NOW()
                WHERE id = :id
            ");

            $updateStmt->execute([
                'master_plan' => $masterPlan,
                'images' => !empty($remainingImages) ? json_encode($remainingImages) : null,
                'id' => $compound['id']
            ]);

            echo "  ✓ Master plan migrated successfully\n";
            echo "  ✓ Images array updated (" . count($images) . " → " . count($remainingImages) . " images)\n\n";
            $stats['migrated']++;
        } else {
            echo "  ⊘ No master plan found in images\n\n";
            $stats['no_master_plan']++;
        }

    } catch (Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n\n";
        $stats['errors']++;
    }
}

// Final report
echo "\n";
echo "╔═══════════════════════════════════════════════════════════════════════════╗\n";
echo "║                         MIGRATION COMPLETED                               ║\n";
echo "╚═══════════════════════════════════════════════════════════════════════════╝\n\n";

echo "📊 Statistics:\n";
echo str_repeat('─', 80) . "\n";
echo "  Total processed:               {$stats['processed']}\n";
echo "  ✓ Master plans migrated:       {$stats['migrated']}\n";
echo "  ⊘ No master plan found:        {$stats['no_master_plan']}\n";
echo "  ✗ Errors:                      {$stats['errors']}\n";
echo str_repeat('─', 80) . "\n\n";

// Verification query
echo "🔍 Verification:\n";
echo str_repeat('─', 80) . "\n";

$verifyStmt = $pdo->query("
    SELECT
        COUNT(*) as total,
        COUNT(CASE WHEN master_plan IS NOT NULL THEN 1 END) as with_master_plan,
        COUNT(CASE WHEN images IS NOT NULL THEN 1 END) as with_images
    FROM compounds
");
$verify = $verifyStmt->fetch(PDO::FETCH_ASSOC);

echo "  Total compounds:               {$verify['total']}\n";
echo "  Compounds with master plan:    {$verify['with_master_plan']}\n";
echo "  Compounds with images:         {$verify['with_images']}\n";
echo str_repeat('─', 80) . "\n\n";

echo "✅ Migration completed successfully!\n\n";
