<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            [
                'username' => 'admin',
            ],
            [
                'email' => 'admin@betleague.local',
                'password' => Hash::make('admin'),
                'role' => 'admin',
            ]
        );
    }
}
