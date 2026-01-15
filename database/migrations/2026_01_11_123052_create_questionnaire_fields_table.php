<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('questionnaire_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');

            // Field Definition
            $table->string('field_name', 50);               // "category", "model", "size"
            $table->string('display_name', 100);            // "Product Category"
            $table->enum('field_type', ['text', 'number', 'select', 'multiselect'])->default('text');

            // Question Configuration
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);

            // Unique Key Configuration (Critical for row identification)
            $table->boolean('is_unique_key')->default(false);
            $table->integer('unique_key_order')->nullable();

            // Options Source
            $table->enum('options_source', ['manual', 'catalogue', 'dynamic'])->default('manual');
            $table->json('options_manual')->nullable();      // Manual options
            $table->string('catalogue_field', 50)->nullable(); // Which catalogue field to use

            // Validation
            $table->json('validation_rules')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['admin_id', 'field_name']);
            $table->index(['admin_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questionnaire_fields');
    }
};
