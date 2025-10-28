<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم البوابة
            $table->string('slug')->unique(); // stripe, paysky, paymob, fawry, geidea, easykash, afs
            $table->string('provider'); // المزود الفعلي
            $table->text('description')->nullable(); // وصف البوابة
            $table->boolean('is_active')->default(false); // نشط/غير نشط
            $table->boolean('is_default')->default(false); // البوابة الافتراضية
            $table->json('countries')->nullable(); // الدول المخصصة ['SA', 'EG', 'AE']
            $table->json('credentials')->nullable(); // بيانات الاعتماد (API Keys, Secrets, etc.)
            $table->json('config')->nullable(); // إعدادات إضافية
            $table->string('currency')->nullable(); // العملة الافتراضية (USD, SAR, EGP)
            $table->boolean('is_test_mode')->default(true); // وضع الاختبار
            $table->integer('transactions_count')->default(0); // عدد المعاملات
            $table->integer('failed_count')->default(0); // عدد المعاملات الفاشلة
            $table->decimal('success_rate', 5, 2)->default(0); // نسبة النجاح
            $table->decimal('total_amount', 15, 2)->default(0); // إجمالي المبالغ
            $table->timestamp('last_used_at')->nullable(); // آخر استخدام
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
