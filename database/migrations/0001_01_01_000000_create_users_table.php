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
        // Users table
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('photo')->default('default.png');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('country')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('role', ['is_admin', 'user', 'agent'])->default('user');
            $table->boolean('is_blocked')->default(false);
            // Referral system
            $table->unsignedBigInteger('referred_by')->nullable()->comment('User ID who referred this user');
            $table->unsignedBigInteger('ref_id')->nullable()->comment('Alternate referral user ID');
            $table->string('ref_code')->unique()->nullable();

            // Wallet & commission
            $table->decimal('balance', 15, 2)->default(0.00)->comment('User wallet balance');
            $table->decimal('refer_income', 15, 2)->default(0.00)->comment('Direct referral commission earned');
            $table->decimal('generation_income', 15, 2)->default(0.00)->comment('Generation level commission earned');
            $table->string('wallet_address')->default('default_address');
            $table->string('mobile')->default('mobile');

            $table->rememberToken();
            $table->timestamps();

            // Foreign keys
            $table->foreign('referred_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('ref_id')->references('id')->on('users')->nullOnDelete();
        });

        // Password reset tokens table
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Sessions table
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
