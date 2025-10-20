<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProductionDataSeeder extends Seeder
{
    /**
     * Seed the application's database with production data.
     *
     * This seeder contains real data exported from the database.
     * Run this seeder to restore production data on the server.
     *
     * Usage: php artisan db:seed --class=ProductionDataSeeder
     */
    public function run(): void
    {
        $this->command->info('Seeding production data...');

        // Seed in correct order to maintain foreign key relationships
        $this->call([
            CompaniesSeeder::class,
            UsersSeeder::class,
            // Add more seeders here as needed for:
            // - CompoundsSeeder::class,
            // - UnitsSeeder::class,
            // - SalesSeeder::class,
            // etc.
        ]);

        $this->command->info('Production data seeded successfully!');
        $this->command->info('');
        $this->command->info('Available users:');
        $this->command->info('- admin@realestate.com (Admin)');
        $this->command->info('- company-admin@bdcbiz.com (Company Admin)');
        $this->command->info('- buyer@test.com (Buyer)');
        $this->command->info('- seller@test.com (Seller)');
    }
}
