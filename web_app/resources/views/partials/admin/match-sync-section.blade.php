<div class="admin-section compact mb-4">

    <div class="admin-section-header">
        <h3>
            <i class="bi bi-calendar-event-fill"></i>
            Pobieranie meczów
        </h3>
    </div>

    <div class="row g-3 mb-3">

        <div class="col-3">

            <div class="admin-card compact-card">

                <div class="admin-widget-icon sm">
                    <i class="bi bi-calendar-event-fill"></i>
                </div>

                <div class="admin-widget-label">
                    Dzisiejsze
                </div>

                <div class="big-number">
                    {{ $todayFixturesCount }}
                </div>

            </div>

        </div>

        <div class="col-3">

            <div class="admin-card compact-card">

                <div class="admin-widget-icon sm">
                    <i class="bi bi-broadcast-pin"></i>
                </div>

                <div class="admin-widget-label">
                    LIVE
                </div>

                <div class="big-number">
                    {{ $liveFixtures }}
                </div>

            </div>

        </div>

        <div class="col-3">

            <div class="admin-card compact-card">

                <div class="admin-widget-icon sm">
                    <i class="bi bi-clock-history"></i>
                </div>

                <div class="admin-widget-label">
                    Nadchodzące
                </div>

                <div class="big-number">
                    {{ $upcomingFixtures }}
                </div>

            </div>

        </div>

        <div class="col-3">

            <div class="admin-card compact-card">

                <div class="admin-widget-icon sm">
                    <i class="bi bi-check2-circle"></i>
                </div>

                <div class="admin-widget-label">
                    Zakończone
                </div>

                <div class="big-number">
                    {{ $finishedFixtures }}
                </div>

            </div>

        </div>

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
