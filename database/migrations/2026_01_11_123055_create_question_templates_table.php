<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('question_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');

            $table->string('field_name', 50);               // "category", "model", "city"
            $table->string('language', 20);                 // "hi", "en", "gu"

            $table->text('question_text');                  // "Aapko kaunsa product chahiye?"
            $table->text('confirmation_text')->nullable();  // "Maine {value} note kar liya"
            $table->text('error_text')->nullable();         // "Yeh option available nahi hai"
            $table->text('options_text')->nullable();       // "Options: {options}"

            $table->timestamps();

            $table->unique(['admin_id', 'field_name', 'language']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_templates');
    }
};
