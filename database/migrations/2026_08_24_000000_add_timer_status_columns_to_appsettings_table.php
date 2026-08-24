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
            if (!Schema::hasColumn('appsettings', 'stario_timer_status')) {
                $table->string('stario_timer_status')->default('yes')->nullable()->after('star_io_id');
            }
            if (!Schema::hasColumn('appsettings', 'admob_timer_status')) {
                $table->string('admob_timer_status')->default('yes')->nullable()->after('admob_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appsettings', function (Blueprint $table) {
            if (Schema::hasColumn('appsettings', 'stario_timer_status')) {
                $table->dropColumn('stario_timer_status');
            }
            if (Schema::hasColumn('appsettings', 'admob_timer_status')) {
                $table->dropColumn('admob_timer_status');
            }
        });
    }
};
