<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * * Walidację parametrów filtrowania widoku społeczności.
 * * Odpowiada za:
 * 1. Zapewnienie, że parametr daty jest poprawnym formatem daty.
 * 2. Ograniczenie parametrów sortowania do dozwolonego zbioru wartości (Whitelisting),
 * co zapobiega błędom w zapytaniach SQL (np. SQL injection przez parametr sort).
 */

class CommunityFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => 'nullable|date',
            'sort' => 'nullable|in:success_rate,time,odds_asc,odds_desc', // dzięki temu nie można np DROP TABLE
        ];
    }
}
