<?php

namespace App\Console\Commands;

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Console\Command;

class AssignFreeSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:assign-free {--force : Force assign even if user has subscription}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign free subscription to all users without an active subscription';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Starting free subscription assignment...');

        // Get the free plan
        $freePlan = SubscriptionPlan::getFreePlan();

        if (!$freePlan) {
            $this->error('❌ Free plan not found! Please create a plan with slug "free" first.');
            return Command::FAILURE;
        }

        $this->info("✅ Free plan found: {$freePlan->name} (ID: {$freePlan->id})");

        // Get users without admin role
        $users = User::where('role', '!=', 'admin')->get();
        $this->info("👥 Found {$users->count()} non-admin users");

        $assigned = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($users as $user) {
            // Check if user already has an active subscription
            $hasActiveSubscription = $user->hasActiveSubscription();

            if ($hasActiveSubscription && !$this->option('force')) {
                $this->line("⏭️  Skipped: {$user->name} ({$user->email}) - Already has active subscription");
                $skipped++;
                continue;
            }

            try {
                $startedAt = now();
                $expiresAt = $freePlan->validity_days > 0
                    ? $startedAt->copy()->addDays($freePlan->validity_days)
                    : null;

                UserSubscription::create([
                    'user_id' => $user->id,
                    'subscription_plan_id' => $freePlan->id,
                    'started_at' => $startedAt,
                    'expires_at' => $expiresAt,
                    'searches_used' => 0,
                    'status' => 'active',
                    'notes' => 'تفعيل الباقة المجانية للمستخدمين الحاليين',
                ]);

                $this->info("✅ Assigned: {$user->name} ({$user->email})");
                $assigned++;
            } catch (\Exception $e) {
                $this->error("❌ Error for {$user->name}: {$e->getMessage()}");
                $errors++;
            }
        }

        $this->newLine();
        $this->info('═══════════════════════════════════════');
        $this->info('📊 Summary:');
        $this->info("   ✅ Assigned: {$assigned}");
        $this->info("   ⏭️  Skipped:  {$skipped}");
        $this->info("   ❌ Errors:   {$errors}");
        $this->info('═══════════════════════════════════════');

        return Command::SUCCESS;
    }
}
