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
        Schema::rename('questionnaire_fields', 'product_questions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('product_questions', 'questionnaire_fields');
    }
};
