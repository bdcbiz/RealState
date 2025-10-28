<?php

require __DIR__ . '/vendor/autoload.php';

use App\Models\PaymentGateway;
use App\Models\User;
use App\Services\PaymentGateways\AFSService;

// Bootstrap Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== اختبار تفصيلي لبوابة AFS Mastercard في مشروع عقار ===\n\n";

try {
    // Get AFS Gateway
    $gateway = PaymentGateway::getBySlug('afs');

    if (!$gateway) {
        echo "✗ خطأ: بوابة AFS غير موجودة\n";
        exit(1);
    }

    echo "✓ تم العثور على بوابة AFS Mastercard\n";
    echo "  الاسم: {$gateway->name}\n";
    echo "  المعرف: {$gateway->slug}\n";
    echo "  مفعلة: " . ($gateway->is_active ? "نعم" : "لا") . "\n";
    echo "  وضع التجربة: " . ($gateway->is_test_mode ? "نعم" : "لا") . "\n";
    echo "  العملة: {$gateway->currency}\n\n";

    // Check credentials
    echo "=== معلومات الاعتماد ===\n";
    $credentials = $gateway->credentials;
    echo "  Merchant ID: {$credentials['merchant_id']}\n";
    echo "  API Username: {$credentials['api_username']}\n";
    echo "  API Password: " . substr($credentials['api_password'], 0, 10) . "...\n\n";

    // Get user for testing
    $user = User::first();

    if (!$user) {
        echo "✗ خطأ: لا يوجد مستخدمين في النظام\n";
        exit(1);
    }

    echo "✓ مستخدم الاختبار: {$user->name} (ID: {$user->id})\n\n";

    // Initialize AFS Service
    echo "=== تهيئة AFS Service ===\n";
    $afsService = new AFSService();
    echo "✓ تم تهيئة الخدمة بنجاح\n\n";

    // Create payment data
    $paymentData = [
        'payable' => null, // For testing purposes
        'user_id' => $user->id,
        'amount' => 10.00,
        'currency' => 'USD',
        'description' => 'اختبار دفع AFS Mastercard من موقع عقار',
        'customer' => [
            'name' => $user->name,
            'email' => $user->email,
        ],
        'return_url' => 'https://aqar.bdcbiz.com/api/payment/afs/return',
        'cancel_url' => 'https://aqar.bdcbiz.com/api/payment/afs/cancel',
    ];

    echo "=== بيانات الدفع ===\n";
    echo "  المبلغ: {$paymentData['amount']} {$paymentData['currency']}\n";
    echo "  الوصف: {$paymentData['description']}\n";
    echo "  العميل: {$paymentData['customer']['name']}\n\n";

    // Create session
    echo "=== إنشاء جلسة دفع ===\n";
    $result = $afsService->createSession($paymentData);

    if (isset($result['success']) && $result['success']) {
        echo "✓ تم إنشاء جلسة الدفع بنجاح!\n\n";

        echo "=== معلومات المعاملة ===\n";
        echo "  رقم المعاملة: {$result['transaction']->transaction_id}\n";
        echo "  المبلغ: {$result['transaction']->amount} {$result['transaction']->currency}\n";
        echo "  الحالة: {$result['transaction']->status}\n\n";

        if (isset($result['session'])) {
            echo "=== معلومات الجلسة ===\n";
            echo "  Session ID: {$result['session']['id']}\n";
            echo "  Session Version: {$result['session']['version']}\n\n";
        }

        if (isset($result['checkout_url'])) {
            echo "=== رابط الدفع ===\n";
            echo "  URL: {$result['checkout_url']}\n\n";
        }

        echo "✓ الاختبار اكتمل بنجاح!\n";

    } else {
        echo "✗ فشل إنشاء جلسة الدفع\n";
        echo "الرسالة: " . ($result['message'] ?? 'Unknown error') . "\n";

        if (isset($result['errors'])) {
            echo "\nالأخطاء:\n";
            print_r($result['errors']);
        }

        if (isset($result['response'])) {
            echo "\nالاستجابة:\n";
            print_r($result['response']);
        }
    }

} catch (\Exception $e) {
    echo "\n✗ حدث خطأ:\n";
    echo "  الرسالة: {$e->getMessage()}\n";
    echo "  الملف: {$e->getFile()}:{$e->getLine()}\n";
    echo "\n";
}
