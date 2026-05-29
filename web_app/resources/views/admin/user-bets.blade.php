@extends('admin.layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/community.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-user-bets.css') }}">
@endpush

@section('content')

    <div class="admin-dashboard">

        {{-- HEADER --}}
        <div class="admin-page-header">

            <div>

                <h1 class="admin-page-title">
                    Typy użytkowników
                </h1>

                <p class="admin-page-subtitle">
                    Zarządzaj zakładami użytkowników oraz monitoruj aktywność społeczności.
                </p>

            </div>

        </div>

        {{-- CONSOLE --}}
        @include('partials.admin.python-console')

        {{-- FILTERS --}}
        <div class="admin-section mb-4">

            <form method="GET" class="d-flex align-items-center gap-3 flex-wrap">

                {{-- DATE --}}
                <div>

                    <label class="fw-bold mb-1 d-block">
                        Data:
                    </label>

                    <input type="date" name="date" value="{{ $date }}" class="admin-date-input">

                </div>

                {{-- USER --}}
                <div style="min-width: 260px;">

                    <label class="fw-bold mb-1 d-block">
                        Użytkownik:
                    </label>

                    <select name="user_id" class="form-select">

                        <option value="">
                            Wszyscy użytkownicy
                        </option>

                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected($selectedUser == $user->id)>
                                {{ $user->username }}
                            </option>
                        @endforeach

                    </select>

                </div>

                <div class="pt-4">

                    <button class="btn btn-primary">
                        Filtruj
                    </button>

                </div>

            </form>

        </div>

        {{-- FEED --}}
        <div class="admin-bets-feed">

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
                                            <span class="form-dot {{ $form === 'won' ? 'win' : 'lose' }}">
                                            </span>
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

                                {{-- Akcje: Usuń (Admin) --}}
                                <div class="community-analysis-actions">
                                    <form method="POST" action="{{ route('admin.bets.analysis.destroy', $bet) }}"
                                        onsubmit="return confirm('Na pewno usunąć tę analizę użytkownika?')"
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

                            <div>

                                <span class="finance-label">
                                    POTENCJALNA WYGRANA
                                </span>

                                <span class="finance-value">
                                    {{ number_format($bet->potential_win, 2) }} PKT
                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            @empty

                <div class="text-center text-muted py-5">

                    Brak typów użytkowników dla tej daty.

                </div>
            @endforelse

        </div>

    </div>

@endsection

@push('scripts')
    <script src="{{ asset('js/community.js') }}"></script>
@endpush
