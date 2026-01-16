<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Create followup templates table for customized automated followups
     */
    public function up(): void
    {
        Schema::create('followup_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            // Message template with {field_name} placeholders
            $table->text('message_template');
            // Delay in minutes before sending this followup
            $table->integer('delay_minutes')->default(60);
            // Order in the followup sequence
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['admin_id', 'order', 'is_active']);
        });

        // Add followup delay setting to admins
        Schema::table('admins', function (Blueprint $table) {
            $table->integer('followup_delay_minutes')->default(60)->after('lead_timeout_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('followup_templates');

        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('followup_delay_minutes');
        });
    }
};
