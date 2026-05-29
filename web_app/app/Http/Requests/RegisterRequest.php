<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * * Walidację danych podczas rejestracji nowego użytkownika.
 * * Odpowiada za:
 * 1. Sprawdzenie unikalności danych (username i email muszą być unikalne w tabeli `users`).
 * 2. Weryfikację bezpieczeństwa haseł (`confirmed` – sprawdza zgodność z polem password_confirmation).
 * 3. Ograniczenie długości pól dla optymalizacji bazy danych.
 * * Dzięki niej kontroler rejestracji jest odciążony z logiki sprawdzania poprawności formatu danych.
 */

class RegisterRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Zasady walidacji dla formularza rejestracji.
     */
    public function rules(): array
    {
        return [
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed',
            'promo_code' => 'nullable|string|max:50', // opcjonalny
        ];
    }
}
