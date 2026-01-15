<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add admin_id to super_admins
        Schema::table('super_admins', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->boolean('is_super_admin')->default(false)->after('role');
        });

        // Add admin_id to whatsapp_users
        Schema::table('whatsapp_users', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->index('admin_id');
        });

        // Add admin_id to leads
        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->index('admin_id');
        });

        // Add admin_id to products
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->after('id')->constrained()->onDelete('cascade');
        });

        // Add admin_id to whatsapp_chats
        Schema::table('whatsapp_chats', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->index('admin_id');
        });

        // Add admin_id to catalogues (only if not already added by earlier migration)
        if (!Schema::hasColumn('catalogues', 'admin_id')) {
            Schema::table('catalogues', function (Blueprint $table) {
                $table->foreignId('admin_id')->nullable()->after('id')->constrained()->onDelete('cascade');
                $table->index('admin_id');
            });
        }

        // Add admin_id to followups
        Schema::table('followups', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->after('id')->constrained()->onDelete('cascade');
        });

        // Update existing super admin
        \DB::table('super_admins')->where('role', 'super_admin')->update(['is_super_admin' => true]);
    }

    public function down(): void
    {
        Schema::table('super_admins', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn(['admin_id', 'is_super_admin']);
        });

        Schema::table('whatsapp_users', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn('admin_id');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn('admin_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn('admin_id');
        });

        Schema::table('whatsapp_chats', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn('admin_id');
        });

        Schema::table('catalogues', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn('admin_id');
        });

        Schema::table('followups', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn('admin_id');
        });
    }
};
