<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PartySeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RealPartySeeder::class);
    }
}
