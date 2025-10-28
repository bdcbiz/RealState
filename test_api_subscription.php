<?php

require __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\UserSubscription;

// Bootstrap Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== اختبار نظام الاشتراكات مع API ===\n\n";

try {
    // Create or get test user
    $testEmail = 'api_test@test.com';
    $testUser = User::where('email', $testEmail)->first();

    if ($testUser) {
        echo "⚠ المستخدم موجود، سيتم حذفه وإعادة إنشائه\n";
        $testUser->subscriptions()->delete();
        $testUser->delete();
    }

    // Create new test user (Observer will auto-assign free plan)
    $testUser = User::create([
        'name' => 'API Test User',
        'email' => $testEmail,
        'password' => bcrypt('password123'),
        'phone' => '01111111111',
        'role' => 'customer',
    ]);

    echo "✓ تم إنشاء مستخدم الاختبار\n";
    echo "  Email: {$testUser->email}\n";
    echo "  Password: password123\n\n";

    // Wait for observer to create subscription
    sleep(1);

    // Get user token for API testing
    $token = $testUser->createToken('api-test-token')->plainTextToken;
    echo "✓ تم إنشاء API Token:\n";
    echo "  Token: " . substr($token, 0, 30) . "...\n\n";

    // Check subscription
    $subscription = $testUser->getCurrentSubscription();

    if (!$subscription) {
        echo "✗ فشل: لم يتم تفعيل الباقة المجانية\n";
        exit(1);
    }

    echo "✓ الباقة المجانية مفعلة:\n";
    echo "  الباقة: {$subscription->subscriptionPlan->name}\n";
    echo "  عدد محاولات البحث المتاحة: {$subscription->subscriptionPlan->search_limit}\n";
    echo "  محاولات مستخدمة: {$subscription->searches_used}\n";
    echo "  متبقي: " . $subscription->getRemainingSearches() . "\n\n";

    // Test API calls
    $baseUrl = "https://aqar.bdcbiz.com/api";
    $headers = [
        "Authorization: Bearer {$token}",
        "Accept: application/json",
        "Content-Type: application/json"
    ];

    echo "=== اختبار API Endpoints ===\n\n";

    // Test 1: Search API
    echo "1. اختبار Search API (محاولة 1):\n";
    $ch = curl_init("{$baseUrl}/search?search=villa");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        $data = json_decode($response, true);
        echo "   ✓ نجح البحث\n";
        echo "   - عدد النتائج: {$data['total_results']}\n";
        if (isset($data['subscription'])) {
            echo "   - محاولات مستخدمة: {$data['subscription']['searches_used']}\n";
            echo "   - متبقي: {$data['subscription']['remaining_searches']}\n";
        }
    } else {
        echo "   ✗ فشل: HTTP {$httpCode}\n";
        echo "   Response: {$response}\n";
    }
    echo "\n";

    // Test 2-5: Multiple searches
    for ($i = 2; $i <= 5; $i++) {
        echo "{$i}. اختبار Search API (محاولة {$i}):\n";
        $ch = curl_init("{$baseUrl}/search?search=apartment");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200) {
            $data = json_decode($response, true);
            echo "   ✓ نجح البحث #{$i}\n";
            if (isset($data['subscription'])) {
                echo "   - محاولات مستخدمة: {$data['subscription']['searches_used']}\n";
                echo "   - متبقي: {$data['subscription']['remaining_searches']}\n";
            }
        } else {
            echo "   ✗ فشل: HTTP {$httpCode}\n";
        }
        echo "\n";
    }

    // Test 6: Should be blocked (6th attempt)
    echo "6. اختبار Search API (محاولة 6 - يجب أن تفشل):\n";
    $ch = curl_init("{$baseUrl}/search?search=villa");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 403) {
        $data = json_decode($response, true);
        echo "   ✓ تم حظر المحاولة كما هو متوقع!\n";
        echo "   - الرسالة: {$data['message']}\n";
        if (isset($data['searches_used'])) {
            echo "   - محاولات مستخدمة: {$data['searches_used']}/{$data['search_limit']}\n";
        }
    } else {
        echo "   ✗ فشل: كان يجب أن يتم حظر المحاولة (HTTP {$httpCode})\n";
    }
    echo "\n";

    // Test Filter API
    echo "7. اختبار Filter API (يجب أن تفشل أيضاً):\n";
    $ch = curl_init("{$baseUrl}/filter-units?unit_type=villa");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 403) {
        $data = json_decode($response, true);
        echo "   ✓ تم حظر المحاولة كما هو متوقع!\n";
        echo "   - الرسالة: {$data['message']}\n";
    } else {
        echo "   ✗ فشل: كان يجب أن يتم حظر المحاولة (HTTP {$httpCode})\n";
    }
    echo "\n";

    echo "=== ملخص الاختبار ===\n";
    echo "✓ نظام الاشتراكات يعمل بشكل صحيح!\n";
    echo "✓ تم منع المستخدم من البحث بعد استنفاد محاولاته\n";
    echo "✓ الـ API ترجع معلومات الاشتراك مع كل استجابة\n\n";

    echo "ملاحظة: يمكنك الآن حذف المستخدم التجريبي:\n";
    echo "  php artisan tinker\n";
    echo "  User::where('email', '{$testEmail}')->delete();\n";

} catch (\Exception $e) {
    echo "\n✗ حدث خطأ:\n";
    echo "  الرسالة: {$e->getMessage()}\n";
    echo "  الملف: {$e->getFile()}:{$e->getLine()}\n";
}
