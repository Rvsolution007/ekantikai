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
        Schema::table('product_questions', function (Blueprint $table) {
            $table->text('question_template')->nullable()->after('display_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_questions', function (Blueprint $table) {
            $table->dropColumn('question_template');
        });
    }
};
