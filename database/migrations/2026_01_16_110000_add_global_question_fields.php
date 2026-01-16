<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('global_questions', function (Blueprint $table) {
            // Add question_name column (alias for field_name for backward compatibility)
            if (!Schema::hasColumn('global_questions', 'question_name')) {
                $table->string('question_name', 50)->nullable()->after('admin_id');
            }

            // Add question_type column (alias for field_type)
            if (!Schema::hasColumn('global_questions', 'question_type')) {
                $table->enum('question_type', ['text', 'select'])->default('text')->after('display_name');
            }

            // Add add_question column for custom question text
            if (!Schema::hasColumn('global_questions', 'add_question')) {
                $table->string('add_question', 500)->nullable()->after('options');
            }

            // Update trigger_position to include more options
            // Note: This may require manual alteration on some databases
        });

        // Copy field_name to question_name where question_name is null
        \DB::table('global_questions')
            ->whereNull('question_name')
            ->update(['question_name' => \DB::raw('field_name')]);
    }

    public function down(): void
    {
        Schema::table('global_questions', function (Blueprint $table) {
            $table->dropColumn(['question_name', 'question_type', 'add_question']);
        });
    }
};
