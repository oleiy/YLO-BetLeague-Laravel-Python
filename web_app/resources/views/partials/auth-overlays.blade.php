@guest
    <div class="login-overlay" id="loginOverlay" style="{{ $errors->has('login') || $errors->has('password') ? 'display: flex;' : 'display: none;' }}">
        <div class="login-glass-card">
            <button class="btn-close-overlay" onclick="document.getElementById('loginOverlay').style.display='none'">
                <i class="bi bi-x-lg"></i>
            </button>

            <div class="text-center login-header-section">
                <img src="{{ asset('assets/logo.png') }}" alt="Logo" class="login-logo-large mb-2">
                <h4 class="fw-bold" style="letter-spacing: -1px;">WITAJ W <span class="brand-accent">BETLEAGUE</span></h4>
                <p class="small text-dim" style="font-size: 0.75rem;">Zaloguj się, aby zacząć typować mecze</p>

                @if ($errors->any() && !$errors->has('name'))
                    <div class="login-errors-container">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="mb-3 text-start">
                    <label class="login-label">Nazwa użytkownika / Email</label>
                    <input type="text" name="login" class="login-input" placeholder="Email lub nazwa użytkownika" required value="{{ old('login') }}">
                </div>

                <div class="mb-3 text-start">
                    <label class="login-label">Hasło</label>
                    <input type="password" name="password" class="login-input" placeholder="••••••••" required>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember">
                        <label class="form-check-label small text-dim" style="font-size: 0.65rem;" for="remember">Zapamiętaj mnie</label>
                    </div>
                    <a href="#" class="small text-info text-decoration-none fw-bold" style="font-size: 0.6rem;">ZAPOMNIAŁEŚ HASŁA?</a>
                </div>

                <button type="submit" class="btn-login-submit">ZALOGUJ SIĘ TERAZ</button>

                <p class="text-center mt-3 small text-dim">
                    Nie masz konta? <a href="javascript:void(0)" onclick="switchToRegister()" class="register-link-hover">DOŁĄCZ ZA DARMO</a>
                </p>
            </form>
        </div>
    </div>

    <div class="login-overlay" id="registerOverlay" style="{{ $errors->has('username') || $errors->has('email') ? 'display: flex;' : 'display: none;' }}">
        <div class="login-glass-card">
            <button class="btn-close-overlay" onclick="document.getElementById('registerOverlay').style.display='none'">
                <i class="bi bi-x-lg"></i>
            </button>

            <div class="text-center login-header-section">
                <img src="{{ asset('assets/logo.png') }}" alt="Logo" class="login-logo-large mb-2">
                <h4 class="fw-bold" style="letter-spacing: -1px;">DOŁĄCZ DO <span class="brand-accent">BETLEAGUE</span></h4>
                <p class="small text-dim" style="font-size: 0.75rem;">Stwórz darmowe konto i odbierz bonus na start</p>

                @if ($errors->any() && ($errors->has('username') || $errors->has('email')))
                    <div class="login-errors-container">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <form action="{{ route('register') }}" method="POST">
                @csrf
                <div class="mb-3 text-start">
                    <label class="login-label">Nazwa użytkownika</label>
                    <input type="text" name="username" class="login-input" value="{{ old('username') }}" placeholder="Wpisz nazwę użytkownika" required>
                </div>

                <div class="mb-3 text-start">
                    <label class="login-label">Email</label>
                    <input type="email" name="email" class="login-input" value="{{ old('email') }}" placeholder="email@przyklad.pl" required>
                </div>

                <div class="mb-3 text-start">
                    <label class="login-label">Kod promocyjny <span style="font-size: 0.65rem; color: #5a5d61;">(opcjonalnie)</span></label>
                    <div class="d-flex align-items-center">
                        <span class="promo-icon-addon">
                            <i class="bi bi-gift"></i>
                        </span>
                        <input type="text" name="promo_code" class="login-input promo-input-field" value="{{ old('promo_code') }}" placeholder="np. BETLEAGUE">
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6 text-start">
                        <label class="login-label">Hasło</label>
                        <input type="password" name="password" class="login-input" placeholder="••••••••" required>
                    </div>
                    <div class="col-6 text-start">
                        <label class="login-label">Powtórz</label>
                        <input type="password" name="password_confirmation" class="login-input" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn-login-submit">STWÓRZ KONTO</button>

                <p class="text-center mt-3 small text-dim">
                    Masz już konto? <a href="javascript:void(0)" onclick="switchToLogin()" class="register-link-hover">ZALOGUJ SIĘ</a>
                </p>
            </form>
        </div>
    </div>
@endguest
