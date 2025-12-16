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
        Schema::create('chat_instructions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->con­strained()->cascadeOnDelete();
            $table->json('about_you')->nullable();
            $table->json('behaviour')->nullable();
            $table->json('commands')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_instructions');
    }
};
