<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // A
            $table->decimal('balance', 15, 2)->default(0.00);

            // C
            $table->string('country')->nullable();

            // D
            $table->enum('deposite_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('device_type', ['android', 'ios', 'web'])->default('android');
            $table->string('device_id')->nullable()->index();

            // E
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();

            // F
            $table->unsignedBigInteger('firebase_app_id')->nullable();
            $table->text('fcm_token')->nullable();
            $table->timestamp('fcm_updated_at')->nullable();

            // G
            $table->decimal('generation_income', 15, 2)->default(0.00);

            // I
            $table->boolean('is_blocked')->default(false);
            $table->boolean('is_locked_override')->default(false); // ✅ Individual lock control

            // L
            $table->decimal('locked_amount', 15, 2)->default(0.00); // ✅ Lock amount
            $table->timestamp('last_active_at')->nullable();

            // M
            $table->string('mobile')->default('mobile');

            // N
            $table->string('name');

            // O
            $table->string('onesignal_player_id')->nullable();
            $table->timestamp('onesignal_updated_at')->nullable();

            // P
            $table->string('password');
            $table->string('photo')->default('default.png');

            // R
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->string('ref_code')->unique()->nullable();
            $table->unsignedBigInteger('referred_by')->nullable();
            $table->decimal('refer_income', 15, 2)->default(0.00);
            $table->enum('role', ['is_admin', 'user', 'agent'])->default('user');

            // S
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            // W
            $table->string('wallet_address')->default('default_address');

            // Auth
            $table->rememberToken();
            $table->timestamps();

            // Indexes
            $table->index('email');
            $table->index('ref_code');
            $table->index('status');
            $table->index('role');
            $table->index('firebase_app_id');
            $table->index('onesignal_player_id');
        });

        // Foreign Keys
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('referred_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('ref_id')->references('id')->on('users')->onDelete('set null');
        });

        // Password reset tokens
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Sessions
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
            $table->index('user_id');
        });

        Schema::table('sessions', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referred_by']);
            $table->dropForeign(['ref_id']);
        });

        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
