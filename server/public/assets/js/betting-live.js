(() => {
    const POLL_INTERVAL_MS = 3000;
    const ANIM_DURATION_MS = 2000;
    const API_INDEX = '/api/paris/matchs';
    const API_MATCH = (id) => `/api/paris/matchs/${id}`;

    const formatOdds = (value) => {
        const num = Number.parseFloat(value);
        return Number.isFinite(num) ? num.toFixed(2) : '';
    };

    const getMatchIds = () => {
        const ids = new Set();
        document.querySelectorAll('[data-bet-match-id]').forEach((el) => {
            const id = el.dataset.betMatchId;
            if (id) ids.add(id);
        });
        document.querySelectorAll('.odds[data-match-id]').forEach((el) => {
            const id = el.dataset.matchId;
            if (id) ids.add(id);
        });
        return Array.from(ids);
    };

    const updateOption = (container, option) => {
        const oddsSpan = container.querySelector(`[data-option-id="${option.id}"]`);
        if (!oddsSpan) return;

        const oldValue = Number.parseFloat(oddsSpan.textContent.replace(',', '.'));
        const newValue = Number.parseFloat(option.current_odds);

        if (Number.isFinite(newValue)) {
            oddsSpan.textContent = formatOdds(newValue);
        }

        if (window.BettingUI?.applyOddsChangeEffect) {
            window.BettingUI.applyOddsChangeEffect(oddsSpan, oldValue, newValue);
        } else {
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
            }, ANIM_DURATION_MS);
        }

        const poolSpan = container.querySelector(`[data-option-pool="${option.id}"]`);
        if (poolSpan) {
            poolSpan.textContent = Number(option.pool_total).toLocaleString('fr-FR');
        }
    };

    const updateFromPayload = (matchId, options) => {
        const containers = document.querySelectorAll(`[data-bet-match-id="${matchId}"]`);
        if (containers.length === 0) return;
        containers.forEach((container) => {
            options.forEach((option) => updateOption(container, option));
        });
    };

    const fetchJson = async (url) => {
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });
        if (!response.ok) return null;
        return response.json();
    };

    const pollIndex = async () => {
        const payload = await fetchJson(API_INDEX);
        if (!payload?.data) return;
        payload.data.forEach((match) => {
            if (!match?.id || !match?.options) return;
            updateFromPayload(String(match.id), match.options);
        });
    };

    const pollMatch = async (matchId) => {
        const payload = await fetchJson(API_MATCH(matchId));
        if (!payload?.data?.options) return;
        updateFromPayload(String(matchId), payload.data.options);
    };

    const startPolling = (matchIds) => {
        if (matchIds.length === 0) return;
        const hasSingle = matchIds.length === 1;

        const run = () => {
            if (hasSingle) {
                pollMatch(matchIds[0]);
            } else {
                pollIndex();
            }
        };

        run();
        // window.setInterval(run, POLL_INTERVAL_MS);
    };

    const startEcho = (matchIds) => {
        const echo = window.Echo;
        if (!echo) return false;

        matchIds.forEach((matchId) => {
            echo.channel(`bet.match.${matchId}`).listen('BetOddsUpdated', (event) => {
                if (!event?.options) return;
                updateFromPayload(String(matchId), event.options);
            });
        });

        return true;
    };

    const init = () => {
        const matchIds = getMatchIds();
        if (matchIds.length === 0) return;

        const echoEnabled = startEcho(matchIds);
        if (!echoEnabled) {
            startPolling(matchIds);
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
