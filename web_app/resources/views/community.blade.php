@extends('layouts.app')

@section('title', 'Społeczność | YLO TypeLeague')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/community.css') }}">
@endpush

@section('content')

    <div class="matches-header mb-4">

        <h1 class="h-title text-white">
            Typy <span class="brand-accent">Społeczności</span>
        </h1>

        <p class="dashboard-subtitle text-secondary">
            Sprawdź typy innych graczy i dołącz do rywalizacji.
        </p>

    </div>

    {{-- =========================================
     GŁÓWNE TABS
========================================= --}}
    <ul class="nav nav-tabs cyber-tabs mb-4" id="communityTabs" role="tablist">

        <li class="nav-item" role="presentation">

            <button class="nav-link active" id="types-tab" data-bs-toggle="tab" data-bs-target="#types-pane" type="button"
                role="tab">
                <i class="bi bi-lightning-charge me-2"></i>
                Typy społeczności
            </button>

        </li>

        <li class="nav-item" role="presentation">

            <button class="nav-link" id="ranking-tab" data-bs-toggle="tab" data-bs-target="#ranking-pane" type="button"
                role="tab">
                <i class="bi bi-trophy me-2"></i>
                Ranking
            </button>

        </li>

    </ul>

    <div class="tab-content" id="communityTabContent">

        {{-- =========================================
         COMMUNITY TYPES
    ========================================= --}}
        <div class="tab-pane fade show active" id="types-pane" role="tabpanel">

            {{-- KALENDARZ --}}
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

                    @for ($i = 0; $i <= 14; $i++)
                        @php

                            $currentDate = $start->copy()->addDays($i);

                            if ($currentDate->isToday()) {
                                $dayName = 'Dziś';
                            } elseif ($currentDate->isYesterday()) {
                                $dayName = 'Wcz';
                            } elseif ($currentDate->isTomorrow()) {
                                $dayName = 'Jtr';
                            } else {
                                $enDay = $currentDate->format('D');
                                $dayName = $daysMap[$enDay] ?? $enDay;
                            }

                        @endphp

                        <button class="day-btn {{ $currentDate->toDateString() === $date ? 'active' : '' }}"
                            id="{{ $currentDate->toDateString() === $date ? 'todayBtn' : '' }}"
                            data-date="{{ $currentDate->toDateString() }}">

                            <span class="day-name">
                                {{ $dayName }}.
                            </span>

                            <span class="day-date">
                                {{ $currentDate->format('d.m') }}
                            </span>

                        </button>
                    @endfor

                </div>

                <button class="scroll-arrow right" onclick="scrollDays('right')">
                    <i class="bi bi-chevron-right"></i>
                </button>

            </div>

            {{-- SORT --}}
            <div class="sorting-wrapper mb-4 d-flex flex-wrap gap-2 align-items-center">

                <span class="text-secondary x-small fw-bold me-2 text-uppercase">
                    Sortowanie:
                </span>

                <button class="btn btn-cyber-sort {{ $sort === 'success_rate' ? 'active' : '' }}"
                    onclick="sortTips('success_rate')">
                    Skuteczność typera
                </button>

                <button class="btn btn-cyber-sort {{ $sort === 'time' ? 'active' : '' }}" onclick="sortTips('time')">
                    Godzina spotkania
                </button>

                <button class="btn btn-cyber-sort {{ $sort === 'odds_asc' ? 'active' : '' }}"
                    onclick="sortTips('odds_asc')">
                    Kurs rosnąco
                </button>

                <button class="btn btn-cyber-sort {{ $sort === 'odds_desc' ? 'active' : '' }}"
                    onclick="sortTips('odds_desc')">
                    Kurs malejąco
                </button>

                <div class="analysis-filter-wrapper">

                    <label class="analysis-filter">

                        <span>
                            Tylko z analizą
                        </span>

                        <input type="checkbox" id="analysisOnlyCheckbox" {{ $analysisOnly ? 'checked' : '' }}>

                    </label>

                </div>

            </div>

            {{-- FEED --}}
            <div id="matches-list-container">

                @forelse($bets as $bet)

                    @php
                        $user = $bet->user;
                    @endphp

                    <div class="community-feed-card">

                        {{-- TOPBAR --}}
                        <div class="community-topbar">

                            <div class="community-user">

                                <div class="community-avatar">
                                    {{ strtoupper(substr($user->username, 0, 2)) }}
                                </div>

                                <div>

                                    <div class="community-username">
                                        {{ $user->username }}
                                    </div>

                                    <div class="community-user-meta">

                                        <span class="success-rate">

                                            {{ number_format($user->calculated_accuracy ?? 0, 1) }}%

                                        </span>

                                        <div class="recent-form">

                                            @foreach ($user->recent_form ?? [] as $form)
                                                <span class="form-dot {{ $form === 'won' ? 'win' : 'lose' }}"></span>
                                            @endforeach

                                        </div>

                                    </div>

                                </div>

                            </div>

                            <div>

                                @php

                                    $status = in_array($bet->status, ['pending', 'active', 'settling'])
                                        ? 'pending'
                                        : $bet->status;

                                @endphp

                                @if ($status === 'pending')
                                    <span class="community-status pending">
                                        AKTYWNY
                                    </span>
                                @elseif($status === 'won')
                                    <span class="community-status won">
                                        TRAFIONY
                                    </span>
                                @elseif($status === 'lost')
                                    <span class="community-status lost">
                                        NIETRAFIONY
                                    </span>
                                @elseif($status === 'cancelled')
                                    <span class="community-status cancelled">
                                        ANULOWANY
                                    </span>
                                @endif

                            </div>

                        </div>

                        {{-- MATCH --}}
                        <div class="community-match-header">

                            <div>

                                <div class="community-league">
                                    {{ $bet->fixture->league->name }}
                                </div>

                                <div class="community-teams">

                                    {{ $bet->fixture->homeTeam->name }}
                                    -
                                    {{ $bet->fixture->awayTeam->name }}

                                </div>

                            </div>

                            <div class="community-time">
                                {{ $bet->fixture->match_date->format('d.m H:i') }}
                            </div>

                        </div>

                        {{-- BODY --}}
                        <div class="community-bet-body">

                            <div class="d-flex justify-content-between align-items-center mb-3">

                                <span class="community-bet-type">

                                    {{ $bet->items->count() > 1 ? 'BET BUILDER' : 'SOLO' }}

                                </span>

                                <span class="community-total-odd">

                                    {{ number_format($bet->total_odd, 2) }}

                                </span>

                            </div>

                            <ul class="community-items-list">

                                @foreach ($bet->items as $item)
                                    <li class="community-item">

                                        <div class="community-item-left">

                                            <div class="community-item-outcome">

                                                <span class="outcome-name">
                                                    {{ $item->odd->outcome_name }}
                                                </span>

                                                @if (!is_null($item->odd->specifier) && $item->odd->specifier > 0)
                                                    <span class="outcome-specifier">
                                                        {{ number_format($item->odd->specifier, 1) }}
                                                    </span>
                                                @endif

                                            </div>

                                            <div class="community-item-market">

                                                {{ $item->odd->market_name }}

                                                @if ($item->odd->team)
                                                    <span class="market-team">
                                                        -
                                                        {{ $item->odd->team->name }}
                                                    </span>
                                                @endif

                                            </div>

                                        </div>

                                        <span class="community-item-odd">

                                            {{ number_format($item->odd->value, 2) }}

                                        </span>

                                    </li>
                                @endforeach

                            </ul>

                        </div>

                        {{-- ANALIZA --}}
                        @if (!empty(trim($bet->analysis ?? '')))
                            <div class="community-analysis-wrapper">

                                <button class="community-analysis-toggle" type="button">
                                    <i class="bi bi-journal-text"></i>

                                    Analiza typu

                                    <i class="bi bi-chevron-down analysis-arrow"></i>
                                </button>

                                <div class="community-analysis-content">
                                    <div class="analysis-text">{{ $bet->analysis }}</div>
                                </div>

                            </div>
                        @endif

                        {{-- FOOTER --}}
                        <div class="community-footer">

                            <div class="community-finance">

                                <div>

                                    <span class="finance-label">
                                        STAWKA
                                    </span>

                                    <span class="finance-value">
                                        {{ $bet->stake }} PKT
                                    </span>

                                </div>

                                @if (in_array($bet->status, ['pending', 'active', 'settling']))
                                    <div>

                                        <span class="finance-label">
                                            POTENCJALNA WYGRANA
                                        </span>

                                        <span class="finance-value">
                                            {{ number_format($bet->potential_win, 2) }} PKT
                                        </span>

                                    </div>
                                @elseif($bet->status === 'won')
                                    <div>

                                        <span class="finance-label">
                                            WYGRANA
                                        </span>

                                        <span class="finance-value text-success">
                                            {{ number_format($bet->potential_win, 2) }} PKT
                                        </span>

                                    </div>
                                @endif

                            </div>

                        </div>

                    </div>

                @empty

                    <div class="text-center text-secondary py-5">

                        Brak typów społeczności dla tego dnia.

                    </div>

                @endforelse

            </div>

        </div>

        {{-- =========================================
         RANKINGS
    ========================================= --}}
        <div class="tab-pane fade" id="ranking-pane" role="tabpanel">

            {{-- RANKING TABS --}}
            <div class="ranking-tabs mb-4">

                <button class="ranking-tab active" data-ranking="weekly">
                    <i class="bi bi-fire"></i>
                    Tygodnia
                </button>

                <button class="ranking-tab" data-ranking="monthly">
                    <i class="bi bi-calendar2-week"></i>
                    Miesiąca
                </button>

                <button class="ranking-tab" data-ranking="global">
                    <i class="bi bi-trophy"></i>
                    Hall Of Fame
                </button>

                <button class="ranking-tab" data-ranking="odds">
                    <i class="bi bi-graph-up-arrow"></i>
                    Najwyższe kursy
                </button>

            </div>

            @auth

                <button class="btn-show-me mb-4" onclick="scrollToMyRank()">
                    <i class="bi bi-crosshair"></i>
                    Pokaż mnie w rankingu
                </button>

            @endauth

            {{-- WEEKLY --}}
            <div class="ranking-panel active" id="ranking-weekly">

                @foreach ($weeklyRanking as $index => $user)
                    @include('partials.ranking-user-card', [
                        'user' => $user,
                        'index' => $index,
                        'points' => number_format($user->points_gained ?? 0, 0) . ' PKT',
                    ])
                @endforeach

            </div>

            {{-- MONTHLY --}}
            <div class="ranking-panel" id="ranking-monthly" style="display:none;">

                @foreach ($monthlyRanking as $index => $user)
                    @include('partials.ranking-user-card', [
                        'user' => $user,
                        'index' => $index,
                        'points' => number_format($user->points_gained ?? 0, 0) . ' PKT',
                    ])
                @endforeach

            </div>

            {{-- GLOBAL --}}
            <div class="ranking-panel" id="ranking-global" style="display:none;">

                @foreach ($globalRanking as $index => $user)
                    @include('partials.ranking-user-card', [
                        'user' => $user,
                        'index' => $index,
                        'points' => number_format($user->stats->balance_points ?? 0, 0) . ' PKT',
                    ])
                @endforeach

            </div>

            {{-- HIGHEST ODDS --}}
            <div class="ranking-panel" id="ranking-odds" style="display:none;">

                @foreach ($highestOddsRanking as $index => $bet)
                    <div class="ranking-user-card compact">

                        <div class="ranking-position">
                            #{{ $index + 1 }}
                        </div>

                        <div class="ranking-user-info">

                            <div class="ranking-avatar">

                                {{ strtoupper(substr($bet->user->username, 0, 2)) }}

                            </div>

                            <div>

                                <div class="ranking-username">

                                    {{ $bet->user->username }}

                                </div>

                                <div class="ranking-meta">

                                    {{ $bet->fixture->homeTeam->name }}
                                    -
                                    {{ $bet->fixture->awayTeam->name }}

                                </div>

                            </div>

                        </div>

                        <div class="ranking-points">

                            {{ number_format($bet->total_odd, 2) }}

                        </div>

                    </div>
                @endforeach

            </div>

        </div>

    </div>

@endsection

@push('scripts')
    <script src="{{ asset('js/community.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            initRankingTabs();

            // domyślnie pokaż ranking tygodnia
            switchRankingPanel('weekly');
        });

        function initRankingTabs() {
            const tabs = document.querySelectorAll('.ranking-tab');

            tabs.forEach(tab => {

                tab.addEventListener('click', () => {

                    const target = tab.dataset.ranking;

                    switchRankingPanel(target);
                });
            });
        }

        function switchRankingPanel(target) {
            const tabs = document.querySelectorAll('.ranking-tab');

            const panels = document.querySelectorAll('.ranking-panel');

            // reset tabów
            tabs.forEach(tab => {

                tab.classList.remove('active');

                if (tab.dataset.ranking === target) {
                    tab.classList.add('active');
                }
            });

            // ukryj wszystkie panele
            panels.forEach(panel => {

                panel.classList.remove('active');

                panel.style.display = 'none';
            });

            // pokaż wybrany panel
            const targetPanel = document.getElementById(
                `ranking-${target}`
            );

            if (targetPanel) {

                targetPanel.classList.add('active');

                targetPanel.style.display = 'block';
            }
        }

        function scrollToMyRank() {
            const activePanel = document.querySelector(
                '.ranking-panel.active'
            );

            if (!activePanel) return;

            const userCard = activePanel.querySelector(
                '.ranking-user-card.current-user'
            );

            if (!userCard) return;

            userCard.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }
    </script>
@endpush
