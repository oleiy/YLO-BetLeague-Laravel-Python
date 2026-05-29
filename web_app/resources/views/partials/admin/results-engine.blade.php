<div class="admin-section compact mb-4">

    <div class="admin-section-header">
        <h3>
            <i class="bi bi-check2-circle"></i>
            Silnik wyników i rozliczeń
        </h3>
    </div>

    <div class="admin-actions-grid double-grid">

        {{-- SYNC RESULTS --}}
        <form
            method="POST"
            action="{{ route('admin.sync-fixture-stats') }}"
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
