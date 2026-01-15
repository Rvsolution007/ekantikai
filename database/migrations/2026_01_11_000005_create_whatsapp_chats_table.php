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
        Schema::create('whatsapp_chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whatsapp_user_id')->constrained('whatsapp_users')->onDelete('cascade');
            $table->string('number'); // WhatsApp number
            $table->enum('role', ['user', 'assistant', 'system'])->default('user');
            $table->text('content');
            $table->string('message_id')->nullable(); // WhatsApp message ID
            $table->string('quoted_message_id')->nullable(); // Reply to message ID
            $table->text('quoted_message_text')->nullable(); // Reply to message text
            $table->string('media_type')->nullable(); // text, image, document, video, audio
            $table->string('media_url')->nullable();
            $table->json('metadata')->nullable(); // Additional message metadata
            $table->timestamps();

            $table->index('number');
            $table->index('message_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_chats');
    }
};
