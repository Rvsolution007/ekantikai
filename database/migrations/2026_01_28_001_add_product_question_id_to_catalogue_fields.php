<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Links CatalogueField to ProductQuestion for auto-sync
     */
    public function up(): void
    {
        Schema::table('catalogue_fields', function (Blueprint $table) {
            $table->unsignedBigInteger('product_question_id')->nullable()->after('admin_id');
            $table->foreign('product_question_id')
                ->references('id')
                ->on('product_questions')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalogue_fields', function (Blueprint $table) {
            $table->dropForeign(['product_question_id']);
            $table->dropColumn('product_question_id');
        });
    }
};
