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
        Schema::create('subscription_pricing_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_plan_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // نوع الدفع (bank_transfer, local_card, international_card)
            $table->string('name'); // الاسم بالعربية
            $table->string('name_en'); // الاسم بالإنجليزية
            $table->decimal('percentage', 5, 2)->default(0); // النسبة المئوية (مثل 2.4%)
            $table->decimal('fixed_fee', 10, 2)->default(0); // الرسوم الثابتة (مثل 1 ريال)
            $table->integer('sort_order')->default(0); // الترتيب
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_pricing_tiers');
    }
};
