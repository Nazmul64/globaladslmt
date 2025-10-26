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
            $table->decimal('available_balance')->default(0.00);
            $table->unsignedInteger('duration')->comment('Time in minutes');
            $table->string('payment_name')->nullable();
            $table->unsignedBigInteger('category_id');
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
