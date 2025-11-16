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
    Schema::create('agentbuysellposts', function (Blueprint $table) {
           $table->id();
            $table->unsignedBigInteger('agent_id')->nullable()->index();
            $table->string('photo')->nullable();
            $table->unsignedInteger('trade_limit')->default(0);
            $table->unsignedInteger('trade_limit_two')->default(0);
            $table->unsignedInteger('rate_balance')->default(0);
            $table->string('payment_name')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('dollarsigends_id');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved');
            $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agentbuysellposts');
    }
};
