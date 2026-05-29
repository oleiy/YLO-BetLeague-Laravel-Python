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
    /**
     * @desc Wylogowuje użytkownika, unieważnia sesję i regeneruje token CSRF.
     * @params Request $request
     * @returns \Illuminate\Http\RedirectResponse
     */
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
