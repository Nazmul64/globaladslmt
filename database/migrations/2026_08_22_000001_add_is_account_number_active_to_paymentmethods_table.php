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
        Schema::table('paymentmethods', function (Blueprint $table) {
            if (!Schema::hasColumn('paymentmethods', 'is_account_number_active')) {
                $table->boolean('is_account_number_active')->default(true)->after('is_exchange_rate_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paymentmethods', function (Blueprint $table) {
            if (Schema::hasColumn('paymentmethods', 'is_account_number_active')) {
                $table->dropColumn('is_account_number_active');
            }
        });
    }
};
