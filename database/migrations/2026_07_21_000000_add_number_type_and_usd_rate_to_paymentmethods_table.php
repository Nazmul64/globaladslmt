<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paymentmethods', function (Blueprint $table) {
            if (!Schema::hasColumn('paymentmethods', 'number_type')) {
                $table->string('number_type')->default('Account Number')->after('method_number');
            }
            if (!Schema::hasColumn('paymentmethods', 'usd_rate')) {
                $table->string('usd_rate')->nullable()->after('number_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('paymentmethods', function (Blueprint $table) {
            if (Schema::hasColumn('paymentmethods', 'number_type')) {
                $table->dropColumn('number_type');
            }
            if (Schema::hasColumn('paymentmethods', 'usd_rate')) {
                $table->dropColumn('usd_rate');
            }
        });
    }
};
