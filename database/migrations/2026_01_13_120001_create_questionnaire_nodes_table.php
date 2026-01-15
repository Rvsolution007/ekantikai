<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('questionnaire_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');

            // Node Type: start, question, condition, action, end
            $table->enum('node_type', ['start', 'question', 'condition', 'action', 'end'])->default('question');

            // Display Label
            $table->string('label', 100);

            // Node Configuration (JSON)
            // For question: {field_name, display_name, field_type, options, is_required, is_unique_key}
            // For condition: {field_to_check, conditions: [{operator, value, output_id}]}
            // For action: {action_type, message}
            $table->json('config')->nullable();

            // React Flow position
            $table->integer('pos_x')->default(100);
            $table->integer('pos_y')->default(100);

            // Link to existing QuestionnaireField for sync
            $table->foreignId('questionnaire_field_id')->nullable()->constrained('questionnaire_fields')->onDelete('set null');

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['admin_id', 'node_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questionnaire_nodes');
    }
};
