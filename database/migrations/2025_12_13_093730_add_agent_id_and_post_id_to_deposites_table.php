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
        Schema::table('deposites', function (Blueprint $table) {
            $table->unsignedBigInteger('agent_id')->nullable()->after('id');
            $table->unsignedBigInteger('post_id')->nullable()->after('agent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deposites', function (Blueprint $table) {
            // $table->dropForeign(['agent_id']);
            // $table->dropForeign(['post_id']);
            $table->dropColumn(['agent_id', 'post_id']);
        });
    }
};
