<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 100)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();

            // 알림 ON/OFF
            $table->boolean('enabled')->default(true);

            // 파티 시작 전 알림 (분 단위, 복수 가능)
            $table->json('remind_before')->nullable(); // 하루 전, 3시간 전

            // 관심 지역 / 장르
            $table->json('preferred_areas')->nullable();
            $table->json('preferred_genres')->nullable();

            // 신규 파티 매칭 알림
            $table->boolean('new_party_alert')->default(true);

            // 오늘 밤 추천 알림 (금/토)
            $table->boolean('tonight_recommendation')->default(true);

            // 웹 푸시 구독 정보 (추후 확장)
            $table->text('push_subscription')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
