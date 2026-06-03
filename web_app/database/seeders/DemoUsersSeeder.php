<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserStat;
use Illuminate\Database\Seeder;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()
            ->count(100)
            ->create()
            ->each(function (User $user) {
                UserStat::updateOrCreate(
                    [
                        'user_id' => $user->id,
                    ],
                    [
                        'balance_points' => fake()->numberBetween(800, 5000),
                        'total_bets' => 0,
                        'won_bets' => 0,
                        'lost_bets' => 0,
                        'accuracy_rate' => 0,
                        'yield' => 0,
                        'current_streak' => 0,
                        'best_streak' => 0,
                        'referral_count' => 0,
                        'is_banned' => false,
                        'ban_until' => null,
                        'daily_login_streak' => fake()->numberBetween(0, 10),
                        'last_daily_reward' => fake()->optional(0.5)->dateTimeBetween('-14 days', 'now'),
                        'total_referral_earned' => 0,
                    ]
                );
            });
    }
}
