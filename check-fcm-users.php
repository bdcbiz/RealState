<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Users with FCM tokens:\n";
echo "═══════════════════════════════════════════════════════\n\n";

$users = App\Models\User::whereNotNull('fcm_token')->get(['id', 'email', 'role', 'fcm_token']);

if ($users->isEmpty()) {
    echo "No users with FCM tokens found.\n";
} else {
    foreach ($users as $user) {
        $tokenPreview = substr($user->fcm_token, 0, 40) . '...';
        $isRealToken = strlen($user->fcm_token) > 100;
        $tokenType = $isRealToken ? '✅ REAL FCM Token' : '⚠️  Test/Dummy Token';

        echo "User ID: {$user->id}\n";
        echo "Email: {$user->email}\n";
        echo "Role: {$user->role}\n";
        echo "Token: {$tokenPreview}\n";
        echo "Type: {$tokenType}\n";
        echo "───────────────────────────────────────────────────────\n\n";
    }

    echo "Total users with FCM tokens: {$users->count()}\n";
}

echo "\n═══════════════════════════════════════════════════════\n";
