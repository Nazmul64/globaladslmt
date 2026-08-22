<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('firebase_apps', function (Blueprint $table) {
            $table->id();
            $table->string('app_name');
            $table->string('package_name')->unique();
            $table->json('firebase_credentials');
            $table->text('server_key')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('package_name');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('firebase_apps');
    }
};
