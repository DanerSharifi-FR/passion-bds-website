(() => {
    const STATUS_CLASSES = {
        OPEN: 'bet-status-open',
        UPCOMING: 'bet-status-upcoming',
        CLOSED: 'bet-status-closed',
        FINISHED: 'bet-status-finished',
        CANCELLED: 'bet-status-closed',
    };

    const formatDateFR = (isoString) => {
        if (!isoString) return '—';
        const date = new Date(isoString);
        if (Number.isNaN(date.getTime())) return '—';
        const formatter = new Intl.DateTimeFormat('fr-FR', {
            weekday: 'short',
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
        return formatter.format(date);
    };

    const fillHumanDates = () => {
        document.querySelectorAll('.human-dt').forEach((el) => {
            const dt = el.dataset.dt;
            el.textContent = formatDateFR(dt);
        });
    };

    const updateStatusBadges = () => {
        const now = Date.now();
        document.querySelectorAll('.bet-status-badge').forEach((badge) => {
            const openAt = Date.parse(badge.dataset.betOpenAt || '');
            const endAt = Date.parse(badge.dataset.matchEndAt || '');
            const serverStatus = badge.dataset.serverStatus;

            let label = 'FERMÉ';
            let statusKey = 'CLOSED';

            if (serverStatus === 'CANCELLED') {
                label = 'ANNULÉ';
                statusKey = 'CANCELLED';
            } else if (serverStatus === 'FINISHED') {
                label = 'TERMINÉ';
                statusKey = 'FINISHED';
            } else if (serverStatus !== 'OPEN') {
                label = 'FERMÉ';
                statusKey = 'CLOSED';
            } else {
                if (!Number.isNaN(openAt) && now < openAt) {
                    label = 'À venir';
                    statusKey = 'UPCOMING';
                } else if (!Number.isNaN(endAt) && now >= endAt) {
                    label = 'TERMINÉ';
                    statusKey = 'FINISHED';
                } else {
                    label = 'OUVERT';
                    statusKey = 'OPEN';
                }
            }

            badge.textContent = label;
            badge.classList.remove(
                STATUS_CLASSES.OPEN,
                STATUS_CLASSES.UPCOMING,
                STATUS_CLASSES.CLOSED,
                STATUS_CLASSES.FINISHED,
                STATUS_CLASSES.CANCELLED
            );
            const nextClass = STATUS_CLASSES[statusKey] || STATUS_CLASSES.CLOSED;
            badge.classList.add(nextClass);
        });
    };

    const formatCountdown = (ms) => {
        if (ms <= 0) return 'Fenêtre de modification terminée';
        const totalSeconds = Math.floor(ms / 1000);
        const minutes = Math.floor(totalSeconds / 60);
        const seconds = totalSeconds % 60;
        return `Modifiable encore ${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    };

    const updateCountdowns = () => {
        const now = Date.now();
        document.querySelectorAll('.bet-edit-countdown').forEach((el) => {
            const editableUntil = Date.parse(el.dataset.editableUntil || '');
            if (Number.isNaN(editableUntil)) {
                el.textContent = 'Fenêtre de modification terminée';
                return;
            }
            const remaining = editableUntil - now;
            el.textContent = formatCountdown(remaining);

            if (remaining <= 0) {
                const container = el.closest('tr') || el.closest('.bet-actions') || el.closest('.bet-container') || el.parentElement;
                if (container) {
                    container.querySelectorAll('[data-action="edit"], [data-action="delete"]').forEach((btn) => {
                        btn.setAttribute('disabled', 'disabled');
                        btn.classList.add('opacity-50', 'cursor-not-allowed');
                    });
                }
            }
        });
    };

    const applyOddsChangeEffect = (el, oldValue, newValue) => {
        el.classList.remove('odds-up', 'odds-down');
        if (!Number.isFinite(oldValue) || !Number.isFinite(newValue)) {
            return;
        }
        if (newValue > oldValue) {
            el.classList.add('odds-up');
        } else if (newValue < oldValue) {
            el.classList.add('odds-down');
        }
        window.setTimeout(() => {
            el.classList.remove('odds-up', 'odds-down');
        }, 2000);
    };

    const setupModal = () => {
        const modal = document.getElementById('confirm-modal');
        if (!modal) return;

        const titleEl = modal.querySelector('#confirm-title');
        const textEl = modal.querySelector('#confirm-text');
        const checkWrap = modal.querySelector('#confirm-check-wrap');
        const checkInput = modal.querySelector('#confirm-check');
        const checkLabel = modal.querySelector('#confirm-check-label');
        const cancelBtn = modal.querySelector('[data-cancel]');
        const confirmBtn = modal.querySelector('[data-confirm]');
        const backdrop = modal.querySelector('[data-close]');
        let pendingForm = null;
        let requireCheck = false;

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('active');
            if (checkInput) {
                checkInput.checked = false;
            }
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.classList.remove('opacity-60', 'cursor-not-allowed');
            }
            if (checkWrap) {
                checkWrap.classList.add('hidden');
            }
            pendingForm = null;
        };

        const openModal = (form, title, text, needsCheck, checkText) => {
            pendingForm = form;
            requireCheck = needsCheck;
            titleEl.textContent = title || 'Confirmer';
            textEl.textContent = text || 'Cette action est irréversible.';
            modal.classList.remove('hidden');
            requestAnimationFrame(() => modal.classList.add('active'));
            if (checkWrap && checkInput && checkLabel) {
                if (needsCheck) {
                    checkWrap.classList.remove('hidden');
                    checkInput.checked = false;
                    checkLabel.textContent = checkText || 'Je confirme cette action.';
                    if (confirmBtn) {
                        confirmBtn.disabled = true;
                        confirmBtn.classList.add('opacity-60', 'cursor-not-allowed');
                    }
                } else {
                    checkWrap.classList.add('hidden');
                    if (confirmBtn) {
                        confirmBtn.disabled = false;
                        confirmBtn.classList.remove('opacity-60', 'cursor-not-allowed');
                    }
                }
            }
            confirmBtn.focus();
        };

        document.body.addEventListener('submit', (event) => {
            const target = event.target;
            if (!(target instanceof HTMLFormElement)) return;
            const trigger = target.querySelector('[data-confirm]');
            if (!trigger) return;
            event.preventDefault();
            openModal(
                target,
                trigger.dataset.confirmTitle,
                trigger.dataset.confirmText,
                trigger.dataset.confirmRequireCheck === '1',
                trigger.dataset.confirmCheckLabel
            );
        });

        if (checkInput && confirmBtn) {
            checkInput.addEventListener('change', () => {
                if (!requireCheck) return;
                confirmBtn.disabled = !checkInput.checked;
                confirmBtn.classList.toggle('opacity-60', !checkInput.checked);
                confirmBtn.classList.toggle('cursor-not-allowed', !checkInput.checked);
            });
        }

        cancelBtn?.addEventListener('click', closeModal);
        backdrop?.addEventListener('click', closeModal);
        confirmBtn?.addEventListener('click', () => {
            if (pendingForm) {
                pendingForm.submit();
            }
        });

        window.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });
    };

    const init = () => {
        fillHumanDates();
        updateStatusBadges();
        updateCountdowns();
        setupModal();

        // window.setInterval(updateStatusBadges, 5000);
        // window.setInterval(updateCountdowns, 1000);
    };

    window.BettingUI = {
        applyOddsChangeEffect,
        formatDateFR,
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
