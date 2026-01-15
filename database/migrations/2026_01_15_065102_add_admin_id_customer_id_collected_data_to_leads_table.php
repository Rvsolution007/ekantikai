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
        Schema::table('leads', function (Blueprint $table) {
            // Add customer_id as alternative to whatsapp_user_id
            if (!Schema::hasColumn('leads', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->after('whatsapp_user_id')->constrained('customers')->onDelete('cascade');
                $table->index('customer_id');
            }

            // Store all collected questionnaire data as JSON
            if (!Schema::hasColumn('leads', 'collected_data')) {
                $table->json('collected_data')->nullable()->after('notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (Schema::hasColumn('leads', 'customer_id')) {
                $table->dropForeign(['customer_id']);
                $table->dropColumn('customer_id');
            }
            if (Schema::hasColumn('leads', 'collected_data')) {
                $table->dropColumn('collected_data');
            }
        });
    }
};
