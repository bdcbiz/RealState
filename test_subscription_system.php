<?php

require __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;

// Bootstrap Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

echo "=== اختبار نظام الاشتراكات ===\n\n";

try {
    // Test 1: Check Free Plan
    echo "1. التحقق من الباقة المجانية:\n";
    $freePlan = SubscriptionPlan::getFreePlan();

    if ($freePlan) {
        echo "   ✓ الباقة المجانية موجودة\n";
        echo "     - الاسم: {$freePlan->name}\n";
        echo "     - عدد محاولات البحث: " . ($freePlan->search_limit == -1 ? 'غير محدود' : $freePlan->search_limit) . "\n";
        echo "     - مدة الصلاحية: " . ($freePlan->validity_days == -1 ? 'غير محدود' : $freePlan->validity_days . ' يوم') . "\n\n";
    } else {
        echo "   ✗ الباقة المجانية غير موجودة!\n\n";
        exit(1);
    }

    // Test 2: Create a test user
    echo "2. إنشاء مستخدم تجريبي:\n";

    // Check if test user already exists
    $testUser = User::where('email', 'subscription_test@test.com')->first();

    if ($testUser) {
        echo "   ⚠ المستخدم موجود بالفعل، سيتم حذفه وإعادة إنشائه\n";
        $testUser->subscriptions()->delete();
        $testUser->delete();
    }

    $newUser = User::create([
        'name' => 'Test Subscription User',
        'email' => 'subscription_test@test.com',
        'password' => bcrypt('password'),
        'phone' => '01234567890',
        'role' => 'customer',
    ]);

    echo "   ✓ تم إنشاء المستخدم: {$newUser->name} (ID: {$newUser->id})\n\n";

    // Test 3: Check if free subscription was auto-assigned
    echo "3. التحقق من تفعيل الباقة المجانية تلقائيًا:\n";
    sleep(1); // Wait for observer to process

    $subscription = $newUser->getCurrentSubscription();

    if ($subscription) {
        echo "   ✓ تم تفعيل الباقة تلقائيًا!\n";
        echo "     - الباقة: {$subscription->subscriptionPlan->name}\n";
        echo "     - تاريخ البدء: {$subscription->started_at}\n";
        echo "     - تاريخ الانتهاء: {$subscription->expires_at}\n";
        echo "     - عدد محاولات البحث المستخدمة: {$subscription->searches_used}\n";
        echo "     - الحالة: {$subscription->status}\n\n";
    } else {
        echo "   ✗ لم يتم تفعيل الباقة تلقائيًا!\n\n";
        exit(1);
    }

    // Test 4: Test search capability
    echo "4. اختبار القدرة على البحث:\n";

    if ($newUser->canSearch()) {
        echo "   ✓ المستخدم يمكنه البحث\n";
        echo "     - محاولات البحث المتبقية: " . $subscription->getRemainingSearches() . "\n\n";
    } else {
        echo "   ✗ المستخدم لا يمكنه البحث\n\n";
    }

    // Test 5: Simulate searches
    echo "5. محاكاة عمليات البحث:\n";

    for ($i = 1; $i <= 6; $i++) {
        $subscription->refresh();

        if ($subscription->canSearch()) {
            $newUser->incrementSearchCount();
            $subscription->refresh();
            echo "   ✓ البحث #{$i} - متبقي: " . $subscription->getRemainingSearches() . "\n";
        } else {
            echo "   ✗ البحث #{$i} - تم تجاوز الحد المسموح!\n";
            echo "     - محاولات البحث المستخدمة: {$subscription->searches_used}\n";
            echo "     - الحد الأقصى: {$subscription->subscriptionPlan->search_limit}\n";
        }
    }

    echo "\n";

    // Test 6: All plans summary
    echo "6. ملخص جميع الباقات:\n";
    $allPlans = SubscriptionPlan::where('is_active', true)
        ->orderBy('sort_order')
        ->get();

    foreach ($allPlans as $plan) {
        echo "   - {$plan->name}:\n";
        echo "     السعر الشهري: {$plan->monthly_price} جنيه\n";
        echo "     محاولات البحث: " . ($plan->search_limit == -1 ? 'غير محدود' : $plan->search_limit) . "\n";
        echo "     مدة الصلاحية: " . ($plan->validity_days == -1 ? 'غير محدود' : $plan->validity_days . ' يوم') . "\n";
        echo "     عدد المميزات: " . $plan->features()->count() . "\n\n";
    }

    echo "✓ جميع الاختبارات اكتملت بنجاح!\n";
    echo "\nملاحظة: يمكنك الآن حذف المستخدم التجريبي من لوحة الإدارة إذا أردت.\n";

} catch (\Exception $e) {
    echo "\n✗ حدث خطأ:\n";
    echo "  الرسالة: {$e->getMessage()}\n";
    echo "  الملف: {$e->getFile()}:{$e->getLine()}\n";
    echo "\n";
}
