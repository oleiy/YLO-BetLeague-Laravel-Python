@extends('layouts.app')

@section('title', 'Moje Typy | YLO TypeLeague')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/community.css') }}">
    <link rel="stylesheet" href="{{ asset('css/my-bets.css') }}">
@endpush

@section('content')

    <div class="matches-header mb-4">

        <h1 class="h-title text-white">
            Moje <span class="brand-accent">Typy</span>
        </h1>

        <p class="dashboard-subtitle text-secondary">
            Zarządzaj swoimi kuponami i sprawdzaj historię typów.
        </p>

    </div>

    @if ($isGuest)

        <div class="mybets-login-box">

            <div class="mybets-login-icon">
                <i class="bi bi-lock-fill"></i>
            </div>

            <h3 class="mybets-login-title">
                Musisz być zalogowany
            </h3>

            <p class="mybets-login-subtitle">
                Zaloguj się aby zobaczyć swoje kupony i historię typów.
            </p>

            <button class="btn btn-primary px-4 py-3 fw-bold" onclick="openLoginModal()">
                Zaloguj się
            </button>

        </div>
    @else
        {{-- =========================================
     GŁÓWNE TABS
========================================= --}}
        <div class="mybets-main-tabs mb-4">

            <a href="{{ route('my-bets', ['status' => 'active', 'sort' => $sort]) }}"
                class="mybets-main-tab {{ $status === 'active' ? 'active' : '' }}">
                <i class="bi bi-lightning-charge-fill"></i>

                <span>
                    Aktywne
                </span>
            </a>

            <a href="{{ route('my-bets', ['status' => 'settled', 'sort' => $sort]) }}"
                class="mybets-main-tab {{ in_array($status, ['settled', 'won', 'lost', 'cancelled']) ? 'active' : '' }}">
                <i class="bi bi-trophy-fill"></i>

                <span>
                    Rozliczone
                </span>
            </a>

            <div class="analysis-filter-wrapper">

            </div>

        </div>

        {{-- =========================================
     FILTRY ROZLICZONYCH
========================================= --}}
        @if (in_array($status, ['settled', 'won', 'lost', 'cancelled']))
            <div class="mybets-subfilters mb-3">

                <a href="{{ route('my-bets', ['status' => 'settled', 'sort' => $sort]) }}"
                    class="mybets-subfilter {{ $status === 'settled' ? 'active' : '' }}">
                    Wszystkie
                </a>

                <a href="{{ route('my-bets', ['status' => 'won', 'sort' => $sort]) }}"
                    class="mybets-subfilter won {{ $status === 'won' ? 'active' : '' }}">
                    Wygrane
                </a>

                <a href="{{ route('my-bets', ['status' => 'lost', 'sort' => $sort]) }}"
                    class="mybets-subfilter lost {{ $status === 'lost' ? 'active' : '' }}">
                    Przegrane
                </a>

                <a href="{{ route('my-bets', ['status' => 'cancelled', 'sort' => $sort]) }}"
                    class="mybets-subfilter cancelled {{ $status === 'cancelled' ? 'active' : '' }}">
                    Anulowane
                </a>


            </div>
        @endif

        {{-- =========================================
     SORTOWANIE
========================================= --}}
        <div class="mybets-sortbar mb-4">

            <div class="mybets-sort-label">
                Sortowanie
            </div>

            <div class="mybets-sort-buttons">

                <a href="{{ route('my-bets', ['status' => $status, 'sort' => 'date_desc']) }}"
                    class="btn btn-cyber-sort {{ $sort === 'date_desc' ? 'active' : '' }}">
                    Najnowsze
                </a>

                <a href="{{ route('my-bets', ['status' => $status, 'sort' => 'date_asc']) }}"
                    class="btn btn-cyber-sort {{ $sort === 'date_asc' ? 'active' : '' }}">
                    Najstarsze
                </a>

                <a href="{{ route('my-bets', ['status' => $status, 'sort' => 'odds_desc']) }}"
                    class="btn btn-cyber-sort {{ $sort === 'odds_desc' ? 'active' : '' }}">
                    Kurs malejąco
                </a>

                <a href="{{ route('my-bets', ['status' => $status, 'sort' => 'odds_asc']) }}"
                    class="btn btn-cyber-sort {{ $sort === 'odds_asc' ? 'active' : '' }}">
                    Kurs rosnąco
                </a>

            </div>

            <div class="analysis-filter-wrapper ms-auto">

                <label class="analysis-filter">

                    <span>
                        Tylko z analizą
                    </span>

                    <input type="checkbox" id="analysisOnlyCheckbox" {{ $analysisOnly ? 'checked' : '' }}>

                </label>

            </div>

        </div>

        {{-- =========================================
     BETS
========================================= --}}
        <div class="mybets-wrapper">

            @forelse($bets as $bet)
                <div class="community-feed-card">

                    {{-- TOPBAR --}}
                    <div class="community-topbar">

                        <div class="community-user">

                            <div class="community-avatar">
                                {{ strtoupper(substr(auth()->user()->username, 0, 2)) }}
                            </div>

                            <div>

                                <div class="community-username">
                                    {{ auth()->user()->username }}
                                </div>

                                <div class="mybet-created">
                                    {{ $bet->created_at->format('d.m.Y H:i') }}
                                </div>

                            </div>

                        </div>

                        <div>

                            @if (in_array($bet->status, ['pending', 'active', 'settling']))
                                <span class="community-status pending">
                                    AKTYWNY
                                </span>
                            @elseif($bet->status === 'won')
                                <span class="community-status won">
                                    TRAFIONY
                                </span>
                            @elseif($bet->status === 'lost')
                                <span class="community-status lost">
                                    NIETRAFIONY
                                </span>
                            @elseif($bet->status === 'cancelled')
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

                                            @php
                                                $hasSpecifier =
                                                    $item->odd->specifier !== null && $item->odd->specifier > 0;
                                            @endphp

                                            @if ($hasSpecifier)
                                                {{ $item->odd->outcome_name }}
                                                {{ number_format($item->odd->specifier, 1) }}
                                            @else
                                                {{ $item->odd->outcome_name }}
                                            @endif

                                        </div>

                                        <div class="community-item-market">

                                            {{ $item->odd->market_name }}

                                            @if ($item->odd->team)
                                                - {{ $item->odd->team->name }}
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

                    @if (!empty(trim($bet->analysis ?? '')))
                        <div class="community-analysis-wrapper">

                            {{-- NOWY NAGŁÓWEK Z AKCJAMI --}}
                            <div class="community-analysis-top">

                                {{-- Przycisk rozwijania analizy --}}
                                <button class="community-analysis-toggle" type="button">
                                    <i class="bi bi-journal-text"></i>
                                    Analiza typu
                                    <i class="bi bi-chevron-down analysis-arrow ms-2"></i>
                                </button>

                                {{-- Akcje: Edytuj i Usuń (tylko ikony) --}}
                                <div class="community-analysis-actions">

                                    {{-- Przycisk Edycji (JS złapie klasę btn-edit-analysis) --}}
                                    <button type="button" class="btn-action-icon edit btn-edit-analysis"
                                        data-id="{{ $bet->id }}" data-analysis="{{ $bet->analysis }}"
                                        title="Edytuj analizę">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>

                                    {{-- Formularz Usuwania --}}
                                    <form method="POST" action="{{ route('my-bets.analysis.destroy', $bet) }}"
                                        onsubmit="return confirm('Na pewno chcesz usunąć tę analizę?')"
                                        class="m-0 p-0 d-inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action-icon delete" title="Usuń analizę">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>

                                </div>
                            </div>

                            {{-- TREŚĆ ANALIZY --}}
                            <div class="community-analysis-content">
                                <div>
                                    <div class="analysis-text" id="analysis-text-{{ $bet->id }}">{{ $bet->analysis }}
                                    </div>

                                    {{-- Niewidoczny domyślnie formularz edycji --}}
                                    <form method="POST" action="{{ route('my-bets.analysis.update', $bet) }}"
                                        class="analysis-edit-form d-none mt-3" id="analysis-form-{{ $bet->id }}">
                                        @csrf
                                        @method('PUT')

                                        <textarea name="analysis" class="form-control rows="4"
                                            style="background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1); color: #fff;">{{ $bet->analysis }}</textarea>

                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-sm btn-success fw-bold px-3">
                                                Zapisz
                                            </button>
                                            <button type="button"
                                                class="btn btn-sm btn-secondary btn-cancel-edit fw-bold px-3"
                                                data-id="{{ $bet->id }}">
                                                Anuluj
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                        </div>
                    @endif

                    {{-- FOOTER --}}
                    <div class="community-footer">

                        <div class="community-finance">

                            {{-- STAWKA --}}
                            <div>

                                <span class="finance-label">
                                    STAWKA
                                </span>

                                <span class="finance-value">
                                    {{ $bet->stake }} PKT
                                </span>

                            </div>

                            {{-- WYGRANA --}}
                            @if ($bet->status === 'won')
                                <div>

                                    <span class="finance-label">
                                        WYGRANA
                                    </span>

                                    <span class="finance-value" style="color:#22c55e;">
                                        {{ number_format($bet->potential_win, 2) }} PKT
                                    </span>

                                </div>

                                {{-- AKTYWNY --}}
                            @elseif(in_array($bet->status, ['pending', 'active', 'settling']))
                                <div>

                                    <span class="finance-label">
                                        POTENCJALNA WYGRANA
                                    </span>

                                    <span class="finance-value" style="color:#cbd5e1;">
                                        {{ number_format($bet->potential_win, 2) }} PKT
                                    </span>

                                </div>
                            @endif

                        </div>

                    </div>

                </div>

            @empty

                <div class="text-center text-secondary py-5">

                    Brak kuponów dla wybranych filtrów.

                </div>
            @endforelse

        </div>

    @endif

@endsection

@push('scripts')
    <script src="{{ asset('js/my-bets.js') }}"></script>
    <script src="{{ asset('js/community.js') }}"></script>
@endpush
