<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@nightlife.kr'],
            [
                'name'     => '관리자',
                'password' => Hash::make('admin1234!'),
                'role'     => 'super_admin',
            ]
        );
    }
}
