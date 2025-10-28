<?php

// Database connection
$host = '127.0.0.1';
$db = 'real_state';
$user = 'laravel';
$pass = 'laravel123';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to database successfully\n\n";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

// Read HTML file
$htmlFile = __DIR__ . '/nawy_filters_full.html';
if (!file_exists($htmlFile)) {
    die("Error: Please save the filters HTML content to 'nawy_filters_full.html' file first.\n");
}

$html = file_get_contents($htmlFile);

// Create DOMDocument
$dom = new DOMDocument();
libxml_use_internal_errors(true);
$dom->loadHTML($html);
libxml_clear_errors();
$xpath = new DOMXPath($dom);

echo "========================================\n";
echo "Extracting Developers from Filters\n";
echo "========================================\n\n";

// Extract developers from filters
$developerNodes = $xpath->query("//div[@id='developers']//input[@type='checkbox']");
$developers = [];
$addedDevelopers = 0;
$existingDevelopers = 0;

foreach ($developerNodes as $node) {
    $developerId = $node->getAttribute('id');
    $developerName = $developerId; // The id contains the developer name

    if (empty($developerName)) {
        continue;
    }

    $developers[] = $developerName;

    echo "Processing: $developerName\n";

    // Check if developer already exists
    $stmt = $pdo->prepare("SELECT id, name FROM companies WHERE name = ? OR name_en = ? LIMIT 1");
    $stmt->execute([$developerName, $developerName]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        echo "  ⚠ Already exists (ID: {$existing['id']})\n\n";
        $existingDevelopers++;
        continue;
    }

    // Insert new developer
    try {
        $stmt = $pdo->prepare("
            INSERT INTO companies (name, name_en, name_ar, email, password, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())
        ");

        $email = strtolower(str_replace(' ', '', $developerName)) . '@developer.com';
        $password = password_hash('password123', PASSWORD_BCRYPT);

        $stmt->execute([
            $developerName,
            $developerName,
            $developerName,
            $email,
            $password
        ]);

        $companyId = $pdo->lastInsertId();
        echo "  ✓ Added successfully (ID: $companyId)\n\n";
        $addedDevelopers++;

    } catch (PDOException $e) {
        echo "  ✗ Error: " . $e->getMessage() . "\n\n";
    }
}

echo "\n========================================\n";
echo "Extracting Areas from Filters\n";
echo "========================================\n\n";

// Extract areas from filters
$areaNodes = $xpath->query("//div[@id='areas']//input[@type='checkbox']");
$areas = [];

foreach ($areaNodes as $node) {
    $areaId = $node->getAttribute('id');
    $areaName = $areaId; // The id contains the area name

    if (empty($areaName)) {
        continue;
    }

    $areas[] = $areaName;
    echo "  • $areaName\n";
}

// Save areas to a JSON file for later use
$areasJson = json_encode($areas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
file_put_contents(__DIR__ . '/areas_list.json', $areasJson);

echo "\n✓ Areas saved to areas_list.json\n";

echo "\n========================================\n";
echo "Summary\n";
echo "========================================\n";
echo "Developers found: " . count($developers) . "\n";
echo "  - Added: $addedDevelopers\n";
echo "  - Already existed: $existingDevelopers\n";
echo "\nAreas found: " . count($areas) . "\n";
echo "\n✓ Done!\n";
