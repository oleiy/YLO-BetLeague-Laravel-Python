{{-- =========================================
    PRESEASON IMPORTS
========================================= --}}

<div class="admin-section compact mb-4">

    <div class="admin-section-header">
        <h3>
            <i class="bi bi-cloud-arrow-down-fill"></i>
            Import przed sezonem
        </h3>
    </div>

    <div class="admin-actions-grid preseason-grid">

        {{-- IMPORT LEAGUES --}}
        <form
            method="POST"
            action="{{ route('admin.import-leagues') }}"
            class="admin-action-form"
            onsubmit="runConsole(event, 'Importowanie lig...', this)"
        >
            @csrf

            <button class="admin-action-btn info">

                <i class="bi bi-trophy-fill"></i>

                <div>
                    <div class="btn-title">
                        Importuj ligi
                    </div>

                    <div class="btn-subtitle">
                        import_leagues.py
                    </div>
                </div>

            </button>

        </form>

        {{-- IMPORT TEAMS --}}
        <form
            method="POST"
            action="{{ route('admin.import-teams') }}"
            class="admin-action-form"
            onsubmit="runConsole(event, 'Importowanie drużyn...', this)"
        >
            @csrf

            <button class="admin-action-btn purple">

                <i class="bi bi-people-fill"></i>

                <div>
                    <div class="btn-title">
                        Importuj drużyny
                    </div>

                    <div class="btn-subtitle">
                        import_teams.py
                    </div>
                </div>

            </button>

        </form>

    </div>

</div>
