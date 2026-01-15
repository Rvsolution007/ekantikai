<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_questionnaire_state', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');

            // Current State
            $table->string('current_field', 50)->nullable();
            $table->integer('current_product_index')->default(0);

            // Completed Fields (Current Item)
            $table->json('completed_fields')->nullable();   // {"category": "Cabinet", "model": "007"}

            // Pending Items (Already Confirmed)
            $table->json('pending_items')->nullable();

            // Gate Status
            $table->boolean('city_asked')->default(false);
            $table->boolean('purpose_asked')->default(false);

            $table->timestamps();

            $table->unique(['admin_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_questionnaire_state');
    }
};
