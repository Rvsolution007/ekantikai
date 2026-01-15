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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->string('number'); // WhatsApp number (for quick lookup)
            $table->string('product')->nullable(); // Product type (Cabinet handles, etc.)
            $table->string('model')->nullable(); // Model code
            $table->string('size')->nullable();
            $table->string('finish')->nullable();
            $table->string('qty')->nullable();
            $table->string('material')->nullable();
            $table->string('packaging')->nullable();
            $table->string('line_key')->nullable(); // Unique identifier: number|product|model|size|finish
            $table->timestamps();

            $table->index('number');
            $table->index('lead_id');
            $table->index('line_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
