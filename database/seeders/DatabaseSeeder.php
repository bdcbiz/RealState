<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@realestate.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        // Create Test Company
        $company = Company::create([
            'name' => 'BDC Real Estate',
            'name_ar' => 'بي دي سي العقارية',
            'name_en' => 'BDC Real Estate',
            'email' => 'company@bdcbiz.com',
            'password' => Hash::make('password'),
            'number_of_compounds' => 0,
            'number_of_available_units' => 0,
        ]);

        // Create Company Admin User
        User::create([
            'name' => 'Company Admin',
            'email' => 'company-admin@bdcbiz.com',
            'password' => Hash::make('password'),
            'role' => 'seller',
            'company_id' => $company->id,
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        // Create Test Buyer
        User::create([
            'name' => 'Test Buyer',
            'email' => 'buyer@test.com',
            'password' => Hash::make('password'),
            'role' => 'buyer',
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        // Create Test Seller
        User::create([
            'name' => 'Test Seller',
            'email' => 'seller@test.com',
            'password' => Hash::make('password'),
            'role' => 'seller',
            'company_id' => $company->id,
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin: admin@realestate.com / password');
        $this->command->info('Company Admin: company-admin@bdcbiz.com / password');
        $this->command->info('Buyer: buyer@test.com / password');
        $this->command->info('Seller: seller@test.com / password');
    }
}
