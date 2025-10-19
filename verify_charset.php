<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $database = env('DB_DATABASE', 'real_state2');

    echo "Verifying character set for database: {$database}\n";
    echo "=========================================\n\n";

    // Check database charset
    $dbCharset = DB::select("SELECT @@character_set_database as charset, @@collation_database as collation");
    echo "Database Charset: " . $dbCharset[0]->charset . "\n";
    echo "Database Collation: " . $dbCharset[0]->collation . "\n\n";

    // Check tables
    echo "Table Character Sets:\n";
    echo "---------------------\n";
    $tables = DB::select("SELECT TABLE_NAME, TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?", [$database]);

    foreach ($tables as $table) {
        $charset = strpos($table->TABLE_COLLATION, 'utf8mb4') !== false ? '✓' : '✗';
        echo "{$charset} {$table->TABLE_NAME}: {$table->TABLE_COLLATION}\n";
    }

    echo "\n=========================================\n";
    echo "Verification complete!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
