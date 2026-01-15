<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained()->onDelete('cascade');

            // Amount
            $table->decimal('amount', 12, 2);
            $table->string('currency')->default('INR');
            $table->decimal('credits_added', 12, 2)->default(0);

            // Payment Details
            $table->enum('payment_method', ['razorpay', 'stripe', 'upi', 'bank_transfer', 'manual'])->default('manual');
            $table->string('transaction_id')->nullable();
            $table->string('payment_gateway_id')->nullable();
            $table->string('invoice_number')->nullable();

            // Status
            $table->enum('status', ['pending', 'processing', 'success', 'failed', 'refunded'])->default('pending');
            $table->text('failure_reason')->nullable();

            // Metadata
            $table->json('gateway_response')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('super_admins');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
