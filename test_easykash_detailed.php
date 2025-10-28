<?php

require __DIR__ . '/vendor/autoload.php';

use App\Models\PaymentGateway;
use App\Models\User;
use App\Services\PaymentGateways\EasyKashService;

// Bootstrap Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== اختبار تفصيلي لبوابة EasyKash في مشروع عقار ===\n\n";

try {
    // Get EasyKash Gateway
    $gateway = PaymentGateway::getBySlug('easykash');

    if (!$gateway) {
        echo "✗ خطأ: بوابة EasyKash غير موجودة\n";
        exit(1);
    }

    echo "✓ تم العثور على بوابة EasyKash\n";
    echo "  الاسم: {$gateway->name}\n";
    echo "  المعرف: {$gateway->slug}\n";
    echo "  مفعلة: " . ($gateway->is_active ? "نعم" : "لا") . "\n";
    echo "  وضع التجربة: " . ($gateway->is_test_mode ? "نعم" : "لا") . "\n";
    echo "  العملة: {$gateway->currency}\n\n";

    // Check credentials
    echo "=== معلومات الاعتماد ===\n";
    $credentials = $gateway->credentials;
    echo "  API Key: {$credentials['api_key']}\n";
    echo "  HMAC Secret: " . substr($credentials['hmac_secret'], 0, 10) . "...\n";
    echo "  Callback URL: {$credentials['callback_url']}\n";
    echo "  Redirect URL: {$credentials['redirect_url']}\n\n";

    // Get user for testing
    $user = User::first();

    if (!$user) {
        echo "✗ خطأ: لا يوجد مستخدمين في النظام\n";
        exit(1);
    }

    echo "✓ مستخدم الاختبار: {$user->name} (ID: {$user->id})\n\n";

    // Initialize EasyKash Service
    echo "=== تهيئة EasyKash Service ===\n";
    $easykashService = new EasyKashService();
    echo "✓ تم تهيئة الخدمة بنجاح\n\n";

    // Create payment data
    $paymentData = [
        'payable' => null, // For testing purposes
        'user_id' => $user->id,
        'amount' => 100.00,
        'currency' => 'EGP',
        'description' => 'اختبار دفع EasyKash من موقع عقار',
        'customer' => [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '01000000000',
        ],
    ];

    echo "=== بيانات الدفع ===\n";
    echo "  المبلغ: {$paymentData['amount']} {$paymentData['currency']}\n";
    echo "  الوصف: {$paymentData['description']}\n";
    echo "  العميل: {$paymentData['customer']['name']}\n\n";

    // Create payment
    echo "=== إنشاء عملية الدفع ===\n";
    $result = $easykashService->createPayment($paymentData);

    if (isset($result['success']) && $result['success']) {
        echo "✓ تم إنشاء الدفع بنجاح!\n\n";

        echo "=== معلومات المعاملة ===\n";
        echo "  رقم المعاملة: {$result['transaction']->transaction_id}\n";
        echo "  المبلغ: {$result['transaction']->amount} {$result['transaction']->currency}\n";
        echo "  الحالة: {$result['transaction']->status}\n\n";

        if (isset($result['payment_url'])) {
            echo "=== رابط الدفع ===\n";
            echo "  URL: {$result['payment_url']}\n\n";
        }

        if (isset($result['easykash_transaction_id'])) {
            echo "  EasyKash Transaction ID: {$result['easykash_transaction_id']}\n\n";
        }

        echo "✓ الاختبار اكتمل بنجاح!\n";

    } else {
        echo "✗ فشل إنشاء الدفع\n";
        echo "الرسالة: " . ($result['message'] ?? 'Unknown error') . "\n";

        if (isset($result['errors'])) {
            echo "\nالأخطاء:\n";
            print_r($result['errors']);
        }
    }

} catch (\Exception $e) {
    echo "\n✗ حدث خطأ:\n";
    echo "  الرسالة: {$e->getMessage()}\n";
    echo "  الملف: {$e->getFile()}:{$e->getLine()}\n";
    echo "\n";
}
