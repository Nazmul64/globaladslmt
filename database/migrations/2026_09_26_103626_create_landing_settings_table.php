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
        Schema::create('landing_settings', function (Blueprint $table) {
            $table->id();
            
            // Hero Section
            $table->string('hero_badge')->nullable()->default('Verified & Secure Platform');
            $table->string('hero_title')->nullable()->default('Next-Gen Micro-Earning & P2P Trading Ecosystem');
            $table->text('hero_subtitle')->nullable();
            
            // App Details Card (Google Play Showcase)
            $table->string('app_name')->nullable()->default('Globalmoney ltd');
            $table->string('app_publisher')->nullable()->default('BD IT POINT');
            $table->string('app_meta')->nullable()->default('Contains ads · In-app purchases');
            $table->string('rating_score')->nullable()->default('4.8');
            $table->string('rating_count')->nullable()->default('1K+ reviews');
            $table->string('downloads_count')->nullable()->default('100+');
            $table->string('content_rating')->nullable()->default('Rated for 3+');
            $table->string('device_compatibility')->nullable()->default('This app is available for your Android devices');
            $table->text('play_store_url')->nullable();
            
            // Hero Stats
            $table->string('stat_1_value')->nullable()->default('100%');
            $table->string('stat_1_label')->nullable()->default('Secure Transactions');
            $table->string('stat_2_value')->nullable()->default('24/7');
            $table->string('stat_2_label')->nullable()->default('Live Agent Support');
            $table->string('stat_3_value')->nullable()->default('4.8 ★');
            $table->string('stat_3_label')->nullable()->default('User Rating');
            
            // Features / Highlights Section
            $table->string('features_badge')->nullable()->default('Platform Highlights');
            $table->string('features_title')->nullable()->default('Designed for Ease, Built for Security');
            $table->text('features_subtitle')->nullable();
            
            // Feature Cards 1-6
            $table->string('feat_1_title')->nullable()->default('P2P USDT Trading');
            $table->text('feat_1_desc')->nullable();
            $table->string('feat_2_title')->nullable()->default('Daily Micro Earning');
            $table->text('feat_2_desc')->nullable();
            $table->string('feat_3_title')->nullable()->default('KYC & Verified Badges');
            $table->text('feat_3_desc')->nullable();
            $table->string('feat_4_title')->nullable()->default('Social Feed & Agent Chat');
            $table->text('feat_4_desc')->nullable();
            $table->string('feat_5_title')->nullable()->default('Instant Withdrawals');
            $table->text('feat_5_desc')->nullable();
            $table->string('feat_6_title')->nullable()->default('Multi-Level Referrals');
            $table->text('feat_6_desc')->nullable();
            
            // Bottom CTA Banner
            $table->string('cta_title')->nullable()->default('Start Earning Today with Global Money Ltd');
            $table->text('cta_subtitle')->nullable();
            
            // Footer Info
            $table->text('footer_about')->nullable();
            $table->string('footer_copyright')->nullable()->default('Designed for High Performance & User Security');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_settings');
    }
};
