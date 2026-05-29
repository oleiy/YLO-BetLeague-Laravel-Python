@extends('layouts.app')

@section('title', 'Dashboard | YLO TypeLeague')

@section('content')

    {{-- Nagłówek Dashboardu --}}
    <div class="dashboard-header mb-4">
        <h1 class="h-title m-0">DASHBOARD <span class="brand-accent">BetLeague</span></h1>
        <p class="dashboard-subtitle mt-2 mb-0">
            Dołącz do elitarnego grona typerów YLO BetLeague! Rywalizuj o miano eksperta, analizuj topowe spotkania i
            wspinaj się w rankingu tygodnia całkowicie za darmo.
        </p>
    </div>

    {{-- Sekcja: Topowe Nadchodzące Mecze --}}
    <div class="section-header-alignment mt-5">
        <h5 class="section-title-cyber m-0">Nadchodzące Mecze</h5>
        <div class="swiper-nav d-flex gap-2">
            <div class="swiper-button-prev-custom s-prev-matches"><i class="bi bi-chevron-left"></i></div>
            <div class="swiper-button-next-custom s-next-matches"><i class="bi bi-chevron-right"></i></div>
        </div>
    </div>

    <div class="swiper topMatchesSwiper mb-5">
        <div class="swiper-wrapper">
            @forelse($matches as $match)
                <div class="swiper-slide">
                    <div class="card-cyber-grid p-3">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center gap-2">
                                @php
                                    $leagueLogos = [
                                        'la liga' => 'la_liga.png',
                                        'laliga' => 'la_liga.png',
                                        'serie a' => 'serie_a.png',
                                        'ligue 1' => 'ligue1.png',
                                        'bundesliga' => 'bundesliga.png',
                                        'premier league' => 'premier_league.png',
                                        'uefa champions league' => 'champions.png',
                                    ];
                                    $logo = $leagueLogos[strtolower($match->league->name ?? '')] ?? 'default.png';
                                @endphp
                                <img src="{{ asset("assets/leagues/{$logo}") }}" class="league-logo-sm" alt="league">
                                <span class="league-tag-mini">{{ $match->league->name ?? 'Inna Liga' }}</span>
                            </div>
                            <div class="text-end">
                                <span class="match-date-tag d-block">{{ $match->match_date->format('d.m.Y') }}</span>
                                <span class="match-time-tag">
                                    <i class="bi bi-clock"></i> {{ $match->match_date->format('H:i') }}
                                </span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-center gap-3 mb-3">
                            <div class="text-center" style="flex: 1;">
                                <img src="{{ asset($match->homeTeam->logo_path) }}" alt="{{ $match->homeTeam->name }}"
                                    class="team-logo-grid mb-1">
                                <div class="small fw-bold lh-1 text-truncate"
                                    style="font-size: 0.7rem; max-width: 80px; margin: 0 auto;">
                                    {{ $match->homeTeam->name }}
                                </div>
                            </div>

                            <div class="vs-badge-compact">VS</div>

                            <div class="text-center" style="flex: 1;">
                                <img src="{{ asset($match->awayTeam->logo_path) }}" alt="{{ $match->awayTeam->name }}"
                                    class="team-logo-grid mb-1">
                                <div class="small fw-bold lh-1 text-truncate"
                                    style="font-size: 0.7rem; max-width: 80px; margin: 0 auto;">
                                    {{ $match->awayTeam->name }}
                                </div>
                            </div>
                        </div>

                        {{-- Sekcja z kursami - ZOSTAJE TAK JAK BYŁO --}}
                        <div class="odds-grid-container mb-2">
                            <div class="row g-1 text-center">
                                @php
                                    $o1 = $match->getWinOdd('1');
                                    $oX = $match->getWinOdd('X');
                                    $o2 = $match->getWinOdd('2');
                                @endphp

                                <div class="col-4">
                                    <div class="odd-box-mini clickable-odd" data-odd-id="{{ $o1->id ?? '' }}"
                                        data-match-id="{{ $match->id }}"
                                        data-league-name="{{ $match->league->name ?? '' }}"
                                        data-home-team="{{ $match->homeTeam->name }}"
                                        data-away-team="{{ $match->awayTeam->name }}"
                                        data-match-date="{{ $match->match_date }}" data-market-name="Wynik"
                                        data-outcome-name="1" data-odd-value="{{ $o1->value ?? 1 }}">
                                        <span class="odd-label-xs">1</span>
                                        <span class="odd-val">{{ $o1->value ?? '---' }}</span>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="odd-box-mini clickable-odd" data-odd-id="{{ $oX->id ?? '' }}"
                                        data-match-id="{{ $match->id }}"
                                        data-league-name="{{ $match->league->name ?? '' }}"
                                        data-home-team="{{ $match->homeTeam->name }}"
                                        data-away-team="{{ $match->awayTeam->name }}"
                                        data-match-date="{{ $match->match_date }}" data-market-name="Wynik"
                                        data-outcome-name="X" data-odd-value="{{ $oX->value ?? 1 }}">
                                        <span class="odd-label-xs">X</span>
                                        <span class="odd-val">{{ $oX->value ?? '---' }}</span>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="odd-box-mini clickable-odd" data-odd-id="{{ $o2->id ?? '' }}"
                                        data-match-id="{{ $match->id }}"
                                        data-league-name="{{ $match->league->name ?? '' }}"
                                        data-home-team="{{ $match->homeTeam->name }}"
                                        data-away-team="{{ $match->awayTeam->name }}"
                                        data-match-date="{{ $match->match_date }}" data-market-name="Wynik"
                                        data-outcome-name="2" data-odd-value="{{ $o2->value ?? 1 }}">
                                        <span class="odd-label-xs">2</span>
                                        <span class="odd-val">{{ $o2->value ?? '---' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ZMIENIONY TYLKO TEN PRZYCISK NA DOLE --}}
                        <a href="{{ route('matches') }}?date={{ $match->match_date->toDateString() }}#match-{{ $match->id }}"
                            class="btn-see-more-cyber text-decoration-none mt-2">
                            <span>ZOBACZ WIĘCEJ</span>
                            <i class="bi bi-arrow-right-short"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-4">
                    <p class="text-dim">Brak meczów na dziś i jutro.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/dashboard.js') }}"></script>
@endpush
