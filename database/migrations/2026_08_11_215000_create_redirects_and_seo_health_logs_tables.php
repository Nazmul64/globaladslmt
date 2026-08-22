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
        if (!Schema::hasTable('redirects')) {
            Schema::create('redirects', function (Blueprint $table) {
                $table->id();
                $table->string('source_url')->index();
                $table->string('destination_url');
                $table->integer('status_code')->default(301);
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('hits')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('seo_health_logs')) {
            Schema::create('seo_health_logs', function (Blueprint $table) {
                $table->id();
                $table->string('url')->index();
                $table->string('referer')->nullable();
                $table->text('user_agent')->nullable();
                $table->string('ip_address')->nullable();
                $table->unsignedBigInteger('hits')->default(1);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redirects');
        Schema::dropIfExists('seo_health_logs');
    }
};
