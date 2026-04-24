<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nearby_visibility_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('is_enabled')->default(false);
            $table->boolean('is_visible')->default(false);
            $table->string('share_scope', 20)->default('off');
            $table->boolean('hide_exact_venue')->default(true);
            $table->boolean('foreign_mode')->default(false);
            $table->json('preferred_languages')->nullable();
            $table->json('preferred_interests')->nullable();
            $table->json('preferred_intentions')->nullable();
            $table->string('profile_gender', 20)->nullable();
            $table->string('profile_age_band', 20)->nullable();
            $table->unsignedSmallInteger('auto_hide_after_minutes')->default(10);
            $table->timestamps();
            $table->index(['is_enabled', 'is_visible']);
            $table->index('share_scope');
        });

        Schema::create('user_location_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('session_id', 120)->nullable();
            $table->decimal('last_lat', 10, 7)->nullable();
            $table->decimal('last_lng', 10, 7)->nullable();
            $table->decimal('last_accuracy_m', 8, 2)->nullable();
            $table->string('last_area', 40)->nullable();
            $table->string('last_geohash', 32)->nullable();
            $table->string('venue_type', 20)->nullable();
            $table->unsignedBigInteger('venue_id')->nullable();
            $table->boolean('is_location_enabled')->default(false);
            $table->boolean('is_visible_nearby')->default(false);
            $table->timestamp('seen_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index(['last_geohash', 'expires_at']);
            $table->index(['venue_type', 'venue_id', 'expires_at']);
            $table->index('seen_at');
        });

        Schema::create('venue_checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('venue_type', 20);
            $table->unsignedBigInteger('venue_id');
            $table->string('source', 20)->default('manual');
            $table->boolean('is_active')->default(true);
            $table->timestamp('checked_in_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'is_active']);
            $table->index(['venue_type', 'venue_id', 'is_active', 'expires_at']);
        });

        Schema::create('user_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blocker_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('blocked_id')->constrained('users')->cascadeOnDelete();
            $table->string('reason', 120)->nullable();
            $table->timestamps();
            $table->unique(['blocker_id', 'blocked_id']);
            $table->index(['blocked_id', 'blocker_id']);
        });

        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_one_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_two_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('starter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('last_message_id')->nullable();
            $table->string('last_message_preview', 160)->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamp('last_read_user_one_at')->nullable();
            $table->timestamp('last_read_user_two_at')->nullable();
            $table->timestamp('left_by_user_one_at')->nullable();
            $table->timestamp('left_by_user_two_at')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->timestamps();
            $table->unique(['user_one_id', 'user_two_id']);
            $table->index(['user_one_id', 'last_message_at']);
            $table->index(['user_two_id', 'last_message_at']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->json('meta')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();
            $table->index(['conversation_id', 'created_at']);
            $table->index(['recipient_id', 'read_at']);
            $table->index('expires_at');
            $table->index(['sender_id', 'created_at']);
        });

        Schema::create('message_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->nullable()->constrained('messages')->nullOnDelete();
            $table->foreignId('conversation_id')->nullable()->constrained('conversations')->nullOnDelete();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reported_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason', 30);
            $table->text('detail')->nullable();
            $table->text('snapshot_body')->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['message_id', 'reporter_id']);
            $table->index(['status', 'created_at']);
            $table->index(['reported_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_reports');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('user_blocks');
        Schema::dropIfExists('venue_checkins');
        Schema::dropIfExists('user_location_statuses');
        Schema::dropIfExists('nearby_visibility_settings');
    }
};
