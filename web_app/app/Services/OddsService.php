<?php

namespace App\Services;

/**
 * * Serwis pomocniczy odpowiedzialny za formatowanie danych dotyczących kursów i rynków.
 * * Kluczowe role:
 * 1. Logika biznesowa specyfikatorów: Odpowiada za to, kiedy do nazwy zakładu
 * (np. "Total Goals") należy dopisać wartość (np. "2.5"), a kiedy nie (zwycięzca rywalizacji).
 * 2. Standaryzacja UI: Gwarantuje, że kursy zawsze wyświetlają się w tym samym formacie
 * (np. dwie liczby po przecinku), co zapewnia profesjonalny wygląd aplikacji.
 */

class OddsService
{
    /**
     * Czy market używa specifiera.
     */
    public function usesSpecifier(string $marketName): bool
    {
        $marketsWithoutSpecifier = [
            'Match Winner',
            '1X2',
            'Double Chance',
            'Both Teams To Score',
            'Draw No Bet'
        ];

        return !in_array(
            $marketName,
            $marketsWithoutSpecifier
        );
    }

    /**
     * Formatuje nazwę typu.
     */
    public function formatOutcome(
        string $outcome,
        ?float $specifier,
        string $market
    ): string {

        if (
            !$specifier
            || !$this->usesSpecifier($market)
        ) {
            return $outcome;
        }

        return
            $outcome
            . ' '
            . number_format($specifier, 1);
    }

    /**
     * Formatuje kurs.
     */
    public function formatOdd(float $value): string
    {
        return number_format($value, 2);
    }
}
