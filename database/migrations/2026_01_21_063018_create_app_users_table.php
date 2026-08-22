<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firebase_app_id')->constrained('firebase_apps')->onDelete('cascade');
            $table->string('user_name');
            $table->string('user_email')->unique();
            $table->string('password');
            $table->text('fcm_token')->nullable();
            $table->enum('device_type', ['android', 'ios'])->default('android');
            $table->timestamps();

            $table->index('firebase_app_id');
            $table->index('user_email');
            $table->index('fcm_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_users');
    }
};
