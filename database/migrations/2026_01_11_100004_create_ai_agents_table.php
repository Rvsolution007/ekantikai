<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained()->onDelete('cascade');

            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();

            // AI Configuration
            $table->enum('type', ['classifier', 'sales', 'support', 'custom'])->default('sales');
            $table->string('model')->default('gemini-2.5-flash');
            $table->text('system_prompt');
            $table->decimal('temperature', 3, 2)->default(0.7);
            $table->integer('max_tokens')->default(2048);

            // Behavior
            $table->string('persona_name')->nullable();
            $table->string('language')->default('hinglish');
            $table->json('allowed_topics')->nullable();
            $table->json('blocked_keywords')->nullable();

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);

            // Stats
            $table->integer('total_conversations')->default(0);
            $table->integer('successful_responses')->default(0);

            $table->timestamps();

            $table->unique(['admin_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_agents');
    }
};
