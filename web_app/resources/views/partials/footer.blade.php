<footer class="footer-custom mt-auto py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <img src="{{ asset('assets/logo.png') }}" alt="Logo" class="footer-logo mb-3">
                <p class="footer-description">
                    Najlepsza liga typerska w sieci. Rywalizuj z innymi, analizuj mecze i zdobywaj punkty w rankingu YLO TypeLeague. Wszystko całkowicie za darmo.
                </p>
            </div>

            <div class="col-lg-2 offset-lg-1">
                <h5 class="footer-title">NAWIGACJA</h5>
                <ul class="footer-links list-unstyled">
                    <li><a href="{{ url('/') }}">Dashboard</a></li>
                    <li><a href="#">Mecze</a></li>
                    <li><a href="#">Społeczność</a></li>
                    <li><a href="#">Moje Typy</a></li>
                    <li><a href="#">Zdobywaj balans</a></li>
                </ul>
            </div>

            <div class="col-lg-2">
                <h5 class="footer-title">WSPARCIE</h5>
                <ul class="footer-links list-unstyled">
                    <li><a href="#">Regulamin</a></li>
                    <li><a href="#">Polityka prywatności</a></li>
                    <li><a href="#">FAQ</a></li>
                    <li><a href="#">Kontakt</a></li>
                </ul>
            </div>

            <div class="col-lg-3">
                <h5 class="footer-title">OSTATNIA AKTUALIZACJA</h5>
                <div class="update-pill d-flex align-items-center gap-2">
                    <div class="dot-online"></div>
                    <span>System gotowy do gry</span>
                </div>
                <p class="footer-copyright mt-4">
                    &copy; {{ date('Y') }} YLO TypeLeague. <br>
                    Wszystkie prawa zastrzeżone.
                </p>
            </div>
        </div>
    </div>
</footer>
