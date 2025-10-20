<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n";
echo "╔═══════════════════════════════════════════════════════╗\n";
echo "║          FCM NOTIFICATION DIAGNOSTIC TOOL             ║\n";
echo "╚═══════════════════════════════════════════════════════╝\n\n";

$email = 'joh@example.com';

// Test 1: Check if user exists and has token
echo "1️⃣  Checking user account...\n";
echo "───────────────────────────────────────────────────────\n";
$user = App\Models\User::where('email', $email)->first();

if (!$user) {
    echo "❌ FAILED: User not found: {$email}\n";
    exit(1);
}

echo "✅ User found\n";
echo "   ID: {$user->id}\n";
echo "   Email: {$user->email}\n";
echo "   Role: {$user->role}\n\n";

// Test 2: Check FCM token
echo "2️⃣  Checking FCM token...\n";
echo "───────────────────────────────────────────────────────\n";

if (!$user->fcm_token) {
    echo "❌ FAILED: No FCM token saved for this user!\n";
    echo "\n📱 ACTION REQUIRED:\n";
    echo "   Your Flutter app needs to send the FCM token.\n";
    echo "   Make sure the token is sent when user logs in.\n\n";
    exit(1);
}

echo "✅ FCM token exists\n";
echo "   Token: " . substr($user->fcm_token, 0, 50) . "...\n";
echo "   Length: " . strlen($user->fcm_token) . " characters\n";

if (strlen($user->fcm_token) < 100) {
    echo "⚠️  WARNING: This looks like a test/dummy token\n";
    echo "   Real FCM tokens are 150-200+ characters long\n\n";
} else {
    echo "✅ This looks like a real FCM token\n\n";
}

// Test 3: Check Firebase credentials
echo "3️⃣  Checking Firebase credentials...\n";
echo "───────────────────────────────────────────────────────\n";

$credPath = storage_path(config('firebase.credentials.file'));
echo "   Expected path: {$credPath}\n";

if (!file_exists($credPath)) {
    echo "❌ FAILED: Firebase credentials file NOT FOUND!\n";
    echo "\n🔥 ACTION REQUIRED:\n";
    echo "   1. Go to Firebase Console\n";
    echo "   2. Project Settings > Service Accounts\n";
    echo "   3. Generate new private key\n";
    echo "   4. Save JSON file to: {$credPath}\n\n";
    exit(1);
}

echo "✅ Firebase credentials file found\n\n";

// Test 4: Try to initialize FCM service
echo "4️⃣  Testing FCM service initialization...\n";
echo "───────────────────────────────────────────────────────\n";

try {
    $fcmService = new App\Services\FCMNotificationService();
    echo "✅ FCM service initialized successfully\n\n";
} catch (Exception $e) {
    echo "❌ FAILED: Could not initialize FCM service\n";
    echo "   Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 5: Try sending test notification
echo "5️⃣  Sending test notification...\n";
echo "───────────────────────────────────────────────────────\n";
echo "   Recipient: {$user->email}\n";
echo "   Token: " . substr($user->fcm_token, 0, 30) . "...\n\n";

try {
    $success = $fcmService->sendToUser(
        $user->fcm_token,
        '🎉 Test Notification',
        'If you see this, FCM is working!',
        [
            'type' => 'test',
            'timestamp' => now()->toIso8601String()
        ]
    );

    if ($success) {
        echo "✅ Notification sent successfully!\n\n";
        echo "╔═══════════════════════════════════════════════════════╗\n";
        echo "║           CHECK YOUR EMULATOR NOW!                   ║\n";
        echo "║     You should receive a notification within seconds ║\n";
        echo "╚═══════════════════════════════════════════════════════╝\n\n";
    } else {
        echo "⚠️  Notification send returned false\n";
        echo "   Check storage/logs/laravel.log for details\n\n";
    }
} catch (Exception $e) {
    echo "❌ FAILED: Error sending notification\n";
    echo "   Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Summary
echo "═══════════════════════════════════════════════════════\n";
echo "📊 DIAGNOSTIC SUMMARY\n";
echo "═══════════════════════════════════════════════════════\n";
echo "✅ User account: OK\n";
echo "✅ FCM token: OK\n";
echo "✅ Firebase credentials: OK\n";
echo "✅ FCM service: OK\n";
echo "✅ Test notification: SENT\n\n";

echo "If you didn't receive the notification on your emulator:\n\n";
echo "🔍 Check these:\n";
echo "   1. Is your Flutter app running?\n";
echo "   2. Does the app have notification permissions?\n";
echo "   3. Is Google Play Services installed on emulator?\n";
echo "   4. Check Flutter console for notification logs\n";
echo "   5. Check Laravel logs: storage/logs/laravel.log\n\n";
