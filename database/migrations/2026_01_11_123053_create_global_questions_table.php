<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('global_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');

            $table->string('field_name', 50);               // "city", "purpose_of_purchase"
            $table->string('display_name', 100);
            $table->enum('field_type', ['text', 'select'])->default('text');
            $table->json('options')->nullable();            // For select: ["Wholesale", "Retail"]

            // Trigger Configuration
            $table->enum('trigger_position', ['first', 'after_field'])->default('first');
            $table->string('trigger_after_field', 50)->nullable();

            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['admin_id', 'field_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('global_questions');
    }
};
