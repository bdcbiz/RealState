<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompaniesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('companies')->updateOrInsert(
            ['id' => 1],
            [
                'name' => 'BDC Real Estate',
                'name_ar' => 'بي دي سي العقارية',
                'name_en' => 'BDC Real Estate',
                'logo' => null,
                'sales_ids' => null,
                'email' => 'company@bdcbiz.com',
                'password' => '$2y$12$JaXm4jr5DIMYiC7bmssMd.F/kI1tlwyJ9rqGOpHEinVLmkFQaeHAa',
                'remember_token' => null,
                'number_of_compounds' => 0,
                'number_of_available_units' => 0,
                'created_at' => '2025-10-20 03:52:21',
                'updated_at' => '2025-10-20 03:52:21',
            ]
        );
    }
}
