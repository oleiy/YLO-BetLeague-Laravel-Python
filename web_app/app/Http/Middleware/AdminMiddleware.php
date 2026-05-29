<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware zabezpieczający panel administratora.
 *
 * Zadania:
 * - sprawdza, czy użytkownik jest zalogowany,
 * - sprawdza, czy użytkownik ma rolę `admin`,
 * - blokuje dostęp zwykłym użytkownikom i gościom.
 *
 * Jeśli warunki nie są spełnione, Laravel zwraca błąd 403 Forbidden.
 */
class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403);
        }

        return $next($request);
    }
}
