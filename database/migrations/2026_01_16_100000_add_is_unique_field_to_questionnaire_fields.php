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
        Schema::table('questionnaire_fields', function (Blueprint $table) {
            if (!Schema::hasColumn('questionnaire_fields', 'is_unique_field')) {
                $table->boolean('is_unique_field')->default(false)->after('unique_key_order');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questionnaire_fields', function (Blueprint $table) {
            $table->dropColumn('is_unique_field');
        });
    }
};
