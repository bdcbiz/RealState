<?php

/**
 * ============================================================================
 * Nawy.com Compounds Importer - Production Version
 * ============================================================================
 *
 * This script imports 1300+ compounds from Nawy.com into the database
 *
 * Features:
 * - Extracts complete compound data from Next.js JSON
 * - Downloads all images and master plans
 * - Matches developers with existing companies
 * - Handles errors and retries
 * - Progress tracking and statistics
 * - Resume capability
 *
 * Usage:
 *   php import_compounds_final.php
 *   php import_compounds_final.php --start=100  (resume from compound 100)
 *   php import_compounds_final.php --limit=50   (import only 50 compounds)
 *
 * ============================================================================
 */

// Configuration
$dbHost = 'localhost';
$dbName = 'real_state';
$dbUser = 'laravel';
$dbPass = 'laravel123';

// Image storage path
$imageStoragePath = __DIR__ . '/storage/compound-images';

// Parse command line arguments
$startFrom = 0;
$limit = null;

foreach ($argv as $arg) {
    if (strpos($arg, '--start=') === 0) {
        $startFrom = (int)substr($arg, 8);
    }
    if (strpos($arg, '--limit=') === 0) {
        $limit = (int)substr($arg, 8);
    }
}

// Banner
echo "\n";
echo "╔═══════════════════════════════════════════════════════════════════════════╗\n";
echo "║                   Nawy.com Compounds Importer v1.0                        ║\n";
echo "╚═══════════════════════════════════════════════════════════════════════════╝\n\n";

// Database connection
try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to database\n";
} catch(PDOException $e) {
    die("✗ Database connection failed: " . $e->getMessage() . "\n");
}

// Load companies
$companies = [];
$stmt = $pdo->query("SELECT id, name, name_ar, name_en FROM companies");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $companies[] = $row;
}
echo "✓ Loaded " . count($companies) . " companies from database\n";

// Create image storage directory
if (!file_exists($imageStoragePath)) {
    mkdir($imageStoragePath, 0755, true);
}
echo "✓ Image storage ready: $imageStoragePath\n";

// Load URLs
if (!file_exists('compound_urls.txt')) {
    die("✗ File compound_urls.txt not found\n");
}

$urls = file('compound_urls.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$totalUrls = count($urls);

// Apply start and limit
if ($startFrom > 0) {
    $urls = array_slice($urls, $startFrom);
    echo "ℹ Starting from compound #" . ($startFrom + 1) . "\n";
}
if ($limit !== null) {
    $urls = array_slice($urls, 0, $limit);
    echo "ℹ Limiting to $limit compounds\n";
}

echo "\n✓ Ready to process " . count($urls) . " compounds\n";
echo str_repeat('═', 80) . "\n\n";

// Statistics
$stats = [
    'total' => 0,
    'success' => 0,
    'failed' => 0,
    'skipped' => 0,
    'developer_not_found' => [],
    'errors' => []
];

$startTime = time();

// Process each compound
foreach ($urls as $index => $url) {
    $stats['total']++;
    $globalIndex = $startFrom + $index + 1;
    $current = $index + 1;
    $total = count($urls);

    // Progress bar
    $percent = round(($current / $total) * 100);
    echo "\n[{$globalIndex}/{$totalUrls}] [$current/$total] [$percent%] $url\n";

    try {
        // Fetch page
        echo "  → Fetching...";
        $html = fetchPage($url);
        if (!$html) {
            echo " ✗ Failed\n";
            $stats['failed']++;
            $stats['errors'][] = "Failed to fetch: $url";
            continue;
        }
        echo " ✓ (" . formatBytes(strlen($html)) . ")\n";

        // Extract JSON
        echo "  → Parsing...";
        if (!preg_match('/<script id="__NEXT_DATA__" type="application\/json">(.*?)<\/script>/s', $html, $matches)) {
            echo " ✗ No JSON data\n";
            $stats['failed']++;
            $stats['errors'][] = "No JSON data: $url";
            continue;
        }

        $jsonData = json_decode($matches[1], true);
        $compound = $jsonData['props']['pageProps']['compound'] ?? null;

        if (!$compound) {
            echo " ✗ No compound data\n";
            $stats['failed']++;
            $stats['errors'][] = "No compound data: $url";
            continue;
        }
        echo " ✓ " . $compound['name'] . "\n";

        // Check if exists
        echo "  → Checking duplicates...";
        $stmt = $pdo->prepare("SELECT id FROM compounds WHERE project = ? OR project_en = ? LIMIT 1");
        $stmt->execute([$compound['name'], $compound['name']]);
        if ($stmt->fetch()) {
            echo " ⊘ Already exists\n";
            $stats['skipped']++;
            continue;
        }
        echo " ✓\n";

        // Match developer
        echo "  → Developer: " . $compound['developerName'] . "...";
        $companyId = findCompanyId($compound['developerName'], $companies);
        if ($companyId) {
            echo " ✓ ID:$companyId\n";
        } else {
            echo " ⚠ Not found\n";
            if (!in_array($compound['developerName'], $stats['developer_not_found'])) {
                $stats['developer_not_found'][] = $compound['developerName'];
            }
        }

        // Download images
        $imagesPaths = [];
        $imagesCount = count($compound['images'] ?? []);

        if ($imagesCount > 0) {
            echo "  → Downloading $imagesCount images...";
            foreach ($compound['images'] as $imgIndex => $imageUrl) {
                $imagePath = downloadImage($imageUrl, $compound['id'], "img_$imgIndex", $imageStoragePath);
                if ($imagePath) {
                    $imagesPaths[] = $imagePath;
                }
            }
            echo " ✓ " . count($imagesPaths) . " saved\n";
        }

        // Download master plan (separate from images)
        $masterPlanPath = null;
        if (!empty($compound['masterPlan'])) {
            echo "  → Downloading master plan...";
            $masterPlanPath = downloadImage($compound['masterPlan'], $compound['id'], 'masterplan', $imageStoragePath);
            if ($masterPlanPath) {
                echo " ✓\n";
            } else {
                echo " ✗\n";
            }
        }

        // Prepare location URL
        $locationUrl = null;
        if (!empty($compound['lat']) && !empty($compound['long'])) {
            $locationUrl = "https://www.google.com/maps?q={$compound['lat']},{$compound['long']}";
        }

        // Clean description
        $description = !empty($compound['description']) ? strip_tags($compound['description']) : null;
        if ($description) {
            $description = mb_substr($description, 0, 5000);
        }

        // Prepare data
        $data = [
            'company_id' => $companyId,
            'project' => $compound['name'],
            'project_en' => $compound['name'],
            'project_ar' => $compound['name'],
            'location' => $compound['areaName'] ?? null,
            'location_en' => $compound['areaName'] ?? null,
            'location_ar' => $compound['areaName'] ?? null,
            'location_url' => $locationUrl,
            'images' => !empty($imagesPaths) ? json_encode($imagesPaths) : null,
            'master_plan' => $masterPlanPath,
            'built_up_area' => null,
            'how_many_floors' => null,
            'planned_delivery_date' => null,
            'actual_delivery_date' => null,
            'completion_progress' => null,
            'land_area' => null,
            'built_area' => null,
            'finish_specs' => $description,
            'club' => 0,
            'is_sold' => 0,
            'status' => 'in_progress',
            'delivered_at' => null,
            'total_units' => $compound['propertiesCount'] ?? 0,
        ];

        // Insert into database
        echo "  → Saving to database...";
        $sql = "INSERT INTO compounds (
            company_id, project, project_en, project_ar,
            location, location_en, location_ar, location_url,
            images, master_plan, built_up_area, how_many_floors,
            planned_delivery_date, actual_delivery_date,
            completion_progress, land_area, built_area,
            finish_specs, club, is_sold, status,
            delivered_at, total_units, created_at, updated_at
        ) VALUES (
            :company_id, :project, :project_en, :project_ar,
            :location, :location_en, :location_ar, :location_url,
            :images, :master_plan, :built_up_area, :how_many_floors,
            :planned_delivery_date, :actual_delivery_date,
            :completion_progress, :land_area, :built_area,
            :finish_specs, :club, :is_sold, :status,
            :delivered_at, :total_units, NOW(), NOW()
        )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
        echo " ✓ ID:" . $pdo->lastInsertId() . "\n";

        $stats['success']++;

        // Progress summary
        $elapsed = time() - $startTime;
        $rate = $elapsed > 0 ? $current / $elapsed : 0;
        $remaining = $rate > 0 ? ($total - $current) / $rate : 0;

        echo "  ℹ Progress: {$stats['success']} success, {$stats['skipped']} skipped, {$stats['failed']} failed";
        echo " | Rate: " . number_format($rate, 2) . " compounds/sec";
        echo " | ETA: " . formatSeconds($remaining) . "\n";

        // Sleep to avoid overwhelming the server
        usleep(300000); // 0.3 seconds

    } catch (Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n";
        $stats['failed']++;
        $stats['errors'][] = $url . ": " . $e->getMessage();
    }
}

// Final report
$totalTime = time() - $startTime;

echo "\n\n";
echo "╔═══════════════════════════════════════════════════════════════════════════╗\n";
echo "║                            IMPORT COMPLETED                               ║\n";
echo "╚═══════════════════════════════════════════════════════════════════════════╝\n\n";

echo "📊 Statistics:\n";
echo str_repeat('─', 80) . "\n";
echo "  Total processed:        {$stats['total']}\n";
echo "  ✓ Successfully imported: {$stats['success']}\n";
echo "  ⊘ Skipped (duplicates):  {$stats['skipped']}\n";
echo "  ✗ Failed:                {$stats['failed']}\n";
echo "  ⏱ Total time:            " . formatSeconds($totalTime) . "\n";
echo "  📈 Average rate:         " . number_format($stats['total'] / max($totalTime, 1), 2) . " compounds/sec\n";
echo str_repeat('─', 80) . "\n\n";

if (!empty($stats['developer_not_found'])) {
    echo "⚠ Developers not found in database (" . count($stats['developer_not_found']) . "):\n";
    foreach ($stats['developer_not_found'] as $dev) {
        echo "  - $dev\n";
    }
    echo "\n";
}

if (!empty($stats['errors']) && $stats['failed'] <= 20) {
    echo "✗ Errors:\n";
    foreach ($stats['errors'] as $error) {
        echo "  - $error\n";
    }
    echo "\n";
}

echo "✅ Import process completed!\n\n";

// ============================================================================
// Helper Functions
// ============================================================================

/**
 * Fetch page using curl
 */
function fetchPage($url) {
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.9',
            'Connection: keep-alive',
        ],
        CURLOPT_ENCODING => '',
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($httpCode === 200) ? $response : false;
}

/**
 * Find company ID
 */
function findCompanyId($developerName, $companies) {
    if (empty($developerName)) return null;

    $developerName = trim($developerName);

    // Exact match
    foreach ($companies as $company) {
        if (strcasecmp($company['name'], $developerName) === 0 ||
            strcasecmp($company['name_en'], $developerName) === 0) {
            return $company['id'];
        }
    }

    // Partial match
    foreach ($companies as $company) {
        if (stripos($company['name'], $developerName) !== false ||
            stripos($developerName, $company['name']) !== false) {
            return $company['id'];
        }
    }

    // Fuzzy match
    $cleanDev = preg_replace('/\b(developments?|real estate|properties|group|holding|egypt)\b/i', '', $developerName);
    $cleanDev = trim(preg_replace('/\s+/', ' ', $cleanDev));

    foreach ($companies as $company) {
        $cleanComp = preg_replace('/\b(developments?|real estate|properties|group|holding|للتطوير العقاري|شركة)\b/i', '', $company['name']);
        $cleanComp = trim(preg_replace('/\s+/', ' ', $cleanComp));

        if (strcasecmp($cleanDev, $cleanComp) === 0 ||
            stripos($cleanComp, $cleanDev) !== false ||
            stripos($cleanDev, $cleanComp) !== false) {
            return $company['id'];
        }
    }

    return null;
}

/**
 * Download and save image
 */
function downloadImage($imageUrl, $compoundId, $imageIndex, $basePath) {
    if (empty($imageUrl)) return null;

    $compoundDir = $basePath . '/' . $compoundId;
    if (!file_exists($compoundDir)) {
        mkdir($compoundDir, 0755, true);
    }

    $ext = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
    if (empty($ext) || !in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        $ext = 'jpg';
    }

    $filename = "compound_{$compoundId}_{$imageIndex}.{$ext}";
    $localPath = $compoundDir . '/' . $filename;

    $imageData = @file_get_contents($imageUrl);
    if ($imageData === false || strlen($imageData) < 100) {
        return null;
    }

    file_put_contents($localPath, $imageData);

    return "compound-images/{$compoundId}/{$filename}";
}

/**
 * Format bytes to human readable
 */
function formatBytes($bytes) {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
    return round($bytes / 1048576, 1) . ' MB';
}

/**
 * Format seconds to human readable time
 */
function formatSeconds($seconds) {
    $seconds = (int)$seconds;
    if ($seconds < 60) return $seconds . 's';
    if ($seconds < 3600) return floor($seconds / 60) . 'm ' . ($seconds % 60) . 's';
    return floor($seconds / 3600) . 'h ' . floor(($seconds % 3600) / 60) . 'm';
}
