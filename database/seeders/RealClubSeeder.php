<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Support\CuratedNightlifeData;
use Illuminate\Database\Seeder;

class RealClubSeeder extends Seeder
{
    public function run(): void
    {
        foreach (CuratedNightlifeData::clubs() as $club) {
            Club::query()->updateOrCreate(
                ['slug' => $club['slug']],
                $club
            );
        }
    }
}
