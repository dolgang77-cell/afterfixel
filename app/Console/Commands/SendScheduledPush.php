<?php

namespace App\Console\Commands;

use App\Models\PushCampaign;
use App\Services\PushService;
use Illuminate\Console\Command;

class SendScheduledPush extends Command
{
    protected $signature = 'nite:send-scheduled-push';
    protected $description = '예약된 푸쉬 캠페인을 발송합니다';

    public function handle(PushService $pushService): int
    {
        $campaigns = PushCampaign::scheduledReady()->get();

        if ($campaigns->isEmpty()) {
            return self::SUCCESS;
        }

        foreach ($campaigns as $campaign) {
            $this->info("Sending campaign #{$campaign->id}: {$campaign->title}");
            $pushService->sendCampaign($campaign);
            $this->info("  → sent={$campaign->sent_count}, failed={$campaign->failed_count}");
        }

        $this->info("Total {$campaigns->count()} campaigns processed.");
        return self::SUCCESS;
    }
}
