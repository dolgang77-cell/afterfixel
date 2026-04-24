<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendPartyReminders extends Command
{
    protected $signature = 'nite:send-party-reminders';
    protected $description = '찜한 파티 시작 전 리마인더 알림 발송';

    public function handle(NotificationService $service): int
    {
        $count = $service->sendPartyReminders();
        $this->info("파티 리마인더 {$count}건 발송 완료");
        return self::SUCCESS;
    }
}
