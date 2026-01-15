<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');

            $table->string('phone', 20);
            $table->string('name')->nullable();

            // Global Fields (Dynamic based on tenant config)
            $table->json('global_fields')->nullable();      // {"city": "Rajkot", "purpose": "Wholesale"}
            $table->json('global_asked')->nullable();       // {"city": true, "purpose": false}

            // Bot Status
            $table->boolean('bot_enabled')->default(true);
            $table->string('pause_reason')->nullable();

            // Language
            $table->string('detected_language', 20)->default('hi');

            // Activity
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();

            $table->unique(['admin_id', 'phone']);
            $table->index(['admin_id', 'last_activity_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
