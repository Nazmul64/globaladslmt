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
        Schema::create('agentcommissonsetups', function (Blueprint $table) {
        $table->id();
        $table->decimal('deposit_agent_commission', 8, 2)->default(0);
        $table->decimal('withdraw_total_commission')->default(0);
        $table->enum('commission_type', ['fixed', 'percent'])->default('percent');
        $table->decimal('agent_share_percent')->default(0);
        $table->decimal('admin_share_percent')->default(0);
        $table->boolean('status')->default(1);
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agentcommissonsetups');
    }
};
