<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('upload_uuid', 36)->index();
            $table->string('disk', 40)->default('local');
            $table->string('original_path', 500)->nullable();
            $table->string('image_path', 500);
            $table->string('thumb_path', 500);
            $table->string('mime_type', 120);
            $table->unsignedInteger('original_size')->nullable();
            $table->unsignedInteger('optimized_size');
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->string('moderation_provider', 50)->nullable();
            $table->string('moderation_verdict', 20)->default('suspicious');
            $table->unsignedTinyInteger('moderation_score')->nullable();
            $table->json('moderation_labels')->nullable();
            $table->string('status', 20)->default('pending');
            $table->boolean('is_current')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('rejection_reason', 500)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'is_current']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_images');
    }
};
