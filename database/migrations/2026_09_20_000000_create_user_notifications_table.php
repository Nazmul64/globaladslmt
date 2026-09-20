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
        if (!Schema::hasTable('user_notifications')) {
            Schema::create('user_notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('title');
                $table->text('body');
                $table->string('type'); // friend_request, friend_accepted, new_post, chat_message, admin_message, p2p_order, deposit, withdraw
                $table->json('payload')->nullable(); // order_id, post_id, sender_id, etc.
                $table->boolean('is_read')->default(false);
                $table->timestamps();

                $table->index('user_id');
                $table->index('type');
                $table->index('is_read');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
    }
};
