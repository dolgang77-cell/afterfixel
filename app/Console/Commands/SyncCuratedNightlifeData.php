<?php

namespace App\Console\Commands;

use App\Models\Club;
use App\Models\Party;
use App\Support\CuratedNightlifeData;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class SyncCuratedNightlifeData extends Command
{
    protected $signature = 'nightlife:sync-curated-data {--dry-run : Show what would change without writing to the database}';

    protected $description = 'Sync verified nightlife venues, deactivate legacy closed clubs, and refresh rolling party content.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $today = CarbonImmutable::today();

        $clubSummary = $this->syncClubs($dryRun);
        $partySummary = $this->syncParties($today, $dryRun);

        $this->info(sprintf(
            'clubs: %d created, %d updated, %d deactivated',
            $clubSummary['created'],
            $clubSummary['updated'],
            $clubSummary['deactivated']
        ));

        $this->info(sprintf(
            'parties: %d expired, %d deleted, %d created, %d updated',
            $partySummary['expired'],
            $partySummary['deleted'],
            $partySummary['created'],
            $partySummary['updated']
        ));

        if ($dryRun) {
            $this->warn('dry-run complete: no database changes were written.');
        }

        return self::SUCCESS;
    }

    private function syncClubs(bool $dryRun): array
    {
        $created = 0;
        $updated = 0;

        foreach (CuratedNightlifeData::clubs() as $payload) {
            $club = Club::query()->where('slug', $payload['slug'])->first();

            if (!$club) {
                $created++;

                if (!$dryRun) {
                    Club::query()->create($payload);
                }

                continue;
            }

            if ($this->payloadDiffers($club->only(array_keys($payload)), $payload)) {
                $updated++;

                if (!$dryRun) {
                    $club->fill($payload)->save();
                }
            }
        }

        $deactivateQuery = Club::query()
            ->whereIn('slug', CuratedNightlifeData::legacyClosedClubSlugs())
            ->where('is_active', true);

        $deactivated = $deactivateQuery->count();

        if (!$dryRun && $deactivated > 0) {
            $deactivateQuery->update(['is_active' => false]);
        }

        return compact('created', 'updated', 'deactivated');
    }

    private function syncParties(CarbonImmutable $today, bool $dryRun): array
    {
        $expireQuery = Party::query()
            ->whereDate('event_date', '<', $today->toDateString())
            ->whereIn('status', ['upcoming', 'ongoing']);

        $expired = $expireQuery->count();

        if (!$dryRun && $expired > 0) {
            $expireQuery->update(['status' => 'ended']);
        }

        $generatedPartyQuery = Party::query()->where(function (Builder $query) {
            foreach (CuratedNightlifeData::generatedPartyPrefixes() as $index => $prefix) {
                $method = $index === 0 ? 'where' : 'orWhere';
                $query->{$method}('slug', 'like', $prefix . '-%');
            }
        });

        $deleted = $generatedPartyQuery->count();

        if (!$dryRun && $deleted > 0) {
            $generatedPartyQuery->delete();
        }

        $clubIds = Club::query()->pluck('id', 'slug')->all();
        $created = 0;
        $updated = 0;

        foreach (CuratedNightlifeData::partyRecords($today, $clubIds) as $payload) {
            $party = Party::query()->where('slug', $payload['slug'])->first();

            if (!$party) {
                $created++;

                if (!$dryRun) {
                    Party::query()->create($payload);
                }

                continue;
            }

            if ($this->payloadDiffers($party->only(array_keys($payload)), $payload)) {
                $updated++;

                if (!$dryRun) {
                    $party->fill($payload)->save();
                }
            }
        }

        return compact('expired', 'deleted', 'created', 'updated');
    }

    private function payloadDiffers(array $current, array $payload): bool
    {
        foreach ($payload as $key => $value) {
            if (json_encode($current[$key] ?? null, JSON_UNESCAPED_UNICODE) !== json_encode($value, JSON_UNESCAPED_UNICODE)) {
                return true;
            }
        }

        return false;
    }
}
