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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whatsapp_user_id')->constrained('whatsapp_users')->onDelete('cascade');
            $table->enum('stage', ['New Lead', 'Qualified', 'Confirm', 'Lose'])->default('New Lead');
            $table->string('status')->default('open'); // open, closed, on_hold
            $table->enum('purpose_of_purchase', ['Wholesale', 'Retail', ''])->nullable();
            $table->boolean('purpose_asked')->default(false);
            $table->boolean('city_asked')->default(false);
            $table->foreignId('assigned_to')->nullable()->constrained('super_admins')->onDelete('set null');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('sheet_exported_at')->nullable();
            $table->integer('lead_score')->default(0);
            $table->string('lead_quality')->default('cold'); // cold, warm, hot, at_risk
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('stage');
            $table->index('status');
            $table->index('assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
