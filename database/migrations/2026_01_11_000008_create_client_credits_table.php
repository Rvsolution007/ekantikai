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
        Schema::create('client_credits', function (Blueprint $table) {
            $table->id();
            $table->integer('client_id')->default(1); // For multi-tenant support
            $table->string('user_number'); // WhatsApp number
            $table->string('user_name')->nullable();
            $table->string('instance')->nullable();
            $table->decimal('fixed_credit', 10, 2)->default(0);
            $table->decimal('extra_credit', 10, 2)->default(0);
            $table->decimal('total_credit', 10, 2)->default(0);
            $table->integer('usage_message')->default(0); // Total messages used
            $table->decimal('usage_credit', 10, 2)->default(0);
            $table->decimal('available_credit', 10, 2)->default(0);
            $table->string('last_sender')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('user_number');
            $table->index('client_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_credits');
    }
};
