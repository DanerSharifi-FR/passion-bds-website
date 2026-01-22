const initBettingLive = () => {
    if (window.__BETTING_LIVE_READY__) {
        return;
    }
    window.__BETTING_LIVE_READY__ = true;

    const echo = window.Echo;
    if (!echo) {
        console.warn('[betting-live] Echo indisponible, live désactivé.');
        return;
    }

    const formatOdds = (value) => Number.isFinite(value) ? value.toFixed(2) : '';

    const updateOption = (container, option) => {
        const oddsSpan = container.querySelector(`[data-option-id="${option.id}"]`);
        if (!oddsSpan) return;

        const oldValue = parseFloat(oddsSpan.textContent.replace(',', '.'));
        const newValue = parseFloat(option.current_odds);

        if (Number.isFinite(newValue)) {
            oddsSpan.textContent = formatOdds(newValue);
        }

        oddsSpan.classList.remove('odds-up', 'odds-down');
        if (Number.isFinite(oldValue) && Number.isFinite(newValue)) {
            if (newValue > oldValue) {
                oddsSpan.classList.add('odds-up');
            } else if (newValue < oldValue) {
                oddsSpan.classList.add('odds-down');
            }
        }

        window.setTimeout(() => {
            oddsSpan.classList.remove('odds-up', 'odds-down');
        }, 700);

        const poolSpan = container.querySelector(`[data-option-pool="${option.id}"]`);
        if (poolSpan) {
            poolSpan.textContent = Number(option.pool_total).toLocaleString('fr-FR');
        }
    };

    const matchContainers = document.querySelectorAll('[data-bet-match-id]');
    const matchIds = new Set();
    matchContainers.forEach((el) => {
        const matchId = el.dataset.betMatchId;
        if (matchId) matchIds.add(matchId);
    });

    matchIds.forEach((matchId) => {
        echo.channel(`bet.match.${matchId}`).listen('BetOddsUpdated', (event) => {
            if (!event?.options) return;

            document.querySelectorAll(`[data-bet-match-id="${matchId}"]`).forEach((container) => {
                event.options.forEach((option) => updateOption(container, option));
            });
        });
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBettingLive);
} else {
    initBettingLive();
}
