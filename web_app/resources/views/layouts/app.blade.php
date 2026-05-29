<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">
    <title>@yield('title', 'YLO TypeLeague')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Zewnętrzne style (Bootstrap, ikony oraz Swiper) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    {{-- Własne style aplikacji --}}
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/betslip.css') }}">
    @stack('styles')


    {{-- Style kuponu --}}
</head>
<body>

    {{-- Górny pasek nawigacyjny --}}
    @include('partials.header')

    {{-- Główny kontener strony --}}
    <main class="flex-grow-1 dashboard-wrapper">
        <div class="container h-100">
            <div class="row g-4 h-100">

                @hasSection('full-width')
                    {{-- Strona z własnym, niestandardowym układem --}}
                    <div class="col-12 py-4">
                        @yield('content')
                    </div>
                @else
                    {{-- Domyślny układ dla pozostałych podstron (z miejscem na sidebar) --}}
                    <div class="col-lg-8 py-4">
                        @yield('content')
                    </div>

                    <div class="col-lg-4 d-none d-lg-block py-4">
                        @include('partials.sidebar')
                    </div>
                @endif

            </div>
        </div>
    </main>

    {{-- Modale i nakładki muszą być umieszczone przed skryptami JS --}}
    @include('partials.auth-overlays')

    @include('partials.footer')

    {{-- Ładowanie zewnętrznych bibliotek JavaScript --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    {{-- Skrypty kuponu --}}
    {{-- <script src="{{ asset('js/betting-core.js') }}"></script> --}}
    <script src="{{ asset('js/bet-slip.js') }}"></script>


    <script>
        // Otwieranie nakładki z logowaniem
        window.openLogin = function() {
            document.getElementById('registerOverlay').style.display = 'none';
            document.getElementById('loginOverlay').style.display = 'flex';
        }

        // Otwieranie nakładki z rejestracją
        window.openRegister = function() {
            document.getElementById('loginOverlay').style.display = 'none';
            document.getElementById('registerOverlay').style.display = 'flex';
        }

        // Przełączanie między widokami w nakładkach (z poziomu logowania do rejestracji)
        window.switchToRegister = function() {
            openRegister();
        }

        // Przełączanie między widokami w nakładkach (z poziomu rejestracji do logowania)
        window.switchToLogin = function() {
            openLogin();
        }

        // Zamykanie nakładek po kliknięciu poza ich obszarem
        window.onclick = function(event) {
            if (event.target.classList.contains('login-overlay')) {
                event.target.style.display = 'none';
            }
        }
        // Funkcja otwierająca modal logowania
    window.openLoginModal = function() {
        const loginOverlay = document.getElementById('loginOverlay');
        if (loginOverlay) {
            loginOverlay.style.display = 'flex';
        }
    };

    // Funkcje przełączania między Loginem a Rejestracją (potrzebne do linków wewnątrz modali)
    window.switchToRegister = function() {
        document.getElementById('loginOverlay').style.display = 'none';
        document.getElementById('registerOverlay').style.display = 'flex';
    };

    window.switchToLogin = function() {
        document.getElementById('registerOverlay').style.display = 'none';
        document.getElementById('loginOverlay').style.display = 'flex';
    };
    </script>
    <script>
        // To dynamicznie sprawdza, czy użytkownik jest zalogowany w PHP i przekazuje to do JS
        window.isAuthenticated = {{ Auth::check() ? 'true' : 'false' }};
    </script>

    {{-- Dodatkowe skrypty dla poszczególnych podstron --}}
    @yield('scripts')
    @stack('scripts')

</body>
</html>
