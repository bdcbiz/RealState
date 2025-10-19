<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // Get database name
    $database = env('DB_DATABASE', 'real_state2');

    echo "Converting all tables in database: {$database}\n";
    echo "=========================================\n\n";

    // Get all tables
    $tables = DB::select("SHOW TABLES");
    $tableKey = "Tables_in_{$database}";

    $successCount = 0;
    $errorCount = 0;

    foreach ($tables as $table) {
        $tableName = $table->$tableKey;

        echo "Converting table: {$tableName}...";

        try {
            // Convert table to utf8mb4
            DB::statement("ALTER TABLE `{$tableName}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo " ✓ SUCCESS\n";
            $successCount++;
        } catch (Exception $e) {
            echo " ✗ FAILED: " . $e->getMessage() . "\n";
            $errorCount++;
        }
    }

    echo "\n=========================================\n";
    echo "Conversion complete!\n";
    echo "Success: {$successCount} tables\n";
    echo "Failed: {$errorCount} tables\n";

    // Also convert the database default charset
    echo "\nConverting database default charset...\n";
    try {
        DB::statement("ALTER DATABASE `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "Database charset converted successfully!\n";
    } catch (Exception $e) {
        echo "Failed to convert database charset: " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
