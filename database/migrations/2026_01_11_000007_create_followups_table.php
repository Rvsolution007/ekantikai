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
        Schema::create('followups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->integer('stage')->default(0);
            $table->string('status')->default('pending'); // pending, completed, skipped
            $table->boolean('has_pending_details')->default(false);
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('next_followup_at')->nullable();
            $table->timestamp('override_next_at')->nullable();
            $table->string('timezone')->default('Asia/Kolkata');
            $table->text('ai_message')->nullable(); // Last AI-generated message
            $table->string('expected_global_field')->nullable(); // Which field we're waiting for
            $table->timestamps();

            $table->index('lead_id');
            $table->index('next_followup_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('followups');
    }
};
