<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Create custom lead statuses table for admin-defined lead stages
     */
    public function up(): void
    {
        Schema::create('lead_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('color')->default('#6366f1');
            $table->integer('order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            // Connected questionnaire field IDs for auto-detection
            $table->json('connected_question_ids')->nullable();
            $table->timestamps();

            $table->index(['admin_id', 'order']);
        });

        // Seed default statuses for existing admins
        $admins = \App\Models\Admin::all();
        $defaultStatuses = [
            ['name' => 'New Lead', 'color' => '#3b82f6', 'order' => 1, 'is_default' => true],
            ['name' => 'Qualified', 'color' => '#f59e0b', 'order' => 2],
            ['name' => 'Negotiation', 'color' => '#8b5cf6', 'order' => 3],
            ['name' => 'Confirmed', 'color' => '#10b981', 'order' => 4],
            ['name' => 'Lost', 'color' => '#ef4444', 'order' => 5],
        ];

        foreach ($admins as $admin) {
            foreach ($defaultStatuses as $status) {
                \App\Models\LeadStatus::create(array_merge($status, [
                    'admin_id' => $admin->id,
                    'slug' => \Illuminate\Support\Str::slug($status['name']),
                ]));
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_statuses');
    }
};
