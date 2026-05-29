document.addEventListener('DOMContentLoaded', () => {

    initCommunityDays();
    centerActiveDay();
});


/*
|--------------------------------------------------------------------------
| DAY SCROLL
|--------------------------------------------------------------------------
*/

function scrollDays(direction)
{
    const container = document.getElementById('daysContainer');

    if (!container) return;

    const amount = 220;

    container.scrollBy({
        left: direction === 'left' ? -amount : amount,
        behavior: 'smooth'
    });
}


/*
|--------------------------------------------------------------------------
| CENTER ACTIVE DAY
|--------------------------------------------------------------------------
*/

function centerActiveDay()
{
    const container = document.getElementById('daysContainer');

    if (!container) return;

    const activeBtn = container.querySelector('.day-btn.active');

    if (!activeBtn) return;

    const containerWidth = container.offsetWidth;

    const scrollPosition =
        activeBtn.offsetLeft
        - (containerWidth / 2)
        + (activeBtn.offsetWidth / 2);

    container.scrollTo({
        left: scrollPosition,
        behavior: 'smooth'
    });
}


/*
|--------------------------------------------------------------------------
| COMMUNITY DAY BUTTONS
|--------------------------------------------------------------------------
*/

function initCommunityDays()
{
    const buttons = document.querySelectorAll('.day-btn');

    buttons.forEach(btn => {

        btn.addEventListener('click', () => {

            const date = btn.dataset.date;

            const url = new URL(window.location.href);

            url.searchParams.set('date', date);

            window.location.href = url.toString();
        });
    });
}


/*
|--------------------------------------------------------------------------
| SORTING
|--------------------------------------------------------------------------
*/

function sortTips(sortType)
{
    const url = new URL(window.location.href);

    url.searchParams.set('sort', sortType);

    window.location.href = url.toString();
}


/*
|--------------------------------------------------------------------------
| COPY BET TO BETSLIP
|--------------------------------------------------------------------------
*/

function copyBetToSlip(items)
{
    if (!items || !items.length) return;

    // Pobranie aktualnego slipsa
    let currentSlip = JSON.parse(
        localStorage.getItem('user_bet_slip')
    ) || [];

    items.forEach(item => {

        // Sprawdzenie czy typ już istnieje
        const alreadyExists = currentSlip.some(
            slipItem => String(slipItem.oddId) === String(item.odd_id)
        );

        if (!alreadyExists) {

            currentSlip.push({

                matchId: item.match_id,
                oddId: item.odd_id,

                leagueName: item.league_name,

                homeTeam: item.home_team,
                awayTeam: item.away_team,

                matchDate: item.match_date,

                marketName: item.market_name,
                outcomeName: item.outcome_name,

                oddValue: parseFloat(item.odd_value)
            });
        }
    });

    // Zapis
    localStorage.setItem(
        'user_bet_slip',
        JSON.stringify(currentSlip)
    );

    // Aktualizacja globalnego stanu
    window.betSlip = currentSlip;

    // Re-render slipsa
    if (typeof window.renderBetSlip === 'function') {
        window.renderBetSlip();
    }

    // Odświeżenie aktywnych przycisków
    if (typeof window.restoreButtonStates === 'function') {
        window.restoreButtonStates();
    }

    console.log('Kupon skopiowany:', currentSlip);
}

/*
|--------------------------------------------------------------------------
| RANKING TABS
|--------------------------------------------------------------------------
*/

document.querySelectorAll('.ranking-tab').forEach(tab => {

    tab.addEventListener('click', () => {

        document
            .querySelectorAll('.ranking-tab')
            .forEach(t => t.classList.remove('active'));

        tab.classList.add('active');

        const target = tab.dataset.ranking;

        document
            .querySelectorAll('.ranking-panel')
            .forEach(panel => {
                panel.classList.remove('active');
                panel.style.display = 'none';
            });

        const activePanel = document.getElementById(
            `ranking-${target}`
        );

        if (activePanel) {
            activePanel.style.display = 'block';
            activePanel.classList.add('active');
        }
    });
});

/*
|--------------------------------------------------------------------------
| SHOW MY RANK
|--------------------------------------------------------------------------
*/

function scrollToMyRank()
{
    const currentUser = document.querySelector(
        '.ranking-user-card.current-user'
    );

    if (!currentUser) return;

    currentUser.scrollIntoView({

        behavior: 'smooth',
        block: 'center'
    });

    currentUser.classList.add('highlight-rank');

    setTimeout(() => {

        currentUser.classList.remove(
            'highlight-rank'
        );

    }, 2500);
}

const analysisCheckbox = document.getElementById(
    'analysisOnly'
);

if (analysisCheckbox) {

    analysisCheckbox.addEventListener('change', () => {

        const url = new URL(window.location.href);

        if (analysisCheckbox.checked) {

            url.searchParams.set(
                'analysis_only',
                '1'
            );

        } else {

            url.searchParams.delete(
                'analysis_only'
            );
        }

        window.location.href = url.toString();
    });
}

function toggleAnalysis(button)
{
    const wrapper = button.closest(
        '.analysis-wrapper'
    );

    const content = wrapper.querySelector(
        '.analysis-content'
    );

    wrapper.classList.toggle('active');

    if (wrapper.classList.contains('active')) {

        content.style.maxHeight =
            content.scrollHeight + 'px';

    } else {

        content.style.maxHeight = null;
    }
}

document
    .getElementById('analysisOnlyCheckbox')
    ?.addEventListener('change', function () {

        const url = new URL(window.location.href);

        if (this.checked) {

            url.searchParams.set(
                'analysis_only',
                '1'
            );

        } else {

            url.searchParams.delete(
                'analysis_only'
            );
        }

        window.location.href = url.toString();
    });

    document
    .querySelectorAll('.community-analysis-toggle')
    .forEach(button => {

        button.addEventListener('click', () => {

            const wrapper = button.closest(
                '.community-analysis-wrapper'
            );

            wrapper.classList.toggle('open');
        });
    });
