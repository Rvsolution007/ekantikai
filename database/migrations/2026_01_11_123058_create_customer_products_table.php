<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('lead_id')->nullable()->constrained('leads')->onDelete('set null');

            // Dynamic Fields (Based on Questionnaire Config)
            $table->json('field_values');                   // {"category": "Cabinet", "model": "007"}

            // Unique Key (Generated from unique_key fields)
            $table->string('unique_key', 500);              // "cabinet handles|007|6inch|gold"
            $table->string('line_key', 500)->nullable();    // "919876543210|cabinet handles|007|6inch|gold"

            // Status
            $table->enum('status', ['pending', 'confirmed', 'rejected', 'deleted'])->default('pending');

            $table->timestamps();

            $table->unique(['admin_id', 'customer_id', 'unique_key'], 'customer_product_unique');
            $table->index(['admin_id', 'customer_id']);
            $table->index(['admin_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_products');
    }
};
