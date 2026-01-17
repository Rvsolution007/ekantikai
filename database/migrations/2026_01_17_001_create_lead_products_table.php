<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lead_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->foreignId('admin_id')->constrained()->onDelete('cascade');

            // Dynamic product fields (from Product Questions)
            $table->string('category')->nullable();
            $table->string('model')->nullable();
            $table->string('size')->nullable();
            $table->string('finish')->nullable();
            $table->integer('qty')->default(1);
            $table->string('material')->nullable();
            $table->string('packaging')->nullable();

            // Composite unique key for matching (dynamic based on admin's unique_key fields)
            $table->string('unique_key', 500)->nullable()->index();

            // Metadata
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['lead_id', 'unique_key']);
            $table->index(['admin_id', 'lead_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_products');
    }
};
