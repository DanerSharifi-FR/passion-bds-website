@extends('app')

@section('title', "P'AS'SION BDS - Allos")
@section('meta_description', "Espace Allos 2025-2026")

@section('content')
    <!-- Background Decors (Repositioned to avoid overlap) -->
    <div class="sticker text-8xl top-24 -left-4 animate-float lg:left-10 lg:top-32">🔒</div>
    <div class="sticker text-9xl bottom-32 -right-4 animate-float lg:right-10 lg:bottom-10"
         style="animation-delay: 2s;">🔑
    </div>

    <div class="w-full max-w-6xl mb-20">

        <!-- Header -->
        <div class="text-center mb-10">
            <h1 class="font-display font-black text-3xl md:text-5xl text-passion-red uppercase tracking-tighter leading-none">
                Catalogue <span class="text-passion-fire-orange">Allos</span>
            </h1>
            <p class="text-passion-pink-500 font-medium mt-2 text-sm md:text-base">
                Rappel : ne commandez pas un allo pour quelqu'un d'autre que vous-même.
            </p>
        </div>

        @guest
            <div class="bg-white border-2 border-passion-red shadow-[6px_6px_0_#000] p-5 text-center mb-8">
                <p class="text-passion-red font-semibold">
                    Connecte-toi pour pouvoir réserver.
                </p>
                <a href="{{ route('login') }}"
                   class="inline-block mt-4 bg-passion-red text-white font-display font-black uppercase px-6 py-3 shadow-[4px_4px_0_#000] hover:bg-passion-fire-orange hover:text-passion-red transition-colors">
                    Se connecter
                </a>
            </div>
        @endguest

        <div class="flex flex-wrap items-center justify-center gap-3 mb-8">
            <button data-filter="active"
                    class="allo-filter-btn bg-passion-red text-white font-display font-black uppercase px-5 py-2 shadow-[4px_4px_0_#000]">
                Allos actifs
            </button>
            <button data-filter="all"
                    class="allo-filter-btn bg-white text-passion-red border-2 border-passion-red font-display font-black uppercase px-5 py-2 shadow-[4px_4px_0_#000]">
                Tout voir
            </button>
        </div>

        <div id="allos-status" class="text-center text-sm text-passion-pink-500 font-semibold mb-6">
            Chargement des allos...
        </div>

        <div id="allos-catalog" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"></div>
    </div>
@endsection

@push('end_scripts')
    <script>
        const isAuthenticated = @json(auth()->check());
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const catalogElement = document.getElementById('allos-catalog');
        const statusElement = document.getElementById('allos-status');
        const filterButtons = document.querySelectorAll('.allo-filter-btn');

        let allosData = [];
        let activeFilter = 'active';
        const bookingStatusLabels = {
            PENDING: 'En attente',
            ACCEPTED: 'Acceptée',
            DONE: 'Réalisée',
            CANCELLED: 'Annulée',
        };

        function formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return new Intl.DateTimeFormat('fr-FR', {
                weekday: 'short',
                day: '2-digit',
                month: 'short',
                hour: '2-digit',
                minute: '2-digit',
            }).format(date);
        }

        function formatDateLabel(date) {
            return new Intl.DateTimeFormat('fr-FR', {
                weekday: 'short',
                day: '2-digit',
                month: 'short',
            }).format(date);
        }

        function formatTime(date) {
            return new Intl.DateTimeFormat('fr-FR', {
                hour: '2-digit',
                minute: '2-digit',
            }).format(date);
        }

        function formatNumericDate(date) {
            return new Intl.DateTimeFormat('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
            }).format(date);
        }

        function formatDateTimeRangeLabel(start, end) {
            return `du ${formatNumericDate(start)} ${formatTime(start)} au ${formatNumericDate(end)} ${formatTime(end)}`;
        }

        function formatTimeLabel(time) {
            return time.replace(':', 'h');
        }

        function formatDateRangeLabel(startDate, endDate) {
            if (startDate.toDateString() === endDate.toDateString()) {
                return `Le ${formatDateLabel(startDate)}`;
            }

            return `Du ${formatDateLabel(startDate)} au ${formatDateLabel(endDate)}`;
        }

        function formatWindowLabel(startAt, endAt, timeSlots = []) {
            if (Array.isArray(timeSlots) && timeSlots.length) {
                if (timeSlots.length === 1) {
                    const slot = timeSlots[0];
                    if (slot?.start_date && slot?.end_date && slot?.start_time && slot?.end_time) {
                        const start = new Date(`${slot.start_date}T${slot.start_time}`);
                        const end = new Date(`${slot.end_date}T${slot.end_time}`);
                        return `
                            <div class="flex items-center gap-2">
                                <span aria-hidden="true">📅</span>
                                <span>${formatDateTimeRangeLabel(start, end)}</span>
                            </div>
                        `;
                    }
                }
                const ranges = new Map();

                timeSlots.forEach((slot) => {
                    if (!slot?.start_date || !slot?.end_date || !slot?.start_time || !slot?.end_time) return;
                    const key = `${slot.start_date}|${slot.end_date}`;
                    if (!ranges.has(key)) {
                        ranges.set(key, []);
                    }
                    ranges.get(key).push({
                        start_time: slot.start_time,
                        end_time: slot.end_time,
                    });
                });

                return Array.from(ranges.entries()).map(([key, windows]) => {
                    const [startDate, endDate] = key.split('|');
                    const dateLabel = formatDateRangeLabel(
                        new Date(`${startDate}T00:00:00`),
                        new Date(`${endDate}T00:00:00`)
                    );
                    const windowLabels = windows.map((window) => `
                        <div class="flex items-center gap-2 ml-5 pl-3 border-l border-passion-red/30">
                            <span aria-hidden="true">🕒</span>
                            <span>de ${formatTimeLabel(window.start_time)} à ${formatTimeLabel(window.end_time)}</span>
                        </div>
                    `).join('');

                    return `
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center gap-2">
                                <span aria-hidden="true">📅</span>
                                <span>${dateLabel}</span>
                            </div>
                            ${windowLabels}
                        </div>
                    `;
                }).join('');
            }

            if (!startAt || !endAt) return '<span>Dates à venir</span>';
            const start = new Date(startAt);
            const end = new Date(endAt);
            const dateTimeLabel = formatDateTimeRangeLabel(start, end);

            return `
                <div class="flex items-center gap-2">
                    <span aria-hidden="true">📅</span>
                    <span>${dateTimeLabel}</span>
                </div>
            `;
        }

        function toMinutes(time) {
            const [hours, minutes] = String(time).split(':').map((part) => Number(part));
            if (Number.isNaN(hours) || Number.isNaN(minutes)) {
                return null;
            }
            return (hours * 60) + minutes;
        }

        function toDateKey(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function isSlotWithinTimeSlots(slotStart, slotEnd, timeSlots = []) {
            if (!Array.isArray(timeSlots) || timeSlots.length === 0) {
                return true;
            }

            const slotDate = toDateKey(slotStart);
            const slotEndDate = toDateKey(slotEnd);
            if (slotEndDate !== slotDate) {
                return false;
            }
            const slotStartMinutes = (slotStart.getHours() * 60) + slotStart.getMinutes();
            const slotEndMinutes = (slotEnd.getHours() * 60) + slotEnd.getMinutes();

            return timeSlots.some((window) => {
                if (!window?.start_date || !window?.end_date || !window?.start_time || !window?.end_time) {
                    return false;
                }

                if (slotDate < window.start_date || slotDate > window.end_date) {
                    return false;
                }

                const windowStart = toMinutes(window.start_time);
                const windowEnd = toMinutes(window.end_time);

                if (windowStart === null || windowEnd === null) {
                    return false;
                }

                return slotStartMinutes >= windowStart && slotEndMinutes <= windowEnd;
            });
        }

        function formatRemainingLabel(remaining) {
            if (remaining === null || Number.isNaN(remaining)) {
                return '';
            }

            if (remaining <= 0) {
                return 'Complet';
            }

            const suffix = remaining > 1 ? 's' : '';
            return `${remaining} place${suffix} restante${suffix}`;
        }

        function isSlotDurationMatching(slotStart, slotEnd, expectedMinutes) {
            if (!Number.isFinite(expectedMinutes) || expectedMinutes <= 0) {
                return true;
            }

            const durationMinutes = Math.round((slotEnd.getTime() - slotStart.getTime()) / 60000);
            return durationMinutes === expectedMinutes;
        }

        function getAvailabilityThreshold(allo) {
            const margin = Number(allo?.security_margin_minutes ?? 0);
            if (!Number.isFinite(margin) || margin <= 0) {
                return new Date();
            }
            return new Date(Date.now() + (margin * 60000));
        }

        function buildSlotOptions(allo) {
            if (!allo?.slots?.length) {
                return '<option value="">Aucun créneau disponible</option>';
            }

            const threshold = getAvailabilityThreshold(allo);
            const slots = allo.slots.filter((slot) => {
                if (!slot.slot_start_at || !slot.slot_end_at) return false;
                const slotStart = new Date(slot.slot_start_at);
                const slotEnd = new Date(slot.slot_end_at);
                if (!isSlotWithinTimeSlots(slotStart, slotEnd, allo.time_slots)) {
                    return false;
                }
                if (!isSlotDurationMatching(slotStart, slotEnd, allo.slot_duration_minutes)) {
                    return false;
                }
                return slotStart >= threshold;
            });

            if (!slots.length) {
                return '<option value="">Aucun créneau disponible</option>';
            }

            const options = slots.map((slot) => {
                const slotStart = new Date(slot.slot_start_at);
                const slotEnd = new Date(slot.slot_end_at);
                const remaining = slot.remaining ?? slot.remaining_capacity ?? null;
                const remainingLabel = formatRemainingLabel(remaining);
                const isSelectable = ['available', 'partial'].includes(slot.status)
                    && (remaining === null || remaining > 0)
                    && allo.status === 'OPEN'
                    && slotStart >= threshold;
                const statusLabel = remainingLabel
                    ? ` • ${remainingLabel}`
                    : '';
                const label = `${formatDateLabel(slotStart)} · ${formatTime(slotStart)} → ${formatTime(slotEnd)}${statusLabel}`;

                return `<option value="${slot.id}" ${isSelectable ? '' : 'disabled'}>${label}</option>`;
            }).join('');

            return `<option value="">Sélectionne un créneau</option>${options}`;
        }

        function setFilterButtons() {
            filterButtons.forEach((btn) => {
                const isActive = btn.dataset.filter === activeFilter;
                btn.classList.toggle('bg-passion-red', isActive);
                btn.classList.toggle('text-white', isActive);
                btn.classList.toggle('bg-white', !isActive);
                btn.classList.toggle('text-passion-red', !isActive);
            });
        }

        function isWindowOpen(allo) {
            if (typeof allo.is_window_open === 'boolean') {
                return allo.is_window_open;
            }
            if (!allo.window_end_at) return false;
            const now = new Date().getTime();
            const end = new Date(allo.window_end_at).getTime();
            if (Number.isNaN(end)) return false;
            return now <= end;
        }

        function isWindowEnded(allo) {
            if (typeof allo.is_window_ended === 'boolean') {
                return allo.is_window_ended;
            }
            if (!allo.window_end_at) return false;
            const now = new Date().getTime();
            const end = new Date(allo.window_end_at).getTime();
            if (Number.isNaN(end)) return false;
            return now > end;
        }

        function getVisibleAllos() {
            if (activeFilter === 'all') return allosData;
            return allosData.filter((allo) => {
                const status = String(allo.status || '').toUpperCase();
                return status === 'OPEN' && isWindowOpen(allo);
            });
        }

        function renderAllos() {
            const visibleAllos = getVisibleAllos();

            if (!visibleAllos.length) {
                statusElement.textContent = activeFilter === 'active'
                    ? 'Aucun allo actif pour le moment.'
                    : 'Aucun allo disponible.';
                catalogElement.innerHTML = '';
                return;
            }

            statusElement.textContent = '';
            catalogElement.innerHTML = '';

            visibleAllos.forEach((allo) => {
                const status = String(allo.status || '').toUpperCase();
                const windowOpen = isWindowOpen(allo);
                const windowEnded = isWindowEnded(allo);
                const isEnded = windowEnded || status !== 'OPEN';
                const isDisabled = isEnded;
                const userBookings = allo.slots
                    .map((slot) => slot.user_booking)
                    .filter((booking) => booking !== null);
                const userBooking = userBookings[0];
                const hasBooking = Boolean(userBooking);
                const canBookNew = allo.can_book_new ?? true;
                const hasAvailableSlots = allo.has_available_slots ?? true;
                const hasAlternativeSlots = allo.has_alternative_slots ?? hasAvailableSlots;
                const isPending = userBooking?.status === 'PENDING';
                const showBookingStatus = hasBooking && !isEnded;
                const bookingStatusLabel = userBooking?.status
                    ? `Réservation : ${bookingStatusLabels[userBooking.status] || userBooking.status}`
                    : '';
                const showModifyButton = !isDisabled && hasBooking && isAuthenticated && isPending && hasAlternativeSlots;
                const showDesistButton = !isDisabled && hasBooking && isAuthenticated && isPending && !hasAlternativeSlots;
                const showSlotsButton = !isDisabled && !hasBooking && canBookNew && hasAvailableSlots;
                const showLimitMessage = !isDisabled && !hasBooking && !canBookNew;
                const showNoSlotsMessage = !isDisabled && !hasBooking && canBookNew && !hasAvailableSlots;
                const bookingButtonLabel = userBookings.length > 1
                    ? 'Modifier mes réservations'
                    : 'Modifier ma réservation';

                const card = document.createElement('div');
                card.className = `relative border-2 shadow-[6px_6px_0_#000] p-6 flex flex-col gap-4 ${
                    isEnded
                        ? 'bg-slate-100 border-slate-300 text-slate-500'
                        : 'bg-white border-passion-red'
                }`;

                card.innerHTML = `
                    ${isEnded ? `
                        <span class="absolute -top-4 right-4 rotate-[-8deg] bg-slate-600 text-white text-xs font-black uppercase px-3 py-1 shadow-[2px_2px_0_#000] animate-pulse">
                            Victime de son succès...
                        </span>
                    ` : ''}
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="font-display font-black uppercase text-2xl ${isEnded ? 'text-slate-600' : 'text-passion-red'}">${allo.title}</h2>
                        </div>
                    </div>
                    <div class="${isEnded ? 'bg-slate-200 border-slate-300 text-slate-600' : 'bg-passion-pink-100 border-passion-red text-passion-red'} border px-4 py-3 text-sm font-semibold flex flex-col gap-1">
                        ${formatWindowLabel(allo.window_start_at, allo.window_end_at, allo.time_slots)}
                    </div>
                    ${showBookingStatus ? `
                        <div class="text-xs font-semibold uppercase text-slate-500">
                            ${bookingStatusLabel}
                        </div>
                    ` : ''}
                    ${isDisabled ? '' : (showModifyButton ? `
                        <a href="/allos/${allo.id}/creneaux"
                           class="text-center bg-passion-fire-orange text-passion-red font-display font-black uppercase py-3 shadow-[4px_4px_0_#000] hover:bg-passion-fire-yellow transition-colors">
                            ${bookingButtonLabel}
                        </a>
                    ` : (showDesistButton ? `
                        <button type="button"
                                data-cancel-booking-id="${userBooking?.id ?? ''}"
                                class="text-center bg-white text-passion-red font-display font-black uppercase py-3 border-2 border-passion-red shadow-[4px_4px_0_#000] hover:bg-passion-pink-100 transition-colors">
                            Se désister
                        </button>
                    ` : (showSlotsButton ? `
                        <a href="/allos/${allo.id}/creneaux"
                           class="text-center bg-passion-red text-white font-display font-black uppercase py-3 shadow-[4px_4px_0_#000] hover:bg-passion-fire-orange hover:text-passion-red transition-colors">
                            Voir les créneaux
                        </a>
                    ` : (showLimitMessage ? `
                        <span class="text-center text-sm font-semibold text-slate-500">
                            Limite quotidienne atteinte.
                        </span>
                    ` : (showNoSlotsMessage ? `
                        <span class="text-center text-sm font-semibold text-slate-500">
                            Plus de place disponible.
                        </span>
                    ` : '')))))}
                    <div class="allo-form hidden space-y-4 border-t border-passion-red/30 pt-4">
                        <div class="space-y-3">
                            <label class="text-xs font-bold uppercase text-passion-red">Choisis un créneau</label>
                            <select class="allo-slot-select w-full border-2 border-passion-red px-3 py-2 text-sm" ${isEnded ? 'disabled' : ''}>
                                ${buildSlotOptions(allo)}
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold uppercase text-passion-red"></label>
                            <textarea class="allo-note w-full border-2 border-passion-red px-3 py-2 text-sm" rows="2" ${isEnded ? 'disabled' : ''} placeholder="Ex: sieste après 15h, merci !"></textarea>
                        </div>
                        <button class="allo-book-btn bg-passion-red text-white font-display font-black uppercase py-3 shadow-[4px_4px_0_#000] hover:bg-passion-fire-orange hover:text-passion-red transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            ${isEnded ? 'Créneaux clôturés' : 'Réserver cet allo'}
                        </button>
                        <div class="allo-feedback text-sm font-semibold text-passion-red"></div>
                        ${userBookings.length ? `
                            <div class="bg-slate-100 border border-slate-300 px-4 py-3 text-sm space-y-2">
                                <p class="font-semibold text-slate-700 uppercase text-xs">Tes réservations</p>
                                ${userBookings.map((booking) => `
                                    <div class="space-y-1">
                                        <div class="font-semibold text-slate-700">Statut: ${booking.status}</div>
                                    </div>
                                `).join('')}
                            </div>
                        ` : ''}
                    </div>
                `;

                catalogElement.appendChild(card);
            });
        }

        function showToast(message, type = 'error') {
            const showToastFn = window.PassionToast?.show;
            if (!showToastFn || !message) return;
            showToastFn({ message, type, duration: 4000 });
        }

        async function cancelBooking(bookingId, button) {
            if (!bookingId) return;
            if (button) {
                button.disabled = true;
            }

            try {
                const response = await fetch(`/api/allos/bookings/${bookingId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });
                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    showToast(data.message || 'Impossible de se désister.');
                    return;
                }

                showToast('Réservation annulée.', 'success');
                await loadAllos();
            } catch (error) {
                showToast('Impossible de se désister.');
            } finally {
                if (button) {
                    button.disabled = false;
                }
            }
        }

        filterButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                activeFilter = btn.dataset.filter;
                setFilterButtons();
                renderAllos();
            });
        });

        catalogElement.addEventListener('click', (event) => {
            const button = event.target.closest('[data-cancel-booking-id]');
            if (!button) return;
            event.preventDefault();
            cancelBooking(button.dataset.cancelBookingId, button);
        });

        async function loadAllos() {
            try {
                const response = await fetch('/api/allos');
                const data = await response.json();
                allosData = data.allos || [];
                renderAllos();
            } catch (error) {
                statusElement.textContent = 'Impossible de charger le catalogue.';
            }
        }

        setFilterButtons();
        loadAllos();
    </script>
@endpush
