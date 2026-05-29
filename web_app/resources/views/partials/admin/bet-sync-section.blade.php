<div class="admin-section compact mb-4">

    <div class="admin-section-header">
        <h3>
            <i class="bi bi-activity"></i>
            Synchronizacja typów
        </h3>
    </div>

    <div class="row g-3 mb-3">

        <div class="col-3">

            <div class="admin-card compact-card">

                <div class="admin-widget-icon sm">
                    <i class="bi bi-lightning-charge-fill"></i>
                </div>

                <div class="admin-widget-label">
                    LIVE
                </div>

                <div class="big-number">
                    {{ $liveBets }}
                </div>

            </div>

        </div>

        <div class="col-3">

            <div class="admin-card compact-card">

                <div class="admin-widget-icon sm">
                    <i class="bi bi-hourglass-split"></i>
                </div>

                <div class="admin-widget-label">
                    Oczekujące
                </div>

                <div class="big-number">
                    {{ $pendingBets }}
                </div>

            </div>

        </div>

        <div class="col-3">

            <div class="admin-card compact-card">

                <div class="admin-widget-icon sm">
                    <i class="bi bi-controller"></i>
                </div>

                <div class="admin-widget-label">
                    Aktywne
                </div>

                <div class="big-number">
                    {{ $activeBets }}
                </div>

            </div>

        </div>

        <div class="col-3">

            <div class="admin-card compact-card">

                <div class="admin-widget-icon sm">
                    <i class="bi bi-trophy-fill"></i>
                </div>

                <div class="admin-widget-label">
                    Rozliczone
                </div>

                <div class="big-number">
                    {{ $settledBets }}
                </div>

            </div>

        </div>

    </div>

    <div class="admin-actions-grid bets-grid">

        {{-- SYNC RESULTS --}}
        <form
            method="POST"
            action="{{ route('admin.sync-fixtures') }}"
            class="admin-action-form"
            onsubmit="runConsole(event, 'Synchronizacja wyników...', this)"
        >
            @csrf

            <button class="admin-action-btn primary">

                <i class="bi bi-arrow-repeat"></i>

                <div>
                    <div class="btn-title">
                        Synchronizuj wyniki
                    </div>

                    <div class="btn-subtitle">
                        import_fixture_statistics.py
                    </div>
                </div>

            </button>

        </form>

        {{-- SETTLE BETS --}}
        <form
            method="POST"
            action="{{ route('admin.settle-bets') }}"
            class="admin-action-form"
            onsubmit="runConsole(event, 'Rozliczanie typów...', this)"
        >
            @csrf

            <button class="admin-action-btn success">

                <i class="bi bi-cash-stack"></i>

                <div>
                    <div class="btn-title">
                        Rozlicz typy
                    </div>

                    <div class="btn-subtitle">
                        settle_bets.py
                    </div>
                </div>

            </button>

        </form>

    </div>

</div>
