@extends('layouts.app')

@section('title', 'Zdobywaj Balans | YLO TypeLeague')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/earn-balance.css') }}">
@endpush

@section('content')

    <div class="earn-page">

        {{-- HEADER --}}
        <div class="earn-header">

            <h1 class="earn-title">

                Zdobywaj <span>Balans</span>

            </h1>

            <p class="earn-subtitle">

                Odbieraj codzienne bonusy,
                zapraszaj znajomych
                i zdobywaj darmowe punkty.

            </p>

        </div>
        @if ($isGuest)

            <div class="mybets-login-box">

                <div class="mybets-login-icon">

                    <i class="bi bi-gift-fill"></i>

                </div>

                <h2 class="mybets-login-title">

                    Zaloguj się aby zdobywać balans

                </h2>

                <p class="mybets-login-subtitle">

                    Odbieraj codzienne bonusy,
                    buduj streak logowań
                    i zapraszaj znajomych po darmowe punkty.

                </p>

                <button class="btn btn-primary px-4 py-2" onclick="openLoginModal()">
                    Zaloguj się
                </button>

            </div>
        @else
            {{-- HERO --}}
            <div class="earn-hero-card">

                <div>

                    <div class="earn-label">
                        AKTUALNE SALDO
                    </div>

                    <div class="earn-balance">

                        {{ number_format($user->stats->balance_points, 0, '.', ',') }}

                        PKT

                    </div>

                </div>

                <div class="earn-streak-box">

                    <div class="earn-streak-value">

                        🔥
                        {{ $user->stats->daily_login_streak }}

                    </div>

                </div>

            </div>

            {{-- DAILY REWARD --}}
            <div class="earn-card">

                <div class="earn-card-header">

                    <div>

                        <h3>
                            CODZIENNA NAGRODA ZA LOGOWANIE
                        </h3>

                        <p>
                            Loguj się codziennie
                            aby zwiększać passę.
                        </p>

                    </div>

                    <div class="daily-reward-badge">

                        +{{ match ($user->stats->daily_login_streak) {
                            1 => 100,
                            2 => 150,
                            3 => 200,
                            4 => 300,
                            5 => 500,
                            6 => 750,
                            7 => 1000,
                            default => 100,
                        } }}

                        PKT

                    </div>

                </div>

                <div class="streak-grid">

                    @for ($i = 1; $i <= 7; $i++)
                        <div
                            class="streak-day
                        {{ $i <= $user->stats->daily_login_streak ? 'active' : '' }}
                    ">

                            <span>
                                {{ $i }}
                            </span>

                        </div>
                    @endfor

                </div>

            </div>

            {{-- REFERRALS --}}
            <div class="earn-card referral-big-card">

                <div class="earn-card-header">

                    <div>

                        <h3>
                            Program poleceń
                        </h3>

                        <p>
                            Zapraszaj znajomych
                            i zdobywaj bonusy.
                        </p>

                    </div>

                </div>

                <div class="referral-code-wrapper">

                    <div class="referral-code" id="refCode">
                        {{ $user->referral_code }}
                    </div>

                    <button class="copy-ref-btn-big" onclick="copyReferralCode()">
                        <i class="bi bi-copy"></i>

                        Kopiuj kod

                    </button>

                </div>

                <div class="referral-stats-grid">

                    <div class="referral-stat-card">

                        <div class="referral-stat-label">
                            Poleceni użytkownicy
                        </div>

                        <div class="referral-stat-value">

                            {{ $referralsCount }}

                        </div>

                    </div>

                    <div class="referral-stat-card">

                        <div class="referral-stat-label">
                            Zdobyte punkty
                        </div>

                        <div class="referral-stat-value blue">

                            {{ number_format($referralEarned, 0, '.', ',') }}

                            PKT

                        </div>

                    </div>

                </div>

            </div>

        @endif

    </div>

@endsection

@push('scripts')
    <script>
        function copyReferralCode() {

            const code =
                document.getElementById('refCode').innerText;

            navigator.clipboard.writeText(code);

        }
    </script>
@endpush
