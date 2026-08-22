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
        Schema::table('earnings', function (Blueprint $table) {
            if (!Schema::hasColumn('earnings', 'ad_views_before_click')) {
            $table->integer('ad_views_before_click')->default(0)->after('ads_watched_today');
        }

        if (!Schema::hasColumn('earnings', 'invalid_clicks_today')) {
            $table->integer('invalid_clicks_today')->default(0)->after('ad_views_before_click');
        }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('earnings', function (Blueprint $table) {
            $table->dropColumn(['ad_views_before_click', 'invalid_clicks_today']);
        });
    }
};
