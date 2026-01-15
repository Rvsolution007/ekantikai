<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained()->onDelete('cascade');

            $table->string('name');
            $table->text('description')->nullable();

            // Trigger Configuration
            $table->enum('trigger_type', ['message_received', 'keyword', 'schedule', 'lead_stage', 'manual'])->default('message_received');
            $table->json('trigger_conditions')->nullable();

            // Actions
            $table->json('actions'); // Array of action objects

            // Scheduling (for scheduled triggers)
            $table->string('schedule_cron')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->timestamp('last_run_at')->nullable();

            // Status
            $table->boolean('is_active')->default(true);
            $table->integer('execution_count')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflows');
    }
};
