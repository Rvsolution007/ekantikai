<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('questionnaire_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');

            // Source and Target Nodes
            $table->foreignId('source_node_id')->constrained('questionnaire_nodes')->onDelete('cascade');
            $table->foreignId('target_node_id')->constrained('questionnaire_nodes')->onDelete('cascade');

            // React Flow handle identifiers
            $table->string('source_handle', 50)->nullable(); // output_1, output_2, etc.
            $table->string('target_handle', 50)->nullable(); // input

            // For conditional connections
            // {field: "answer", operator: "equals|contains|greater", value: "option_value"}
            $table->json('condition')->nullable();

            // Priority for multiple paths (lower = first)
            $table->integer('priority')->default(0);

            // Label for the connection line
            $table->string('label', 50)->nullable();

            $table->timestamps();

            $table->index(['admin_id', 'source_node_id']);
            $table->index(['admin_id', 'target_node_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questionnaire_connections');
    }
};
