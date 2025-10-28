<?php

require __DIR__ . '/vendor/autoload.php';

use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Log;

// Bootstrap Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== اختبار بوابات الدفع في مشروع عقار ===\n\n";

// Get all active payment gateways
$gateways = PaymentGateway::where('is_active', true)->get();

echo "البوابات المتاحة:\n";
foreach ($gateways as $gateway) {
    echo "  - {$gateway->name} ({$gateway->slug}) - ";
    echo ($gateway->is_test_mode ? "وضع التجربة" : "وضع الإنتاج") . "\n";
    echo "    الدولة: {$gateway->currency}\n";
    echo "    مفعل: " . ($gateway->is_active ? "نعم" : "لا") . "\n";
    echo "    معلومات الاعتماد: " . ($gateway->isConfigured() ? "موجودة ✓" : "غير موجودة ✗") . "\n";
    echo "\n";
}

echo "\n=== اختبار إنشاء معاملة دفع ===\n\n";

// Get first user for testing
$user = User::first();

if (!$user) {
    echo "خطأ: لا يوجد مستخدمين في النظام\n";
    exit(1);
}

echo "المستخدم: {$user->name} (ID: {$user->id})\n\n";

// Test PaySky Gateway
$payskyGateway = PaymentGateway::getBySlug('paysky');

if ($payskyGateway) {
    echo "=== اختبار بوابة PaySky ===\n\n";

    try {
        // Create test transaction
        $transaction = PaymentTransaction::create([
            'payment_gateway_id' => $payskyGateway->id,
            'user_id' => $user->id,
            'amount' => 100.00,
            'currency' => 'EGP',
            'description' => 'معاملة تجريبية - اختبار النظام',
            'status' => 'pending',
            'customer_data' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);

        echo "✓ تم إنشاء معاملة دفع بنجاح\n";
        echo "  - رقم المعاملة: {$transaction->transaction_id}\n";
        echo "  - المبلغ: {$transaction->amount} {$transaction->currency}\n";
        echo "  - الحالة: {$transaction->status}\n";
        echo "  - تاريخ الإنشاء: {$transaction->created_at}\n\n";

        // Test PaySky Service
        echo "=== اختبار PaySky Service ===\n\n";

        $payskyService = new \App\Services\PaymentGateways\PaySkyService();

        echo "✓ تم تفعيل PaySky Service بنجاح\n";
        echo "  - Merchant ID: يبدأ بـ " . substr($payskyService->merchantId ?? 'N/A', 0, 5) . "...\n";
        echo "  - Terminal ID: يبدأ بـ " . substr($payskyService->terminalId ?? 'N/A', 0, 5) . "...\n";
        echo "  - وضع التجربة: " . ($payskyGateway->is_test_mode ? "نعم" : "لا") . "\n\n";

        // Initialize payment
        $paymentData = [
            'user_id' => $user->id,
            'amount' => 100.00,
            'currency' => 'EGP',
            'description' => 'معاملة تجريبية',
            'customer' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
        ];

        $result = $payskyService->initializePayment($paymentData);

        echo "✓ تم تهيئة الدفع بنجاح\n";
        echo "  - رقم المعاملة: {$result['transaction']->transaction_id}\n";
        echo "  - المبلغ بالقرش: {$result['request_data']['AmountTrxn']}\n";
        echo "  - Secure Hash: " . substr($result['request_data']['SecureHash'], 0, 20) . "...\n\n";

    } catch (\Exception $e) {
        echo "✗ خطأ: {$e->getMessage()}\n";
        echo "  الملف: {$e->getFile()}:{$e->getLine()}\n\n";
    }
}

// Test EasyKash Gateway
$easykashGateway = PaymentGateway::getBySlug('easykash');

if ($easykashGateway && $easykashGateway->isConfigured()) {
    echo "=== اختبار بوابة EasyKash ===\n\n";

    try {
        $easykashService = new \App\Services\PaymentGateways\EasyKashService();
        echo "✓ تم تفعيل EasyKash Service بنجاح\n\n";
    } catch (\Exception $e) {
        echo "✗ خطأ: {$e->getMessage()}\n\n";
    }
}

// Test AFS Gateway
$afsGateway = PaymentGateway::getBySlug('afs');

if ($afsGateway && $afsGateway->isConfigured()) {
    echo "=== اختبار بوابة AFS Mastercard ===\n\n";

    try {
        $afsService = new \App\Services\PaymentGateways\AFSService();
        echo "✓ تم تفعيل AFS Service بنجاح\n\n";
    } catch (\Exception $e) {
        echo "✗ خطأ: {$e->getMessage()}\n\n";
    }
}

echo "\n=== تقرير نهائي ===\n\n";

$totalTransactions = PaymentTransaction::count();
$successTransactions = PaymentTransaction::where('status', 'success')->count();
$pendingTransactions = PaymentTransaction::where('status', 'pending')->count();
$failedTransactions = PaymentTransaction::where('status', 'failed')->count();

echo "إجمالي المعاملات: {$totalTransactions}\n";
echo "معاملات ناجحة: {$successTransactions}\n";
echo "معاملات قيد الانتظار: {$pendingTransactions}\n";
echo "معاملات فاشلة: {$failedTransactions}\n\n";

echo "✓ تم اكتمال الاختبار بنجاح!\n";
