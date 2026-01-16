<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Adds flowchart enhancements: ask_digit for optional questions, unique_field flag
     */
    public function up(): void
    {
        Schema::table('questionnaire_nodes', function (Blueprint $table) {
            // How many times to ask optional question (0 = unlimited until answered)
            $table->integer('ask_digit')->default(0)->after('is_required');
            // Track unique field separately from unique key
            $table->boolean('is_unique_field')->default(false)->after('ask_digit');
        });

        // Track how many times each optional question has been asked per customer
        Schema::create('customer_question_ask_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('questionnaire_node_id')->constrained()->cascadeOnDelete();
            $table->integer('ask_count')->default(0);
            $table->timestamps();

            $table->unique(['customer_id', 'questionnaire_node_id'], 'customer_node_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questionnaire_nodes', function (Blueprint $table) {
            $table->dropColumn(['ask_digit', 'is_unique_field']);
        });

        Schema::dropIfExists('customer_question_ask_counts');
    }
};
