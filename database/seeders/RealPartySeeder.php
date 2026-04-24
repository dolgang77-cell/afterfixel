<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\Party;
use App\Support\CuratedNightlifeData;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class RealPartySeeder extends Seeder
{
    public function run(): void
    {
        $clubIds = Club::query()->pluck('id', 'slug')->all();

        foreach (CuratedNightlifeData::partyRecords(CarbonImmutable::today(), $clubIds) as $party) {
            Party::query()->updateOrCreate(
                ['slug' => $party['slug']],
                $party
            );
        }
    }
}
