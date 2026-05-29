<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * * Klasa odpowiedzialna za rygorystyczną walidację kuponów.
 * * Odpowiada za:
 * 1. Zapewnienie integralności struktury zakładu (kolekcja typów w ramach jednego meczu).
 * 2. Weryfikację istnienia kursów w bazie danych (`exists:odds,id`).
 * 3. Ograniczenie długości analizy użytkownika (ochrona bazy przed zbyt dużym tekstem).
 * * Dzięki niej BetController otrzymuje w pełni sprawdzone i bezpieczne dane.
 */
class BetStoreRequest extends FormRequest
{
    /**
     * @desc Określa, czy użytkownik ma uprawnienia do wykonania tego żądania.
     * @returns bool
     */
    public function authorize()
    {
        // Zmieniamy na true, aby każdy zalogowany użytkownik mógł wysłać kupon
        return true;
    }

    /**
     * @desc Definiuje zasady walidacji dla pól kuponu.
     * @returns array
     */
public function rules()
{
    return [
        'bets' => 'required|array|min:1',

        'bets.*.fixture_id' => 'required|integer',
        'bets.*.stake' => 'required|numeric|min:1',
        'bets.*.analysis' => 'nullable|string|max:500',

        'bets.*.selections' => 'required|array|min:1',

        'bets.*.selections.*.odd_id' => 'required|exists:odds,id',
        'bets.*.selections.*.market_name' => 'required|string',
        'bets.*.selections.*.outcome_name' => 'required|string',
        'bets.*.selections.*.value' => 'required|numeric',
    ];
}

    /**
     * @desc Niestandardowe komunikaty błędów w języku polskim.
     * @returns array
     */
    public function messages()
    {
        return [
            'selections.required' => 'Kupon nie może być pusty.',
            'stake.min' => 'Minimalna stawka to 1 punkt.',
            'analysis.max' => 'Analiza może mieć maksymalnie 500 znaków.',
        ];
    }
}
