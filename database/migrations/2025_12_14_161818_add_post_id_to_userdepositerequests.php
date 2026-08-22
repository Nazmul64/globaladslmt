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
        // ✅ সঠিক table name: userdepositerequests (শেষে s আছে)
        Schema::table('userdepositerequests', function (Blueprint $table) {
            // Check if column doesn't exist before adding
            if (!Schema::hasColumn('userdepositerequests', 'post_id')) {
                $table->unsignedBigInteger('post_id')->nullable()->after('agent_id');

                // Add foreign key
                $table->foreign('post_id')
                    ->references('id')
                    ->on('agentbuysellposts')
                    ->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('userdepositerequests', function (Blueprint $table) {
            if (Schema::hasColumn('userdepositerequests', 'post_id')) {
                $table->dropForeign(['post_id']);
                $table->dropColumn('post_id');
            }
        });
    }
};
