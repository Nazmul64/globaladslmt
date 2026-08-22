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
            $table->string('icon_type')->default('code')->after('title'); // 'code' or 'image'
            $table->string('image')->nullable()->after('icon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('home_card_settings', function (Blueprint $table) {
            $table->dropColumn(['icon_type', 'image']);
        });
    }
};
