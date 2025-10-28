<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FreePlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if free plan already exists
        $existingPlan = SubscriptionPlan::where('slug', 'free')->first();

        if ($existingPlan) {
            $this->command->info('✅ Free plan already exists. Skipping...');
            return;
        }

        // Create free plan
        $freePlan = SubscriptionPlan::create([
            'name' => 'باقة مجانية',
            'name_en' => 'Free Plan',
            'slug' => 'free',
            'description' => 'باقة مجانية للمستخدمين الجدد بميزات محدودة',
            'description_en' => 'Free plan for new users with limited features',
            'monthly_price' => 0,
            'yearly_price' => 0,
            'max_users' => 1,
            'search_limit' => 10, // 10 searches per month
            'validity_days' => -1, // Unlimited validity
            'icon' => 'heroicon-o-gift',
            'color' => '#10b981',
            'badge' => 'مجاني',
            'badge_en' => 'Free',
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 0,
        ]);

        $this->command->info("✅ Free plan created successfully! (ID: {$freePlan->id})");
        $this->command->info("   Name: {$freePlan->name}");
        $this->command->info("   Slug: {$freePlan->slug}");
        $this->command->info("   Search Limit: {$freePlan->search_limit} searches");
    }
}
