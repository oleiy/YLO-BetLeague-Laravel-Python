<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Kontroler odpowiedzialny za bezpieczne zakończenie sesji użytkownika.
 */
class LogoutController extends Controller
{
    public function logout(Request $request)
    {
        // Wylogowanie
        Auth::logout();

        // Unieważnienie bieżącej sesji użytkownika
        $request->session()->invalidate();

        // Odświeżenie tokenu CSRF dla bezpieczeństwa (zapobieganie CSRF Fixation)
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Zostałeś pomyślnie wylogowany!');
    }
}
