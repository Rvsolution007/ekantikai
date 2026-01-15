<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('whatsapp_users', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique(); // WhatsApp number (primary identifier)
            $table->string('name')->nullable();
            $table->string('instance')->nullable(); // WhatsApp instance name
            $table->string('city')->nullable();
            $table->boolean('bot_enabled')->default(true);
            $table->string('pause_reason')->nullable();
            $table->string('conversation_mode')->default('ai_bot'); // ai_bot, human_only, hybrid
            $table->boolean('catalog_sent')->default(false);
            $table->timestamp('catalog_sent_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();

            $table->index('number');
            $table->index('bot_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_users');
    }
};
