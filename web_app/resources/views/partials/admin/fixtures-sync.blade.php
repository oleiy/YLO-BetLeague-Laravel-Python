<div class="admin-section compact mb-4">

    <div class="admin-section-header">
        <h3>
            <i class="bi bi-calendar-event-fill"></i>
            Synchronizacja meczów
        </h3>
    </div>

    <div class="admin-actions-grid single-grid">

        <form
            method="POST"
            action="{{ route('admin.import-fixtures') }}"
            class="admin-action-form"
            onsubmit="runConsole(event, 'Pobieranie meczów...', this)"
        >
            @csrf

            <button class="admin-action-btn secondary">

                <i class="bi bi-download"></i>

                <div>
                    <div class="btn-title">
                        Pobierz mecze
                    </div>

                    <div class="btn-subtitle">
                        import_fixtures.py
                    </div>
                </div>

            </button>

        </form>

    </div>

</div>
