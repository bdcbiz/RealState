<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'id' => 1,
                'name' => 'Admin User',
                'email' => 'admin@realestate.com',
                'phone' => null,
                'image' => null,
                'role' => 'admin',
                'company_id' => null,
                'email_verified_at' => '2025-10-20 03:52:21',
                'is_verified' => 1,
                'is_banned' => 0,
                'verification_token' => null,
                'verification_token_expires_at' => null,
                'reset_token' => null,
                'reset_token_expires_at' => null,
                'password' => '$2y$12$Ua/E7YIORpkApbsdhB8Awe54ccfctOcGm4e7G3YTd3gPT/AbWSu5G',
                'remember_token' => null,
                'fcm_token' => null,
                'created_at' => '2025-10-20 03:52:21',
                'updated_at' => '2025-10-20 03:52:21',
            ],
            [
                'id' => 2,
                'name' => 'Company Admin',
                'email' => 'company-admin@bdcbiz.com',
                'phone' => null,
                'image' => null,
                'role' => 'seller',
                'company_id' => 1,
                'email_verified_at' => '2025-10-20 03:52:21',
                'is_verified' => 1,
                'is_banned' => 0,
                'verification_token' => null,
                'verification_token_expires_at' => null,
                'reset_token' => null,
                'reset_token_expires_at' => null,
                'password' => '$2y$12$uARFgPDnngwUWq66RlOmqO3io7aEsoLvPeNcgYR5UNQey5XespuOS',
                'remember_token' => null,
                'fcm_token' => null,
                'created_at' => '2025-10-20 03:52:21',
                'updated_at' => '2025-10-20 03:52:21',
            ],
            [
                'id' => 3,
                'name' => 'Test Buyer',
                'email' => 'buyer@test.com',
                'phone' => null,
                'image' => null,
                'role' => 'buyer',
                'company_id' => null,
                'email_verified_at' => '2025-10-20 03:52:21',
                'is_verified' => 1,
                'is_banned' => 0,
                'verification_token' => null,
                'verification_token_expires_at' => null,
                'reset_token' => null,
                'reset_token_expires_at' => null,
                'password' => '$2y$12$3JBQ.eZCFLhibAmF2E4a1Oxrbe8zDHWeO6ujAeNVwBkHpJeFPvU8G',
                'remember_token' => null,
                'fcm_token' => null,
                'created_at' => '2025-10-20 03:52:21',
                'updated_at' => '2025-10-20 03:52:21',
            ],
            [
                'id' => 4,
                'name' => 'Test Seller',
                'email' => 'seller@test.com',
                'phone' => null,
                'image' => null,
                'role' => 'seller',
                'company_id' => 1,
                'email_verified_at' => '2025-10-20 03:52:22',
                'is_verified' => 1,
                'is_banned' => 0,
                'verification_token' => null,
                'verification_token_expires_at' => null,
                'reset_token' => null,
                'reset_token_expires_at' => null,
                'password' => '$2y$12$wHsX1gRp1PaUjExHXVKUHur4ja5nC7o/veDhbExQZCPWB.s16AKBm',
                'remember_token' => null,
                'fcm_token' => null,
                'created_at' => '2025-10-20 03:52:22',
                'updated_at' => '2025-10-20 03:52:22',
            ]
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['id' => $user['id']],
                $user
            );
        }
    }
}
