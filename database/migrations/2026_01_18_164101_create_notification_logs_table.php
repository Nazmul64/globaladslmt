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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            // Firebase App Reference
            $table->foreignId('firebase_app_id')
                ->nullable()
                ->constrained('firebase_apps')
                ->onDelete('cascade');

            // Notification Content
            $table->string('title');
            $table->text('message');
            $table->string('image_url')->nullable();
            $table->string('action_url')->nullable();

            // Notification Type
            $table->enum('notification_type', ['firebase', 'onesignal', 'both'])
                ->default('firebase');

            // Send Configuration
            $table->enum('send_to', ['all', 'specific'])->default('all');
            $table->json('user_ids')->nullable();

            // Delivery Stats
            $table->integer('total_sent')->default(0);
            $table->integer('total_failed')->default(0);
            $table->integer('recipients_count')->default(0);

            // Read Status
            $table->tinyInteger('is_read')->default(0);

            // Timestamps
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();

            // Indexes
            $table->index('firebase_app_id');
            $table->index('notification_type');
            $table->index('send_to');
            $table->index('is_read');
            $table->index('sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
