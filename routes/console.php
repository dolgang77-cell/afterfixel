<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── 알림 스케줄 ──

// 찜한 파티 리마인더: 매 10분마다 체크
Schedule::command('nite:send-party-reminders')->everyTenMinutes();

// 오늘 밤 추천: 금/토 18시
Schedule::command('nite:send-tonight')->weeklyOn(5, '18:00');
Schedule::command('nite:send-tonight')->weeklyOn(6, '18:00');

// 예약 푸쉬 발송: 매분 체크
Schedule::command('nite:send-scheduled-push')->everyMinute();

// 미디어 파생 이미지 누락 자동 복구: 매시간 점검
Schedule::command('media:generate-variants')->hourly()->withoutOverlapping();

if (config('nearby-messaging.enabled')) {
    Schedule::command('nearby:expire-stale-presence')->everyMinute()->withoutOverlapping();
    Schedule::command('nearby:purge-expired-messages')->everyMinute()->withoutOverlapping();
}

Schedule::command('nightlife:sync-curated-data')->dailyAt('12:05')->withoutOverlapping();
