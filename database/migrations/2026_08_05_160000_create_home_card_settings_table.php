<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('home_card_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('title');
            $table->string('icon')->default('help');
            $table->string('bg_color')->default('#4A80F6');
            $table->string('icon_color')->default('#FFFFFF');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed initial 12 default cards
        $defaultCards = [
            [
                'key' => 'start_task',
                'title' => 'Start Task',
                'icon' => 'task_alt',
                'bg_color' => '#4A80F6',
                'icon_color' => '#FFFFFF',
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'profile',
                'title' => 'Profile',
                'icon' => 'person',
                'bg_color' => '#4A80F6',
                'icon_color' => '#FFFFFF',
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'refer',
                'title' => 'Refer',
                'icon' => 'card_giftcard',
                'bg_color' => '#4A80F6',
                'icon_color' => '#FFFFFF',
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'option',
                'title' => 'Option',
                'icon' => 'settings',
                'bg_color' => '#4A80F6',
                'icon_color' => '#FFFFFF',
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'friend_request',
                'title' => 'Friend Request',
                'icon' => 'people',
                'bg_color' => '#4A80F6',
                'icon_color' => '#FFFFFF',
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'withdraw',
                'title' => 'Withdraw',
                'icon' => 'money',
                'bg_color' => '#4A80F6',
                'icon_color' => '#FFFFFF',
                'sort_order' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'p2p',
                'title' => 'P2P',
                'icon' => 'swap_horiz',
                'bg_color' => '#4A80F6',
                'icon_color' => '#FFFFFF',
                'sort_order' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'how_to_work',
                'title' => 'How To Work',
                'icon' => 'info',
                'bg_color' => '#4A80F6',
                'icon_color' => '#FFFFFF',
                'sort_order' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'total_deposit',
                'title' => 'Total Deposit',
                'icon' => 'attach_money',
                'bg_color' => '#4A80F6',
                'icon_color' => '#FFFFFF',
                'sort_order' => 9,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'support',
                'title' => 'Support',
                'icon' => 'support_agent',
                'bg_color' => '#4A80F6',
                'icon_color' => '#FFFFFF',
                'sort_order' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'social_post',
                'title' => 'SocialPost',
                'icon' => 'public',
                'bg_color' => '#4A80F6',
                'icon_color' => '#FFFFFF',
                'sort_order' => 11,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'total_withdraw',
                'title' => 'Total Withdraw',
                'icon' => 'account_balance_wallet',
                'bg_color' => '#4A80F6',
                'icon_color' => '#FFFFFF',
                'sort_order' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('home_card_settings')->insert($defaultCards);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_card_settings');
    }
};
