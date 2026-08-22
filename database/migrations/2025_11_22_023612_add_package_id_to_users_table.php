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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'package_id')) {
                $table->unsignedBigInteger('package_id')->nullable()->after('id');

                // Add foreign key constraint (optional but recommended)
                $table->foreign('package_id')
                    ->references('id')
                    ->on('packages')
                    ->onDelete('set null');
            }
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['package_id']);

            // Then drop column
            $table->dropColumn('package_id');
        });
    }
};
