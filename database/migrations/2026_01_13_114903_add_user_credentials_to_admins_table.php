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
        Schema::table('admins', function (Blueprint $table) {
            $table->string('user_id')->nullable()->after('slug');
            $table->string('password')->nullable()->after('email');
            $table->enum('role', ['super_admin', 'admin', 'manager', 'staff'])->default('admin')->after('password');
            $table->boolean('is_admin_active')->default(true)->after('role');
            $table->timestamp('last_login_at')->nullable()->after('is_admin_active');
            $table->rememberToken()->after('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'password', 'role', 'is_admin_active', 'last_login_at', 'remember_token']);
        });
    }
};
