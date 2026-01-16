<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Create AI usage logs table for cost tracking per admin
     */
    public function up(): void
    {
        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained()->cascadeOnDelete();
            // AI model used
            $table->string('model_name');
            // Provider (google, openai, deepseek)
            $table->string('provider')->default('google');
            // Token counts
            $table->integer('input_tokens')->default(0);
            $table->integer('output_tokens')->default(0);
            $table->integer('total_tokens')->default(0);
            // Cost calculation in USD
            $table->decimal('cost_usd', 10, 6)->default(0);
            // Request type for categorization
            $table->string('request_type')->default('message'); // message, extraction, generation
            // Optional metadata
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['admin_id', 'created_at']);
            $table->index(['provider', 'model_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
    }
};
