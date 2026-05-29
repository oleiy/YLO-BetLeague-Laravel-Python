<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">

    {{-- Tytuł --}}
    <title>@yield('title', 'ADMIN | YLO BetLeague')</title>

    {{-- CSRF --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    {{-- Swiper --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    {{-- Global styles --}}
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">

    {{-- Admin styles --}}
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">

    {{-- Additional page styles --}}
    @stack('styles')

</head>

<body>

    <div class="admin-layout">

        {{-- =========================================
         SIDEBAR
    ========================================= --}}
        <aside class="admin-sidebar">

            {{-- LOGO --}}
            <div class="navbar-brand">

                <img src="{{ asset('assets/logo.png') }}" alt="BetLeague Logo">

                <div class="admin-logo-text">

                    <div class="admin-logo-title">
                        YLO Admin
                    </div>

                    <div class="admin-logo-subtitle">
                        BetLeague Panel
                    </div>

                </div>

            </div>

            {{-- NAVIGATION --}}
            <nav class="admin-nav">

                <a href="{{ route('admin.dashboard') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">

                    <i class="bi bi-grid-1x2-fill"></i>

                    <span>Dashboard</span>

                </a>

                <a href="{{ route('admin.matches') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.matches') ? 'active' : '' }}">

                    <i class="bi bi-controller"></i>

                    <span>Mecze</span>

                </a>

                <a href="{{ route('admin.user-bets') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.user-bets') ? 'active' : '' }}">

                    <i class="bi bi-bar-chart-fill"></i>

                    <span>Typy użytkowników</span>

                </a>

                <a href="{{ route('admin.users') }}"
                    class="admin-nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">

                    <i class="bi bi-people-fill"></i>

                    <span>Użytkownicy</span>

                </a>

            </nav>

            {{-- SIDEBAR FOOTER --}}
            <div class="admin-sidebar-footer">

                <form method="POST" action="{{ route('logout') }}"
                    onsubmit="return confirm('Na pewno chcesz się wylogować?')">
                    @csrf

                    <button type="submit" class="admin-logout-btn">

                        <i class="bi bi-box-arrow-right"></i>

                        <span>Wyloguj</span>

                    </button>

                </form>

            </div>

        </aside>

        {{-- =========================================
         CONTENT
    ========================================= --}}
        <main class="admin-content">

            @yield('content')

        </main>

    </div>

    {{-- =========================================
     BOOTSTRAP JS
========================================= --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    {{-- SWIPER --}}
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    {{-- AUTH INFO FOR JS --}}
    <script>
        window.isAuthenticated = {{ Auth::check() ? 'true' : 'false' }};
    </script>

    {{-- ADMIN ACTIONS --}}

    {{-- PAGE SCRIPTS --}}
    @yield('scripts')
    @stack('scripts')

</body>

</html>
