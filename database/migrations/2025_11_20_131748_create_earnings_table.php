<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('earnings', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('package_id')->nullable()->index();

            // Daily limits & tracking
            $table->integer('daily_ad_limit')->default(0);
            $table->integer('ads_watched_today')->default(0);

            // Claim cycle control (NEW FIELD)
            $table->integer('last_claimed_cycle')->default(0);

            // Earnings
            $table->decimal('income_per_ad', 10, 2)->default(0);
            $table->decimal('today_earning', 14, 2)->default(0);
            $table->decimal('total_earning', 14, 2)->default(0);

            // Date tracking
            $table->date('earning_date')->nullable()->index();

            // Break timer - used to prevent instant claim
            $table->timestamp('last_break_started')->nullable();

            // Prevent duplicate reward claim within same cycle
            $table->boolean('last_reward_claimed')->default(true);

            $table->timestamps();

            // Foreign key relations
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('package_id')
                ->references('id')
                ->on('packages')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('earnings');
    }
};
