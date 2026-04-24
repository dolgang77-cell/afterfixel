<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 클럽 상세소개 확장 ──
        Schema::table('clubs', function (Blueprint $table) {
            $table->string('short_description', 200)->nullable()->after('description');
            $table->text('full_description')->nullable()->after('short_description');
            $table->string('intro_title', 100)->nullable()->after('full_description');
            $table->text('guide_text')->nullable()->after('intro_title');
            $table->json('highlight_tags')->nullable()->after('guide_text');
        });

        // ── 파티 상세소개 확장 ──
        Schema::table('parties', function (Blueprint $table) {
            $table->string('short_description', 200)->nullable()->after('description');
            $table->text('full_description')->nullable()->after('short_description');
            $table->string('intro_title', 100)->nullable()->after('full_description');
            $table->text('guide_text')->nullable()->after('intro_title');
            $table->json('highlight_tags')->nullable()->after('guide_text');
        });

        // ── 미디어 (승인 흐름 포함) ──
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('owner_type', 30);           // md_profile, club, party, review
            $table->unsignedBigInteger('owner_id');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('uploaded_by_role', 20);      // admin, md, user
            $table->string('file_path', 500);
            $table->string('file_url', 500);
            $table->string('approval_status', 20)->default('pending'); // pending, approved, rejected, hidden
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('rejected_reason', 500)->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['owner_type', 'owner_id']);
            $table->index('approval_status');
            $table->index('uploaded_by_role');
        });

        // ── 후기 ──
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('target_type', 20);           // club, party
            $table->unsignedBigInteger('target_id');
            $table->text('content');
            $table->unsignedTinyInteger('rating')->nullable(); // 1~5
            $table->json('tags')->nullable();              // 분위기, 음악, 입장, 추천 등
            $table->unsignedInteger('like_count')->default(0);
            $table->unsignedInteger('report_count')->default(0);
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();
            $table->index(['target_type', 'target_id']);
            $table->index('user_id');
        });

        // ── 문의 ──
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('target_type', 20)->nullable(); // club, party
            $table->unsignedBigInteger('target_id')->nullable();
            $table->foreignId('assigned_md_id')->nullable()->constrained('md_profiles')->nullOnDelete();
            $table->string('status', 20)->default('pending'); // pending, answered, closed, hidden
            $table->string('subject', 200);
            $table->text('message');
            $table->string('preferred_contact', 100)->nullable();
            $table->date('visit_date')->nullable();
            $table->unsignedTinyInteger('party_size')->nullable();
            $table->timestamps();
            $table->index(['target_type', 'target_id']);
            $table->index('assigned_md_id');
            $table->index('status');
            $table->index('user_id');
        });

        // ── 문의 답변 ──
        Schema::create('inquiry_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inquiry_id')->constrained()->cascadeOnDelete();
            $table->string('author_type', 20);           // user, md, admin
            $table->unsignedBigInteger('author_id');
            $table->text('message');
            $table->boolean('is_internal')->default(false); // 내부 메모
            $table->timestamps();
            $table->index('inquiry_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiry_replies');
        Schema::dropIfExists('inquiries');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('media');

        Schema::table('parties', function (Blueprint $table) {
            $table->dropColumn(['short_description', 'full_description', 'intro_title', 'guide_text', 'highlight_tags']);
        });
        Schema::table('clubs', function (Blueprint $table) {
            $table->dropColumn(['short_description', 'full_description', 'intro_title', 'guide_text', 'highlight_tags']);
        });
    }
};
