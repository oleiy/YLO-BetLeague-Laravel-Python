@extends('admin.layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-users.css') }}">
@endpush

@section('content')

<div class="admin-dashboard">

    {{-- HEADER --}}
    <div class="admin-page-header mb-4">

        <div>

            <h1 class="admin-page-title">
                Użytkownicy
            </h1>

            <p class="admin-page-subtitle">
                Zarządzanie użytkownikami systemu.
            </p>

        </div>

    </div>

    {{-- FILTERS --}}
    <div class="admin-section mb-4">

        <form
            method="GET"
            class="users-toolbar"
        >

            <input
                type="text"
                name="search"
                placeholder="Szukaj po nicku..."
                value="{{ $search }}"
                class="users-search-input"
            >

            <select
                name="sort"
                class="users-sort-select"
                onchange="this.form.submit()"
            >

                <option value="username_asc"
                    {{ $sort === 'username_asc' ? 'selected' : '' }}>
                    Nick A-Z
                </option>

                <option value="username_desc"
                    {{ $sort === 'username_desc' ? 'selected' : '' }}>
                    Nick Z-A
                </option>

                <option value="newest"
                    {{ $sort === 'newest' ? 'selected' : '' }}>
                    Najnowsi
                </option>

                <option value="oldest"
                    {{ $sort === 'oldest' ? 'selected' : '' }}>
                    Najstarsi
                </option>

            </select>

            <button class="admin-action-btn primary">

                <i class="bi bi-search"></i>

                <span>
                    Szukaj
                </span>

            </button>

        </form>

    </div>

    {{-- USERS --}}
<div class="users-list">

    @foreach($users as $user)

        <div class="user-row">

            <div class="user-row-top">

                {{-- LEWA STRONA --}}
                <div class="user-left">

                    <div class="user-main-info">

                        <div class="user-avatar">
                            {{ strtoupper(substr($user->username, 0, 2)) }}
                        </div>

                        <div class="user-basic">

                            <div class="user-name-row">

                                <div class="user-name">
                                    {{ $user->username }}
                                </div>

                                @if($user->stats?->is_banned)

                                    <span class="user-status banned">
                                        ZBANOWANY
                                    </span>

                                @else

                                    <span class="user-status active">
                                        AKTYWNY
                                    </span>

                                @endif

                            </div>

                            <div class="user-email">
                                {{ $user->email }}
                            </div>

                        </div>

                    </div>

                    {{-- STATYSTYKI --}}
                    <div class="user-stats">

                        <div class="user-stat-box">
                            <span>Rola</span>
                            <strong>
                                {{ strtoupper($user->role) }}
                            </strong>
                        </div>

                        <div class="user-stat-box">
                            <span>Saldo</span>
                            <strong>
                                {{ number_format($user->stats?->balance_points ?? 0) }}
                            </strong>
                        </div>

                        <div class="user-stat-box">
                            <span>Typy</span>
                            <strong>
                                {{ $user->bets->count() }}
                            </strong>
                        </div>

                        <div class="user-stat-box">
                            <span>Winrate</span>
                            <strong>
                                {{ number_format($user->stats?->accuracy_rate ?? 0, 1) }}%
                            </strong>
                        </div>

                        <div class="user-stat-box">
                            <span>Streak</span>
                            <strong>
                                {{ $user->stats?->current_streak ?? 0 }}
                            </strong>
                        </div>

                        <div class="user-stat-box">
                            <span>Najlepszy streak</span>
                            <strong>
                                {{ $user->stats?->best_streak ?? 0 }}
                            </strong>
                        </div>

                    </div>

                </div>

                {{-- PRAWA STRONA --}}
                <div class="user-right">

                    <div class="user-buttons">

                        <button
                            type="button"
                            class="user-mini-btn edit"
                            onclick="togglePanel('edit-{{ $user->id }}')"
                        >
                            <i class="bi bi-pencil-fill"></i>
                            Edytuj
                        </button>

                        <button
                            type="button"
                            class="user-mini-btn ban"
                            onclick="togglePanel('ban-{{ $user->id }}')"
                        >
                            <i class="bi bi-slash-circle-fill"></i>

                            {{ $user->stats?->is_banned
                                ? 'Odbanuj'
                                : 'Zbanuj'
                            }}
                        </button>

                        <button
                            type="button"
                            class="user-mini-btn delete"
                            onclick="togglePanel('delete-{{ $user->id }}')"
                        >
                            <i class="bi bi-trash-fill"></i>
                            Usuń
                        </button>

                    </div>

                </div>

            </div>

            {{-- =========================================
                 EDIT PANEL
            ========================================= --}}
            <div
                id="edit-{{ $user->id }}"
                class="expand-panel"
            >

                <form
                    method="POST"
                    action="{{ route('admin.users.update', $user) }}"
                    class="expand-form"
                    onsubmit="return confirm('Zapisać zmiany użytkownika?')"
                >

                    @csrf
                    @method('PUT')

                    <input
                        type="text"
                        name="username"
                        value="{{ $user->username }}"
                        class="user-input"
                    >

                    <input
                        type="email"
                        name="email"
                        value="{{ $user->email }}"
                        class="user-input"
                    >

                    <select
                        name="role"
                        class="user-input"
                    >

                        <option
                            value="user"
                            {{ $user->role === 'user' ? 'selected' : '' }}
                        >
                            USER
                        </option>

                        <option
                            value="admin"
                            {{ $user->role === 'admin' ? 'selected' : '' }}
                        >
                            ADMIN
                        </option>

                    </select>

                    <button class="admin-action-btn warning">

                        <i class="bi bi-check-lg"></i>

                        <span>
                            Zapisz
                        </span>

                    </button>

                </form>

            </div>

            {{-- =========================================
                 BAN PANEL
            ========================================= --}}
            <div
                id="ban-{{ $user->id }}"
                class="expand-panel"
            >

                <form
                    method="POST"
                    action="{{ route('admin.users.ban', $user) }}"
                    class="expand-form"
                    onsubmit="return confirm('Zmienić status bana użytkownika?')"
                >

                    @csrf

                    @if($user->stats?->is_banned)

                        <input
                            type="hidden"
                            name="is_banned"
                            value="0"
                        >

                        <button class="admin-action-btn success">

                            <i class="bi bi-unlock-fill"></i>

                            <span>
                                Odbanuj użytkownika
                            </span>

                        </button>

                    @else

                        <input
                            type="hidden"
                            name="is_banned"
                            value="1"
                        >

                        <input
                            type="datetime-local"
                            name="ban_until"
                            class="user-input"
                        >

                        <button class="admin-action-btn dark">

                            <i class="bi bi-slash-circle-fill"></i>

                            <span>
                                Zapisz bana
                            </span>

                        </button>

                    @endif

                </form>

            </div>

            {{-- =========================================
                 DELETE PANEL
            ========================================= --}}
            <div
                id="delete-{{ $user->id }}"
                class="expand-panel"
            >

                <div class="user-danger-box">

                    <p>
                        Ta operacja usunie użytkownika oraz wszystkie
                        jego dane z bazy.
                    </p>

                    <form
                        method="POST"
                        action="{{ route('admin.users.destroy', $user) }}"
                        onsubmit="return confirm('USUNĄĆ użytkownika?')"
                    >

                        @csrf
                        @method('DELETE')

                        <button class="admin-action-btn danger">

                            <i class="bi bi-trash-fill"></i>

                            <span>
                                Usuń użytkownika
                            </span>

                        </button>

                    </form>

                </div>

            </div>

        </div>

    @endforeach

</div>

    {{-- PAGINATION --}}
    <div class="mt-4">

        {{ $users->withQueryString()->links() }}

    </div>

</div>
@section('scripts')

<script>

    function togglePanel(id)
    {
        const panel = document.getElementById(id);

        panel.classList.toggle('active');
    }

</script>

@endsection

@endsection

