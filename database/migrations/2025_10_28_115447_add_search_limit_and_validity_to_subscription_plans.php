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
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->integer('search_limit')->nullable()->after('max_users')->comment('عدد محاولات البحث المسموح بها (-1 = غير محدود)');
            $table->integer('validity_days')->nullable()->after('search_limit')->comment('مدة صلاحية الباقة بالأيام (-1 = غير محدود)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn(['search_limit', 'validity_days']);
        });
    }
};
