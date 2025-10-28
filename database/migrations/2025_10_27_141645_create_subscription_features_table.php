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
        Schema::create('subscription_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_plan_id')->constrained()->cascadeOnDelete();
            $table->string('feature'); // المميزة بالعربية
            $table->string('feature_en'); // المميزة بالإنجليزية
            $table->boolean('is_included')->default(true); // متضمنة في الباقة
            $table->string('value')->nullable(); // القيمة (مثل: غير محدود، 5 أشخاص)
            $table->string('value_en')->nullable(); // القيمة بالإنجليزية
            $table->integer('sort_order')->default(0); // الترتيب
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_features');
    }
};
