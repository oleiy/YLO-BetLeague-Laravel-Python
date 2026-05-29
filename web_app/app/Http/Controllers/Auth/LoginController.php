<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\RewardService;

/**
 * Kontroler obsługujący proces uwierzytelniania użytkowników.
 * Odpowiada za:
 * 1. Logowanie przez email lub nazwę użytkownika (dynamiczne wykrywanie pola).
 * 2. Weryfikację statusu blokady konta (z automatycznym odblokowaniem po czasie).
 * 3. Przyznawanie bonusów za codzienne logowanie (Daily Reward).
 * 4. Przekierowanie zależne od roli użytkownika (Admin vs User).
 */
class LoginController extends Controller
{
    protected RewardService $rewardService;

    public function __construct(
        RewardService $rewardService
    ) {
        $this->rewardService = $rewardService;
    }

    public function login(LoginRequest $request)
    {
        /*
    |--------------------------------------------------------------------------
    | LOGIN FIELD
    |--------------------------------------------------------------------------
    */

        $loginValue = $request->input('login');

        // wykrycie maila lub usernamea
        $loginField = filter_var(
            $loginValue,
            FILTER_VALIDATE_EMAIL
        )
            ? 'email'
            : 'username';

        /*
    |--------------------------------------------------------------------------
    | FIND USER
    |--------------------------------------------------------------------------
    */

        $user = \App\Models\User::with('stats')
            ->where($loginField, $loginValue)
            ->first();

        /*
    |--------------------------------------------------------------------------
    | BAN CHECK
    |--------------------------------------------------------------------------
    */

        if (
            $user &&
            $user->stats &&
            $user->stats->is_banned
        ) {

            $banUntil = $user->stats->ban_until;

            /*
    |--------------------------------------------------------------------------
    | AUTO UNBAN
    |--------------------------------------------------------------------------
    */

            if (
                $banUntil &&
                now()->greaterThanOrEqualTo($banUntil)
            ) {

                $user->stats->update([
                    'is_banned' => false,
                    'ban_until' => null,
                ]);
            } else {

                $message = 'Twoje konto zostało zbanowane.';

                if ($banUntil) {

                    $message .= ' Ban obowiązuje do: '
                        . $banUntil->format('d.m.Y H:i');
                }

                return back()
                    ->withErrors([
                        'login' => $message,
                    ])
                    ->onlyInput('login');
            }
        }

        /*
    |--------------------------------------------------------------------------
    | LOGIN ATTEMPT
    |--------------------------------------------------------------------------
    */

        $credentials = [
            $loginField => $loginValue,
            'password' => $request->input('password'),
        ];

        if (Auth::attempt(
            $credentials,
            $request->filled('remember')
        )) {

            // zabezpieczenie przed atakiem na sesji
            $request->session()->regenerate();

            /** @var \App\Models\User $user */
            $user = Auth::user();

            /*
        |--------------------------------------------------------------------------
        | DAILY LOGIN BONUS
        |--------------------------------------------------------------------------
        */

            $today = Carbon::today();

            $lastLogin = $user->last_login
                ? Carbon::parse($user->last_login)
                : null;

            $dailyRewardGranted = false;

            if (
                !$lastLogin ||
                !$lastLogin->isSameDay($today)
            ) {

                $this->rewardService
                    ->grantDailyLoginReward($user);

                $dailyRewardGranted = true;
            }

            /*
        |--------------------------------------------------------------------------
        | UPDATE LAST LOGIN
        |--------------------------------------------------------------------------
        */

            $user->update([
                'last_login' => now(),
            ]);

            /*
        |--------------------------------------------------------------------------
        | SUCCESS MESSAGE // to bez sensu w sumie usuniemy później
        |--------------------------------------------------------------------------
        */

            $message = $dailyRewardGranted
                ? 'Witaj ponownie! Otrzymujesz +50 PKT za codzienne logowanie.'
                : 'Witaj ponownie!';

            if ($user->role === 'admin') {

                return redirect()
                    ->intended('/admin')
                    ->with('success', $message);
            }

            return redirect()
                ->intended('/')
                ->with('success', $message);
        }

        /*
    |--------------------------------------------------------------------------
    | Błędy logowania
    |--------------------------------------------------------------------------
    */

        return back()
            ->withErrors([
                'login' => 'Podane dane logowania są błędne.',
            ])
            ->onlyInput('login');
    }
}
