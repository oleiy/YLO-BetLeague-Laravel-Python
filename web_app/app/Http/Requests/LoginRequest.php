<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * * Klasa odpowiedzialna za walidację danych wejściowych w procesie logowania.
 * * Odpowiada za:
 * 1. Wymuszenie obecności pola loginu oraz hasła.
 * 2. Standaryzację komunikatów błędów,
 * * Dzięki zastosowaniu tej klasy, kontroler `LoginController` otrzymuje gwarancję,
 * że dane są poprawnego typu, co pozwala uniknąć obsługi błędów typu `null` wewnątrz logiki autoryzacji.
 */

class LoginRequest extends FormRequest
{

    // Każdy użtywkonik może próbować się zalgować
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Zasady walidacji dla formularza logowania.
     * Login musi istnieć
     * Hasło musi istnieć
     */
    public function rules(): array
    {
        return [
            'login' => 'required|string',
            'password' => 'required|string',
        ];
    }

    /**
     * Komunikaty błędów wyświetlane w panelu logowania
     */
    public function messages(): array
    {
        return [
            'login.required' => 'Login/Email jest wymagany.',
            'password.required' => 'Hasło jest wymagane.',
        ];
    }
}
