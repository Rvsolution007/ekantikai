<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admin_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained()->onDelete('cascade');

            // Credits
            $table->decimal('total_credits', 12, 2)->default(0);
            $table->decimal('used_credits', 12, 2)->default(0);
            $table->decimal('available_credits', 12, 2)->default(0);

            // Limits
            $table->integer('monthly_message_limit')->default(1000);
            $table->integer('monthly_messages_used')->default(0);
            $table->integer('ai_calls_limit')->default(500);
            $table->integer('ai_calls_used')->default(0);

            // Rates
            $table->decimal('credit_per_message', 8, 4)->default(0.10);
            $table->decimal('credit_per_ai_call', 8, 4)->default(0.50);

            // Status
            $table->boolean('low_credit_notified')->default(false);
            $table->timestamp('last_reset_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_credits');
    }
};
