<div class="admin-section compact mb-4">

    <div class="admin-section-header">
        <h3>
            <i class="bi bi-cpu-fill"></i>
            Silnik generowania kursów
        </h3>
    </div>

    <div class="admin-actions-grid bets-grid">

        {{-- UPDATE CSV --}}
        <form
            method="POST"
            action="{{ route('admin.update-csv') }}"
            class="admin-action-form"
            onsubmit="runConsole(event, 'Synchronizacja CSV...', this)"
        >
            @csrf

            <button class="admin-action-btn dark">

                <i class="bi bi-database-fill-gear"></i>

                <div>
                    <div class="btn-title">
                        Synchronizuj statystyki
                    </div>

                    <div class="btn-subtitle">
                        update_csv_data.py
                    </div>
                </div>

            </button>

        </form>

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

    </div>

</div>
