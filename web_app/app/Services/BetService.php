<?php

namespace App\Services;

use App\Models\Bet;
use App\Models\BetItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * * Serwis obsługujący logikę zawierania zakładów.
 * * Kluczowe funkcje:
 * 1. tworzenie kuponów
 * 2. obliczanie kursów
 * 3. odejmowanie punktów
 * 4. tworzenie pozycji typu
 */

class BetService
{
    public function placeBets(
        User $user,
        array $bets
    ): array {

        $stats = $user->stats;

        /*
        |--------------------------------------------------------------------------
        | LICZENIE WYMAGANEGO BALANSU
        |--------------------------------------------------------------------------
        */

        $requiredBalance = collect($bets)
            ->sum('stake');

        if ($stats->balance_points < $requiredBalance) {

            return [
                'success' => false,
                'message' => 'Niewystarczająca liczba punktów.',
                'status' => 422
            ];
        }

        try {

            DB::transaction(function () use (
                $bets,
                $stats,
                $user
            ) {

                foreach ($bets as $betData) {

                    /*
                    |--------------------------------------------------------------------------
                    | KURS ŁĄCZNY BET BUILDERA
                    |--------------------------------------------------------------------------
                    */

                    $finalTotalOdd =
                        $this->calculateOdds(
                            $betData['selections']
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | POTENCJALNA WYGRANA
                    |--------------------------------------------------------------------------
                    */

                    $potentialWin =
                        round(
                            $betData['stake'] * $finalTotalOdd,
                            2
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | TWORZENIE ZAKŁADU
                    |--------------------------------------------------------------------------
                    */

                    $bet = Bet::create([

                        'user_id' => $user->id,

                        'fixture_id' => $betData['fixture_id'],

                        'total_odd' => round(
                            $finalTotalOdd,
                            2
                        ),

                        'stake' => $betData['stake'],

                        'potential_win' => $potentialWin,

                        'analysis' =>
                            $betData['analysis'] ?? null,

                        'is_betbuilder' =>
                            count($betData['selections']) > 1,

                        'status' => 'pending'
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | POZYCJE ZAKŁADU
                    |--------------------------------------------------------------------------
                    */

                    foreach (
                        $betData['selections']
                        as $selection
                    ) {

                        BetItem::create([

                            'bet_id' => $bet->id,

                            'odd_id' => $selection['odd_id'],

                            'status' => 'pending'
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | ODJĘCIE PUNKTÓW
                    |--------------------------------------------------------------------------
                    */

                    $stats->decrement(
                        'balance_points',
                        $betData['stake']
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | STATYSTYKI
                    |--------------------------------------------------------------------------
                    */

                    $stats->increment('total_bets');
                }
            });

            return [

                'success' => true,

                'message' => 'Typy zostały dodane!',

                'new_balance' =>
                    $stats->fresh()->balance_points,

                'status' => 200
            ];

        } catch (\Exception $e) {

            return [

                'success' => false,

                'message' => $e->getMessage(),

                'status' => 500
            ];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | OBLICZANIE KURSU BET BUILDERA
    |--------------------------------------------------------------------------
    */

    private function calculateOdds(
        array $selections
    ): float {

        usort(
            $selections,
            fn($a, $b) =>
                $b['value'] <=> $a['value']
        );

        $total = 1.0;

        foreach ($selections as $index => $bet) {

            /*
            |--------------------------------------------------------------------------
            | PIERWSZY KURS = 100%
            |--------------------------------------------------------------------------
            */

            if ($index === 0) {

                $total *= $bet['value'];

            } else {

                /*
                |--------------------------------------------------------------------------
                | REDUKCJA KOLEJNYCH RYNKÓW
                |--------------------------------------------------------------------------
                */

                $total *= (
                    1 + ($bet['value'] - 1) * 0.6
                );
            }
        }

        return $total;
    }
}
