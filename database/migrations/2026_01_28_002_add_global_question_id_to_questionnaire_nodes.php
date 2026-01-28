<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Links QuestionnaireNode to GlobalQuestion for flowchart integration
     */
    public function up(): void
    {
        Schema::table('questionnaire_nodes', function (Blueprint $table) {
            $table->unsignedBigInteger('global_question_id')->nullable()->after('questionnaire_field_id');
            $table->foreign('global_question_id')
                ->references('id')
                ->on('global_questions')
                ->onDelete('set null');

            // Add lead_status_id for status tracking
            $table->unsignedBigInteger('lead_status_id')->nullable()->after('global_question_id');
            $table->foreign('lead_status_id')
                ->references('id')
                ->on('lead_statuses')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questionnaire_nodes', function (Blueprint $table) {
            $table->dropForeign(['global_question_id']);
            $table->dropColumn('global_question_id');
            $table->dropForeign(['lead_status_id']);
            $table->dropColumn('lead_status_id');
        });
    }
};
