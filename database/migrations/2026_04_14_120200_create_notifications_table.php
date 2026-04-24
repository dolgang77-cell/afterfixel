<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nite_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 100)->index();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();

            // 알림 유형
            $table->enum('type', [
                'party_reminder',        // 찜한 파티 시작 전
                'new_party_match',       // 관심사 기반 신규 파티
                'tonight_recommendation', // 오늘 밤 추천
            ]);

            // 내용
            $table->string('title');
            $table->text('body');
            $table->string('link')->nullable();       // 클릭 시 이동 URL
            $table->json('data')->nullable();         // 추가 데이터

            // 발송/읽음 상태
            $table->enum('channel', ['in_app', 'web_push'])->default('in_app');
            $table->boolean('is_read')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            $table->index(['session_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nite_notifications');
    }
};
