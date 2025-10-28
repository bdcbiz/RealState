<?php

/**
 * Test Import Script
 * Tests the import process with first 5 compounds only
 */

require __DIR__.'/vendor/autoload.php';

// Database configuration
$dbHost = 'localhost';
$dbName = 'real_state';
$dbUser = 'laravel';
$dbPass = 'laravel123';

// Connect to database
try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to database\n";
} catch(PDOException $e) {
    die("✗ Database connection failed: " . $e->getMessage() . "\n");
}

// Load companies for developer matching
$companies = [];
$stmt = $pdo->query("SELECT id, name, name_ar, name_en FROM companies");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $companies[] = $row;
}
echo "✓ Loaded " . count($companies) . " companies from database\n\n";

// Test URLs (first 5)
$testUrls = [
    'https://www.nawy.com/compound/980-d-line',
    'https://www.nawy.com/compound/258-o-west-orascom',
    'https://www.nawy.com/compound/1648-crysta',
    'https://www.nawy.com/compound/3-new-giza',
    'https://www.nawy.com/compound/764-city-gate'
];

echo "Testing with " . count($testUrls) . " compounds\n\n";

foreach ($testUrls as $index => $url) {
    $current = $index + 1;
    echo "[$current/5] Processing: $url\n";

    try {
        // Fetch page
        echo "  → Fetching page...\n";
        $html = @file_get_contents($url);
        if (!$html) {
            echo "  ✗ Failed to fetch page\n\n";
            continue;
        }

        // Extract JSON data
        echo "  → Extracting JSON data...\n";
        if (!preg_match('/<script id="__NEXT_DATA__" type="application\/json">(.*?)<\/script>/s', $html, $matches)) {
            echo "  ✗ Could not find JSON data\n\n";
            continue;
        }

        $jsonData = json_decode($matches[1], true);
        $compound = $jsonData['props']['pageProps']['compound'] ?? null;

        if (!$compound) {
            echo "  ✗ No compound data found\n\n";
            continue;
        }

        echo "  ✓ Found compound: " . $compound['name'] . "\n";

        // Match developer
        echo "  → Matching developer: " . $compound['developerName'] . "\n";
        $companyId = findCompanyId($compound['developerName'], $companies);
        if ($companyId) {
            echo "  ✓ Matched with company ID: $companyId\n";
        } else {
            echo "  ⚠ Developer not found in database\n";
        }

        // Display data summary
        echo "\n  Data Summary:\n";
        echo "  - Name: " . $compound['name'] . "\n";
        echo "  - Developer: " . $compound['developerName'] . "\n";
        echo "  - Location: " . ($compound['areaName'] ?? 'N/A') . "\n";
        echo "  - Images: " . count($compound['images'] ?? []) . "\n";
        echo "  - Master Plan: " . (!empty($compound['masterPlan']) ? 'Yes' : 'No') . "\n";
        echo "  - Coordinates: " . ($compound['lat'] ?? 'N/A') . ", " . ($compound['long'] ?? 'N/A') . "\n";
        echo "  - Properties Count: " . ($compound['propertiesCount'] ?? 0) . "\n";
        echo "  - Starting Price: " . ($compound['prices']['developerStartingPrice'] ?? 'N/A') . " " . ($compound['prices']['currency'] ?? '') . "\n";

        // Test image download (first image only)
        if (!empty($compound['images'][0])) {
            echo "\n  → Testing image download...\n";
            $imageUrl = $compound['images'][0];
            echo "  URL: $imageUrl\n";

            $imageData = @file_get_contents($imageUrl);
            if ($imageData !== false) {
                echo "  ✓ Successfully downloaded image (" . strlen($imageData) . " bytes)\n";
            } else {
                echo "  ✗ Failed to download image\n";
            }
        }

        echo "\n" . str_repeat('-', 60) . "\n\n";

    } catch (Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n\n";
    }

    // Sleep between requests
    sleep(1);
}

echo "\nTest completed!\n";

/**
 * Find company ID by matching developer name
 */
function findCompanyId($developerName, $companies) {
    if (empty($developerName)) {
        return null;
    }

    $developerName = trim($developerName);

    // Try exact match first
    foreach ($companies as $company) {
        if (strcasecmp($company['name'], $developerName) === 0 ||
            strcasecmp($company['name_en'], $developerName) === 0 ||
            strcasecmp($company['name_ar'], $developerName) === 0) {
            return $company['id'];
        }
    }

    // Try partial match
    foreach ($companies as $company) {
        if (stripos($company['name'], $developerName) !== false ||
            stripos($developerName, $company['name']) !== false ||
            stripos($company['name_en'], $developerName) !== false ||
            stripos($developerName, $company['name_en']) !== false) {
            return $company['id'];
        }
    }

    // Try fuzzy match (remove common words)
    $cleanDeveloperName = preg_replace('/\b(developments?|real estate|properties|group|holding|egypt)\b/i', '', $developerName);
    $cleanDeveloperName = trim(preg_replace('/\s+/', ' ', $cleanDeveloperName));

    foreach ($companies as $company) {
        $cleanCompanyName = preg_replace('/\b(developments?|real estate|properties|group|holding|للتطوير العقاري|شركة)\b/i', '', $company['name']);
        $cleanCompanyName = trim(preg_replace('/\s+/', ' ', $cleanCompanyName));

        if (strcasecmp($cleanDeveloperName, $cleanCompanyName) === 0 ||
            stripos($cleanCompanyName, $cleanDeveloperName) !== false ||
            stripos($cleanDeveloperName, $cleanCompanyName) !== false) {
            return $company['id'];
        }
    }

    return null;
}
