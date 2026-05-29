<?php

namespace App\Services;

use App\Models\Fixture;
use App\Models\Bet;
use Carbon\Carbon;

/**
 * * Serwis odpowiedzialny za agregację statystyk systemowych na potrzeby panelu administratora.
 */

class AdminStatsService
{
    // Zlicza mecze z podziałem na ich status
    public function getFixtureStats(): array
    {
        $today = Carbon::today();

        return [
            'todayFixturesCount' => Fixture::whereDate('match_date', $today)->count(),

            'upcomingFixtures' => Fixture::where('match_date', '>', now())
                ->where('status', 'NS')
                ->count(),

            'liveFixtures' => Fixture::whereIn('status', ['LIVE', '1H', '2H'])->count(),

            'finishedFixtures' => Fixture::where('status', 'FT')->count(),
        ];
    }

public function getBetStats(): array
{
    return [

        'activeBets' => Bet::whereIn('status', [
            'pending',
            'active',
            'settling'
        ])->count(),

        'liveBets' => Bet::where('status', 'active')
            ->count(),

        'pendingBets' => Bet::where('status', 'pending')
            ->count(),

        'settledBets' => Bet::whereIn('status', [
            'won',
            'lost',
            'cancelled'
        ])->count(),
    ];
}

    public function getDashboardStats(): array
    {
        return array_merge(
            $this->getFixtureStats(),
            $this->getBetStats()
        );
    }
}
