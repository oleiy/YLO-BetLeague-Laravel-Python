/**
 * @desc Główny skrypt obsługujący koszyk zakładów (Bet Slip).
 * Zarządza stanem listy typów, interakcjami UI oraz zapisem w LocalStorage.
 */

// 1. INICJALIZACJA STANU
// Sprawdzamy, czy obiekt betSlip już istnieje, jeśli nie - ładujemy go z pamięci przeglądarki
if (typeof window.betSlip === 'undefined') {
    const savedSlip = localStorage.getItem('user_bet_slip');
    window.betSlip = savedSlip ? JSON.parse(savedSlip) : [];
}

/**
 * @desc Zapisuje aktualny stan tablicy betSlip do LocalStorage (trwałość danych po odświeżeniu).
 */
window.saveSlipToStorage = function () {
    localStorage.setItem('user_bet_slip', JSON.stringify(window.betSlip));
};

// 2. GLOBALNA OBSŁUGA KLIKNIĘĆ (Event Delegation)
if (typeof window.betSlipClickHandler === 'undefined') {
    window.betSlipClickHandler = true;
    document.addEventListener('click', (e) => {
        // Przechwytuje kliknięcia w przyciski kursów na Dashboardzie (.clickable-odd) oraz w ofercie (.btn-odd)
        const btn = e.target.closest('.btn-odd') || e.target.closest('.clickable-odd');

        // Ignorujemy przyciski "więcej kursów"
        if (btn && !btn.classList.contains('btn-more-odds')) {
            e.preventDefault();
            window.toggleBetSelection(btn);
        }
    });
}

/**
 * @desc Inicjalizacja widoku po załadowaniu strony.
 */
document.addEventListener('DOMContentLoaded', () => {
    window.renderBetSlip();
    window.restoreButtonStates();
});

/**
 * @desc Logika dodawania i usuwania selekcji z koszyka.
 * Obsługuje mechanizm podmiany kursów w obrębie tego samego rynku.
 * @params HTMLElement btn - Kliknięty element zawierający atrybuty data-*
 */
// 1. Definicja rynków i ich wzajemnych relacji
// 1. Rozszerzona definicja rynków i ich wzajemnych relacji
const CONFLICT_MATRIX = {
    'Wynik': {
        conflicts: ['Podwójna szansa'],
    },
    'Podwójna szansa': {
        conflicts: ['Wynik'],
    },
    'Liczba goli': {
        conflicts: [],
    },
    'Obie drużyny strzelą': {
        conflicts: [],
    },
    'Liczba goli drużyny': {
        conflicts: [],
    },
    'Rzuty rożne': {
        conflicts: [],
    },
    'Rzuty rożne drużyny': {
        conflicts: [],
    },
    'Celne strzały': {
        conflicts: [],
    },
    'Celne strzały drużyny': {
        conflicts: [],
    },
    'Liczba kartek': {
        conflicts: [],
    },
    'Liczba kartek drużyny': { // Dodane dla spójności
        conflicts: [],
    }
};

/**
 * @desc Sprawdza logiczne sprzeczności, w tym zaawansowane porównania linii bramkowych.
 */
window.checkLogicalConflicts = function (newSel, existingSlip) {
    const { matchId: nId, marketName: nM, outcomeName: nO } = newSel;

    // 1. Pomocnicze funkcje narzędziowe
    const parseVal = (str) => {
        const match = str.match(/(\d+(\.\d+)?)/);
        return match ? parseFloat(match[1]) : null;
    };

    const getStatType = (name) => {
        const n = name.toLowerCase();
        if (n.includes('goli')) return 'goals';
        if (n.includes('rożne')) return 'corners';
        if (n.includes('kartki')) return 'cards';
        if (n.includes('strzały')) return 'shots';
        return null;
    };

    const isTeamMarket = (mName) => {
        return mName.toLowerCase().includes('drużyny') ||
            mName.includes(newSel.homeTeam) ||
            mName.includes(newSel.awayTeam);
    };

    // --- 2. WALIDACJA SUMARYCZNA (Gole, Rogi, Kartki, Strzały) ---
    const currentStatType = getStatType(nM);
    if (currentStatType) {
        // Symulujemy stan koszyka po dodaniu nowego typu
        const allStatsInMatch = existingSlip
            .filter(item => String(item.matchId) === String(nId) && getStatType(item.marketName) === currentStatType)
            .map(item => ({ marketName: item.marketName, outcomeName: item.outcomeName, val: parseVal(item.outcomeName) }));

        allStatsInMatch.push({ marketName: nM, outcomeName: nO, val: parseVal(nO) });

        let matchMax = Infinity; // Najniższe znalezione "Poniżej" dla meczu
        let teamMinSum = 0;      // Suma "Powyżej" dla obu drużyn

        // Pomocnicze do sumowania konkretnych stron
        let homeMin = 0;
        let awayMin = 0;

        allStatsInMatch.forEach(s => {
            const isUnder = s.outcomeName.includes('Poniżej');
            const isOver = s.outcomeName.includes('Powyżej');
            const team = isTeamMarket(s.marketName);

            if (!team) {
                if (isUnder) matchMax = Math.min(matchMax, s.val);
            } else {
                if (isOver) {
                    // Sprawdzamy czy to gospodarz czy gość (na podstawie nazw z nowego selekcji)
                    if (s.marketName.includes(newSel.homeTeam)) homeMin = Math.max(homeMin, s.val);
                    else if (s.marketName.includes(newSel.awayTeam)) awayMin = Math.max(awayMin, s.val);
                    else teamMinSum += s.val; // Jeśli nie da się rozróżnić, dodajemy do ogólnej sumy drużynowej
                }
            }
        });

        const totalMinRequired = homeMin + awayMin + teamMinSum;

        if (totalMinRequired >= matchMax) {
            return `Sprzeczność sumaryczna: suma typów drużynowych (${totalMinRequired}) nie może być większa lub równa limitowi meczu (${matchMax}).`;
        }
    }

    // --- 3. TWOJE DOTYCHCZASOWE WALIDACJE (Pętla po istniejących) ---
    for (let item of existingSlip) {
        if (String(item.matchId) !== String(nId)) continue;

        const oM = item.marketName;
        const oO = item.outcomeName;
        const nVal = parseVal(nO);
        const oVal = parseVal(oO);

        // A. BTTS "TAK" vs KAŻDE "PONIŻEJ 0.5/1.5"
        const isBttsTak = (m, o) => m.includes('Obie drużyny strzelą') && o.includes('Tak');
        const isLowUnder = (o) => o.includes('Poniżej') && parseVal(o) <= 1.5;

        if ((isBttsTak(nM, nO) && isLowUnder(oO)) || (isBttsTak(oM, oO) && isLowUnder(nO))) {
            return "BTTS 'Tak' wyklucza wynik poniżej 1.5 gola.";
        }

        // B. PROSTE PORÓWNANIE LINII (np. Poniżej 2.5 vs Powyżej 2.5 na tym samym rynku)
        if (getStatType(nM) === getStatType(oM)) {
            if (nO.includes('Powyżej') && oO.includes('Poniżej') && nVal >= oVal) return `Sprzeczność: ${nM} (${nO}) vs ${oM} (${oO})`;
            if (nO.includes('Poniżej') && oO.includes('Powyżej') && nVal <= oVal) return `Sprzeczność: ${nM} (${nO}) vs ${oM} (${oO})`;
        }

        // C. WYNIK vs LICZBA GOLI DRUŻYNY
        if (nM === 'Wynik' && oM.toLowerCase().includes('liczba goli')) {
            if (nO === '1' && oM.includes(newSel.homeTeam) && oO.includes('Poniżej 0.5')) return "Nie można postawić na wygraną drużyny i 0 goli tej drużyny.";
            if (nO === '2' && oM.includes(newSel.awayTeam) && oO.includes('Poniżej 0.5')) return "Nie można postawić na wygraną drużyny i 0 goli tej drużyny.";
        }
        // Rewers (jeśli najpierw dodano gole, potem wynik)
        if (oM === 'Wynik' && nM.toLowerCase().includes('liczba goli')) {
            if (oO === '1' && nM.includes(newSel.homeTeam) && nO.includes('Poniżej 0.5')) return "Wybrałeś wygraną gospodarza, a teraz dodajesz mu 0 goli.";
            if (oO === '2' && nM.includes(newSel.awayTeam) && nO.includes('Poniżej 0.5')) return "Wybrałeś wygraną gościa, a teraz dodajesz mu 0 goli.";
        }

        // D. WYNIK vs PODWÓJNA SZANSA
        const winX2 = (nM === 'Wynik' && nO === '1' && oM === 'Podwójna szansa' && oO === 'X2') ||
            (oM === 'Wynik' && oO === '1' && nM === 'Podwójna szansa' && nO === 'X2');
        const win1X = (nM === 'Wynik' && nO === '2' && oM === 'Podwójna szansa' && oO === '1X') ||
            (oM === 'Wynik' && oO === '2' && nM === 'Podwójna szansa' && nO === '1X');
        const win12 = (nM === 'Wynik' && nO === 'X' && oM === 'Podwójna szansa' && oO === '12') ||
            (oM === 'Wynik' && oO === 'X' && nM === 'Podwójna szansa' && nO === '12');

        if (winX2 || win1X || win12) return "Wybrane typy na wynik wzajemnie się wykluczają.";
    }

    return false;
};

/**
 * @desc Dodaje lub usuwa typ z koszyka.
 * Obsługuje logikę "jeden typ na rynek" wewnątrz jednego meczu.
 */
window.toggleBetSelection = function (btn) {
    const matchId = btn.getAttribute('data-match-id');
    const oddId = btn.getAttribute('data-odd-id'); // <--- POBIERAMY NOWE ID
    if (!oddId) {
        alert('Ten kurs jest chwilowo niedostępny.');
        return;
    }
    const marketName = btn.getAttribute('data-market-name');
    const outcomeName = btn.getAttribute('data-outcome-name');
    const oddValue = parseFloat(btn.getAttribute('data-odd-value'));

    // Sprawdzamy, czy ten konkretny kurs (oddId) już jest w koszyku
    const existingIndex = window.betSlip.findIndex(item => item.oddId === oddId);

    if (existingIndex > -1) {
        // Jeśli kliknięto w ten sam kurs - usuwamy go
        window.betSlip.splice(existingIndex, 1);
    } else {
        // Jeśli kliknięto w inny kurs, najpierw usuwamy inne typy z tego samego rynku w tym meczu
        // (W Bet Builderze nie możesz postawić jednocześnie Over 2.5 i Under 2.5 na raz)
        const conflictIndex = window.betSlip.findIndex(item =>
            item.matchId === matchId && item.marketName === marketName
        );
        if (conflictIndex > -1) {
            window.betSlip.splice(conflictIndex, 1);
        }

        // Dodajemy nowy typ z kompletem danych
        window.betSlip.push({
            matchId,
            oddId, // <--- ZAPISUJEMY DO STRUKTURY
            homeTeam: btn.getAttribute('data-home-team'),
            awayTeam: btn.getAttribute('data-away-team'),
            matchDate: btn.getAttribute('data-match-date'),
            marketName,
            outcomeName,
            oddValue
        });
    }

    window.saveSlipToStorage();
    window.renderBetSlip();
    window.restoreButtonStates();
};

// Funkcja obliczająca kursy z uwzględnieniem redukcji dla Bet Buildera
window.calculateTotalOdds = function () {
    let totalOdd = 1.0;
    const grouped = {};

    // Grupowanie po meczach
    window.betSlip.forEach(item => {
        if (!grouped[item.matchId]) grouped[item.matchId] = [];
        grouped[item.matchId].push(item);
    });

    for (const matchId in grouped) {
        const selections = grouped[matchId];
        // Sortujemy od najwyższego kursu
        selections.sort((a, b) => b.oddValue - a.oddValue);

        let matchMultiplier = 1.0;
        selections.forEach((sel, index) => {
            if (index === 0) {
                matchMultiplier *= sel.oddValue; // Pierwszy (najwyższy) kurs 100%
            } else {
                // Każdy kolejny kurs z tego samego meczu redukujemy (np. o 40%)
                // Bo zdarzenia są skorelowane
                matchMultiplier *= (1 + (sel.oddValue - 1) * 0.6);
            }
        });
        totalOdd *= matchMultiplier;
    }
    return totalOdd;
};

window.renderBetSlip = function () {
    const container = document.querySelector('.slip-items-container');
    if (!container) return;
    container.innerHTML = '';

    window.betSlip.forEach((item, index) => {
        const html = `
            <div class="slip-item card bg-darker border-secondary mb-2" data-index="${index}">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-start">
                        <span class="text-white small fw-bold">${item.homeTeam} - ${item.awayTeam}</span>
                        <button class="btn btn-sm text-danger p-0" onclick="window.removeBet(${index})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="text-secondary smaller">${item.marketName}</div>
                    <div class="d-flex justify-content-between">
                        <span class="text-accent fw-bold">${item.outcomeName}</span>
                        <span class="text-white fw-bold">${item.oddValue.toFixed(2)}</span>
                    </div>
                </div>
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
    });

    const totalOdd = window.calculateTotalOdds();
    const totalOddDisplay = document.getElementById('totalOdd');
    if (totalOddDisplay) totalOddDisplay.textContent = totalOdd.toFixed(2);

    // Aktualizacja potencjalnej wygranej
    const stakeInput = document.getElementById('stakeInput');
    const potentialWinDisplay = document.getElementById('potentialWin');
    if (stakeInput && potentialWinDisplay) {
        const stake = parseFloat(stakeInput.value) || 0;
        potentialWinDisplay.textContent = (stake * totalOdd).toFixed(2) + ' PKT';
    }
};

window.removeBet = function (index) {
    window.betSlip.splice(index, 1);
    window.saveSlipToStorage();
    window.renderBetSlip();
    window.restoreButtonStates();
};

/**
 * @desc Generuje dynamiczny kod HTML koszyka.
 * Grupuje zakłady według meczów, umożliwiając tworzenie "Bet Buildera" (wiele typów z 1 meczu).
 */
window.renderBetSlip = function () {
    const container = document.getElementById('bet-slip-items');
    const countBadge = document.getElementById('slipCountBadge');
    const footer = document.getElementById('slipFooter');

    if (!container) return;

    // Obsługa pustego koszyka
    if (window.betSlip.length === 0) {
        container.innerHTML = `<div class="empty-slip-msg text-center text-secondary py-5"><p>Wybierz kursy, aby stworzyć typy na wybrany mecz.</p></div>`;
        if (countBadge) countBadge.textContent = 'TYPY: 0';
        if (footer) footer.classList.add('d-none');
        return;
    }

    if (footer) footer.classList.remove('d-none');

    // GRUPOWANIE: Tworzymy obiekt, gdzie kluczem jest matchId, a wartością lista zakładów dla tego meczu
    const groupedBets = {};
    window.betSlip.forEach(item => {
        if (!groupedBets[item.matchId]) groupedBets[item.matchId] = [];
        groupedBets[item.matchId].push(item);
    });

    let html = '';
    // Pętla po zgrupowanych meczach
    for (const [matchId, items] of Object.entries(groupedBets)) {
        const isBetBuilder = items.length > 1;
        // Obliczanie kursu łącznego dla konkretnego meczu (iloczyn kursów)
        const totalMatchOdd = items.reduce((acc, i) => acc * i.oddValue, 1.0);
        const first = items[0];
        let displayTime = first.matchDate ? new Date(first.matchDate).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : "";

        // Generowanie struktury karty meczu
        html += `
                <div class="bet-card-main mb-3 mx-0 w-100" data-match-id="${matchId}">
                    <div class="bet-card-header">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="bet-league-name d-block">${first.leagueName}</span>
                                <div class="bet-teams">${first.homeTeam} - ${first.awayTeam}</div>
                            </div>
                            <button class="btn btn-sm text-danger p-0 border-0" onclick="window.removeMatchFromSlip('${matchId}')">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        <div class="bet-time mt-1">${displayTime}</div>
                    </div>

                    <div class="bet-card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="bet-builder-title text-uppercase" style="color: #58a6ff; font-size: 0.65rem; font-weight: 800;">
                                ${isBetBuilder ? 'BET BUILDER' : 'SOLO'}
                            </span>
                            <span class="bet-builder-odd fw-bold text-white">${totalMatchOdd.toFixed(2)}</span>
                        </div>

                        <ul class="bet-items-list">
                            ${items.map(item => `
                                <li class="bet-item">
                                    <div class="bet-item-line"></div>
                                    <span class="bullet"></span>
                                    <div class="bet-item-content">
                                        <div class="bet-item-details">
                                            <span class="bet-value">${item.outcomeName}</span>
                                            <span class="bet-label">${item.marketName}</span>
                                        </div>
                                        <button class="delete-item-btn" onclick="window.removeBetItem('${matchId}', '${item.marketName.replace(/'/g, "\\'")}', '${item.outcomeName.replace(/'/g, "\\'")}')">×</button>
                                    </div>
                                </li>
                            `).join('')}
                        </ul>
                    </div>

                    <button class="btn-slip-analysis" type="button" onclick="window.toggleAnalysisInput(this)">
                        <span>TWOJA ANALIZA</span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <div class="analysis-input-container d-none p-2">
                        <textarea
    class="form-control slip-analysis-input"
    data-match-id="${matchId}"
    rows="2"
    placeholder="Dodaj analizę..."
></textarea>
                    </div>

                    <div class="bet-card-finance">
                        <div class="finance-row">
                            <span class="finance-label">STAWKA</span>
                            <input type="number" class="slip-stake-input form-control" placeholder="0" min="1" oninput="window.updateTotals()">
                        </div>
                        <div class="finance-row mt-1">
                            <span class="finance-label">WYGRANA</span>
                            <span class="finance-val win">0.00 PKT</span>
                        </div>
                    </div>
                </div>`;
    }

    container.innerHTML = html;
    if (countBadge) countBadge.textContent = `TYPY: ${Object.keys(groupedBets).length}`;

    // Obsługa przycisku wysyłania (logowanie vs wysyłka)
    const submitBtn = document.getElementById('submitSlipBtn');
    if (submitBtn) {
        submitBtn.className = 'btn-cyber-primary';
        if (window.isAuthenticated) {
            submitBtn.textContent = 'POSTAW TYPY';
            submitBtn.onclick = window.submitBetSlip;
            submitBtn.style.filter = 'none';
        } else {
            submitBtn.textContent = 'ZALOGUJ SIĘ';
            submitBtn.style.filter = 'grayscale(0.5)';
            submitBtn.onclick = window.openLoginModal;
        }
    }

    window.updateTotals();
};

/**
 * @desc Usuwa konkretny wynik (wiersz) z karty meczu.
 */
window.removeBetItem = function (matchId, marketName, outcomeName) {
    window.betSlip = window.betSlip.filter(item =>
        !(String(item.matchId) === String(matchId) && item.marketName === marketName && item.outcomeName === outcomeName)
    );
    window.saveSlipToStorage();
    window.renderBetSlip();
    window.restoreButtonStates();
};

/**
 * @desc Usuwa wszystkie typy powiązane z danym meczem (całą kartę).
 */
window.removeMatchFromSlip = function (matchId) {
    window.betSlip = window.betSlip.filter(item => String(item.matchId) !== String(matchId));
    window.saveSlipToStorage();
    window.renderBetSlip();
    window.restoreButtonStates();
};

/**
 * @desc Rozwija/zwija pole tekstowe na analizę pod kartą meczu.
 */
window.toggleAnalysisInput = function (btn) {
    const container = btn.nextElementSibling;
    const icon = btn.querySelector('i');
    container.classList.toggle('d-none');
    icon.className = container.classList.contains('d-none') ? 'bi bi-chevron-down' : 'bi bi-chevron-up';
};

/**
 * @desc Oblicza wygrane dla każdej karty oraz aktualizuje łączny licznik punktów na dole koszyka.
 */
window.updateTotals = function () {
    let totalWin = 0;
    document.querySelectorAll('.bet-card-main').forEach(card => {
        const matchId = card.getAttribute('data-match-id');
        const matchItems = window.betSlip.filter(i => String(i.matchId) === String(matchId));
        const matchOdd = matchItems.reduce((acc, i) => acc * i.oddValue, 1.0);

        const stakeInput = card.querySelector('.slip-stake-input');
        const winDisplay = card.querySelector('.finance-val.win');

        const stake = parseFloat(stakeInput.value) || 0;
        const win = stake * matchOdd;

        if (winDisplay) winDisplay.textContent = win.toFixed(2) + ' PKT';
        totalWin += win;
    });

    const totalDisplay = document.getElementById('totalOdd');
    if (totalDisplay) totalDisplay.textContent = totalWin.toFixed(2) + ' PKT';
};

/**
 * @desc Synchronizuje stan wizualny przycisków w całym systemie.
 * Nadaje klasę .active tym przyciskom, które użytkownik ma obecnie w koszyku.
 */
window.restoreButtonStates = function () {
    document.querySelectorAll('.btn-odd, .clickable-odd').forEach(btn => btn.classList.remove('active'));

    window.betSlip.forEach(item => {
        const selector = `[data-match-id="${item.matchId}"][data-market-name="${item.marketName}"][data-outcome-name="${item.outcomeName}"]`;
        document.querySelectorAll(`.btn-odd${selector}, .clickable-odd${selector}`)
            .forEach(btn => btn.classList.add('active'));
    });
};

/**
 * @desc Finalizacja typów - wysyłka danych do kontrolera.
 */
window.placeBet = function () {
    console.log('Rozpoczynam wysyłkę typów...'); // Testowy log

    const stakeInput = document.querySelector('.slip-stake-input');
    const stake = parseFloat(stakeInput?.value || 0);

    if (window.betSlip.length === 0) {
        alert('Twoja lista typów jest pusta!');
        return;
    }

    if (stake < 1) {
        alert('Minimalna stawka to 1 PKT');
        return;
    }

    const grouped = {};

    window.betSlip.forEach(item => {

        if (!grouped[item.matchId]) {

            const analysisField = document.querySelector(
                `.slip-analysis-input[data-match-id="${item.matchId}"]`
            );

            const stakeField = document.querySelector(
                `.bet-card-main[data-match-id="${item.matchId}"] .slip-stake-input`
            );

            grouped[item.matchId] = {
                fixture_id: item.matchId,
                stake: parseFloat(stakeField?.value || 0),
                analysis: analysisField?.value || null,
                selections: []
            };
        }

        grouped[item.matchId].selections.push({
            odd_id: item.oddId,
            market_name: item.marketName,
            outcome_name: item.outcomeName,
            value: item.oddValue
        });
    });

    const data = {
        bets: Object.values(grouped)
    };

    fetch('/api/bets/store', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
        .then(async response => {

            const text = await response.text();

            let res;

            try {
                res = JSON.parse(text);
            } catch (e) {
                console.error('Backend nie zwrócił JSON:', text);
                throw new Error('Niepoprawna odpowiedź serwera');
            }

            if (!response.ok) {
                throw new Error(res.message || 'Błąd serwera');
            }

            return res;
        })
        .then(res => {
            if (res.success) {

                alert(res.message);

                window.betSlip = [];
                window.saveSlipToStorage();
                window.renderBetSlip();
                window.restoreButtonStates();

                if (res.new_balance) {
                    document.getElementById('user-balance').innerText = res.new_balance + ' PKT';
                }

            } else {
                alert('Błąd: ' + res.message);
            }
        })
        .catch(error => {
            console.error(error);
            alert(error.message);
        });
};

// Jeśli chcesz zachować nazwę submitBetSlip jako alias, możesz zrobić tak:
window.submitBetSlip = window.placeBet;
