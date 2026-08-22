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
        Schema::create('themechanges', function (Blueprint $table) {
            $table->id();
            $table->string('color_code', 7)->default('#4361EE'); // hex color only (7 char with #)
            $table->string('name')->nullable(); // optional: light, dark, blue-theme etc.
            $table->boolean('is_active')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        // প্রথমবার ডিফল্ট থিম ঢুকিয়ে দিচ্ছি (যাতে API কখনো null না দেয়)
        DB::table('themechanges')->insert([
            [
                'color_code'    => '#4361EE',
                'name'          => 'Default Blue Theme',
                'is_active'     => true,
                'published_at'  => now(),
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'color_code'    => '#10B981',
                'name'          => 'Emerald Green',
                'is_active'     => false,
                'published_at'  => null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'color_code'    => '#8B5CF6',
                'name'          => 'Purple Violet',
                'is_active'     => false,
                'published_at'  => null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('themechanges');
    }
};
