<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('server_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('server_mode', ['local', 'live'])->default('local');
            $table->string('local_url')->default('http://10.0.2.2:8000');
            $table->string('live_url')->default('https://globalmoney.ltd');
            $table->timestamps();
        });

        // Insert initial default setting
        DB::table('server_settings')->insert([
            'server_mode' => 'local',
            'local_url' => 'http://10.0.2.2:8000',
            'live_url' => 'https://globalmoney.ltd',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('server_settings');
    }
};
