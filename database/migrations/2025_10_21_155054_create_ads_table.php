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
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->text('banner_ad_1')->nullable();
            $table->text('banner_ad_2')->nullable();
            $table->text('interstitial')->nullable();
            $table->text('rewarded_video')->nullable();
            $table->text('native')->nullable();
            $table->text('code')->nullable();
            $table->enum('show_mrce_ads', ['enabled', 'disabled'])->default('disabled');
            $table->enum('show_button_timer_ads', ['enabled', 'disabled'])->default('disabled');
            $table->enum('show_banner_ads', ['enabled', 'disabled'])->default('disabled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
