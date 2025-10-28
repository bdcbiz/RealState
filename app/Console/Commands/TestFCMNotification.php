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

        // 4. Print Firebase Project Details
        try {
            $credentials = json_decode(file_get_contents($fullPath), true);
            $this->info("🔑 Firebase Project Details:");
            $this->line("   Project ID: " . ($credentials['project_id'] ?? 'N/A'));
            $this->line("   Client Email: " . ($credentials['client_email'] ?? 'N/A'));
            $this->line("   Firebase Console: https://console.firebase.google.com/project/{$credentials['project_id']}/notification");
        } catch (\Exception $e) {
            $this->warn("   Could not read project details");
        }

        $this->newLine();

        // 5. Test sending notification with detailed logging
        if ($this->option('token')) {
            $testToken = $this->option('token');
            $this->info("🧪 Testing notification to specific token...");
            $this->line("   Token: " . substr($testToken, 0, 50) . "...");
            $this->newLine();

            try {
                $this->info("📤 Step 1: Preparing notification payload...");
                $title = "Test Notification";
                $body = "This is a test notification from " . config('app.name') . " at " . now()->format('Y-m-d H:i:s');
                $data = [
                    'type' => 'test',
                    'timestamp' => now()->toDateTimeString(),
                    'test_id' => uniqid(),
                ];

                $this->line("   Title: {$title}");
                $this->line("   Body: {$body}");
                $this->line("   Data: " . json_encode($data));
                $this->newLine();

                $this->info("📤 Step 2: Sending to Firebase...");
                $result = $fcmService->sendToUser(
                    $testToken,
                    $title,
                    $body,
                    $data
                );

                $this->newLine();
                if ($result) {
                    $this->info("✅ Step 3: Notification sent successfully!");
                    $this->line("   Check the mobile device for the notification.");
                    $this->line("   Check Laravel logs at: storage/logs/laravel.log");
                } else {
                    $this->error("❌ Step 3: Failed to send notification");
                    $this->line("   Check Laravel logs for details: storage/logs/laravel.log");
                }
            } catch (\Exception $e) {
                $this->error("❌ Failed to send test notification: " . $e->getMessage());
                $this->line("   Stack trace logged to: storage/logs/laravel.log");
            }
        } elseif ($usersWithTokens->count() > 0) {
            if ($this->confirm('Do you want to send a test notification to all users?', false)) {
                $this->newLine();
                $this->info("📤 Sending notifications to {$usersWithTokens->count()} users...");

                try {
                    foreach ($usersWithTokens as $user) {
                        $this->line("   → {$user->name} ({$user->email})");
                        $result = $fcmService->sendToUser(
                            $user->fcm_token,
                            "Test Notification",
                            "This is a test notification from admin panel at " . now()->format('Y-m-d H:i:s'),
                            ['type' => 'test', 'timestamp' => now()->toDateTimeString()]
                        );

                        if ($result) {
                            $this->info("     ✅ Sent");
                        } else {
                            $this->error("     ❌ Failed");
                        }
                    }
                    $this->newLine();
                    $this->info("✅ Test notifications sent!");
                    $this->line("   Check Laravel logs at: storage/logs/laravel.log");
                } catch (\Exception $e) {
                    $this->error("❌ Failed to send notifications: " . $e->getMessage());
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
