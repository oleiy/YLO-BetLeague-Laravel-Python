@extends('admin.layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-matches.css') }}">
@endpush

@section('content')

<div class="admin-dashboard">

    {{-- HEADER --}}
    <div class="admin-page-header">

        <div>
            <h1 class="admin-page-title">Mecze</h1>

            <p class="admin-page-subtitle">
                Przegląd wszystkich meczów z podziałem na ligi i daty.
            </p>
        </div>

    </div>

    {{-- PYTHON ENGINE --}}
    @include('partials.admin.python-console')

    {{-- DATE --}}
    <div class="admin-section">

        <form method="GET" class="d-flex align-items-center gap-2">

            <label class="fw-bold">Data:</label>

            <input
                type="date"
                name="date"
                value="{{ $date }}"
                class="admin-date-input"
                onchange="this.form.submit()"
            >

        </form>

    </div>
{{-- SYSTEM ACTIONS --}}
@include('partials.admin.fixtures-sync')

@include('partials.admin.results-engine')

    {{-- LEAGUES --}}
    @forelse($leagues as $league)

        <div class="league-block">

            {{-- LEAGUE HEADER --}}
            <div class="league-header">

                <i class="bi bi-trophy-fill league-icon"></i>

                <div class="league-title">
                    {{ $league->name }}
                </div>

            </div>

            {{-- MATCHES --}}
            <div class="matches-grid">

                @foreach($league->fixtures as $fixture)

                    <div class="match-card">

                        {{-- TOP --}}
                        <div class="match-top">

                            <span class="match-status status-{{ $fixture->status }}">
                                {{ $fixture->status }}
                            </span>

                            <div class="match-time">
                                {{ $fixture->match_date->format('H:i') }}
                            </div>

                        </div>

                        {{-- TEAMS --}}
                        <div class="match-teams">

                            <div class="team">

                                <img
                                    class="team-logo"
                                    src="{{ asset($fixture->homeTeam->logo_path) }}"
                                >

                                <div class="team-name">
                                    {{ $fixture->homeTeam->name }}
                                </div>

                            </div>

                            <div class="vs">VS</div>

                            <div class="team">

                                <img
                                    class="team-logo"
                                    src="{{ asset($fixture->awayTeam->logo_path) }}"
                                >

                                <div class="team-name">
                                    {{ $fixture->awayTeam->name }}
                                </div>

                            </div>

                        </div>

                        {{-- FOOTER --}}
                        <div class="match-footer">

                            <span>
                                {{ $league->name }}
                            </span>

                            <span>
                                #{{ $fixture->id }}
                            </span>

                        </div>

                    </div>

                @endforeach

            </div>

        </div>

    @empty

        <div class="text-center text-muted py-5">
            Brak meczów dla tej daty.
        </div>

    @endforelse

</div>

@endsection
