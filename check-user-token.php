<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Checking user: joh@gmail.com\n";
echo "═══════════════════════════════════════════════════════\n\n";

$user = App\Models\User::where('email', 'joh@gmail.com')->first();

if ($user) {
    echo "✅ User found!\n";
    echo "User ID: {$user->id}\n";
    echo "Email: {$user->email}\n";
    echo "Role: {$user->role}\n";
    echo "FCM Token: " . ($user->fcm_token ?? 'NULL - NO TOKEN SAVED') . "\n";

    if ($user->fcm_token) {
        echo "\n✅ User HAS an FCM token saved\n";
        echo "Token length: " . strlen($user->fcm_token) . " characters\n";

        if (strlen($user->fcm_token) > 100) {
            echo "✅ This looks like a REAL FCM token\n";
        } else {
            echo "⚠️  This looks like a TEST/DUMMY token\n";
        }
    } else {
        echo "\n❌ User does NOT have an FCM token!\n";
        echo "The Flutter app needs to send the token to the backend.\n";
    }
} else {
    echo "❌ User NOT FOUND: joh@gmail.com\n\n";
    echo "Searching for similar emails...\n";
    $similar = App\Models\User::where('email', 'like', 'joh%')->get(['id', 'email']);
    if ($similar->count() > 0) {
        echo "Found similar users:\n";
        foreach ($similar as $s) {
            echo "  - {$s->email} (ID: {$s->id})\n";
        }
    }
}

echo "\n═══════════════════════════════════════════════════════\n";
