<?php

namespace App\Services;

use App\Models\Bet;
use App\Models\Fixture;

/**
 * MyBetsService
 * * Serwis dedykowany pobieraniu i filtrowaniu historii zakładów konkretnego użytkownika.
 * * Kluczowe role:
 * 1. Kontroler nie musi znać struktury relacji (`with`)
 * ani nazw kolumn w bazie, aby pobrać zakłady.
 * 2. Elastyczne filtrowanie: Obsługuje dynamiczne statusy (aktywne, rozliczone) oraz
 * filtry treści (analiza użytkownika)
 */

class MyBetsService
{
    public function getUserBets($userId, $status, $sort, $analysisOnly = false)
    {

        $query = Bet::with([
            'fixture.homeTeam',
            'fixture.awayTeam',
            'fixture.league',
            'items.odd.team'
        ])
            ->where('user_id', $userId);

        /*
        |--------------------------------------------------------------------------
        | STATUS FILTER
        |--------------------------------------------------------------------------
        */

        if ($status === 'active') {

            $query->whereIn('status', [
                'pending',
                'active',
                'settling'
            ]);
        }

        if ($status === 'settled') {

            $query->whereIn('status', [
                'won',
                'lost',
                'cancelled'
            ]);
        }

        if ($status === 'won') {

            $query->where('status', 'won');
        }

        if ($status === 'lost') {

            $query->where('status', 'lost');
        }

        /*
|--------------------------------------------------------------------------
| ANALYSIS FILTER
|--------------------------------------------------------------------------
*/

        if ($analysisOnly) {
            $query->whereNotNull('analysis')
                ->where('analysis', '!=', '');
        }

        /*
        |--------------------------------------------------------------------------
        | SORTING
        |--------------------------------------------------------------------------
        */

        switch ($sort) {

            case 'odds_asc':

                $query->orderBy('total_odd', 'asc');

                break;

            case 'odds_desc':

                $query->orderBy('total_odd', 'desc');

                break;

            case 'time_asc':

                $query->orderBy(
                    Fixture::select('match_date')
                        ->whereColumn(
                            'fixtures.id',
                            'user_bets.fixture_id'
                        ),
                    'asc'
                );

                break;

            case 'time_desc':

                $query->orderBy(
                    Fixture::select('match_date')
                        ->whereColumn(
                            'fixtures.id',
                            'user_bets.fixture_id'
                        ),
                    'desc'
                );

                break;

            case 'date_asc':

                $query->orderBy('created_at', 'asc');

                break;

            case 'date_desc':

            default:

                $query->orderBy('created_at', 'desc');

                break;
        }

        return $query->get();
    }
}
