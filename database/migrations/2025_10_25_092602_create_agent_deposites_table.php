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
        Schema::create('agent_deposites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id')->nullable(); // no foreign key
            $table->decimal('amount', 15, 2)->nullable();
            $table->string('transaction_id')->nullable()->unique();
            $table->string('sender_account')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('photo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_deposites');
    }
};
