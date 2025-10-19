<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Exporting data...\n";

// Get all data
$companies = App\Models\Company::all()->toArray();
$compounds = App\Models\Compound::all()->toArray();
$units = App\Models\Unit::all()->toArray();
$sales = App\Models\Sale::all()->toArray();
$users = App\Models\User::all()->toArray();

echo "Companies: " . count($companies) . "\n";
echo "Compounds: " . count($compounds) . "\n";
echo "Units: " . count($units) . "\n";
echo "Sales: " . count($sales) . "\n";
echo "Users: " . count($users) . "\n";

// Save to JSON files
file_put_contents('storage/companies_data.json', json_encode($companies, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
file_put_contents('storage/compounds_data.json', json_encode($compounds, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
file_put_contents('storage/units_data.json', json_encode($units, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
file_put_contents('storage/sales_data.json', json_encode($sales, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
file_put_contents('storage/users_data.json', json_encode($users, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

echo "\nData exported successfully to storage folder!\n";
