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
        Schema::table('ad_settings', function (Blueprint $table) {
           $table->integer('ad_duration')->default(30)->after('native_ad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ad_settings', function (Blueprint $table) {
             $table->dropColumn('ad_duration');
        });
    }
};
