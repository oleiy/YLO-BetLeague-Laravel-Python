<?php

namespace App\Services;

use App\Models\Fixture;
use App\Models\League;
use Carbon\Carbon;

/**
 * * Serwis odpowiedzialny za operacje na danych meczowych (Fixtures).
 * * Kluczowe funkcje:
 * 1. Złożone filtrowanie: Obsługuje dynamiczne warunki dla meczów w zależności
 * od ich statusu i daty (przeszłość vs dzisiaj).
 * 2. Zarządza wydajnym pobieraniem relacji (homeTeam, odds, itp.)
 * 3. Logika dostępności: Sprawdza, czy zakład może zostać postawiony (hasMatchStarted).
 */

class MatchService
{
    /**
     * Pobiera mecze dla wybranej daty z podziałem na ligi
     */
    public function getMatchesByDate(string $date)
    {
        return League::where('is_active', true)
            ->with([
                'fixtures' => function ($query) use ($date) {

                    $query->whereDate('match_date', $date)

                        // DZISIAJ → tylko nierozpoczęte
                        ->when(
                            $date === now()->toDateString(),
                            fn($q) => $q->whereIn('status', [
                                'NS',
                                'not_started',
                                'FT',
                                'finished'
                            ])
                        )

                        // PRZESZŁOŚĆ → tylko zakończone
                        ->when(
                            $date < now()->toDateString(),
                            fn($q) => $q->whereIn('status', ['FT', 'finished'])
                        )

                        ->with([
                            'homeTeam',
                            'awayTeam',
                            'league',
                            'odds',
                            'statistics'
                        ])
                        ->orderBy('match_date', 'asc');
                }
            ])
            ->orderBy('priority', 'desc')
            ->get()
            ->filter(fn($league) => $league->fixtures->count() > 0)
            ->values();
    }

    /**
     * Dashboard - najbliższe mecze.
     */
    public function getDashboardMatches()
    {
        return Fixture::with([
            'homeTeam',
            'awayTeam',
            'league',
            'odds'
        ])
            ->where('status', 'NS')
            ->whereHas('odds')
            ->orderBy('match_date', 'asc')
            ->limit(20)
            ->get();
    }
    /**
     * Pobiera pojedynczy mecz.
     */
    public function getFixtureById(int $id): Fixture
    {
        return Fixture::with([
            'homeTeam',
            'awayTeam',
            'league',
            'odds.team'
        ])->findOrFail($id);
    }

    /**
     * Sprawdza czy mecz już się rozpoczął.
     */
    public function hasMatchStarted(Fixture $fixture): bool
    {
        return
            $fixture->match_date->isPast()
            || !in_array($fixture->status, ['NS', 'not_started']);
    }
}
