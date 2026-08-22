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
        Schema::table('appsettings', function (Blueprint $table) {
              $table->decimal('ad_timer_seconds', 10, 2)->nullable()->comment('Ad display timer in seconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appsettings', function (Blueprint $table) {
             $table->dropColumn('ad_timer_seconds');
        });
    }
};
