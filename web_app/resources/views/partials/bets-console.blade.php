<div class="admin-section compact mb-4">

    <div class="admin-section-header">
        <h3>
            <i class="bi bi-cash-stack"></i>
            Zarządzanie Typami
        </h3>
    </div>

    <div class="admin-actions-grid bets-grid">

        {{-- GENERATE ODDS --}}
        <form
            method="POST"
            action="{{ route('admin.generate-odds') }}"
            class="admin-action-form"
            onsubmit="runConsole(event, 'Generowanie kursów...', this)"
        >
            @csrf

            <button class="admin-action-btn warning">

                <i class="bi bi-cpu-fill"></i>

                <div>
                    <div class="btn-title">
                        Generuj kursy
                    </div>

                    <div class="btn-subtitle">
                        odds_engine.py
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
