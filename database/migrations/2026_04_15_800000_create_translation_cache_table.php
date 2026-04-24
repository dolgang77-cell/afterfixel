<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translation_cache', function (Blueprint $table) {
            $table->id();
            $table->string('hash', 64)->index();         // SHA-256 of source text
            $table->string('source_locale', 5)->default('ko');
            $table->string('target_locale', 5);
            $table->text('source_text');
            $table->text('translated_text');
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['hash', 'target_locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translation_cache');
    }
};
