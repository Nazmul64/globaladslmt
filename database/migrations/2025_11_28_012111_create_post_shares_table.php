<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_shares', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('post_id')
                ->constrained('creaetposts')
                ->onDelete('cascade');

            $table->text('share_content')->nullable();

            $table->index('post_id');
            $table->index('user_id');

            $table->timestamps();
            $table->softDeletes();  // added for better data integrity
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_shares');
    }
};
