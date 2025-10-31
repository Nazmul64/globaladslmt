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
    Schema::create('widthrawlimits', function (Blueprint $table) {
        $table->id();
        $table->decimal('max_withdraw_limit');
        $table->decimal('min_withdraw_limit');
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('widthrawlimits');
    }
};
