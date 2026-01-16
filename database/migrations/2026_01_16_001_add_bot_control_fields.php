<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Adds bot control fields to customers table for stop/start via WhatsApp
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('bot_stopped_by_user')->default(false)->after('bot_enabled');
            $table->timestamp('bot_stopped_at')->nullable()->after('bot_stopped_by_user');
            $table->string('bot_stop_reason')->nullable()->after('bot_stopped_at');
        });

        // Add bot control number to admins table
        Schema::table('admins', function (Blueprint $table) {
            $table->string('bot_control_number')->nullable()->after('whatsapp_instance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['bot_stopped_by_user', 'bot_stopped_at', 'bot_stop_reason']);
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('bot_control_number');
        });
    }
};
