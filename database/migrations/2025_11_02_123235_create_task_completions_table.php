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
        Schema::create('task_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('package_buy_id')->constrained('packagebuys')->onDelete('cascade');
            $table->decimal('coins_earned', 10, 2)->default(0);
            $table->decimal('bonus_earned', 10, 2)->default(0);
            $table->timestamp('completed_at');
            $table->string('ip_address')->nullable();
            $table->enum('type', ['task', 'bonus_reward'])->default('task');
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'completed_at']);
            $table->index(['package_buy_id', 'completed_at']);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_completions');
    }
};
