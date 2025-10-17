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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('country')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('role', ['is_admin', 'user', 'agent'])->default('user');
             // Referral system
            $table->unsignedBigInteger('referred_by')->nullable()->comment('User ID who referred this user');
            $table->unsignedBigInteger('ref_id')->nullable()->comment('Alternate referral user ID');
            $table->string('ref_code')->unique()->nullable();

            // Wallet & commission
            $table->decimal('balance')->default(0.00)->comment('User wallet balance');
            $table->decimal('refer_income')->default(0.00)->comment('Direct referral commission earned');
            $table->decimal('generation_income')->default(0.00)->comment('Generation level commission earned');
            $table->string('walate_address')->default('default_address');
            $table->string('mobile')->default('mobile');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
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
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
