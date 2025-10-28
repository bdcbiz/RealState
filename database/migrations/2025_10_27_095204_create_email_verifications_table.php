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
        Schema::create('email_verifications', function (Blueprint $table) {
            $table->id();

            // User (Polymorphic - User or other models)
            $table->morphs('user');

            // Email & Code
            $table->string('email')->index();
            $table->string('code', 6); // 6-digit OTP
            $table->string('token')->unique(); // Unique token for verification

            // Status
            $table->enum('status', ['pending', 'verified', 'expired', 'failed'])->default('pending');

            // Attempts & Security
            $table->integer('attempts')->default(0);
            $table->integer('max_attempts')->default(3);
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            // Expiry
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('code');
            $table->index('token');
            $table->index('status');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_verifications');
    }
};
