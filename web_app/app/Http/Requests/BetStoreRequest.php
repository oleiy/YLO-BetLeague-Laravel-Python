<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * * Walidacja typów.
 * * Odpowiada za:
 * 1. Zapewnienie integralności struktury zakładu (kolekcja typów w ramach jednego meczu).
 * 2. Weryfikację istnienia kursów w bazie danych (`exists:odds,id`).
 * 3. Ograniczenie długości analizy użytkownika (ochrona bazy przed zbyt dużym tekstem).
 * * Dzięki niej BetController otrzymuje w pełni sprawdzone i bezpieczne dane.
 */
class BetStoreRequest extends FormRequest
{
    public function authorize()
    {
        // true, żeby każdy zalogowany użytkownik mógł wysłać kupon
        return true;
    }

public function rules()
{
    return [
        'bets' => 'required|array|min:1', // nie można postawić niczego tak jakby

        'bets.*.fixture_id' => 'required|integer',      // każdy typ musi mieć mecz
        'bets.*.stake' => 'required|numeric|min:1',     // każdy typ musi mieć stawkę jako liczbe: min 1
        'bets.*.analysis' => 'nullable|string|max:500', // analiza jest opcjonalna (nie więcej niż 500 liter)

        'bets.*.selections' => 'required|array|min:1',  // musi istnieć min 1 zdarzenie

        'bets.*.selections.*.odd_id' => 'required|exists:odds,id',  // kurs musi istnieć na to zdarzenie
        'bets.*.selections.*.market_name' => 'required|string',     // tak samo
        'bets.*.selections.*.outcome_name' => 'required|string',    // tak samo
        'bets.*.selections.*.value' => 'required|numeric',          // tak samo
    ];
}
    // komunikaty błędów w alertach
    public function messages()
    {
        return [
            'selections.required' => 'Kupon nie może być pusty.',
            'stake.min' => 'Minimalna stawka to 1 punkt.',
            'analysis.max' => 'Analiza może mieć maksymalnie 500 znaków.',
        ];
    }
}
