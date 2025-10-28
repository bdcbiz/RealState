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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();

            // Polymorphic relationship - يمكن ربطها بأي نموذج (Order, Invoice, Subscription, etc)
            $table->nullableMorphs('payable'); // payable_id, payable_type

            // Payment Gateway reference
            $table->foreignId('payment_gateway_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

            // Transaction identifiers
            $table->string('transaction_id')->unique(); // معرف المعاملة الداخلي
            $table->string('gateway_transaction_id')->nullable(); // معرف المعاملة من البوابة (EasyKash reference)
            $table->string('gateway_reference')->nullable(); // مرجع إضافي من البوابة

            // Amount details
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EGP');
            $table->decimal('fee', 10, 2)->default(0); // رسوم البوابة
            $table->decimal('net_amount', 10, 2); // المبلغ الصافي بعد الرسوم

            // Status & metadata
            $table->enum('status', ['pending', 'processing', 'success', 'failed', 'cancelled', 'refunded'])
                  ->default('pending');
            $table->string('payment_method')->nullable(); // card, wallet, cash, etc
            $table->text('description')->nullable();
            $table->text('failure_reason')->nullable();

            // Request/Response data for debugging
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->json('callback_data')->nullable();

            // Customer information
            $table->json('customer_data')->nullable(); // name, email, phone, etc

            // URLs
            $table->text('redirect_url')->nullable();
            $table->text('callback_url')->nullable();

            // Timestamps
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('status');
            $table->index('gateway_transaction_id');
            $table->index(['payable_id', 'payable_type']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
