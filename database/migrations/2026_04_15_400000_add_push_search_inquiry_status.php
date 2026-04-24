<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 디바이스 토큰 (FCM/APNs) ──
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('platform', 20);          // android, ios, web
            $table->text('token');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'is_active']);
        });

        // ── 푸쉬 캠페인 ──
        Schema::create('push_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->text('body');
            $table->string('image', 500)->nullable();
            $table->string('link', 500)->nullable();
            $table->string('campaign_type', 30)->default('notice'); // notice, event, party, system, marketing
            $table->string('target_type', 30)->default('all');     // all, logged_in, area, genre, custom
            $table->json('target_query')->nullable();
            $table->string('send_type', 20)->default('immediate'); // immediate, scheduled
            $table->timestamp('scheduled_at')->nullable();
            $table->string('status', 20)->default('draft');        // draft, scheduled, sending, sent, failed, cancelled
            $table->unsignedInteger('target_count')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->unsignedInteger('clicked_count')->default(0);
            $table->unsignedInteger('inflow_count')->default(0);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->index('status');
            $table->index('scheduled_at');
        });

        // ── 푸쉬 배송 로그 ──
        Schema::create('push_delivery_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('push_campaigns')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('pending'); // pending, sent, failed
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamps();
            $table->index(['campaign_id', 'status']);
            $table->index('user_id');
        });

        // ── 푸쉬 유입 로그 ──
        Schema::create('push_inflow_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('push_campaigns')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('path', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index('campaign_id');
        });

        // ── 검색 로그 ──
        Schema::create('search_logs', function (Blueprint $table) {
            $table->id();
            $table->string('keyword', 200);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id', 100)->nullable();
            $table->unsignedInteger('result_count')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->index('keyword');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_logs');
        Schema::dropIfExists('push_inflow_logs');
        Schema::dropIfExists('push_delivery_logs');
        Schema::dropIfExists('push_campaigns');
        Schema::dropIfExists('device_tokens');
    }
};
