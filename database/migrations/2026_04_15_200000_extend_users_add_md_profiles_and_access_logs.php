<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── users 테이블 확장 ──
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('status', 20)->default('active')->after('role'); // active, suspended, withdrawn
            $table->timestamp('last_login_at')->nullable()->after('status');
            $table->index('role');
            $table->index('status');
        });

        // ── MD 프로필 ──
        Schema::create('md_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('display_name', 50);
            $table->string('profile_image', 500)->nullable();
            $table->text('intro')->nullable();
            $table->string('contact_info', 200)->nullable();   // 관리자 전용
            $table->string('external_link', 500)->nullable();   // 사용자 공개용 링크
            $table->json('areas')->nullable();
            $table->json('genres')->nullable();
            $table->string('affiliation', 100)->nullable();     // 소속
            $table->text('admin_memo')->nullable();              // 관리자 내부 메모
            $table->string('status', 20)->default('active');     // active, inactive, hidden
            $table->boolean('visible')->default(true);
            $table->unsignedInteger('priority')->default(0);
            $table->timestamps();
            $table->index('status');
            $table->index('visible');
        });

        // ── MD ↔ 클럽 매핑 ──
        Schema::create('md_club', function (Blueprint $table) {
            $table->id();
            $table->foreignId('md_profile_id')->constrained('md_profiles')->cascadeOnDelete();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->boolean('visible')->default(true);
            $table->unsignedInteger('priority')->default(0);
            $table->string('note', 200)->nullable();
            $table->timestamps();
            $table->unique(['md_profile_id', 'club_id']);
        });

        // ── MD ↔ 파티 매핑 ──
        Schema::create('md_party', function (Blueprint $table) {
            $table->id();
            $table->foreignId('md_profile_id')->constrained('md_profiles')->cascadeOnDelete();
            $table->foreignId('party_id')->constrained()->cascadeOnDelete();
            $table->boolean('visible')->default(true);
            $table->unsignedInteger('priority')->default(0);
            $table->string('note', 200)->nullable();
            $table->timestamps();
            $table->unique(['md_profile_id', 'party_id']);
        });

        // ── 접속 로그 ──
        Schema::create('access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id', 100)->nullable();
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->string('device_type', 20)->nullable();   // desktop, mobile, tablet
            $table->string('os', 50)->nullable();
            $table->string('browser', 50)->nullable();
            $table->boolean('is_mobile')->default(false);
            $table->string('path', 500)->nullable();
            $table->string('referer', 500)->nullable();
            $table->boolean('is_logged_in')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->index('user_id');
            $table->index('session_id');
            $table->index('ip_address');
            $table->index('created_at');
            $table->index('is_logged_in');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_logs');
        Schema::dropIfExists('md_party');
        Schema::dropIfExists('md_club');
        Schema::dropIfExists('md_profiles');

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['status']);
            $table->dropColumn(['phone', 'status', 'last_login_at']);
        });
    }
};
