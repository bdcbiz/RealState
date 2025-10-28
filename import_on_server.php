<?php
/**
 * Import script to run ON THE SERVER
 * Upload this file and Excel file to server, then run via SSH
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '1024M');
ini_set('max_execution_time', '600');

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📊 IMPORT TO all_data TABLE (Running on Server)\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

if ($argc < 2) {
    echo "❌ Error: Please provide the Excel file path\n";
    echo "Usage: php import_on_server.php file.xlsx\n";
    exit(1);
}

$filePath = $argv[1];

if (!file_exists($filePath)) {
    echo "❌ Error: File not found: $filePath\n";
    exit(1);
}

echo "📁 File: $filePath\n";
echo "⏰ Started: " . date('Y-m-d H:i:s') . "\n\n";

// Load Composer autoload
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
} elseif (file_exists('/var/www/realestate/vendor/autoload.php')) {
    require '/var/www/realestate/vendor/autoload.php';
} else {
    echo "❌ Composer autoload not found!\n";
    exit(1);
}

use PhpOffice\PhpSpreadsheet\IOFactory;

// Connect to LOCAL MySQL (on the same server)
try {
    echo "🔌 Connecting to MySQL...\n";
    $pdo = new PDO(
        'mysql:host=localhost;port=3306;dbname=real_state;charset=utf8mb4',
        'laravel',
        'laravel123',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    echo "✅ Connected successfully!\n\n";

} catch (PDOException $e) {
    echo "❌ Failed to connect to database!\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

try {
    echo "📖 Loading Excel file...\n";
    $spreadsheet = IOFactory::load($filePath);
    $worksheet = $spreadsheet->getActiveSheet();

    $highestRow = $worksheet->getHighestRow();
    $highestColumn = $worksheet->getHighestColumn();

    echo "📊 Found $highestRow rows\n\n";

    // Get headers from row 1
    $headers = [];
    foreach ($worksheet->getRowIterator(1, 1) as $row) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        foreach ($cellIterator as $cell) {
            $value = $cell->getValue();
            $header = strtolower(str_replace([' ', '(', ')'], ['_', '', ''], $value));
            $headers[] = $header;
        }
    }

    echo "📋 Headers: " . count($headers) . " columns\n\n";

    $importedCount = 0;
    $skippedCount = 0;
    $errorCount = 0;

    echo "🔄 Importing data...\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

    $validColumns = [
        'unit_code', 'unit_name', 'unit_type', 'usage_type', 'category',
        'floor', 'view', 'bedrooms', 'bathrooms', 'living_rooms',
        'built_up_area', 'land_area', 'garden_area', 'roof_area',
        'terrace_area', 'basement_area', 'garage_area', 'total_area',
        'normal_price', 'cash_price', 'price_per_meter', 'down_payment',
        'monthly_installment', 'over_years', 'finishing_type', 'finishing_specs',
        'finishing_price', 'status', 'availability', 'is_featured', 'is_available',
        'delivery_date', 'delivered_at', 'planned_delivery_date', 'actual_delivery_date',
        'completion_progress', 'model', 'phase', 'building_number', 'unit_number',
        'description', 'description_ar', 'features', 'amenities', 'unit_images',
        'floor_plan_image', 'project_name', 'project_name_ar', 'compound_location',
        'compound_city', 'compound_area', 'compound_description', 'compound_description_ar',
        'compound_latitude', 'compound_longitude', 'master_plan_image', 'compound_images',
        'company_name', 'company_name_ar', 'company_email', 'company_phone',
        'company_website', 'company_address', 'sales_id', 'buyer_id',
        'discount', 'total_price_after_discount'
    ];

    for ($row = 2; $row <= $highestRow; $row++) {
        try {
            $rowData = $worksheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, true, false)[0];

            if (empty(array_filter($rowData))) {
                $skippedCount++;
                continue;
            }

            $data = [];
            foreach ($headers as $index => $header) {
                if (in_array($header, $validColumns)) {
                    $value = isset($rowData[$index]) ? $rowData[$index] : null;
                    $data[$header] = $value;
                }
            }

            // Convert date formats from DD-MM-YYYY to YYYY-MM-DD
            $dateFields = ['delivery_date', 'delivered_at', 'planned_delivery_date', 'actual_delivery_date'];
            foreach ($dateFields as $field) {
                if (!empty($data[$field]) && is_string($data[$field])) {
                    // Try to parse DD-MM-YYYY format
                    if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $data[$field], $matches)) {
                        $data[$field] = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
                    }
                }
            }

            // Set default values for NOT NULL columns
            $data['is_featured'] = $data['is_featured'] ?? 0;
            $data['is_available'] = $data['is_available'] ?? 1;
            $data['bedrooms'] = $data['bedrooms'] ?? 0;
            $data['bathrooms'] = $data['bathrooms'] ?? 0;

            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');

            $columns = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            $sql = "INSERT INTO all_data ($columns) VALUES ($placeholders)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_values($data));

            $importedCount++;

            if ($importedCount % 100 == 0) {
                echo "✓ Imported $importedCount rows...\n";
            }

        } catch (\Exception $e) {
            $errorCount++;
            if ($errorCount <= 3) {
                echo "⚠️  Row $row: " . substr($e->getMessage(), 0, 80) . "\n";
            }
        }
    }

    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    echo "✅ IMPORT COMPLETED!\n\n";
    echo "📊 Statistics:\n";
    echo "   - Total rows: " . ($highestRow - 1) . "\n";
    echo "   - Imported: $importedCount\n";
    echo "   - Skipped: $skippedCount\n";
    echo "   - Errors: $errorCount\n\n";
    echo "⏰ Finished: " . date('Y-m-d H:i:s') . "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

} catch (\Exception $e) {
    echo "\n❌ IMPORT FAILED!\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
