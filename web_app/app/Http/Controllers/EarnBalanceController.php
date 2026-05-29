<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

/**
 * * Kontroler obsługujący moduł "Zdobywaj balans".
 * Odpowiada za prezentację danych dotyczących systemu poleceń użytkownika.
 * Przelicza liczbę zaproszonych osób na zdobyte punkty bonusowe,
 * umożliwiając użytkownikowi śledzenie efektów jego działań promocyjnych.
 */

class EarnBalanceController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // widok dostępny dla zalogowanego użytkownika
        if (!$user) {

            return view('earn-balance', [
                'isGuest' => true,
            ]);
        }

        $referralsCount =
            $user->referrals()->count();

        $referralEarned =
            $referralsCount * 500;

        return view('earn-balance', [

            'isGuest' => false,

            'user' => $user,

            'referralsCount' => $referralsCount,

            'referralEarned' => $referralEarned,

        ]);
    }
}
