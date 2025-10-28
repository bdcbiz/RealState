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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم الباقة بالعربية
            $table->string('name_en'); // اسم الباقة بالإنجليزية
            $table->string('slug')->unique(); // معرف فريد (lite, growth, professional)
            $table->text('description')->nullable(); // وصف الباقة بالعربية
            $table->text('description_en')->nullable(); // وصف الباقة بالإنجليزية
            $table->decimal('monthly_price', 10, 2)->default(0); // السعر الشهري
            $table->decimal('yearly_price', 10, 2)->default(0); // السعر السنوي
            $table->integer('max_users')->default(1); // عدد المستخدمين المسموح
            $table->string('icon')->nullable(); // أيقونة الباقة
            $table->string('color')->nullable(); // لون الباقة
            $table->string('badge')->nullable(); // شارة (مثل: الأكثر شعبية)
            $table->string('badge_en')->nullable(); // شارة بالإنجليزية
            $table->boolean('is_active')->default(true); // نشطة أم لا
            $table->boolean('is_featured')->default(false); // مميزة
            $table->integer('sort_order')->default(0); // الترتيب
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
