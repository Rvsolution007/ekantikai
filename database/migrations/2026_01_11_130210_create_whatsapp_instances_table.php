<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('whatsapp_instances');

        Schema::create('whatsapp_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');

            $table->string('instance_name', 100);
            $table->string('api_url', 500)->nullable();
            $table->string('api_key', 500)->nullable();

            $table->enum('status', ['disconnected', 'connecting', 'connected', 'qr_pending'])->default('disconnected');
            $table->text('qr_code')->nullable();
            $table->string('phone_number', 20)->nullable();

            $table->timestamp('connected_at')->nullable();
            $table->timestamps();

            $table->unique(['admin_id', 'instance_name']);
            $table->index('instance_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_instances');
    }
};
