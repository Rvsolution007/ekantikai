<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Enhance catalogue table for dynamic columns, media support
     */
    public function up(): void
    {
        Schema::table('catalogues', function (Blueprint $table) {
            // Unique field value for product identification
            $table->string('unique_field_value')->nullable()->after('model_code');
            // Video URL for product demo
            $table->string('video_url')->nullable()->after('image_url');
            // Multiple images array
            $table->json('images')->nullable()->after('video_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalogues', function (Blueprint $table) {
            $table->dropColumn(['unique_field_value', 'video_url', 'images']);
        });
    }
};
