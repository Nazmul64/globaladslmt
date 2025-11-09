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
        Schema::create('deposit_instructions', function (Blueprint $table) {
            $table->id();
            $table->string('video_url');
            $table->string('member_ship_instructions_title');
            $table->text('member_ship_instructions_description');
            $table->string('deposite_instructions_title');
            $table->text('deposite_instructions_description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposit_instructions');
    }
};
