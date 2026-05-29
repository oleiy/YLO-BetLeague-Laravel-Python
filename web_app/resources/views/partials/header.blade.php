<nav class="navbar navbar-expand-lg navbar-custom">

    <div class="container">

        {{-- LOGO --}}
        <a class="navbar-brand" href="{{ url('/') }}">
            <img
                src="{{ asset('assets/logo.png') }}"
                alt="BetLeague Logo"
            >
        </a>

        {{-- MOBILE ACTIONS --}}
        <div class="mobile-nav-actions d-lg-none">

            <button
                class="navbar-toggler navbar-dark"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNav"
            >
                <span class="navbar-toggler-icon"></span>
            </button>

        </div>

        {{-- DESKTOP TOGGLER --}}
        <button
            class="navbar-toggler navbar-dark d-none"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">

            {{-- NAVIGATION --}}
            <ul class="navbar-nav me-auto">

                <li class="nav-item">
                    <a
                        class="nav-link-custom {{ request()->is('/') ? 'active' : '' }}"
                        href="{{ url('/') }}"
                    >
                        DASHBOARD
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        class="nav-link-custom {{ request()->routeIs('matches') ? 'active' : '' }}"
                        href="{{ route('matches') }}"
                    >
                        MECZE
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        class="nav-link-custom {{ request()->routeIs('community') ? 'active' : '' }}"
                        href="{{ route('community') }}"
                    >
                        SPOŁECZNOŚĆ
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        class="nav-link-custom {{ request()->routeIs('my-bets') ? 'active' : '' }}"
                        href="{{ route('my-bets') }}"
                    >
                        MOJE TYPY
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        class="nav-link-custom"
                        href="{{ route('earn-balance') }}"
                    >
                        ZDOBYWAJ BALANS
                    </a>
                </li>

            </ul>

            {{-- RIGHT SIDE --}}
            <div class="nav-right-section">

                @auth

                    {{-- BALANCE --}}
                    <div class="balance-pill d-none d-lg-flex">

                        <span class="balance-label">
                            SALDO
                        </span>

                        <span
                            id="user-balance"
                            class="balance-value"
                        >
                            {{ number_format(Auth::user()->stats->balance_points ?? 0, 0, '.', ',') }}
                            PKT
                        </span>

                    </div>

                    {{-- PROFILE DROPDOWN --}}
                    <div class="profile-dropdown-wrapper">

                        <button
                            class="user-profile-nav"
                            id="profileDropdownToggle"
                            type="button"
                        >

                            <div class="user-meta">

                                <span class="user-name-header">
                                    {{ Auth::user()->username }}
                                </span>

                            </div>

                            <div class="avatar-circle">
                                <i class="bi bi-person-fill"></i>
                            </div>

                        </button>

                        {{-- DROPDOWN --}}
                        <div
                            class="profile-dropdown-menu"
                            id="profileDropdownMenu"
                        >

                            {{-- TOP --}}
                            <div class="profile-dropdown-top">

                                <div class="profile-big-avatar">
                                    {{ strtoupper(substr(Auth::user()->username, 0, 2)) }}
                                </div>

                                <div>

                                    <div class="profile-dropdown-username">
                                        {{ Auth::user()->username }}
                                    </div>

                                    <div class="profile-dropdown-email">
                                        {{ Auth::user()->email }}
                                    </div>

                                </div>

                            </div>

                            {{-- STATS --}}
                            <div class="profile-dropdown-section">

                                <div class="profile-info-row">

                                    <span class="profile-info-label">
                                        Referral Code
                                    </span>

                                    <div class="referral-code-box">

                                        <span id="referralCodeText">
                                            {{ Auth::user()->referral_code }}
                                        </span>

                                        <button
                                            class="copy-ref-btn"
                                            onclick="copyReferralCode()"
                                        >
                                            <i class="bi bi-copy"></i>
                                        </button>

                                    </div>

                                </div>

                                <div class="profile-info-row">

                                    <span class="profile-info-label">
                                        Polecone osoby
                                    </span>

                                    <span class="profile-info-value">
                                        {{ Auth::user()->referrals()->count() }}
                                    </span>

                                </div>

                                <div class="profile-info-row">

                                    <span class="profile-info-label">
                                        Bonus referral
                                    </span>

                                    <span class="profile-info-value success">
                                        {{ number_format(Auth::user()->referrals()->count() * 500, 0, '.', ',') }}
                                        PKT
                                    </span>

                                </div>

                                <div class="profile-info-row">

                                    <span class="profile-info-label">
                                        Aktualne saldo
                                    </span>

                                    <span class="profile-info-value blue">
                                        {{ number_format(Auth::user()->stats->balance_points ?? 0, 0, '.', ',') }}
                                        PKT
                                    </span>

                                </div>

                            </div>

                            {{-- ACTIONS --}}
                            <div class="profile-dropdown-actions">

                                <a
                                    href="{{ route('logout') }}"
                                    class="dropdown-logout-btn"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                >
                                    <i class="bi bi-box-arrow-right"></i>

                                    <span>
                                        WYLOGUJ
                                    </span>
                                </a>

                            </div>

                        </div>

                    </div>

                    <form
                        id="logout-form"
                        action="{{ route('logout') }}"
                        method="POST"
                        class="d-none"
                    >
                        @csrf
                    </form>

                @else

                    {{-- REGISTER --}}
                    <a
                        href="javascript:void(0)"
                        onclick="openRegister()"
                        class="btn-guest-register"
                    >
                        <i class="bi bi-person-plus-fill"></i>

                        <span>
                            Rejestracja
                        </span>
                    </a>

                    {{-- LOGIN --}}
                    <a
                        href="javascript:void(0)"
                        onclick="openLogin()"
                        class="btn-guest-login"
                    >
                        <i class="bi bi-box-arrow-in-right"></i>

                        <span>
                            Zaloguj
                        </span>
                    </a>

                @endauth

            </div>

        </div>
    </div>
</nav>

@auth

<script>

    const profileDropdownToggle =
        document.getElementById('profileDropdownToggle');

    const profileDropdownMenu =
        document.getElementById('profileDropdownMenu');

    profileDropdownToggle?.addEventListener('click', function(e) {

        e.stopPropagation();

        profileDropdownMenu.classList.toggle('active');

    });

    document.addEventListener('click', function(e) {

        if (
            !profileDropdownMenu.contains(e.target) &&
            !profileDropdownToggle.contains(e.target)
        ) {
            profileDropdownMenu.classList.remove('active');
        }

    });

    function copyReferralCode() {

        const code =
            document.getElementById('referralCodeText').innerText;

        navigator.clipboard.writeText(code);

    }

</script>

@endauth
