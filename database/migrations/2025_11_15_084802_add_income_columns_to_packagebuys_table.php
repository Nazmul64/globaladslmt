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
        Schema::table('packagebuys', function (Blueprint $table) {
             $table->string('daily_income')->nullable()->after('amount');
             $table->string('daily_limit')->nullable()->after('daily_income');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packagebuys', function (Blueprint $table) {
           $table->dropColumn(['daily_income', 'daily_limit']);
        });
    }
};
