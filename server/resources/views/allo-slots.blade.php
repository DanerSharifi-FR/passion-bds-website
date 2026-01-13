@extends('app')

@section('title', "P'AS'SION BDS - Créneaux")
@section('meta_description', "Créneaux pour réserver un allo")

@section('content')
    <div class="w-full max-w-6xl mb-20" data-allo-id="{{ $alloId }}">
        <div class="flex flex-col gap-2 mb-8">
            <a href="{{ route('allos') }}"
               class="inline-flex items-center gap-2 text-passion-red font-semibold hover:text-passion-fire-orange transition-colors">
                <span aria-hidden="true">←</span>
                Retour au catalogue
            </a>
            <div>
                <h1 id="allo-slot-title"
                    class="font-display font-black text-3xl md:text-5xl text-passion-red uppercase tracking-tighter leading-none">
                    Créneaux Allo
                </h1>
                <p id="allo-slot-description" class="text-passion-pink-500 font-medium mt-2 text-sm md:text-base whitespace-pre-line"></p>
                <p id="allo-slot-intent" class="text-sm text-slate-600 mt-3"></p>
            </div>
        </div>

        @guest
            <div class="bg-white border-2 border-passion-red shadow-[6px_6px_0_#000] p-5 text-center mb-8">
                <p class="text-passion-red font-semibold">
                    Connecte-toi pour réserver un créneau.
                </p>
                <a href="{{ route('login') }}"
                   class="inline-block mt-4 bg-passion-red text-white font-display font-black uppercase px-6 py-3 shadow-[4px_4px_0_#000] hover:bg-passion-fire-orange hover:text-passion-red transition-colors">
                    Se connecter
                </a>
            </div>
        @endguest

        <div id="allo-slot-status" class="text-center text-sm text-passion-pink-500 font-semibold mb-6">
            Chargement des créneaux...
        </div>

        <div id="allo-window-card"
             class="hidden bg-passion-pink-100 border border-passion-red px-4 py-3 text-sm font-semibold text-passion-red flex flex-col gap-1 mb-6">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 items-start" id="allo-timetable"></div>

        <div id="allo-booking-card"
             class="mt-8 bg-white border-2 border-passion-red shadow-[6px_6px_0_#000] p-6 space-y-4 hidden">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p id="allo-booking-title" class="text-xs font-bold uppercase text-passion-red">Ton créneau choisi</p>
                    <p id="allo-selected-slot" class="text-sm font-semibold text-slate-700">Sélectionne un créneau</p>
                </div>
            </div>
            <div id="allo-description-reminder"
                 class="hidden flex items-start gap-2 rounded-md border border-blue-300 bg-blue-50 px-3 py-2 text-sm text-blue-700">
                <span aria-hidden="true">ℹ️</span>
                <p class="font-medium whitespace-pre-line"></p>
            </div>
            <div class="space-y-2">
                <label class="text-xs font-bold uppercase text-passion-red">Ta note pour nous</label>
                <textarea id="allo-note" class="w-full border-2 border-passion-red px-3 py-2 text-sm" rows="2"
                          data-default-placeholder="Ex: sieste après 15h, merci !"
                          placeholder="Ex: sieste après 15h, merci !"></textarea>
            </div>
            <button id="allo-book-btn"
                    class="w-full bg-passion-red text-white font-display font-black uppercase py-3 shadow-[4px_4px_0_#000] hover:bg-passion-fire-orange hover:text-passion-red transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                Réserver cet allo
            </button>
            <div id="allo-feedback" class="text-sm font-semibold text-passion-red"></div>
        </div>
    </div>
@endsection

@push('end_scripts')
    <script>
        const isAuthenticated = @json(auth()->check());
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const alloId = Number(document.querySelector('[data-allo-id]')?.dataset?.alloId ?? 0);
        const bookingId = Number(new URLSearchParams(window.location.search).get('booking') ?? 0);
        const isEditing = bookingId > 0;

        const statusElement = document.getElementById('allo-slot-status');
        const titleElement = document.getElementById('allo-slot-title');
        const descriptionElement = document.getElementById('allo-slot-description');
        const intentElement = document.getElementById('allo-slot-intent');
        const windowCard = document.getElementById('allo-window-card');
        const timetableElement = document.getElementById('allo-timetable');
        const bookingCard = document.getElementById('allo-booking-card');
        const bookingTitleElement = document.getElementById('allo-booking-title');
        const selectedSlotElement = document.getElementById('allo-selected-slot');
        const bookingButton = document.getElementById('allo-book-btn');
        const noteInput = document.getElementById('allo-note');
        const feedbackElement = document.getElementById('allo-feedback');
        const descriptionReminder = document.getElementById('allo-description-reminder');

        let alloData = null;
        let selectedSlots = [];
        let currentBooking = null;
        let canEditBooking = false;
        let currentBookingSlot = null;

        function formatDateLabel(date) {
            return new Intl.DateTimeFormat('fr-FR', {
                weekday: 'long',
                day: '2-digit',
                month: 'long',
            }).format(date);
        }

        function formatTime(date) {
            return new Intl.DateTimeFormat('fr-FR', {
                hour: '2-digit',
                minute: '2-digit',
            }).format(date);
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
            const dateLabel = formatDateRangeLabel(start, end);
            const startTime = formatTimeLabel(formatTime(start));
            const endTime = formatTimeLabel(formatTime(end));

            return `
                <div class="flex items-center gap-2">
                    <span aria-hidden="true">📅</span>
                    <span>${dateLabel}</span>
                </div>
                <div class="flex items-center gap-2 ml-5 pl-3 border-l border-passion-red/30">
                    <span aria-hidden="true">🕒</span>
                    <span>de ${startTime} à ${endTime}</span>
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

        function isCurrentBookingSlot(slot) {
            if (!currentBooking || !slot?.user_booking) return false;
            return slot.user_booking.id === currentBooking.id;
        }

        function shouldAllowMultipleSelections() {
            if (isEditing) {
                return false;
            }

            const dailyLimit = alloData?.daily_booking_limit;

            if (dailyLimit === null || dailyLimit === undefined) {
                return true;
            }

            return Number(dailyLimit) > 1;
        }

        function getSelectedSlots() {
            if (!alloData) return [];
            const inputs = Array.from(document.querySelectorAll('input[name="allo-slot"]:checked'));
            return inputs
                .map((input) => alloData.slots.find((slot) => slot.id === Number(input.value)))
                .filter(Boolean);
        }

        function getDailyLimitValue() {
            const dailyLimit = alloData?.daily_booking_limit;
            if (dailyLimit === null || dailyLimit === undefined) {
                return null;
            }
            const numericLimit = Number(dailyLimit);
            return Number.isFinite(numericLimit) ? numericLimit : null;
        }

        function getUserBookingsByDate() {
            if (!alloData?.slots?.length) {
                return {};
            }

            return alloData.slots.reduce((acc, slot) => {
                if (!slot.user_booking) {
                    return acc;
                }

                if (isEditing && currentBooking && slot.user_booking.id === currentBooking.id) {
                    return acc;
                }

                const slotDate = slot.slot_start_at ? toDateKey(new Date(slot.slot_start_at)) : null;
                if (!slotDate) {
                    return acc;
                }

                acc[slotDate] = (acc[slotDate] ?? 0) + 1;
                return acc;
            }, {});
        }

        function getSelectedSlotsByDate(slots) {
            return slots.reduce((acc, slot) => {
                if (isEditing && isCurrentBookingSlot(slot)) {
                    return acc;
                }

                const slotDate = slot.slot_start_at ? toDateKey(new Date(slot.slot_start_at)) : null;
                if (!slotDate) {
                    return acc;
                }

                acc[slotDate] = (acc[slotDate] ?? 0) + 1;
                return acc;
            }, {});
        }

        function getDailyLimitValidation(slots) {
            const dailyLimit = getDailyLimitValue();

            if (!dailyLimit || dailyLimit <= 0) {
                return { ok: true, message: '' };
            }

            const existingByDate = getUserBookingsByDate();
            const selectedByDate = getSelectedSlotsByDate(slots);

            const overLimitDates = Object.keys(selectedByDate).filter((dateKey) => {
                const existingCount = existingByDate[dateKey] ?? 0;
                return existingCount + selectedByDate[dateKey] > dailyLimit;
            });

            if (!overLimitDates.length) {
                return { ok: true, message: '' };
            }

            return { ok: false, message: 'Tu as atteint la limite de réservations pour cet allo ce jour-là.' };
        }

        function renderTimetable() {
            if (!alloData) return;

            const now = new Date();
            const threshold = getAvailabilityThreshold(alloData);
            const windowOpen = typeof alloData.is_window_open === 'boolean'
                ? alloData.is_window_open
                : (alloData.window_end_at && now <= new Date(alloData.window_end_at));
            const windowEnded = typeof alloData.is_window_ended === 'boolean'
                ? alloData.is_window_ended
                : (alloData.window_end_at && now > new Date(alloData.window_end_at));
            const isEnded = windowEnded || alloData.status !== 'OPEN';
            const availableSlotStatuses = ['available', 'partial'];
            const allowMultipleSelections = shouldAllowMultipleSelections();
            const inputType = allowMultipleSelections ? 'checkbox' : 'radio';
            const isSelectableSlot = (slot) => {
                if (!slot?.slot_start_at || !slot?.slot_end_at) return false;
                if (isCurrentBookingSlot(slot)) {
                    return true;
                }
                const slotStart = new Date(slot.slot_start_at);
                const slotEnd = new Date(slot.slot_end_at);
                if (!isSlotWithinTimeSlots(slotStart, slotEnd, alloData.time_slots)) {
                    return false;
                }
                if (!isSlotDurationMatching(slotStart, slotEnd, alloData.slot_duration_minutes)) {
                    return false;
                }
                const remaining = slot.remaining ?? slot.remaining_capacity ?? null;
                return availableSlotStatuses.includes(slot.status)
                    && (remaining === null || remaining > 0)
                    && alloData.status === 'OPEN'
                    && slotStart >= threshold
                    && (!isEditing || canEditBooking);
            };

            const disabledDates = Array.isArray(alloData.disabled_dates) ? alloData.disabled_dates : [];
            const slots = alloData.slots.filter((slot) => {
                if (!slot.slot_start_at || !slot.slot_end_at) return false;
                if (isCurrentBookingSlot(slot)) {
                    return true;
                }
                const slotStart = new Date(slot.slot_start_at);
                const slotEnd = new Date(slot.slot_end_at);
                if (!isSlotWithinTimeSlots(slotStart, slotEnd, alloData.time_slots)) {
                    return false;
                }
                if (!isSlotDurationMatching(slotStart, slotEnd, alloData.slot_duration_minutes)) {
                    return false;
                }
                return slotStart >= threshold;
            });

            if (!slots.length && !disabledDates.length) {
                statusElement.textContent = 'Plus de créneaux disponibles.';
                timetableElement.innerHTML = '';
                bookingCard.classList.add('hidden');
                return;
            }

            const hasSelectableSlots = slots.some((slot) => isSelectableSlot(slot));
            const shouldShowBookingCard = hasSelectableSlots || Boolean(currentBooking);

            statusElement.textContent = isEnded
                ? 'Réservations clôturées pour cet allo.'
                : (hasSelectableSlots ? '' : 'Plus de créneaux disponibles.');
            timetableElement.innerHTML = '';
            bookingCard.classList.toggle('hidden', !shouldShowBookingCard);

            const slotsByDate = slots.reduce((acc, slot) => {
                const date = new Date(slot.slot_start_at);
                const key = toDateKey(date);
                acc[key] = acc[key] || [];
                acc[key].push(slot);
                return acc;
            }, {});

            const dateKeys = new Set([
                ...Object.keys(slotsByDate),
                ...disabledDates,
            ]);

            Array.from(dateKeys)
                .sort((a, b) => new Date(a) - new Date(b))
                .forEach((dateKey) => {
                const daySlots = slotsByDate[dateKey] || [];
                daySlots.sort((a, b) => new Date(a.slot_start_at) - new Date(b.slot_start_at));
                const dayHasSelectableSlots = daySlots.length > 0 && daySlots.some((slot) => isSelectableSlot(slot));
                const card = document.createElement(dayHasSelectableSlots ? 'details' : 'div');
                card.className = dayHasSelectableSlots
                    ? 'bg-white border-2 border-passion-red shadow-[4px_4px_0_#000] p-4 space-y-3 self-start relative'
                    : 'bg-slate-100 border-2 border-slate-300 text-slate-500 shadow-[4px_4px_0_#000] p-4 space-y-3 self-start relative opacity-80';
                const dayDate = new Date(`${dateKey}T00:00:00`);
                card.innerHTML = dayHasSelectableSlots
                    ? `
                        <summary class="font-display font-black uppercase text-passion-red text-lg cursor-pointer list-none flex items-center justify-between">
                            <span>${formatDateLabel(dayDate)}</span>
                            <span aria-hidden="true" class="text-xs">▼</span>
                        </summary>
                        <div class="space-y-2" data-date="${dateKey}"></div>
                    `
                    : `
                        <div class="font-display font-black uppercase text-slate-500 text-lg flex items-center justify-between">
                            <span>${formatDateLabel(dayDate)}</span>
                        </div>
                        <span class="absolute -top-3 right-3 rotate-[-8deg] bg-slate-500 text-white text-xs font-black uppercase px-2 py-1 shadow-[2px_2px_0_#000]">Trop tard</span>
                    `;

                const list = card.querySelector('[data-date]');
                if (dayHasSelectableSlots && list) {
                    daySlots.filter((slot) => isSelectableSlot(slot)).forEach((slot) => {
                        const slotStart = new Date(slot.slot_start_at);
                        const slotEnd = new Date(slot.slot_end_at);
                        const remaining = slot.remaining ?? slot.remaining_capacity ?? null;
                        const remainingLabel = formatRemainingLabel(remaining);
                        const isSelectable = isSelectableSlot(slot);
                        const timeLabel = `${formatTime(slotStart)} → ${formatTime(slotEnd)}`;

                        const label = document.createElement('label');
                        label.className = `flex items-center justify-between gap-3 border border-passion-red/30 px-3 py-2 text-sm font-semibold ${isSelectable ? 'hover:bg-passion-pink-100' : 'opacity-50'}`;
                        label.innerHTML = `
                            <div class="flex items-center gap-2">
                                <input type="${inputType}" name="allo-slot" value="${slot.id}" ${isSelectable && isAuthenticated ? '' : 'disabled'} />
                                <span>${timeLabel}</span>
                            </div>
                            <span class="text-xs text-passion-red">${remainingLabel}</span>
                        `;
                        list.appendChild(label);
                    });
                }

                timetableElement.appendChild(card);
            });

            updateBookingButtonState();
        }

        function updateSelectedSlot() {
            if (!alloData) {
                selectedSlots = [];
                updateBookingButtonState();
                return;
            }

            selectedSlots = getSelectedSlots();

            if (!selectedSlots.length && currentBookingSlot && isEditing) {
                selectedSlots = [currentBookingSlot];
            }

            if (!selectedSlots.length) {
                setFeedback('');
                selectedSlotElement.textContent = 'Sélectionne un créneau';
                updateBookingButtonState();
                return;
            }

            setFeedback('');

            if (selectedSlots.length === 1) {
                const slot = selectedSlots[0];
                const label = `${formatTime(new Date(slot.slot_start_at))} → ${formatTime(new Date(slot.slot_end_at))}`;
                selectedSlotElement.textContent = label;
            } else {
                selectedSlotElement.textContent = `${selectedSlots.length} créneaux sélectionnés`;
            }

            updateBookingButtonState();
        }

        function updateBookingButtonState() {
            if (!alloData) {
                bookingButton.disabled = true;
                return;
            }

            const now = new Date();
            const windowOpen = typeof alloData.is_window_open === 'boolean'
                ? alloData.is_window_open
                : (alloData.window_end_at && now <= new Date(alloData.window_end_at));
            const windowEnded = typeof alloData.is_window_ended === 'boolean'
                ? alloData.is_window_ended
                : (alloData.window_end_at && now > new Date(alloData.window_end_at));
            const isEnded = windowEnded || alloData.status !== 'OPEN';

            const hasSelection = selectedSlots.length > 0;
            const limitValidation = getDailyLimitValidation(selectedSlots);
            if (!limitValidation.ok) {
                setFeedback(limitValidation.message);
            }

            bookingButton.disabled = !isAuthenticated
                || isEnded
                || !windowOpen
                || !hasSelection
                || (isEditing && !canEditBooking)
                || !limitValidation.ok;
        }

        function setFeedback(message, isSuccess = false) {
            feedbackElement.textContent = message;
            feedbackElement.classList.toggle('text-passion-red', !isSuccess);
            feedbackElement.classList.toggle('text-green-600', isSuccess);
        }

        async function bookSelectedSlot() {
            if (!selectedSlots.length || !alloData) {
                setFeedback('Choisis un créneau disponible.');
                return;
            }

            if (isEditing && !currentBooking) {
                setFeedback("Impossible de modifier cette réservation.");
                return;
            }

            const limitValidation = getDailyLimitValidation(selectedSlots);
            if (!limitValidation.ok) {
                setFeedback(limitValidation.message);
                return;
            }

            const threshold = getAvailabilityThreshold(alloData);
            const isInvalidSelection = selectedSlots.some((slot) => {
                if (isCurrentBookingSlot(slot)) {
                    return false;
                }
                if (!slot.slot_start_at) {
                    return true;
                }
                return new Date(slot.slot_start_at) < threshold;
            });

            if (isInvalidSelection) {
                setFeedback('Ce créneau n’est plus disponible. Les créneaux ont été mis à jour.');
                await loadAllo();
                return;
            }

            bookingButton.disabled = true;
            setFeedback('Réservation en cours...');

            try {
                const bookingRequests = isEditing
                    ? [{
                        slot: selectedSlots[0],
                        endpoint: `/api/allos/bookings/${currentBooking.id}`,
                        method: 'PUT',
                    }]
                    : selectedSlots.map((slot) => ({
                        slot,
                        endpoint: '/api/allos/bookings',
                        method: 'POST',
                    }));

                for (const request of bookingRequests) {
                    const response = await fetch(request.endpoint, {
                        method: request.method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            allo_id: alloData.id,
                            allo_slot_id: request.slot.id,
                            user_note: noteInput.value.trim() || null,
                        }),
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        const message = data.message || 'Erreur lors de la réservation.';
                        if (message.includes('limite de réservations')) {
                            setFeedback('Tu as atteint la limite de réservations pour cet allo ce jour-là.');
                            return;
                        }
                        if (message.includes('déjà réservé un créneau pour cet allo')) {
                            setFeedback('Tu as déjà réservé un créneau ce jour-là. Utilise le bouton “Modifier ma réservation”.');
                            return;
                        }
                        if (message.includes('déjà passé')
                            || message.includes('pas encore disponible')
                            || message.includes('déjà réservé')
                            || message.includes('indisponible')) {
                            setFeedback('Ce créneau n’est plus disponible. Les créneaux ont été mis à jour.');
                            await loadAllo();
                            return;
                        }
                        setFeedback(message);
                        return;
                    }
                }

                const successMessage = isEditing
                    ? 'Réservation mise à jour !'
                    : 'Réservation confirmée !';
                setFeedback(successMessage, true);
                await loadAllo();
                window.location.href = '/allos/reservations?allo_id=' + alloData.id;
            } catch (error) {
                setFeedback('Impossible de réserver pour le moment.');
            } finally {
                updateBookingButtonState();
            }
        }

        function resolveCurrentBooking() {
            if (!alloData?.slots?.length) {
                currentBooking = null;
                canEditBooking = true;
                return null;
            }

            const slotWithBooking = alloData.slots.find((slot) => slot.user_booking);
            currentBooking = slotWithBooking?.user_booking || null;
            currentBookingSlot = slotWithBooking || null;
            canEditBooking = !currentBooking || currentBooking.status === 'PENDING';
            return slotWithBooking || null;
        }

        function updateBookingUI(slotWithBooking) {
            if (!intentElement) return;

            if (currentBooking && isEditing) {
                titleElement.textContent = 'Modifier ton créneau';
                intentElement.textContent = canEditBooking
                    ? 'Tu as déjà réservé un créneau. Choisis-en un nouveau pour modifier ta réservation.'
                    : 'Ta réservation est en cours de traitement. La modification est indisponible.';
                bookingTitleElement.textContent = canEditBooking ? 'Ton créneau actuel' : 'Ton créneau réservé';
                bookingButton.textContent = canEditBooking ? 'Modifier ma réservation' : 'Modification indisponible';
                bookingButton.disabled = !canEditBooking;
                if (slotWithBooking) {
                    const slotLabel = `${formatTime(new Date(slotWithBooking.slot_start_at))} → ${formatTime(new Date(slotWithBooking.slot_end_at))}`;
                    selectedSlots = [slotWithBooking];
                    selectedSlotElement.textContent = slotLabel;
                    noteInput.value = currentBooking.user_note || '';
                }
            } else {
                titleElement.textContent = alloData?.title || 'Créneaux Allo';
                intentElement.textContent = 'Choisis un créneau pour réserver ton allo.';
                bookingTitleElement.textContent = shouldAllowMultipleSelections()
                    ? 'Tes créneaux choisis'
                    : 'Ton créneau choisi';
                bookingButton.textContent = shouldAllowMultipleSelections()
                    ? 'Réserver ces créneaux'
                    : 'Réserver cet allo';
                bookingButton.disabled = false;
                noteInput.value = '';
                selectedSlots = [];
                selectedSlotElement.textContent = 'Sélectionne un créneau';
            }
        }

        async function loadAllo() {
            try {
                const bookingQuery = bookingId ? `&booking_id=${bookingId}` : '';
                const response = await fetch(`/api/allos?allo_id=${alloId}&slots_only=1${bookingQuery}`);
                const data = await response.json();
                const allos = data.allos || [];
                alloData = allos.find((allo) => allo.id === alloId) || null;

                if (!alloData) {
                    statusElement.textContent = "Impossible de trouver cet allo.";
                    timetableElement.innerHTML = '';
                    bookingCard.classList.add('hidden');
                    return;
                }

                titleElement.textContent = alloData.title || 'Créneaux Allo';
                descriptionElement.textContent = alloData.description || '';
                if (alloData.description) {
                    descriptionReminder.querySelector('p').textContent = alloData.description;
                    descriptionReminder.classList.remove('hidden');
                    noteInput.placeholder = alloData.description;
                } else {
                    descriptionReminder.classList.add('hidden');
                    noteInput.placeholder = noteInput.dataset.defaultPlaceholder || '';
                }

                windowCard.innerHTML = formatWindowLabel(alloData.window_start_at, alloData.window_end_at, alloData.time_slots);
                windowCard.classList.remove('hidden');

                const slotWithBooking = resolveCurrentBooking();

                if (bookingId && (!currentBooking || currentBooking.id !== bookingId)) {
                    window.location.href = `/allos/reservations?allo_id=${alloId}`;
                    return;
                }
                if (!bookingId && currentBooking && alloData.can_book_new === false) {
                    window.location.href = `/allos/${alloId}/creneaux?booking=${currentBooking.id}`;
                    return;
                }

                updateBookingUI(slotWithBooking);
                renderTimetable();
                if (slotWithBooking && isEditing) {
                    const bookingInput = document.querySelector(`input[name=\"allo-slot\"][value=\"${slotWithBooking.id}\"]`);
                    if (bookingInput) {
                        bookingInput.checked = true;
                    }
                }
                updateSelectedSlot();
            } catch (error) {
                statusElement.textContent = 'Impossible de charger les créneaux.';
            }
        }

        timetableElement.addEventListener('change', (event) => {
            if (event.target && event.target.matches('input[name="allo-slot"]')) {
                updateSelectedSlot();
            }
        });

        bookingButton.addEventListener('click', () => {
            bookSelectedSlot();
        });

        loadAllo();
    </script>
@endpush
