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
        Schema::create('catalogue_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('field_name', 100);
            $table->string('field_key', 50);
            $table->string('field_type', 20)->default('text'); // text, number, select
            $table->boolean('is_unique')->default(false);
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->json('options')->nullable(); // For select type dropdown options
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->unique(['admin_id', 'field_key']);
            $table->index(['admin_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogue_fields');
    }
};
