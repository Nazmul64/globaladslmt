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
        Schema::table('agent_deposites', function (Blueprint $table) {
          $table->unsignedBigInteger('payment_method_id')->nullable()->after('transaction_id')->comment('Payment method selected by agent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_deposites', function (Blueprint $table) {
             $table->dropColumn('payment_method_id');
        });
    }
};
