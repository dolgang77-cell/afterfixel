<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_filters', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 100);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('target_type', 20);
            $table->string('name', 160);
            $table->json('filters');
            $table->string('filter_hash', 64);
            $table->boolean('notification_enabled')->default(true);
            $table->timestamps();

            $table->unique(['session_id', 'target_type', 'filter_hash'], 'saved_filters_unique_filter');
            $table->index(['user_id', 'target_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_filters');
    }
};
