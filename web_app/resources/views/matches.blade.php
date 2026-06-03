@extends('layouts.app')

@section('title', 'Mecze | YLO TypeLeague')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/matches.css') }}">
@endpush

@section('content')
    <div class="matches-header mb-4">
        <h1 class="h-title text-white">Typuj <span class="brand-accent">Mecze</span></h1>
        <p class="dashboard-subtitle text-secondary">Wybierz zdarzenia i twórz swoje typy. Pamiętaj, że każdy mecz na liście
            to oddzielna szansa na wygraną. Typy wewnątrz jednego meczu tworzą Bet Builder i muszą wejść w całości.</p>
    </div>

    {{-- Kalendarz z przewijanymi dniami --}}
    <div class="filter-days-wrapper mb-4 d-flex align-items-center">
        <button class="scroll-arrow left" onclick="scrollDays('left')">
            <i class="bi bi-chevron-left"></i>
        </button>
        <div class="filter-days-container" id="daysContainer">
            @php
                $start = \Carbon\Carbon::today()->subDays(7);
                $daysMap = [
                    'Mon' => 'Pon',
                    'Tue' => 'Wt',
                    'Wed' => 'Śr',
                    'Thu' => 'Czw',
                    'Fri' => 'Pt',
                    'Sat' => 'Sob',
                    'Sun' => 'Niedz',
                ];
            @endphp

            @php
                $selectedDate = request('date', now()->toDateString());
            @endphp

            @for ($i = 0; $i <= 14; $i++)
                @php
                    $currentDate = $start->copy()->addDays($i);
                    $dayName = $currentDate->isToday()
                        ? 'Dziś'
                        : ($currentDate->isYesterday()
                            ? 'Wcz'
                            : ($currentDate->isTomorrow()
                                ? 'Jtr'
                                : $daysMap[$currentDate->format('D')] ?? $currentDate->format('D')));
                @endphp
                <button class="day-btn {{ $currentDate->toDateString() === $selectedDate ? 'active' : '' }}"
                    id="{{ $currentDate->toDateString() === $selectedDate ? 'activeDayBtn' : '' }}"
                    data-date="{{ $currentDate->toDateString() }}" onclick="centerMe(this)">
                    <span class="day-name">{{ $dayName }}.</span>
                    <span class="day-date">{{ $currentDate->format('d.m') }}</span>
                </button>
            @endfor
        </div>
        <button class="scroll-arrow right" onclick="scrollDays('right')">
            <i class="bi bi-chevron-right"></i>
        </button>
    </div>

    <div id="matches-list-container">
        @forelse($leagues as $league)
            <div class="league-section mb-5">
                <div class="league-header">
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
                        $logo = $leagueLogos[strtolower($league->name)] ?? 'default.png';
                    @endphp
                    <img src="{{ asset("assets/leagues/{$logo}") }}" class="league-logo-sm">
                    <span class="section-title-cyber">{{ $league->name }}</span>
                </div>

                @foreach ($league->fixtures as $match)
                    @if (in_array(strtolower($match->status), ['ft', 'finished']))
                        <div class="finished-match-wrapper">

                            <div class="finished-match-compact">

                                <div class="finished-date-small">
                                    {{ $match->match_date->format('d.m.Y H:i') }}
                                </div>

                                <div class="finished-main-row">

                                    <div class="finished-team-mini">
                                        <img src="{{ asset($match->homeTeam->logo_path) }}" class="finished-logo-mini">

                                        <span class="finished-team-name">
                                            {{ $match->homeTeam->name }}
                                        </span>
                                    </div>

                                    <div class="finished-score-mini">

                                        {{ $match->statistics->home_goals ?? ($match->home_score ?? 0) }}
                                        -
                                        {{ $match->statistics->away_goals ?? ($match->away_score ?? 0) }}

                                        <div class="finished-ft">
                                            Zakończony
                                        </div>

                                    </div>

                                    <div class="finished-team-mini">
                                        <img src="{{ asset($match->awayTeam->logo_path) }}" class="finished-logo-mini">

                                        <span class="finished-team-name">
                                            {{ $match->awayTeam->name }}
                                        </span>
                                    </div>

                                </div>

                            </div>

                        </div>
                    @else
                        <div class="match-strip-item" id="match-wrapper-{{ $match->id }}">
                            <div class="d-flex align-items-center p-3">

                                <div class="match-time-info">
                                    <span class="m-hour">
                                        {{ $match->match_date->format('H:i') }}
                                    </span>

                                    <span class="m-date">
                                        {{ $match->match_date->format('d.m') }}
                                    </span>
                                </div>

                                <div class="match-teams-part">

                                    <div class="team-box">
                                        <img src="{{ asset($match->homeTeam->logo_path) }}"
                                            alt="{{ $match->homeTeam->name }}" class="team-logo-match">

                                        <span class="team-name d-none d-md-block">
                                            {{ $match->homeTeam->name }}
                                        </span>
                                    </div>

                                    <span class="vs-label"></span>

                                    <div class="team-box">

                                        <span class="team-name d-none d-md-block">
                                            {{ $match->awayTeam->name }}
                                        </span>

                                        <img src="{{ asset($match->awayTeam->logo_path) }}"
                                            alt="{{ $match->awayTeam->name }}" class="team-logo-match">
                                    </div>

                                </div>

                                <div class="match-odds-part ms-auto">

                                    @php
                                        $o1 = $match->getWinOdd('1');
                                        $oX = $match->getWinOdd('X');
                                        $o2 = $match->getWinOdd('2');
                                    @endphp

                                    {{-- Kurs na 1 --}}
                                    <button class="btn-odd me-1" data-match-id="{{ $match->id }}"
                                        data-odd-id="{{ $o1->id ?? '' }}" data-league-name="{{ $league->name }}"
                                        data-home-team="{{ $match->homeTeam->name }}"
                                        data-away-team="{{ $match->awayTeam->name }}"
                                        data-match-date="{{ $match->match_date }}" data-market-name="Wynik"
                                        data-outcome-name="1" data-odd-value="{{ $o1->value ?? 1 }}">
                                        <span class="odd-label-xs">1</span>

                                        <span class="odd-val">
                                            {{ isset($o1) ? number_format($o1->value, 2) : '---' }}
                                        </span>
                                    </button>

                                    {{-- Kurs na X --}}
                                    <button class="btn-odd me-1" data-match-id="{{ $match->id }}"
                                        data-odd-id="{{ $oX->id ?? '' }}" data-league-name="{{ $league->name }}"
                                        data-home-team="{{ $match->homeTeam->name }}"
                                        data-away-team="{{ $match->awayTeam->name }}"
                                        data-match-date="{{ $match->match_date }}" data-market-name="Wynik"
                                        data-outcome-name="X" data-odd-value="{{ $oX->value ?? 1 }}">
                                        <span class="odd-label-xs">X</span>

                                        <span class="odd-val">
                                            {{ isset($oX) ? number_format($oX->value, 2) : '---' }}
                                        </span>
                                    </button>

                                    {{-- Kurs na 2 --}}
                                    <button class="btn-odd" data-match-id="{{ $match->id }}"
                                        data-odd-id="{{ $o2->id ?? '' }}" data-league-name="{{ $league->name }}"
                                        data-home-team="{{ $match->homeTeam->name }}"
                                        data-away-team="{{ $match->awayTeam->name }}"
                                        data-match-date="{{ $match->match_date }}" data-market-name="Wynik"
                                        data-outcome-name="2" data-odd-value="{{ $o2->value ?? 1 }}">
                                        <span class="odd-label-xs">2</span>

                                        <span class="odd-val">
                                            {{ isset($o2) ? number_format($o2->value, 2) : '---' }}
                                        </span>
                                    </button>

                                    <button class="btn-more-odds ms-2" onclick="toggleMarkets({{ $match->id }})">
                                        <i class="bi bi-chevron-down" id="icon-{{ $match->id }}"></i>
                                    </button>

                                </div>

                            </div>

                            <div class="match-details-expand d-none" id="markets-{{ $match->id }}">
                                <div class="p-3 border-top border-secondary">
                                    <div id="markets-content-{{ $match->id }}"></div>
                                </div>
                            </div>

                        </div>
                    @endif
                @endforeach
            </div>
        @empty
            <div class="text-center text-secondary py-5">Brak meczów na ten dzień.</div>
        @endforelse
    </div>
@endsection

@push('scripts')
    <script>
        window.loadedLeagues = @json($leagues);
    </script>
    <script src="{{ asset('js/matches.js') }}"></script>
@endpush
