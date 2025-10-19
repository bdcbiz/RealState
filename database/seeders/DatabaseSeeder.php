<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use App\Models\Compound;
use App\Models\Unit;
use App\Models\Sale;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with complete data.
     * This seeder contains all the data from the production database.
     */
    public function run(): void
    {
        // Disable foreign key checks to avoid constraint errors
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear existing data
        $this->command->info('Clearing existing data...');
        DB::table('sales')->truncate();
        DB::table('units')->truncate();
        DB::table('compounds')->truncate();
        DB::table('companies')->truncate();
        DB::table('users')->truncate();

        // Load data from JSON files
        $this->command->info('Loading data from JSON files...');

        $dataPath = database_path('data');
        $companiesData = json_decode(file_get_contents($dataPath . '/companies_data.json'), true);
        $compoundsData = json_decode(file_get_contents($dataPath . '/compounds_data.json'), true);
        $unitsData = json_decode(file_get_contents($dataPath . '/units_data.json'), true);
        $salesData = json_decode(file_get_contents($dataPath . '/sales_data.json'), true);
        $usersData = json_decode(file_get_contents($dataPath . '/users_data.json'), true);

        // Seed Companies
        $this->command->info('Seeding companies...');
        foreach ($companiesData as $company) {
            Company::create($company);
        }
        $this->command->info('✓ Seeded ' . count($companiesData) . ' companies');

        // Seed Compounds
        $this->command->info('Seeding compounds...');
        foreach ($compoundsData as $compound) {
            Compound::create($compound);
        }
        $this->command->info('✓ Seeded ' . count($compoundsData) . ' compounds');

        // Seed Units (in chunks to handle large data)
        $this->command->info('Seeding units...');
        $unitsChunks = array_chunk($unitsData, 500);
        foreach ($unitsChunks as $index => $chunk) {
            foreach ($chunk as $unit) {
                Unit::create($unit);
            }
            $this->command->info('  Processed ' . (($index + 1) * 500) . ' units...');
        }
        $this->command->info('✓ Seeded ' . count($unitsData) . ' units');

        // Seed Sales
        $this->command->info('Seeding sales...');
        foreach ($salesData as $sale) {
            Sale::create($sale);
        }
        $this->command->info('✓ Seeded ' . count($salesData) . ' sales');

        // Seed Users
        $this->command->info('Seeding users...');
        foreach ($usersData as $user) {
            // Password is already hashed in the export
            User::create($user);
        }
        $this->command->info('✓ Seeded ' . count($usersData) . ' users');

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('');
        $this->command->info('====================================');
        $this->command->info('Database seeding completed successfully!');
        $this->command->info('====================================');
        $this->command->info('Total records seeded:');
        $this->command->info('- Companies: ' . count($companiesData));
        $this->command->info('- Compounds: ' . count($compoundsData));
        $this->command->info('- Units: ' . count($unitsData));
        $this->command->info('- Sales: ' . count($salesData));
        $this->command->info('- Users: ' . count($usersData));
        $this->command->info('====================================');
    }
}
