<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Add dynamic data column to lead_products for flexible field storage
     */
    public function up(): void
    {
        Schema::table('lead_products', function (Blueprint $table) {
            // Add JSON data column for dynamic fields
            $table->json('data')->nullable()->after('unique_key');

            // Add source tracking
            $table->string('source')->default('bot')->after('data'); // 'bot', 'manual', 'import'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_products', function (Blueprint $table) {
            $table->dropColumn(['data', 'source']);
        });
    }
};
