<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * * Kontroler odpowiedzialny za obsługę procesu rejestracji nowych użytkowników.
 * Odpowiada za:
 * 1. Implementację mechanizmu poleceń użytkowników (Referral System)
 * 2. Inicjalizację unikalnego kodu polecającego dla każdego nowego konta.
 * 3. Tworzenie powiązanego rekordu statystyk (model Stats) z bazowym saldem punktów.
 * 4. Automatyczne uwierzytelnienie użytkownika po pomyślnym utworzeniu konta.
 */

class RegisterController extends Controller
{
    /**
     * Obsługuje proces rejestracji:
     * - Weryfikuje kod promocyjny i aktualizuje statystyki polecającego.
     * - Generuje bezpieczny hash hasła.
     * - Inicjalizuje profil statystyczny użytkownika (punkty startowe + wskaźniki skuteczności).
     */
    public function register(RegisterRequest $request)
    {
        $basePoints = 1000;      // Podstawowe punkty na start
        $referralBonus = 0;      // Bonus za kod polecający
        $referredBy = null;      // ID osoby polecającej

        // 1. Logika obsługi kodu polecającego (refferal)
        if ($request->filled('promo_code')) {
            $code = strtoupper($request->promo_code);
            $referrer = User::where('referral_code', $code)->first(); // szukamy który użytkownik ma przypisany podany kod

            if ($referrer) {
                $referredBy = $referrer->id;
                $referralBonus = 500; // Bonus dla nowego użytkownika za użycie kodu

                // Zwiększenie licznika poleceń u polecającego
                $referrer->stats()->increment('referral_count');
            }
        }

        $totalStartPoints = $basePoints + $referralBonus;

        // 2. Utworzenie nowego rekordu użytkownika w bazie danych
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'referral_code' => Str::random(10), // Generowanie unikalnego kodu dla nowego usera
            'referred_by' => $referredBy,
        ]);

        // 3. Utworzenie powiązanego rekordu statystyk z punktami startowymi
        $user->stats()->create([
            'balance_points' => $totalStartPoints,
            'total_bets' => 0,
            'won_bets' => 0,
            'lost_bets' => 0,
            'accuracy_rate' => 0,
            'yield' => 0,
            'current_streak' => 0,
            'best_streak' => 0,
            'referral_count' => 0,
            'is_banned' => false,
        ]);

        // Automatyczne zalogowanie po rejestracji
        Auth::login($user);

        return redirect('/')->with('success', 'Konto zostało utworzone!');
    }
}
