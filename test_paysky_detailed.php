<?php

require __DIR__ . '/vendor/autoload.php';

use App\Models\PaymentGateway;
use App\Models\User;
use App\Services\PaymentGateways\PaySkyService;

// Bootstrap Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== اختبار تفصيلي لبوابة PaySky في مشروع عقار ===\n\n";

try {
    // Get PaySky Gateway
    $gateway = PaymentGateway::getBySlug('paysky');

    if (!$gateway) {
        echo "✗ خطأ: بوابة PaySky غير موجودة\n";
        exit(1);
    }

    echo "✓ تم العثور على بوابة PaySky\n";
    echo "  الاسم: {$gateway->name}\n";
    echo "  المعرف: {$gateway->slug}\n";
    echo "  مفعلة: " . ($gateway->is_active ? "نعم" : "لا") . "\n";
    echo "  وضع التجربة: " . ($gateway->is_test_mode ? "نعم" : "لا") . "\n";
    echo "  العملة: {$gateway->currency}\n\n";

    // Check credentials
    echo "=== معلومات الاعتماد ===\n";
    $credentials = $gateway->credentials;

    if ($gateway->is_test_mode) {
        echo "  وضع التجربة:\n";
        echo "    Merchant ID: {$credentials['test_merchant_id']}\n";
        echo "    Terminal ID: {$credentials['test_terminal_id']}\n";
        echo "    Secret Key: " . substr($credentials['test_secret_key'], 0, 10) . "...\n\n";
    } else {
        echo "  وضع الإنتاج:\n";
        echo "    Merchant ID: {$credentials['live_merchant_id']}\n";
        echo "    Terminal ID: {$credentials['live_terminal_id']}\n";
        echo "    Secret Key: " . substr($credentials['live_secret_key'], 0, 10) . "...\n\n";
    }

    // Get user for testing
    $user = User::first();

    if (!$user) {
        echo "✗ خطأ: لا يوجد مستخدمين في النظام\n";
        exit(1);
    }

    echo "✓ مستخدم الاختبار: {$user->name} (ID: {$user->id})\n\n";

    // Initialize PaySky Service
    echo "=== تهيئة PaySky Service ===\n";
    $payskyService = new PaySkyService();
    echo "✓ تم تهيئة الخدمة بنجاح\n\n";

    // Create payment data
    $paymentData = [
        'payable' => null, // For testing purposes
        'user_id' => $user->id,
        'amount' => 50.00,
        'currency' => 'EGP',
        'description' => 'اختبار دفع من موقع عقار',
        'customer' => [
            'name' => $user->name,
            'email' => $user->email,
        ],
    ];

    echo "=== بيانات الدفع ===\n";
    echo "  المبلغ: {$paymentData['amount']} {$paymentData['currency']}\n";
    echo "  الوصف: {$paymentData['description']}\n";
    echo "  العميل: {$paymentData['customer']['name']}\n\n";

    // Initialize payment
    echo "=== تهيئة عملية الدفع ===\n";
    $result = $payskyService->initializePayment($paymentData);

    if (isset($result['success']) && $result['success']) {
        echo "✓ تم تهيئة الدفع بنجاح!\n\n";

        echo "=== معلومات المعاملة ===\n";
        echo "  رقم المعاملة: {$result['transaction_id']}\n";
        echo "  المبلغ بالقرش: {$result['config']['AmountTrxn']}\n";
        echo "  التاريخ والوقت: {$result['config']['TrxDateTime']}\n";
        echo "  Secure Hash: " . substr($result['config']['SecureHash'], 0, 30) . "...\n\n";

        echo "=== بيانات التكامل ===\n";
        echo "  Lightbox URL: {$result['lightbox_url']}\n";
        echo "  Merchant ID: {$result['config']['MID']}\n";
        echo "  Terminal ID: {$result['config']['TID']}\n\n";

        // Show transaction record
        $transaction = $result['transaction'];
        echo "=== سجل المعاملة في قاعدة البيانات ===\n";
        echo "  ID: {$transaction->id}\n";
        echo "  Transaction ID: {$transaction->transaction_id}\n";
        echo "  المبلغ: {$transaction->amount} {$transaction->currency}\n";
        echo "  الحالة: {$transaction->status}\n";
        echo "  تاريخ الإنشاء: {$transaction->created_at}\n\n";

        echo "✓ الاختبار اكتمل بنجاح!\n";
        echo "\n=== رابط الدفع ===\n";
        echo "يمكنك الآن استخدام هذه المعلومات لإنشاء واجهة دفع\n";
        echo "وتضمين Lightbox من: {$result['lightbox_url']}\n\n";

    } else {
        echo "✗ فشل تهيئة الدفع\n";
        echo "الرسالة: " . ($result['message'] ?? 'Unknown error') . "\n";
    }

} catch (\Exception $e) {
    echo "\n✗ حدث خطأ:\n";
    echo "  الرسالة: {$e->getMessage()}\n";
    echo "  الملف: {$e->getFile()}:{$e->getLine()}\n";
    echo "\n";
}
