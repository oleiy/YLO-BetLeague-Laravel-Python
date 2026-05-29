@extends('admin.layouts.app')

@section('content')

    <div class="admin-dashboard">

        {{-- =========================================
            PAGE HEADER
        ========================================= --}}
        <div class="admin-page-header mb-4">

            <div>
                <h1 class="admin-page-title">
                    Dashboard Admina
                </h1>

                <p class="admin-page-subtitle">
                    Zarządzaj systemem YLO BetLeague oraz uruchamiaj silniki automatyzacji.
                </p>
            </div>

        </div>

        {{-- =========================================
            ALERTY
        ========================================= --}}
        @if (session('success'))
            <div class="alert alert-success admin-alert">
                <i class="bi bi-check-circle-fill"></i>

                <span>
                    {{ session('success') }}
                </span>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger admin-alert">
                <i class="bi bi-x-circle-fill"></i>

                <span>
                    {{ session('error') }}
                </span>
            </div>
        @endif

        {{-- =========================================
            IMPORT PRZED SEZONEM
        ========================================= --}}
        @include('partials.admin.preseason-imports')

        {{-- =========================================
            STATYSTYKI MECZÓW
        ========================================= --}}
        <div class="admin-section compact">

            <div class="admin-section-header">
                <h3>
                    <i class="bi bi-controller"></i>
                    Statystyki meczów
                </h3>
            </div>

            <div class="admin-stats-grid">

                {{-- DZISIEJSZE --}}
                <div class="admin-card compact-card">

                    <div class="admin-widget-icon sm">
                        <i class="bi bi-calendar-event-fill"></i>
                    </div>

                    <div class="admin-widget-label">
                        Dzisiejsze
                    </div>

                    <div class="big-number">
                        {{ $todayFixturesCount }}
                    </div>

                </div>

                {{-- LIVE --}}
                <div class="admin-card compact-card">

                    <div class="admin-widget-icon sm">
                        <i class="bi bi-broadcast-pin"></i>
                    </div>

                    <div class="admin-widget-label">
                        Trwające
                    </div>

                    <div class="big-number">
                        {{ $liveFixtures }}
                    </div>

                </div>

                {{-- NADCHODZĄCE --}}
                <div class="admin-card compact-card">

                    <div class="admin-widget-icon sm">
                        <i class="bi bi-clock-history"></i>
                    </div>

                    <div class="admin-widget-label">
                        Nadchodzące
                    </div>

                    <div class="big-number">
                        {{ $upcomingFixtures }}
                    </div>

                </div>

                {{-- ZAKOŃCZONE --}}
                <div class="admin-card compact-card">

                    <div class="admin-widget-icon sm">
                        <i class="bi bi-check2-circle"></i>
                    </div>

                    <div class="admin-widget-label">
                        Zakończone
                    </div>

                    <div class="big-number">
                        {{ $finishedFixtures }}
                    </div>

                </div>

            </div>

        </div>

        {{-- =========================================
            SYNCHRONIZACJA MECZÓW
        ========================================= --}}
        @include('partials.admin.fixtures-sync')

        {{-- =========================================
            ANALITYKA + KURSY
        ========================================= --}}
        @include('partials.admin.analytics-engine')

        {{-- =========================================
            TYPY UŻYTKOWNIKÓW
        ========================================= --}}
        <div class="admin-section compact">

            <div class="admin-section-header">
                <h3>
                    <i class="bi bi-bar-chart-fill"></i>
                    Typy użytkowników
                </h3>
            </div>

            <div class="admin-stats-grid">

                {{-- LIVE --}}
                <div class="admin-card compact-card">

                    <div class="admin-widget-icon sm">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </div>

                    <div class="admin-widget-label">
                        LIVE
                    </div>

                    <div class="big-number">
                        {{ $activeBets }}
                    </div>

                </div>

                {{-- OCZEKUJĄCE --}}
                <div class="admin-card compact-card">

                    <div class="admin-widget-icon sm">
                        <i class="bi bi-hourglass-split"></i>
                    </div>

                    <div class="admin-widget-label">
                        Oczekujące
                    </div>

                    <div class="big-number">
                        {{ $pendingBets ?? 0 }}
                    </div>

                </div>

                {{-- ROZLICZANE --}}
                <div class="admin-card compact-card">

                    <div class="admin-widget-icon sm">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>

                    <div class="admin-widget-label">
                        Rozliczane
                    </div>

                    <div class="big-number">
                        {{ $settlingBets ?? 0 }}
                    </div>

                </div>

                {{-- ROZLICZONE --}}
                <div class="admin-card compact-card">

                    <div class="admin-widget-icon sm">
                        <i class="bi bi-trophy-fill"></i>
                    </div>

                    <div class="admin-widget-label">
                        Rozliczone
                    </div>

                    <div class="big-number">
                        {{ $settledBets }}
                    </div>

                </div>

            </div>

        </div>

        {{-- =========================================
            WYNIKI + ROZLICZANIE
        ========================================= --}}
        @include('partials.admin.results-engine')

    </div>

    {{-- =========================================
        CONSOLE + JS
    ========================================= --}}
    @include('partials.admin.python-console')

@endsection
