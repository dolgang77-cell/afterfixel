<?php

namespace App\Console\Commands;

use App\Services\NearbyMessagingService;
use Illuminate\Console\Command;

class ExpireStaleNearbyPresence extends Command
{
    protected $signature = 'nearby:expire-stale-presence';

    protected $description = 'Expire stale nearby visibility and venue check-in records.';

    public function handle(NearbyMessagingService $service): int
    {
        $counts = $service->expireStalePresence();

        $this->info(sprintf(
            'Expired %d location statuses and %d venue checkins.',
            $counts['location_statuses'],
            $counts['venue_checkins'],
        ));

        return self::SUCCESS;
    }
}
