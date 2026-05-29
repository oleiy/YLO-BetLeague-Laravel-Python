<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * * Klasa odpowiedzialna za walidację danych przesyłanych do kontrolerów administracyjnych.
 * * Odpowiada za:
 * 1. Walidację filtrów listy użytkowników (data, wyszukiwanie, sortowanie).
 * 2. Walidację danych przy edycji profilu użytkownika (unikalność emaila/username z wykluczeniem bieżącego rekordu).
 * 3. Walidację parametrów blokady (ban) konta.
 * * Dzięki tej klasie kontroler otrzymuje tylko "czyste", bezpieczne i sprawdzone dane.
 */

class AdminIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [

            /*
            |--------------------------------------------------------------------------
            | FILTERS
            |--------------------------------------------------------------------------
            */

            'date' => [
                'nullable',
                'date',
            ],

            'search' => [
                'nullable',
                'string',
                'max:255',
            ],

            'sort' => [
                'nullable',

                Rule::in([
                    'username_asc',
                    'username_desc',
                    'newest',
                    'oldest',
                ]),
            ],

            /*
            |--------------------------------------------------------------------------
            | USER UPDATE
            |--------------------------------------------------------------------------
            */

            'username' => [
                'sometimes',
                'required',
                'string',
                'max:255',

                Rule::unique('users')
                    ->ignore($user?->id),
            ],

            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',

                Rule::unique('users')
                    ->ignore($user?->id),
            ],

            'role' => [
                'sometimes',
                'required',

                Rule::in([
                    'admin',
                    'user',
                ]),
            ],

            /*
            |--------------------------------------------------------------------------
            | BAN
            |--------------------------------------------------------------------------
            */

            'is_banned' => [
                'sometimes',
                'required',
                'boolean',
            ],

            'ban_until' => [
                'nullable',
                'date',
                'after:now',
            ],
        ];
    }
}
