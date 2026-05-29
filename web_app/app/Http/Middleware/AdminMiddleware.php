<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * * Warstwa pośrednicząca (Middleware) zabezpieczająca dostęp do tras administracyjnych.
 * * Odpowiada za:
 * 1. Weryfikację autentyczności użytkownika (czy jest zalogowany).
 * 2. Autoryzację opartą na roli
 * * Działanie:
 * Jeśli użytkownik nie jest zalogowany lub jego rola w bazie danych nie jest
 * równa 'admin', system przerywa żądanie i zwraca błąd 403 (Forbidden).
 */

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            !auth()->check() ||
            auth()->user()->role !== 'admin'
        ) {
            abort(403);
        }

        return $next($request);
    }
}
