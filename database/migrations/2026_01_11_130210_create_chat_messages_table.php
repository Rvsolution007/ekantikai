<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');

            $table->enum('role', ['user', 'assistant', 'system']);
            $table->text('content');

            $table->string('message_id', 100)->nullable();
            $table->string('quoted_message_id', 100)->nullable();
            $table->text('quoted_message_text')->nullable();

            $table->string('media_type', 50)->nullable();
            $table->text('media_url')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['admin_id', 'customer_id']);
            $table->index('message_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
