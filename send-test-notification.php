<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "═══════════════════════════════════════════════════════\n";
echo "  FCM NOTIFICATION TEST SCRIPT\n";
echo "═══════════════════════════════════════════════════════\n\n";

try {
    // Check credentials
    $credPath = storage_path(config('firebase.credentials.file'));
    echo "1. Checking Firebase credentials...\n";
    echo "   Path: {$credPath}\n";

    if (!file_exists($credPath)) {
        echo "   ❌ Credentials file NOT FOUND!\n";
        echo "   Please download from Firebase Console and place it there.\n";
        exit(1);
    }
    echo "   ✅ Credentials file found!\n\n";

    // Check users with tokens
    $usersWithTokens = App\Models\User::whereNotNull('fcm_token')->get();
    echo "2. Checking users with FCM tokens...\n";
    echo "   Total users: " . App\Models\User::count() . "\n";
    echo "   Users with FCM tokens: " . $usersWithTokens->count() . "\n\n";

    if ($usersWithTokens->isEmpty()) {
        echo "   ❌ No users with FCM tokens found!\n";
        echo "   Please run your Flutter app first to get the token.\n";
        exit(1);
    }

    // Display tokens
    echo "   FCM Tokens found:\n";
    foreach ($usersWithTokens as $user) {
        $tokenPreview = substr($user->fcm_token, 0, 50) . '...';
        echo "   - User: {$user->email}\n";
        echo "     Token: {$tokenPreview}\n";
    }
    echo "\n";

    // Initialize FCM service
    echo "3. Initializing FCM service...\n";
    $fcm = new App\Services\FCMNotificationService();
    echo "   ✅ FCM service initialized!\n\n";

    // Send test notification
    echo "4. Sending test notification...\n";
    $fcm->sendToAllUsers(
        '🎉 Test from Laravel!',
        'If you see this, FCM notifications are working perfectly!',
        [
            'type' => 'test',
            'timestamp' => now()->toIso8601String(),
            'message' => 'This is a test notification'
        ]
    );

    echo "   ✅ Notification sent!\n\n";
    echo "═══════════════════════════════════════════════════════\n";
    echo "  CHECK YOUR FLUTTER EMULATOR NOW!\n";
    echo "  You should see a notification appear.\n";
    echo "═══════════════════════════════════════════════════════\n";

} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
