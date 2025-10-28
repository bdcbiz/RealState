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

echo "========================================\n";
echo "Fetching Compounds from Nawy API\n";
echo "========================================\n\n";

// Initialize cURL
$ch = curl_init();

// Set API endpoint - Nawy's GraphQL or REST API
// Based on Nawy.com structure, they likely use GraphQL
$apiUrl = 'https://www.nawy.com/api/graphql';

// Set up headers to mimic browser request
curl_setopt_array($ch, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'Accept: application/json',
        'Origin: https://www.nawy.com',
        'Referer: https://www.nawy.com/compounds'
    ],
]);

// GraphQL query to fetch all compounds
$graphqlQuery = [
    'query' => '
        query GetCompounds($limit: Int!, $offset: Int!) {
            compounds(limit: $limit, offset: $offset) {
                id
                name
                nameAr
                slug
                location {
                    name
                    nameAr
                }
                developer {
                    id
                    name
                    nameAr
                    logo
                }
                coverImage
                description
                descriptionAr
                developerStartPrice
                resaleStartPrice
                propertyTypes
                financingYears
                maxInstallmentYears
            }
        }
    ',
    'variables' => [
        'limit' => 100,
        'offset' => 0
    ]
];

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($graphqlQuery));
curl_setopt($ch, CURLOPT_POST, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

if ($error) {
    echo "✗ cURL Error: $error\n";
    echo "HTTP Code: $httpCode\n\n";

    // If GraphQL doesn't work, try REST API
    echo "Trying alternative REST API endpoint...\n";
    curl_setopt($ch, CURLOPT_URL, 'https://www.nawy.com/api/compounds?limit=100&offset=0');
    curl_setopt($ch, CURLOPT_POST, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
}

curl_close($ch);

echo "Response Code: $httpCode\n";

if ($httpCode === 200) {
    $data = json_decode($response, true);

    if ($data) {
        echo "✓ Successfully fetched data from API\n";
        echo "Response structure:\n";
        print_r(array_keys($data));
        echo "\n";

        // Save response to file for analysis
        file_put_contents(__DIR__ . '/nawy_api_response.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "✓ Response saved to nawy_api_response.json\n";
    } else {
        echo "✗ Failed to parse JSON response\n";
        echo "Raw response (first 1000 chars):\n";
        echo substr($response, 0, 1000) . "\n";
    }
} else {
    echo "✗ API request failed\n";
    echo "Response (first 1000 chars):\n";
    echo substr($response, 0, 1000) . "\n\n";

    echo "========================================\n";
    echo "Alternative: Manual Data Collection\n";
    echo "========================================\n\n";

    echo "Since the API might not be publicly accessible, here are your options:\n\n";
    echo "1. Browser Console Method:\n";
    echo "   - Go to: https://www.nawy.com/compounds\n";
    echo "   - Open browser console (F12)\n";
    echo "   - Scroll to load all 1345 compounds\n";
    echo "   - Run this JavaScript:\n\n";
    echo "   const html = document.documentElement.outerHTML;\n";
    echo "   const blob = new Blob([html], {type: 'text/html'});\n";
    echo "   const a = document.createElement('a');\n";
    echo "   a.href = URL.createObjectURL(blob);\n";
    echo "   a.download = 'nawy_compounds_full.html';\n";
    echo "   a.click();\n\n";
    echo "   - Save the downloaded file and upload it to the server\n\n";

    echo "2. Network Tab Method:\n";
    echo "   - Go to: https://www.nawy.com/compounds\n";
    echo "   - Open DevTools > Network tab\n";
    echo "   - Filter by 'Fetch/XHR'\n";
    echo "   - Scroll the page to trigger API calls\n";
    echo "   - Find GraphQL or API requests\n";
    echo "   - Copy the request details and provide them to me\n\n";
}

echo "\n✓ Done!\n";
