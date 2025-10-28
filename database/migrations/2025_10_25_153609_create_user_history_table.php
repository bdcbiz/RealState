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
        Schema::create('user_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('action_type'); // 'view_unit', 'search', 'view_compound', etc.
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('cascade');
            $table->foreignId('compound_id')->nullable()->constrained('compounds')->onDelete('cascade');
            $table->text('search_query')->nullable();
            $table->json('metadata')->nullable(); // Additional data (filters, etc.)
            $table->timestamps();

            // Indexes for faster queries
            $table->index(['user_id', 'action_type']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_history');
    }
};
