<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Link leads to custom statuses and add bot control per lead
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Link to custom lead status
            $table->foreignId('lead_status_id')->nullable()->after('stage')->constrained()->nullOnDelete();
            // Whether bot is active for this specific lead
            $table->boolean('bot_active')->default(true)->after('status');
            // All required questions completed
            $table->boolean('completed_all_questions')->default(false)->after('bot_active');
            // Product confirmations from AI extraction
            $table->json('product_confirmations')->nullable()->after('collected_data');
            // Detected language for this lead's conversation
            $table->string('detected_language', 10)->nullable()->after('product_confirmations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['lead_status_id']);
            $table->dropColumn([
                'lead_status_id',
                'bot_active',
                'completed_all_questions',
                'product_confirmations',
                'detected_language'
            ]);
        });
    }
};
