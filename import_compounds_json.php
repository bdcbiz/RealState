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

// Read JSON file
$jsonFile = __DIR__ . '/nawy_compounds_data.json';
if (!file_exists($jsonFile)) {
    die("Error: JSON file not found. Please run the browser extraction script first.\n");
}

$jsonData = file_get_contents($jsonFile);
$compounds = json_decode($jsonData, true);

if (!$compounds) {
    die("Error: Failed to parse JSON file.\n");
}

echo "========================================\n";
echo "Importing Compounds from JSON\n";
echo "========================================\n\n";
echo "Found " . count($compounds) . " compounds in JSON file\n\n";

$processedCount = 0;
$addedCount = 0;
$skippedCount = 0;
$errorCount = 0;
$errors = [];

foreach ($compounds as $index => $compound) {
    try {
        $compoundName = $compound['name'] ?? '';
        $location = $compound['location'] ?? '';
        $developerName = $compound['developer'] ?? '';

        if (empty($compoundName)) {
            echo "Skipping compound #" . ($index + 1) . ": No name found\n";
            $skippedCount++;
            continue;
        }

        $processedCount++;

        if ($processedCount % 50 === 0) {
            echo "\nProcessed $processedCount compounds...\n";
        }

        // Find or create company/developer
        $companyId = null;
        if (!empty($developerName)) {
            $stmt = $pdo->prepare("SELECT id FROM companies WHERE name = ? OR name_en = ? LIMIT 1");
            $stmt->execute([$developerName, $developerName]);
            $company = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$company) {
                // Create new company
                $stmt = $pdo->prepare("
                    INSERT INTO companies (name, name_en, name_ar, logo, email, password, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");

                $email = strtolower(str_replace([' ', '.', '-'], '', $developerName)) . '@developer.com';
                $password = password_hash('password123', PASSWORD_BCRYPT);
                $logo = $compound['logo'] ?? '';

                $stmt->execute([
                    $developerName,
                    $developerName,
                    $developerName,
                    $logo,
                    $email,
                    $password
                ]);

                $companyId = $pdo->lastInsertId();

                if ($processedCount <= 10) {
                    echo "  Created new developer: $developerName (ID: $companyId)\n";
                }
            } else {
                $companyId = $company['id'];
            }
        }

        // Check if compound already exists
        $stmt = $pdo->prepare("SELECT id FROM compounds WHERE project = ? AND location = ? LIMIT 1");
        $stmt->execute([$compoundName, $location]);
        $existing = $stmt->fetch();

        if ($existing) {
            $skippedCount++;
            if ($processedCount <= 10) {
                echo "  Skipped (exists): $compoundName\n";
            }
            continue;
        }

        // Prepare images JSON
        $images = [];
        if (!empty($compound['coverImage'])) {
            $images[] = $compound['coverImage'];
        }
        $imagesJson = !empty($images) ? json_encode($images) : null;

        // Prepare location URL
        $locationUrl = !empty($compound['url']) ? 'https://www.nawy.com' . $compound['url'] : null;

        // Insert compound
        $stmt = $pdo->prepare("
            INSERT INTO compounds (
                company_id, project, project_en, project_ar,
                location, location_en, location_ar, location_url,
                images, finish_specs, status, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'in_progress', NOW(), NOW())
        ");

        $stmt->execute([
            $companyId,
            $compoundName,
            $compoundName,
            $compoundName,
            $location,
            $location,
            $location,
            $locationUrl,
            $imagesJson,
            $compound['propertyTypes'] ?? null
        ]);

        $addedCount++;

        if ($processedCount <= 10) {
            echo "  ✓ Added: $compoundName ($location)\n";
        }

    } catch (Exception $e) {
        $errorCount++;
        $errors[] = "Compound #" . ($index + 1) . " ($compoundName): " . $e->getMessage();

        if ($processedCount <= 10) {
            echo "  ✗ Error: {$e->getMessage()}\n";
        }
    }
}

echo "\n========================================\n";
echo "Import Summary\n";
echo "========================================\n";
echo "Total in JSON file: " . count($compounds) . "\n";
echo "Processed: $processedCount\n";
echo "Successfully added: $addedCount\n";
echo "Skipped (already exist): $skippedCount\n";
echo "Errors: $errorCount\n";

if ($errorCount > 0 && $errorCount <= 20) {
    echo "\nError details:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
} elseif ($errorCount > 20) {
    echo "\nShowing first 20 errors:\n";
    for ($i = 0; $i < 20; $i++) {
        echo "  - {$errors[$i]}\n";
    }
}

// Show sample of added compounds
echo "\n========================================\n";
echo "Sample of Added Compounds\n";
echo "========================================\n";

$stmt = $pdo->query("
    SELECT c.id, c.project, c.location, co.name as developer
    FROM compounds c
    LEFT JOIN companies co ON c.company_id = co.id
    ORDER BY c.id DESC
    LIMIT 10
");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID {$row['id']}: {$row['project']} - {$row['location']} (Developer: {$row['developer']})\n";
}

echo "\n✓ Import complete!\n";
