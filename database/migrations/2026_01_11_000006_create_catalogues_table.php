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
        Schema::create('catalogues', function (Blueprint $table) {
            $table->id();
            $table->string('category')->nullable(); // Handle category
            $table->string('product_type'); // Cabinet handles, Wardrobe handles, etc.
            $table->string('model_code'); // Model number (007, 9005, etc.)
            $table->string('sizes')->nullable(); // Available sizes (comma-separated or JSON)
            $table->string('pack_per_size')->nullable(); // Packaging per size
            $table->string('finishes')->nullable(); // Available finishes
            $table->string('material')->nullable(); // Material type
            $table->string('image_url')->nullable(); // Product image
            $table->decimal('base_price', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('product_type');
            $table->index('model_code');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogues');
    }
};
