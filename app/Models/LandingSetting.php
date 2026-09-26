<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingSetting extends Model
{
    use HasFactory;

    protected $table = 'landing_settings';

    protected $guarded = [];

    /**
     * Get or create default landing page settings singleton.
     */
    public static function getSettings(): self
    {
        $settings = self::first();

        if (!$settings) {
            $settings = self::create([
                'hero_badge' => 'Verified & Secure Platform',
                'hero_title' => 'Next-Gen Micro-Earning & P2P Trading Ecosystem',
                'hero_subtitle' => 'Welcome to Global Money Ltd. Earn daily rewards by completing engaging micro-tasks, trade USDT effortlessly with verified agents, and manage deposits & withdrawals with enterprise-level security.',
                'app_name' => 'Globalmoney ltd',
                'app_publisher' => 'BD IT POINT',
                'app_meta' => 'Contains ads · In-app purchases',
                'rating_score' => '4.8',
                'rating_count' => '1K+ reviews',
                'downloads_count' => '100+',
                'content_rating' => 'Rated for 3+',
                'device_compatibility' => 'This app is available for your Android devices',
                'play_store_url' => 'https://play.google.com/store/apps/details?id=com.globalmoneyltd.globalmoneyltd',
                'stat_1_value' => '100%',
                'stat_1_label' => 'Secure Transactions',
                'stat_2_value' => '24/7',
                'stat_2_label' => 'Live Agent Support',
                'stat_3_value' => '4.8 ★',
                'stat_3_label' => 'User Rating',
                'features_badge' => 'Platform Highlights',
                'features_title' => 'Designed for Ease, Built for Security',
                'features_subtitle' => 'Experience cutting-edge finance and task management engineered with modern transparency and unmatched speed.',
                'feat_1_title' => 'P2P USDT Trading',
                'feat_1_desc' => 'Direct peer-to-peer cryptocurrency buying and selling with authorized agents with automated escrow protection.',
                'feat_2_title' => 'Daily Micro Earning',
                'feat_2_desc' => 'Earn steady income every single day by viewing promotional advertisements and completing verified daily challenges.',
                'feat_3_title' => 'KYC & Verified Badges',
                'feat_3_desc' => 'Full identity verification ensures all users, agents, and community members in social feed maintain trusted reputations.',
                'feat_4_title' => 'Social Feed & Agent Chat',
                'feat_4_desc' => 'Share posts, connect with fellow members, and receive 1-on-1 instant support from registered financial agents.',
                'feat_5_title' => 'Instant Withdrawals',
                'feat_5_desc' => 'Multiple mobile payment methods (bKash, Nagad, Rocket, USDT) with fast manual and automated verification.',
                'feat_6_title' => 'Multi-Level Referrals',
                'feat_6_desc' => 'Earn generous bonus commissions on every package purchase made by your invited direct and indirect referrals.',
                'cta_title' => 'Start Earning Today with Global Money Ltd',
                'cta_subtitle' => 'Install the official Android application from Google Play to access daily tasks, and live P2P trading portal.',
                'footer_about' => 'Leading digital micro-earning, advertising and P2P financial technology ecosystem empowering users worldwide with trusted earning opportunities.',
                'footer_copyright' => 'Designed for High Performance & User Security',
            ]);
        }

        return $settings;
    }
}
