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
$htmlFile = __DIR__ . '/nawy_full_compounds.html';
if (!file_exists($htmlFile)) {
    die("Error: Please save the HTML content to 'nawy_full_compounds.html' file first.\n");
}

$html = file_get_contents($htmlFile);

// Create DOMDocument
$dom = new DOMDocument();
libxml_use_internal_errors(true); // Suppress HTML5 warnings
$dom->loadHTML($html);
libxml_clear_errors();
$xpath = new DOMXPath($dom);

// Find all compound cards
$cards = $xpath->query("//a[contains(@href, '/compound/')]");

echo "Found " . $cards->length . " compounds\n\n";

$processedCompounds = 0;
$errors = [];

foreach ($cards as $card) {
    try {
        // Extract compound data
        $compoundUrl = $card->getAttribute('href');

        // Extract location
        $locationNodes = $xpath->query(".//div[@class='area']", $card);
        $location = '';
        if ($locationNodes->length > 0) {
            $location = trim($locationNodes->item(0)->textContent);
        }

        // Extract compound name
        $nameNode = $xpath->query(".//div[@class='name']", $card);
        $compoundName = $nameNode->length > 0 ? trim($nameNode->item(0)->textContent) : '';

        // Extract developer name from description
        $descNode = $xpath->query(".//h2[contains(@class, 'sc-4b9910fd-0') or contains(@class, 'hHfZHY')]", $card);
        $description = $descNode->length > 0 ? trim($descNode->item(0)->textContent) : '';

        // Parse developer name from "Discover X's Properties in..."
        $developerName = '';
        if (preg_match("/Discover\\s+(.+?)'s Properties/", $description, $matches)) {
            $developerName = trim($matches[1]);
        }

        // Extract financing years
        $financingYears = 0;
        if (preg_match("/(\\d+)\\s+Years/", $description, $matches)) {
            $financingYears = (int)$matches[1];
        }

        // Extract developer start price
        $devPriceNode = $xpath->query(".//span[@class='price']", $card);
        $developerPrice = $devPriceNode->length > 0 ? trim($devPriceNode->item(0)->textContent) : null;
        $developerPrice = $developerPrice ? str_replace(',', '', $developerPrice) : null;

        // Extract property types
        $propertyTypesNodes = $xpath->query(".//span[@class='property-type']", $card);
        $propertyTypes = [];
        foreach ($propertyTypesNodes as $ptNode) {
            $propertyTypes[] = trim($ptNode->textContent);
        }

        // Extract logo URL
        $logoNode = $xpath->query(".//div[contains(@class, 'logo-wrapper')]//img", $card);
        $logoUrl = $logoNode->length > 0 ? $logoNode->item(0)->getAttribute('src') : '';

        // Extract cover image
        $coverNode = $xpath->query(".//div[@class='cover-image']//img", $card);
        $coverImage = $coverNode->length > 0 ? $coverNode->item(0)->getAttribute('src') : '';

        if (empty($compoundName)) {
            $errors[] = "Skipped: No compound name found for URL: $compoundUrl";
            continue;
        }

        echo "Processing: $compoundName ($location)\n";
        echo "  Developer: $developerName\n";
        echo "  Developer Price: " . ($developerPrice ?? 'N/A') . "\n";
        echo "  Property Types: " . implode(', ', $propertyTypes) . "\n";
        echo "  Logo: $logoUrl\n";

        // Find or create company
        $company = null;
        if (!empty($developerName)) {
            $stmt = $pdo->prepare("SELECT * FROM companies WHERE name = ? OR name_en = ? LIMIT 1");
            $stmt->execute([$developerName, $developerName]);
            $company = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$company) {
                // Create new company
                $stmt = $pdo->prepare("
                    INSERT INTO companies (name, name_en, name_ar, logo, email, password, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");

                $email = strtolower(str_replace(' ', '', $developerName)) . '@developer.com';
                $password = password_hash('password123', PASSWORD_BCRYPT);

                $stmt->execute([
                    $developerName,
                    $developerName,
                    $developerName,
                    $logoUrl,
                    $email,
                    $password
                ]);

                $companyId = $pdo->lastInsertId();
                echo "  ✓ Created new company: $developerName (ID: $companyId)\n";
                $company = ['id' => $companyId];
            } else {
                echo "  ✓ Found existing company: {$company['name']} (ID: {$company['id']})\n";
            }
        }

        // Check if compound already exists
        $stmt = $pdo->prepare("SELECT id FROM compounds WHERE project = ? AND location = ? LIMIT 1");
        $stmt->execute([$compoundName, $location]);
        $existingCompound = $stmt->fetch();

        if ($existingCompound) {
            echo "  ⚠ Compound already exists, skipping...\n\n";
            continue;
        }

        // Prepare images JSON
        $images = [];
        if (!empty($coverImage)) {
            $images[] = $coverImage;
        }
        $imagesJson = !empty($images) ? json_encode($images) : null;

        // Insert compound
        $stmt = $pdo->prepare("
            INSERT INTO compounds (
                company_id, project, project_en, project_ar,
                location, location_en, location_ar, location_url,
                images, finish_specs, status, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'in_progress', NOW(), NOW())
        ");

        $stmt->execute([
            $company ? $company['id'] : null,
            $compoundName,
            $compoundName,
            $compoundName,
            $location,
            $location,
            $location,
            'https://www.nawy.com' . $compoundUrl,
            $imagesJson,
            !empty($propertyTypes) ? implode(', ', $propertyTypes) : null
        ]);

        $compoundId = $pdo->lastInsertId();
        echo "  ✓ Compound inserted with ID: $compoundId\n\n";
        $processedCompounds++;

    } catch (Exception $e) {
        $errors[] = "Error processing compound '$compoundName': " . $e->getMessage();
        echo "  ✗ Error: " . $e->getMessage() . "\n\n";
    }
}

echo "\n========================================\n";
echo "Summary:\n";
echo "========================================\n";
echo "Total compounds found: " . $cards->length . "\n";
echo "Successfully processed: $processedCompounds\n";
echo "Errors: " . count($errors) . "\n";

if (!empty($errors)) {
    echo "\nErrors:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
}

echo "\n✓ Done!\n";
