<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reply_templates', function (Blueprint $table) {
            $table->id();
            $table->string('actor_type', 20)->default('shared');
            $table->string('category', 50)->nullable();
            $table->string('title', 120);
            $table->text('body');
            $table->string('intent_type', 30)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['actor_type', 'intent_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reply_templates');
    }
};
