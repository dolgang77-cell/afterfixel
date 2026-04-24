<?php

namespace App\Console\Commands;

use App\Services\NearbyMessagingService;
use Illuminate\Console\Command;

class PurgeExpiredMessages extends Command
{
    protected $signature = 'nearby:purge-expired-messages';

    protected $description = 'Delete expired nearby chat messages and refresh conversation previews.';

    public function handle(NearbyMessagingService $service): int
    {
        $deleted = $service->purgeExpiredMessages();

        $this->info("Purged {$deleted} expired messages.");

        return self::SUCCESS;
    }
}
