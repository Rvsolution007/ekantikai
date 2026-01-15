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
            // Make old columns nullable since we now use JSON data column
            if (Schema::hasColumn('catalogues', 'product_type')) {
                $table->string('product_type')->nullable()->change();
            }
            if (Schema::hasColumn('catalogues', 'model_code')) {
                $table->string('model_code')->nullable()->change();
            }
            if (Schema::hasColumn('catalogues', 'category')) {
                $table->string('category')->nullable()->change();
            }
            if (Schema::hasColumn('catalogues', 'sizes')) {
                $table->text('sizes')->nullable()->change();
            }
            if (Schema::hasColumn('catalogues', 'finishes')) {
                $table->text('finishes')->nullable()->change();
            }
            if (Schema::hasColumn('catalogues', 'material')) {
                $table->string('material')->nullable()->change();
            }
            if (Schema::hasColumn('catalogues', 'pack_per_size')) {
                $table->text('pack_per_size')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse - these columns will remain nullable
    }
};
