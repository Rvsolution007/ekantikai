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
        Schema::table('customer_questionnaire_state', function (Blueprint $table) {
            $table->unsignedBigInteger('current_node_id')->nullable()->after('current_field');
            $table->json('workflow_data')->nullable()->after('current_node_id');
            $table->json('skipped_optional_fields')->nullable()->after('workflow_data');

            // Add foreign key if questionnaire_nodes table exists
            if (Schema::hasTable('questionnaire_nodes')) {
                $table->foreign('current_node_id')
                    ->references('id')
                    ->on('questionnaire_nodes')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_questionnaire_state', function (Blueprint $table) {
            $table->dropForeign(['current_node_id']);
            $table->dropColumn(['current_node_id', 'workflow_data', 'skipped_optional_fields']);
        });
    }
};
