<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ClubSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RealClubSeeder::class);
    }
}
