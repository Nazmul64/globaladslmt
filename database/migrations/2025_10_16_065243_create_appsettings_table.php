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
        Schema::create('appsettings', function (Blueprint $table) {
            $table->id();

            // Google AdMob
            $table->string('admob_app_id')->nullable();
            $table->string('admob_banner_id')->nullable();
            $table->string('admob_interstitial_id')->nullable();
            $table->string('admob_rewarded_interstitial_id')->nullable();
            $table->string('admob_rewarded_id')->nullable();
            $table->string('admob_native_id')->nullable();
            $table->string('admob_app_open_id')->nullable();

            // Global switch
            $table->boolean('admob_status')->default(true);

            // Basic App Settings
            $table->integer('star_io_id')->nullable();
            $table->integer('invalid_click_limit')->nullable();
            $table->decimal('invalid_deduct', 10, 2)->nullable();
            $table->decimal('view_before_click_view_target', 10, 2)->nullable();

            // Time Settings (fractional values allowed)
            $table->decimal('task_break_time_minutes', 10, 2)->default(1)->comment('Wait time in minutes after ad');
            $table->decimal('button_timer_seconds', 10, 2)->nullable();


            $table->string('vpn_modes')->default('yes')->nullable();
            $table->string('vpn_required_in_task_only')->default('yes');
            $table->string('allowed_country')->default('us,uk,au,bangladesh,india');



            // App Control Settings
            $table->string('registration_status')->nullable();
            $table->string('same_device_login')->default('yes')->nullable();
            $table->string('maintenance_mode')->default('yes')->nullable();
            $table->string('app_version')->nullable();
            $table->string('app_link')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appsettings');
    }
};
