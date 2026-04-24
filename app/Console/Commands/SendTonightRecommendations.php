<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendTonightRecommendations extends Command
{
    protected $signature = 'nite:send-tonight';
    protected $description = '오늘 밤 추천 알림 발송 (금/토 저녁)';

    public function handle(NotificationService $service): int
    {
        $dayOfWeek = now()->dayOfWeek; // 5=금, 6=토
        if (!in_array($dayOfWeek, [5, 6]) && !$this->option('no-interaction')) {
            $this->info('금/토요일이 아닙니다. --force 로 강제 실행 가능');
            return self::SUCCESS;
        }

        $count = $service->sendTonightRecommendations();
        $this->info("오늘 밤 추천 {$count}건 발송 완료");
        return self::SUCCESS;
    }
}
