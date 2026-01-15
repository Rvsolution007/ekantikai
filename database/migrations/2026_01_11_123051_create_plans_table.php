<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Create plans table if not exists
        if (!Schema::hasTable('plans')) {
            Schema::create('plans', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->decimal('price', 10, 2)->default(0);
                $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
                $table->json('features')->nullable();
                $table->json('limits')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Update tenants table to add columns if not exists
        if (Schema::hasTable('admins')) {
            Schema::table('admins', function (Blueprint $table) {
                if (!Schema::hasColumn('admins', 'plan_id')) {
                    $table->unsignedBigInteger('plan_id')->nullable()->after('id');
                }
                if (!Schema::hasColumn('admins', 'subscription_status')) {
                    $table->string('subscription_status')->default('trial')->after('plan_id');
                }
                if (!Schema::hasColumn('admins', 'trial_ends_at')) {
                    $table->timestamp('trial_ends_at')->nullable()->after('subscription_status');
                }
            });
        }
    }

    public function down(): void
    {
        // Don't drop plans if it was created by another migration
    }
};
