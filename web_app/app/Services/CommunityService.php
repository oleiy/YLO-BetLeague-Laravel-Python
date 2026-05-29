<?php

namespace App\Services;

use App\Models\Bet;
use App\Models\Fixture;
use App\Models\User;

class CommunityService
{
    /*
    |--------------------------------------------------------------------------
    | COMMUNITY FEED
    |--------------------------------------------------------------------------
    */

    public function getCommunityBets(
        string $date,
        string $sort = 'success_rate',
        bool $analysisOnly = false,
        ?int $userId = null
    ) {

        $bets = Bet::with([
            'user.stats',
            'user.settledBets',
            'user.recentFormBets',
            'fixture.homeTeam',
            'fixture.awayTeam',
            'fixture.league',
            'items.odd.team'
        ])
            ->whereHas('items.odd')
            ->whereHas('fixture', function ($query) use ($date) {
                $query->whereDate('match_date', $date);
            });

        /*
        |------------------------------------------------------------------
        | USER FILTER (SAFE VERSION)
        |------------------------------------------------------------------
        */
        if ($userId) {
            $bets->whereHas('user', function ($q) use ($userId) {
                $q->where('users.id', $userId);
            });
        }

        /*
        |------------------------------------------------------------------
        | ANALYSIS FILTER
        |------------------------------------------------------------------
        */
        if ($analysisOnly) {
            $bets->whereNotNull('analysis')
                ->where('analysis', '!=', '')
                ->whereRaw('LENGTH(TRIM(analysis)) > 0');
        }

        /*
        |------------------------------------------------------------------
        | SORTING
        |------------------------------------------------------------------
        */
        switch ($sort) {

            case 'time':
                $bets->orderBy(
                    Fixture::select('match_date')
                        ->whereColumn('fixtures.id', 'user_bets.fixture_id'),
                    'asc'
                );
                break;

            case 'odds_asc':
                $bets->orderBy('total_odd', 'asc');
                break;

            case 'odds_desc':
                $bets->orderBy('total_odd', 'desc');
                break;

            case 'success_rate':
            default:

                $bets->getQuery()
                    ->leftJoin('user_stats', function ($join) {
                        $join->on('user_stats.user_id', '=', 'user_bets.user_id');
                    })
                    ->select('user_bets.*') // OK
                    ->orderByDesc('user_stats.accuracy_rate')
                    ->select('user_bets.*');

                break;
        }

        $bets = $bets->get();

        /*
        |--------------------------------------------------------------------------
        | DYNAMIC STATS
        |--------------------------------------------------------------------------
        */
        $bets->each(function ($bet) {

            $settledBets = $bet->user->settledBets;

            $wonCount = $settledBets->where('status', 'won')->count();
            $totalSettled = $settledBets->count();

            $bet->user->calculated_accuracy = $totalSettled > 0
                ? round(($wonCount / $totalSettled) * 100, 1)
                : 0;

            $bet->user->recent_form = $bet->user->recentFormBets
                ->take(5)
                ->map(fn($b) => $b->status)
                ->values();
        });

        return $bets;
    }

    /*
    |--------------------------------------------------------------------------
    | WEEKLY RANKING
    |--------------------------------------------------------------------------
    */

    public function getWeeklyRanking()
    {
        $users = User::with([
            'stats',
            'settledBets',
            'recentFormBets'
        ])
            ->withSum([
                'bets as points_gained' => function ($query) {
                    $query->where('status', 'won')
                        ->where('settled_at', '>=', now()->subDays(7));
                }
            ], 'potential_win')
            ->orderByDesc('points_gained')
            ->get()
            ->unique('id')
            ->values();

        return $this->appendUserStats($users);
    }

    /*
    |--------------------------------------------------------------------------
    | MONTHLY RANKING
    |--------------------------------------------------------------------------
    */

    public function getMonthlyRanking()
    {
        $users = User::with([
            'stats',
            'settledBets',
            'recentFormBets'
        ])
            ->withSum([
                'bets as points_gained' => function ($query) {
                    $query->where('status', 'won')
                        ->whereMonth('settled_at', now()->month)
                        ->whereYear('settled_at', now()->year);
                }
            ], 'potential_win')
            ->orderByDesc('points_gained')
            ->get()
            ->unique('id')
            ->values();

        return $this->appendUserStats($users);
    }

    /*
    |--------------------------------------------------------------------------
    | GLOBAL RANKING
    |--------------------------------------------------------------------------
    */

    public function getGlobalRanking()
    {
        $users = User::with([
            'stats',
            'settledBets',
            'recentFormBets'
        ])
            ->join('user_stats', 'user_stats.user_id', '=', 'users.id')
            ->orderByDesc('user_stats.balance_points')
            ->select('users.*')
            ->get()
            ->unique('id')
            ->values();

        return $this->appendUserStats($users);
    }

    /*
    |--------------------------------------------------------------------------
    | HIGHEST ODDS
    |--------------------------------------------------------------------------
    */

    public function getHighestOddsRanking()
    {
        return Bet::with([
            'user.stats',
            'user.recentFormBets',
            'fixture.homeTeam',
            'fixture.awayTeam'
        ])
            ->where('status', 'won')
            ->orderByDesc('total_odd')
            ->take(100)
            ->get()
            ->map(function ($bet) {

                $settledBets = $bet->user->settledBets;

                $wonCount = $settledBets->where('status', 'won')->count();
                $totalSettled = $settledBets->count();

                $bet->user->calculated_accuracy = $totalSettled > 0
                    ? round(($wonCount / $totalSettled) * 100, 1)
                    : 0;

                $bet->user->recent_form = $bet->user->recentFormBets
                    ->take(5)
                    ->map(fn($b) => $b->status)
                    ->values();

                return $bet;
            });
    }

    /*
    |--------------------------------------------------------------------------
    | APPEND USER STATS
    |--------------------------------------------------------------------------
    */

    private function appendUserStats($users)
    {
        return $users->map(function ($user) {

            $settledBets = $user->settledBets;

            $wonCount = $settledBets->where('status', 'won')->count();
            $totalSettled = $settledBets->count();

            $user->calculated_accuracy = $totalSettled > 0
                ? round(($wonCount / $totalSettled) * 100, 1)
                : 0;

            $user->recent_form = $user->recentFormBets
                ->take(5)
                ->map(fn($b) => $b->status)
                ->values();

            return $user;
        });
    }
}
