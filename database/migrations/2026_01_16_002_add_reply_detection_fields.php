<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Adds reply detection fields to whatsapp_chats table
     */
    public function up(): void
    {
        Schema::table('whatsapp_chats', function (Blueprint $table) {
            $table->boolean('is_reply')->default(false)->after('content');
            $table->string('reply_to_message_id')->nullable()->after('is_reply');
            $table->text('reply_to_content')->nullable()->after('reply_to_message_id');
            $table->string('whatsapp_message_id')->nullable()->after('reply_to_content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_chats', function (Blueprint $table) {
            $table->dropColumn(['is_reply', 'reply_to_message_id', 'reply_to_content', 'whatsapp_message_id']);
        });
    }
};
