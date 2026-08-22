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
        Schema::table('home_card_settings', function (Blueprint $table) {
            $table->string('text_color')->default('#FFFFFF')->after('icon_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('home_card_settings', function (Blueprint $table) {
            $table->dropColumn('text_color');
        });
    }
};
