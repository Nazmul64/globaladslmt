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
        Schema::create('user_widhrawrequests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('agent_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'agent_confirmed', 'user_submitted', 'completed', 'rejected'])->default('pending');
            $table->string('transaction_id')->nullable();
            $table->string('sender_account')->nullable();
            $table->string('photo')->nullable();
            $table->decimal('agent_commission', 15, 2)->default(0);
            $table->decimal('admin_commission', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_widhrawrequests');
    }
};
