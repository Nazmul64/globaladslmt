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
        Schema::create('user_widthraws', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_method_id')->nullable()->comment('References id from paymentmethods table');
            $table->string('account_number')->nullable();
            $table->string('wallet_address')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->comment('User who made the deposit');
            $table->decimal('amount')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_widthraws');
    }
};
