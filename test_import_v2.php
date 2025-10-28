<?php

/**
 * Test Import Script V2
 * Tests the import process with curl and better error handling
 */

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

// Test URLs (first 3)
$testUrls = [
    'https://www.nawy.com/compound/980-d-line',
    'https://www.nawy.com/compound/258-o-west-orascom',
    'https://www.nawy.com/compound/1648-crysta'
];

echo "Testing with " . count($testUrls) . " compounds\n\n";

foreach ($testUrls as $index => $url) {
    $current = $index + 1;
    echo "[$current/3] Processing: $url\n";

    try {
        // Fetch page using curl
        echo "  → Fetching page...\n";
        $html = fetchPage($url);
        if (!$html) {
            echo "  ✗ Failed to fetch page\n\n";
            continue;
        }
        echo "  ✓ Page fetched (" . strlen($html) . " bytes)\n";

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
        echo "\n  📊 Data Summary:\n";
        echo "  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "  Name:           " . $compound['name'] . "\n";
        echo "  Developer:      " . $compound['developerName'] . "\n";
        echo "  Developer ID:   " . ($companyId ?? 'NOT FOUND') . "\n";
        echo "  Location:       " . ($compound['areaName'] ?? 'N/A') . "\n";
        echo "  Images:         " . count($compound['images'] ?? []) . "\n";
        echo "  Master Plan:    " . (!empty($compound['masterPlan']) ? 'Yes' : 'No') . "\n";
        echo "  Coordinates:    " . ($compound['lat'] ?? 'N/A') . ", " . ($compound['long'] ?? 'N/A') . "\n";
        echo "  Properties:     " . ($compound['propertiesCount'] ?? 0) . " units\n";
        echo "  Start Price:    " . number_format($compound['prices']['developerStartingPrice'] ?? 0) . " " . ($compound['prices']['currency'] ?? 'EGP') . "\n";
        echo "  Amenities:      " . count($compound['amenities'] ?? []) . "\n";
        echo "  Payment Plans:  " . count($compound['paymentPlans'] ?? []) . "\n";
        echo "  Description:    " . mb_substr(strip_tags($compound['description'] ?? ''), 0, 100) . "...\n";
        echo "  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

        // Test image download (first image only)
        if (!empty($compound['images'][0])) {
            echo "\n  → Testing image download...\n";
            $imageUrl = $compound['images'][0];
            $imageData = fetchPage($imageUrl);
            if ($imageData !== false && strlen($imageData) > 0) {
                echo "  ✓ Successfully downloaded image (" . number_format(strlen($imageData)) . " bytes)\n";
            } else {
                echo "  ✗ Failed to download image\n";
            }
        }

        echo "\n" . str_repeat('═', 80) . "\n\n";

    } catch (Exception $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n\n";
    }

    // Sleep between requests
    sleep(2);
}

echo "\n✅ Test completed!\n";

/**
 * Fetch page using curl with proper headers
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
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.9,ar;q=0.8',
            'Accept-Encoding: gzip, deflate, br',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1',
            'Cache-Control: max-age=0'
        ],
        CURLOPT_ENCODING => '',
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($error) {
        echo "  ✗ Curl error: $error\n";
        return false;
    }

    if ($httpCode !== 200) {
        echo "  ✗ HTTP error: $httpCode\n";
        return false;
    }

    return $response;
}

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
