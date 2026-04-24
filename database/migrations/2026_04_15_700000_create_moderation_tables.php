<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 신고 ──
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->string('target_type', 30);      // community_post, review, media
            $table->unsignedBigInteger('target_id');
            $table->string('reason', 30);            // abuse, spam, adult, false_info, privacy, other
            $table->text('detail')->nullable();
            $table->string('status', 20)->default('pending'); // pending, reviewed, dismissed
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['reporter_id', 'target_type', 'target_id']); // 중복 신고 방지
            $table->index(['target_type', 'target_id']);
            $table->index('status');
        });

        // ── 금칙어 ──
        Schema::create('forbidden_words', function (Blueprint $table) {
            $table->id();
            $table->string('word', 100);
            $table->string('match_type', 20)->default('contains'); // exact, contains, regex
            $table->string('action_type', 20)->default('block');   // block, mask, review, warn
            $table->string('category', 30)->default('general');    // abuse, spam, adult, discrimination, general
            $table->unsignedTinyInteger('severity')->default(1);   // 1~5
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('is_active');
        });

        // ── 사용자 제재 이력 ──
        Schema::create('user_moderation_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action_type', 30);       // warning, restrict_write, restrict_upload, suspend, ban
            $table->string('reason', 50);             // abuse, spam, ad, adult, reports, manual, other
            $table->text('note')->nullable();
            $table->timestamp('starts_at')->useCurrent();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_permanent')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['user_id', 'is_active']);
        });

        // ── 운영 정책 설정 ──
        Schema::create('moderation_policies', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();
            $table->string('value', 200);
            $table->string('description', 200)->nullable();
            $table->timestamps();
        });

        // 기본 정책값 삽입
        \DB::table('moderation_policies')->insert([
            ['key' => 'auto_hide_report_threshold', 'value' => '5', 'description' => '신고 누적 자동숨김 기준', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'spam_post_limit_per_hour', 'value' => '5', 'description' => '시간당 글쓰기 제한', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'review_limit_per_day', 'value' => '10', 'description' => '일일 후기 작성 제한', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'image_upload_limit_per_post', 'value' => '5', 'description' => '게시글당 이미지 제한', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'forbidden_word_default_action', 'value' => 'block', 'description' => '금칙어 기본 처리(block/mask/review)', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ── 운영 로그 ──
        Schema::create('moderation_logs', function (Blueprint $table) {
            $table->id();
            $table->string('target_type', 30);
            $table->unsignedBigInteger('target_id');
            $table->string('action', 30);            // auto_hide, manual_hide, restore, warn, ban, etc.
            $table->string('trigger_type', 20);      // auto, manual
            $table->string('reason', 200)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moderation_logs');
        Schema::dropIfExists('moderation_policies');
        Schema::dropIfExists('user_moderation_actions');
        Schema::dropIfExists('forbidden_words');
        Schema::dropIfExists('reports');
    }
};
