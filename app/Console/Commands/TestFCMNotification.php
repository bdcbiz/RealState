<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\FCMNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestFCMNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fcm:test {--token= : Specific FCM token to test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test FCM notification service and Firebase connection';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Testing FCM Notification Service...');
        $this->newLine();

        // 1. Check Firebase credentials file
        $credentialsPath = env('FIREBASE_CREDENTIALS');
        $fullPath = base_path($credentialsPath);

        $this->line("📄 Firebase Credentials Path: {$credentialsPath}");

        if (file_exists($fullPath)) {
            $this->info("✅ Firebase credentials file exists");
            $this->line("   Full path: {$fullPath}");
        } else {
            $this->error("❌ Firebase credentials file NOT found!");
            $this->line("   Looking for: {$fullPath}");
            return Command::FAILURE;
        }

        $this->newLine();

        // 2. Check FCM Service initialization
        try {
            $fcmService = new FCMNotificationService();
            $this->info("✅ FCM Service initialized successfully");
        } catch (\Exception $e) {
            $this->error("❌ Failed to initialize FCM Service: " . $e->getMessage());
            return Command::FAILURE;
        }

        $this->newLine();

        // 3. Check users with FCM tokens
        $usersWithTokens = User::whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->get();

        $this->line("👥 Users with FCM tokens: " . $usersWithTokens->count());

        if ($usersWithTokens->count() > 0) {
            $this->info("   Users:");
            foreach ($usersWithTokens as $user) {
                $this->line("   - {$user->name} ({$user->email})");
            }
        } else {
            $this->warn("   ⚠️  No users have FCM tokens registered!");
            $this->line("   Users need to login via mobile app to register FCM tokens.");
        }

        $this->newLine();

        // 4. Test sending notification
        if ($this->option('token')) {
            $testToken = $this->option('token');
            $this->info("🧪 Testing notification to specific token...");

            try {
                // Test with a simple notification
                $result = $fcmService->sendToToken(
                    $testToken,
                    "Test Notification",
                    "This is a test notification from " . config('app.name'),
                    ['type' => 'test', 'timestamp' => now()->toDateTimeString()]
                );

                $this->info("✅ Test notification sent successfully!");
                $this->line("   Result: " . json_encode($result));
            } catch (\Exception $e) {
                $this->error("❌ Failed to send test notification: " . $e->getMessage());
            }
        } elseif ($usersWithTokens->count() > 0) {
            if ($this->confirm('Do you want to send a test notification to all users?', false)) {
                try {
                    $fcmService->sendToAllUsers(
                        "Test Notification",
                        "This is a test notification from admin panel",
                        ['type' => 'test', 'timestamp' => now()->toDateTimeString()]
                    );
                    $this->info("✅ Test notification sent to all users!");
                } catch (\Exception $e) {
                    $this->error("❌ Failed to send notification: " . $e->getMessage());
                }
            }
        }

        $this->newLine();
        $this->info('═══════════════════════════════════════');
        $this->info('✅ FCM Test completed');
        $this->info('═══════════════════════════════════════');

        return Command::SUCCESS;
    }
}
