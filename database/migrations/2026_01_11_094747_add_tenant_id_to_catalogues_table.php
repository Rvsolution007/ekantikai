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
        Schema::table('catalogues', function (Blueprint $table) {
            if (!Schema::hasColumn('catalogues', 'admin_id')) {
                $table->unsignedBigInteger('admin_id')->nullable()->after('id');
                $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
                $table->index('admin_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalogues', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn('admin_id');
        });
    }
};
