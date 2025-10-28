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
        // حذف البيانات القديمة
        Schema::table('subscription_features', function (Blueprint $table) {
            $table->dropForeign(['subscription_plan_id']);
            $table->dropColumn(['subscription_plan_id', 'is_included']);
        });

        // إنشاء جدول الربط بين الباقات والميزات
        Schema::create('feature_subscription_plan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_feature_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_included')->default(true);
            $table->timestamps();

            // منع التكرار
            $table->unique(['subscription_plan_id', 'subscription_feature_id'], 'plan_feature_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // حذف جدول الربط
        Schema::dropIfExists('feature_subscription_plan');

        // إعادة الأعمدة القديمة
        Schema::table('subscription_features', function (Blueprint $table) {
            $table->foreignId('subscription_plan_id')->after('id')->constrained()->cascadeOnDelete();
            $table->boolean('is_included')->default(true)->after('feature_en');
        });
    }
};
