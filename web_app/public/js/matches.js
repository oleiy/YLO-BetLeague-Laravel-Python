// public/js/matches.js

/**
 * Zmienna przechowująca dane o ligach i meczach.
 */
let loadedLeagues = window.loadedLeagues || [];

/**
 * Konfiguracja mapowania nazw rynków.
 */
const marketConfig = [
    { original: 'Podwójna szansa', display: 'Podwójna szansa' },
    { original: 'Obie drużyny strzelą', display: 'Obie drużyny strzelą' },
    { original: 'Liczba goli', display: 'Liczba goli' },
    { original: 'Liczba goli drużyny', display: 'Liczba goli drużyny' },
    { original: 'Rzuty rożne', display: 'Rzuty rożne' },
    { original: 'Rzuty rożne drużyny', display: 'Rzuty rożne drużyny' },
    { original: 'Celne strzały', display: 'Celne strzały' },
    { original: 'Celne strzały drużyny', display: 'Celne strzały drużyny' },
    { original: 'Liczba kartek', display: 'Liczba kartek' },
    { original: 'Liczba kartek drużyny', display: 'Liczba kartek drużyny' }
];

/**
 * Główny listener DOMContentLoaded.
 */
window.addEventListener('DOMContentLoaded', () => {
    const activeBtn = document.getElementById('activeDayBtn');

    if (activeBtn) {
        activeBtn.scrollIntoView({
            behavior: 'auto',
            inline: 'center',
            block: 'nearest'
        });
    }

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-odd');
        if (btn) {
            btn.parentElement.querySelectorAll('.btn-odd').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }
    });
});

/**
 * Funkcja centerMe – obsługa zmiany daty.
 */
async function centerMe(el) {
    document.querySelectorAll('.day-btn').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
    el.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });

    const date = el.dataset.date;
    const container = document.getElementById('matches-list-container');

    container.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-accent" role="status"></div>
            <p class="text-secondary mt-2">Ładowanie meczów...</p>
        </div>
    `;

    try {
        const response = await fetch(`/api/matches/${date}`);
        loadedLeagues = await response.json();
        console.log(JSON.stringify(loadedLeagues, null, 2));

        refreshMatchesUI(loadedLeagues);
    } catch (error) {
        console.error('Błąd pobierania:', error);
        container.innerHTML = '<div class="text-center text-danger py-5">Błąd podczas ładowania danych.</div>';
    }
}

function parseMatchDate(dateString) {

    if (!dateString) {
        return {
            time: '--:--',
            day: '--.--',
            full: '--.--.---- --:--'
        };
    }

    /*
    |--------------------------------------------------------------------------
    | POPRAWNE PARSOWANIE UTC → LOCAL TIME
    |--------------------------------------------------------------------------
    */

    const date = new Date(dateString);

    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();

    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    return {
        time: `${hours}:${minutes}`,
        day: `${day}.${month}`,
        full: `${day}.${month}.${year} ${hours}:${minutes}`
    };
}
function renderFinishedMatch(match) {

    const stats = match.statistics;

    const parsedDate = parseMatchDate(match.match_date_local);

    const time = parsedDate.full;


    const statRows = [
        {
            label: 'Rzuty rożne',
            home: stats?.home_corners || 0,
            away: stats?.away_corners || 0
        },
        {
            label: 'Żółte kartki',
            home: stats?.home_yellow_cards || 0,
            away: stats?.away_yellow_cards || 0
        },
        {
            label: 'Czerwone kartki',
            home: stats?.home_red_cards || 0,
            away: stats?.away_red_cards || 0
        },
        {
            label: 'Celne strzały',
            home: stats?.home_shots_on_goal || 0,
            away: stats?.away_shots_on_goal || 0
        }
    ];

    return `
        <div class="finished-match-wrapper">

            <div
                class="finished-match-compact"
                onclick="toggleFinishedStats(${match.id})"
            >

                <div class="finished-date-small">
                    ${time}
                </div>

                <div class="finished-main-row">

                    <div class="finished-team-mini">
    <img
        src="/${match.home_team.logo_path}"
        class="finished-logo-mini"
    >

    <span class="finished-team-name">
        ${match.home_team.name}
    </span>
</div>

                    <div class="finished-score-mini">
                        ${match.statistics?.home_goals ?? 0} - ${match.statistics?.away_goals ?? 0}

                        <div class="finished-ft">
                            Zakończony
                        </div>
                    </div>

                    <div class="finished-team-mini">
    <img
        src="/${match.away_team.logo_path}"
        class="finished-logo-mini"
    >

    <span class="finished-team-name">
        ${match.away_team.name}
    </span>
</div>

                </div>

            </div>

            <div
                class="finished-stats-expand d-none"
                id="finished-stats-${match.id}"
            >

                ${statRows.map(stat => `

                    <div class="stat-row">

                        <div class="stat-value">
                            ${stat.home}
                        </div>

                        <div class="stat-center">

                            <div class="stat-label">
                                ${stat.label}
                            </div>

                            <div class="stat-bars">

                                <div class="bar left">
                                    <div
                                        class="fill"
                                        style="
                                            width:${(stat.home / Math.max(stat.home + stat.away, 1)) * 100}%
                                        "
                                    ></div>
                                </div>

                                <div class="bar right">
                                    <div
                                        class="fill"
                                        style="
                                            width:${(stat.away / Math.max(stat.home + stat.away, 1)) * 100}%
                                        "
                                    ></div>
                                </div>

                            </div>

                        </div>

                        <div class="stat-value">
                            ${stat.away}
                        </div>

                    </div>

                `).join('')}

            </div>

        </div>
    `;
}

function toggleFinishedStats(matchId) {

    const container =
        document.getElementById(`finished-stats-${matchId}`);

    const icon =
        document.getElementById(`finished-icon-${matchId}`);

    if (container.classList.contains('d-none')) {

        container.classList.remove('d-none');

        icon.className = 'bi bi-chevron-up';

    } else {

        container.classList.add('d-none');

        icon.className = 'bi bi-chevron-down';
    }
}


/**
 * Funkcja refreshMatchesUI – odświeża listę meczów.
 */
function refreshMatchesUI(leagues) {

    console.log("Otrzymane dane z API:", leagues);

    const container = document.getElementById('matches-list-container');

    if (leagues.length === 0) {
        container.innerHTML =
            '<div class="text-center text-secondary py-5">Brak meczów na ten dzień.</div>';
        return;
    }

    const leagueLogos = {
        'la liga': 'la_liga.png',
        'laliga': 'la_liga.png',
        'serie a': 'serie_a.png',
        'ligue 1': 'ligue1.png',
        'bundesliga': 'bundesliga.png',
        'premier league': 'premier_league.png'
    };

    let hasAnyMatch = false;

    const html = leagues.map(league => {

        const activeFixtures = league.fixtures || [];

        if (activeFixtures.length === 0) {
            return '';
        }

        hasAnyMatch = true;

        const logo =
            leagueLogos[league.name.toLowerCase()] || 'default.png';

        return `
            <div class="league-section mb-5">

                <div class="league-header">
                    <img
                        src="/assets/leagues/${logo}"
                        class="league-logo-sm"
                    >

                    <span class="section-title-cyber">
                        ${league.name}
                    </span>
                </div>

                ${activeFixtures.map(match => {

            const isFinished = [
                'ft',
                'finished',
                'aet',
                'pen'
            ].includes(match.status?.toLowerCase());

            const odds = match.odds || [];

            const o1Obj = odds.find(o =>
                o.market_name === 'Wynik'
                && o.outcome_name === '1'
            );

            const oXObj = odds.find(o =>
                o.market_name === 'Wynik'
                && o.outcome_name === 'X'
            );

            const o2Obj = odds.find(o =>
                o.market_name === 'Wynik'
                && o.outcome_name === '2'
            );

            const o1 = o1Obj ? Number(o1Obj.value).toFixed(2) : '';
            const oX = oXObj ? Number(oXObj.value).toFixed(2) : '';
            const o2 = o2Obj ? Number(o2Obj.value).toFixed(2) : '';

            const parsedDate = parseMatchDate(match.match_date);

            const time = parsedDate.time;

            const day = parsedDate.day;

            if (isFinished) {
                return renderFinishedMatch(match);
            }

            return `
                        <div
                            class="match-strip-item"
                            id="match-${match.id}"
                        >

                            <div class="d-flex align-items-center p-3">

                                <div class="match-time-info">
                                    <span class="m-hour">${time}</span>
                                    <span class="m-date">${day}</span>
                                </div>

                                <div class="match-teams-part">

                                    <div class="team-box">
                                        <img
                                            src="/${match.home_team.logo_path}"
                                            alt="${match.home_team.name}"
                                            class="team-logo"
                                        >

                                        <span class="team-name d-none d-md-block">
                                            ${match.home_team.name}
                                        </span>
                                    </div>

                                    <span class="vs-label">-</span>

                                    <div class="team-box">

                                        <span class="team-name d-none d-md-block">
                                            ${match.away_team.name}
                                        </span>

                                        <img
                                            src="/${match.away_team.logo_path}"
                                            alt="${match.away_team.name}"
                                            class="team-logo"
                                        >
                                    </div>

                                </div>

                                <div class="match-odds-part ms-auto d-flex align-items-center">

                                    <div class="d-flex">

                                        <button
                                            class="btn-odd ${!o1Obj ? 'disabled' : ''}"
                                            data-league-name="${league.name}"
                                            data-match-id="${match.id}"
                                            data-odd-id="${o1Obj?.id || ''}"
                                            data-home-team="${match.home_team.name}"
                                            data-away-team="${match.away_team.name}"
                                            data-match-date="${match.match_date_local}"
                                            data-market-name="Wynik"
                                            data-outcome-name="1"
                                            data-odd-value="${o1}"
                                        >
                                            <span class="odd-label-xs">1</span>
                                            <span class="odd-val">${o1}</span>
                                        </button>

                                        <button
                                            class="btn-odd ${!oXObj ? 'disabled' : ''}"
                                            data-league-name="${league.name}"
                                            data-match-id="${match.id}"
                                            data-odd-id="${oXObj?.id || ''}"
                                            data-home-team="${match.home_team.name}"
                                            data-away-team="${match.away_team.name}"
                                            data-match-date="${match.match_date_local}"
                                            data-market-name="Wynik"
                                            data-outcome-name="X"
                                            data-odd-value="${oX}"
                                        >
                                            <span class="odd-label-xs">X</span>
                                            <span class="odd-val">${oX}</span>
                                        </button>

                                        <button
                                            class="btn-odd ${!o2Obj ? 'disabled' : ''}"
                                            data-league-name="${league.name}"
                                            data-match-id="${match.id}"
                                            data-odd-id="${o2Obj?.id || ''}"
                                            data-home-team="${match.home_team.name}"
                                            data-away-team="${match.away_team.name}"
                                            data-match-date="${match.match_date_local}"
                                            data-market-name="Wynik"
                                            data-outcome-name="2"
                                            data-odd-value="${o2}"
                                        >
                                            <span class="odd-label-xs">2</span>
                                            <span class="odd-val">${o2}</span>
                                        </button>

                                    </div>

                                    <button
                                        class="btn-more-odds ms-2"
                                        onclick="toggleMarkets(${match.id})"
                                    >
                                        <i
                                            class="bi bi-chevron-down"
                                            id="icon-${match.id}"
                                        ></i>
                                    </button>

                                </div>

                            </div>

                            <div
                                class="match-details-expand d-none"
                                id="markets-${match.id}"
                            >
                                <div class="p-3 border-top border-secondary">
                                    <div id="markets-content-${match.id}"></div>
                                </div>
                            </div>

                        </div>
                    `;

        }).join('')}

            </div>
        `;

    }).join('');

    container.innerHTML = hasAnyMatch
        ? html
        : '<div class="text-center text-secondary py-5">Brak meczów dostępnych do obstawiania.</div>';
}
function scrollDays(dir) {
    const container = document.getElementById('daysContainer');
    container.scrollBy({ left: dir === 'left' ? -300 : 300, behavior: 'smooth' });
}

function toggleMarkets(matchId) {
    const container = document.getElementById(`markets-${matchId}`);
    const icon = document.getElementById(`icon-${matchId}`);
    if (container.classList.contains('d-none')) {
        container.classList.remove('d-none');
        icon.className = 'bi bi-chevron-up';
        renderMarketsList(matchId);
    } else {
        container.classList.add('d-none');
        icon.className = 'bi bi-chevron-down';
    }
}

function renderMarketsList(matchId) {
    let match = null;
    loadedLeagues.forEach(l => {
        const found = l.fixtures.find(f => f.id == matchId);
        if (found) match = found;
    });
    if (!match) return;

    const content = document.getElementById(`markets-content-${matchId}`);
    const allOdds = match.odds;
    let html = '';

    marketConfig.forEach(config => {
        const exists = allOdds.some(o => o.market_name === config.original);
        if (exists) {
            const mIdClean = config.original.replace(/\s+/g, '');
            html += `
                <div class="market-accordion-wrapper">
                    <div class="market-header-item" onclick="toggleSingleMarket(${matchId}, '${mIdClean}', '${config.original}')">
                        <span class="m-name">${config.display}</span>
                        <i class="bi bi-plus-lg text-secondary" id="icon-m-${matchId}-${mIdClean}"></i>
                    </div>
                    <div class="market-content-pane d-none" id="pane-${matchId}-${mIdClean}"></div>
                </div>`;
        }
    });
    content.innerHTML = html || '<div class="text-secondary small">Brak dodatkowych rynków.</div>';
}

function toggleSingleMarket(matchId, mIdClean, mName) {
    const pane = document.getElementById(`pane-${matchId}-${mIdClean}`);
    const icon = document.getElementById(`icon-m-${matchId}-${mIdClean}`);

    if (pane.classList.contains('d-none')) {
        let match = null;
        let leagueName = '';
        loadedLeagues.forEach(l => {
            const found = l.fixtures.find(f => f.id == matchId);
            if (found) { match = found; leagueName = l.name; }
        });
        if (!match) return;

        const mOdds = match.odds.filter(o => o.market_name === mName);
        const hasTeams = mOdds.some(o => o.team_id !== null);
        let html = '';

        if (hasTeams) {
            const homeId = match.home_team_id || match.home_team.id;
            const awayId = match.away_team_id || match.away_team.id;
            html += `
                <div class="team-switcher-mini mb-3">
                    <button class="btn-switch active" onclick="switchTeam(this, ${matchId}, '${mName}', ${homeId})">${match.home_team.name}</button>
                    <button class="btn-switch" onclick="switchTeam(this, ${matchId}, '${mName}', ${awayId})">${match.away_team.name}</button>
                </div>`;
        }

        const defaultTeamId = hasTeams ? (match.home_team_id || match.home_team.id) : null;
        html += `<div id="table-${matchId}-${mIdClean}">${renderMarketContent(mOdds, defaultTeamId, mName, match, leagueName)}</div>`;

        pane.innerHTML = html;
        pane.classList.remove('d-none');
        icon.className = 'bi bi-dash-lg text-accent';
    } else {
        pane.classList.add('d-none');
        icon.className = 'bi bi-plus-lg text-secondary';
    }
}

function switchTeam(btn, matchId, mName, teamId) {
    const parent = btn.parentElement;
    parent.querySelectorAll('.btn-switch').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    let match = null;
    let leagueName = '';
    loadedLeagues.forEach(l => {
        const found = l.fixtures.find(f => f.id == matchId);
        if (found) { match = found; leagueName = l.name; }
    });
    if (!match) return;

    const mOdds = match.odds.filter(o => o.market_name === mName);
    const mIdClean = mName.replace(/\s+/g, '');
    const tableContainer = document.getElementById(`table-${matchId}-${mIdClean}`);
    if (tableContainer) tableContainer.innerHTML = renderMarketContent(mOdds, teamId, mName, match, leagueName);
}

function renderMarketContent(odds, teamId, mName, match, leagueName) {
    let filtered = teamId ? odds.filter(o => o.team_id == teamId) : odds;
    let teamObj = null;
    if (teamId) {
        teamObj = (teamId == match.home_team_id || teamId == match.home_team.id) ? match.home_team : match.away_team;
    }

    if (mName === 'Podwójna szansa') {
        const types = ['1X', '12', 'X2'];
        return `<div class="row g-2 px-2 pb-2">
            ${types.map(t => {
            const o = filtered.find(opt => opt.outcome_name.trim() === t);
            return `<div class="col-4">
                    <button class="btn-odd btn-odd-style w-100 d-flex flex-column py-2"
                        data-match-id="${match.id}"
                        data-odd-id="${o?.id || ''}"
                        data-league-name="${leagueName}"
                        data-home-team="${match.home_team.name}"
                        data-away-team="${match.away_team.name}"
                        data-match-date="${match.match_date_local}"
                        data-market-name="${mName}"
                        data-outcome-name="${t}"
                        data-odd-value="${o?.value || 0.0}">
                        <span class="x-small text-secondary fw-bold">${t}</span>
                        <span class="text-accent fw-bold">${o ? Number(o.value).toFixed(2) : '---'}</span>
                    </button>
                </div>`;
        }).join('')}
        </div>`;
    }

    if (mName === 'Obie drużyny strzelą') {
        const outcomes = ['Tak', 'Nie'];
        return `<div class="row g-2 px-2 pb-2">
            ${outcomes.map(out => {
            const o = filtered.find(opt => opt.outcome_name.toLowerCase().trim() === out.toLowerCase());
            return `<div class="col-6">
                    <button class="btn-odd btn-odd-style w-100 py-2 d-flex justify-content-between px-3 align-items-center"
                        data-match-id="${match.id}"
                        data-odd-id="${o?.id || ''}"
                        data-league-name="${leagueName}"
                        data-home-team="${match.home_team.name}"
                        data-away-team="${match.away_team.name}"
                        data-match-date="${match.match_date_local}"
                        data-market-name="${mName}"
                        data-outcome-name="${out}"
                        data-odd-value="${o?.value || 0.0}">
                        <span class="x-small text-secondary fw-bold">${out.toUpperCase()}</span>
                        <span class="text-accent fw-bold">${o ? Number(o.value).toFixed(2) : '---'}</span>
                    </button>
                </div>`;
        }).join('')}
        </div>`;
    }

    const specifiers = [...new Set(filtered.map(o => o.specifier))].sort((a, b) => parseFloat(a) - parseFloat(b));
    if (filtered.length === 0) return '<div class="text-secondary small px-3">Brak kursów.</div>';

    return `
        <div class="row g-2 text-secondary x-small fw-bold mb-1 px-2">
            <div class="col-4">LINIA</div>
            <div class="col-4 text-center">POWYŻEJ</div>
            <div class="col-4 text-center">PONIŻEJ</div>
        </div>
        ${specifiers.map(spec => {
        const over = filtered.find(o => o.specifier == spec && o.outcome_name.toLowerCase().includes('powyżej'));
        const under = filtered.find(o => o.specifier == spec && o.outcome_name.toLowerCase().includes('poniżej'));
        const displaySpec = parseFloat(spec).toString();

        let finalMarketName = mName;
        if (teamObj) {
            finalMarketName = `${teamObj.name} - ${mName.replace(' drużyny', '').toLowerCase()}`;
        }

        return `<div class="row g-2 ou-row align-items-center px-2 mb-1">
                <div class="col-4 fw-bold text-white ps-2">${displaySpec}</div>
                <div class="col-4">
                    <button class="btn-odd btn-odd-style w-100"
                        data-match-id="${match.id}"
                        data-odd-id="${over?.id || ''}"
                        data-league-name="${leagueName}"
                        data-home-team="${match.home_team.name}"
                        data-away-team="${match.away_team.name}"
                        data-match-date="${match.match_date_local}"
                        data-market-name="${finalMarketName}"
                        data-outcome-name="Powyżej ${displaySpec}"
                        data-odd-value="${over?.value || 0.0}">
                        <span class="text-accent">${over?.value || '---'}</span>
                    </button>
                </div>
                <div class="col-4">
                    <button class="btn-odd btn-odd-style w-100"
                        data-match-id="${match.id}"
                        data-odd-id="${under?.id || ''}"
                        data-league-name="${leagueName}"
                        data-home-team="${match.home_team.name}"
                        data-away-team="${match.away_team.name}"
                        data-match-date="${match.match_date_local}"
                        data-market-name="${finalMarketName}"
                        data-outcome-name="Poniżej ${displaySpec}"
                        data-odd-value="${under?.value || 0.0}">
                        <span class="text-accent">${under?.value || '---'}</span>
                    </button>
                </div>
            </div>`;
    }).join('')}`;
}

// Obsługa kotwic URL
document.addEventListener('DOMContentLoaded', function () {
    const hash = window.location.hash;
    if (hash && hash.startsWith('#match-')) {
        const matchId = hash.replace('#match-', '');
        setTimeout(() => {
            const element = document.getElementById('match-' + matchId);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                if (typeof toggleMarkets === 'function') toggleMarkets(matchId);
            }
        }, 500);
    }
});

//window.addOddToBetslip = addOddToBetslip;


