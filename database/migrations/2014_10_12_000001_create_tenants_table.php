<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('company_name')->nullable();
            $table->string('industry')->nullable();
            $table->text('address')->nullable();

            // Subscription
            $table->enum('subscription_plan', ['free', 'basic', 'pro', 'enterprise'])->default('free');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();

            // WhatsApp Config
            $table->string('whatsapp_api_url')->nullable();
            $table->text('whatsapp_api_key')->nullable();
            $table->string('whatsapp_instance')->nullable();
            $table->boolean('whatsapp_connected')->default(false);

            // AI Config
            $table->text('gemini_api_key')->nullable();
            $table->string('ai_model')->default('gemini-2.5-flash');

            // Settings
            $table->string('timezone')->default('Asia/Kolkata');
            $table->string('language')->default('en');
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
