<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class IpgDevelopersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $developersJson = file_get_contents('/tmp/all_ipg_developers.json');
        $developers = json_decode($developersJson, true);

        if (!is_array($developers)) {
            $this->command->error('Could not parse developers JSON file');
            return;
        }

        // Default password hash for: "password123"
        $defaultPassword = Hash::make('password123');

        $now = now();

        foreach ($developers as $developer) {
            // Determine logo file extension
            $logoUrl = $developer['logo'];
            $extension = pathinfo(parse_url($logoUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
            $logoPath = 'company-logos/' . $developer['slug'] . '.' . $extension;

            // Clean Arabic name - remove English part if present
            $nameAr = $developer['name_ar'];
            if (strpos($nameAr, '|') !== false) {
                $parts = explode('|', $nameAr);
                $nameAr = trim($parts[0]);
            }

            // Insert or update company
            DB::table('companies')->updateOrInsert(
                ['name_en' => $developer['name_en']], // Match by English name
                [
                    'name' => $developer['name_en'],
                    'name_ar' => $nameAr,
                    'name_en' => $developer['name_en'],
                    'logo' => $logoPath,
                    'email' => strtolower($developer['slug']) . '@ipgegypt.com',
                    'password' => $defaultPassword,
                    'sales_ids' => null,
                    'remember_token' => null,
                    'number_of_compounds' => 0,
                    'number_of_available_units' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $this->command->info("✓ Imported: {$developer['name_en']} ({$nameAr})");
        }

        $this->command->info("\nTotal developers imported: " . count($developers));
    }
}
