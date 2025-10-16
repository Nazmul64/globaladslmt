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

            // Basic App Settings
            $table->integer('star_io_id')->nullable();
            $table->string('app_theme')->nullable();
            $table->string('home_icon_themes')->nullable();
            $table->string('currency_symbol')->nullable();
            $table->string('enabled')->default('enabled');

            // Task Rewards (Level-wise)
            $table->integer('task_rewards_level_1')->nullable();
            $table->integer('task_rewards_level_2')->nullable();
            $table->integer('task_rewards_level_3')->nullable();
            $table->integer('task_rewards_level_4')->nullable();
            $table->integer('task_rewards_level_5')->nullable();

            // Task Limits (Level-wise)
            $table->integer('task_limit_level_1')->nullable();
            $table->integer('task_limit_level_2')->nullable();
            $table->integer('task_limit_level_3')->nullable();
            $table->integer('task_limit_level_4')->nullable();
            $table->integer('task_limit_level_5')->nullable();

            // Referral & Invalid Click Settings
            $table->integer('refer_commission')->nullable();
            $table->integer('invalid_click_limit')->nullable();
            $table->integer('invalid_deduct')->nullable();
            $table->integer('view_before_click_view_target')->nullable();

            // Time & Rate Settings
            $table->integer('task_break_time_minutes')->nullable();
            $table->integer('button_timer_seconds')->nullable();
            $table->integer('statistics_point_rate')->nullable();
            $table->integer('paywell_point_rate')->nullable();

            // Withdraw & VPN Settings
            $table->string('fixed_withdraw')->default('yes')->nullable();
            $table->string('vpn_modes')->default('yes')->nullable();
            $table->string('vpn_required_in_task_only')->default('yes');
            $table->string('allowed_country')->default('us,uk,au,bangladesh,india');
            $table->string('info_api_key')->nullable();

            // Contact & Policy Links
            $table->string('telegram')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('privacy_policy')->nullable();
            $table->string('how_to_work_link')->nullable();

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
