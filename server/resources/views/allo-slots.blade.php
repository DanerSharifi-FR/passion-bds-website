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
                <p id="allo-slot-description" class="text-passion-pink-500 font-medium mt-2 text-sm md:text-base"></p>
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

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="allo-timetable"></div>

        <div id="allo-booking-card"
             class="mt-8 bg-white border-2 border-passion-red shadow-[6px_6px_0_#000] p-6 space-y-4 hidden">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase text-passion-red">Ton créneau choisi</p>
                    <p id="allo-selected-slot" class="text-sm font-semibold text-slate-700">Sélectionne un créneau</p>
                </div>
                <span id="allo-points"
                      class="bg-passion-fire-orange text-passion-red font-display font-black text-sm px-3 py-1 shadow-[3px_3px_0_#000]">
                </span>
            </div>
            <div class="space-y-2">
                <label class="text-xs font-bold uppercase text-passion-red">Ton note pour nous</label>
                <textarea id="allo-note" class="w-full border-2 border-passion-red px-3 py-2 text-sm" rows="2"
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

        const statusElement = document.getElementById('allo-slot-status');
        const titleElement = document.getElementById('allo-slot-title');
        const descriptionElement = document.getElementById('allo-slot-description');
        const windowCard = document.getElementById('allo-window-card');
        const timetableElement = document.getElementById('allo-timetable');
        const bookingCard = document.getElementById('allo-booking-card');
        const selectedSlotElement = document.getElementById('allo-selected-slot');
        const bookingButton = document.getElementById('allo-book-btn');
        const noteInput = document.getElementById('allo-note');
        const feedbackElement = document.getElementById('allo-feedback');
        const pointsElement = document.getElementById('allo-points');

        let alloData = null;
        let selectedSlot = null;

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
                        <div class="flex items-center gap-2">
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
                <div class="flex items-center gap-2">
                    <span aria-hidden="true">🕒</span>
                    <span>de ${startTime} à ${endTime}</span>
                </div>
            `;
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

        function renderTimetable() {
            if (!alloData) return;

            const now = new Date();
            const windowOpen = alloData.window_start_at
                && alloData.window_end_at
                && now >= new Date(alloData.window_start_at)
                && now <= new Date(alloData.window_end_at);
            const isEnded = !windowOpen || alloData.status !== 'OPEN';
            const slots = alloData.slots.filter((slot) => {
                if (!slot.slot_start_at) return true;
                return new Date(slot.slot_start_at) >= now;
            });

            if (!slots.length) {
                statusElement.textContent = 'Plus de créneaux disponibles.';
                timetableElement.innerHTML = '';
                bookingCard.classList.add('hidden');
                return;
            }

            statusElement.textContent = isEnded
                ? 'Réservations clôturées pour cet allo.'
                : '';
            timetableElement.innerHTML = '';
            bookingCard.classList.remove('hidden');

            const slotsByDate = slots.reduce((acc, slot) => {
                const date = new Date(slot.slot_start_at);
                const key = date.toISOString().split('T')[0];
                acc[key] = acc[key] || [];
                acc[key].push(slot);
                return acc;
            }, {});

            Object.entries(slotsByDate).forEach(([dateKey, daySlots]) => {
                daySlots.sort((a, b) => new Date(a.slot_start_at) - new Date(b.slot_start_at));
                const card = document.createElement('div');
                card.className = 'bg-white border-2 border-passion-red shadow-[4px_4px_0_#000] p-4 space-y-3';

                const dayDate = new Date(`${dateKey}T00:00:00`);
                card.innerHTML = `
                    <div class="font-display font-black uppercase text-passion-red text-lg">
                        ${formatDateLabel(dayDate)}
                    </div>
                    <div class="space-y-2" data-date="${dateKey}"></div>
                `;

                const list = card.querySelector('[data-date]');
                daySlots.forEach((slot) => {
                    const remaining = slot.remaining ?? slot.remaining_capacity ?? null;
                    const remainingLabel = formatRemainingLabel(remaining);
                    const isSelectable = ['available', 'partial'].includes(slot.status)
                        && (remaining === null || remaining > 0)
                        && windowOpen
                        && alloData.status === 'OPEN';
                    const timeLabel = `${formatTime(new Date(slot.slot_start_at))} → ${formatTime(new Date(slot.slot_end_at))}`;

                    const label = document.createElement('label');
                    label.className = `flex items-center justify-between gap-3 border border-passion-red/30 px-3 py-2 text-sm font-semibold ${isSelectable ? 'hover:bg-passion-pink-100' : 'opacity-50'}`;
                    label.innerHTML = `
                        <div class="flex items-center gap-2">
                            <input type="radio" name="allo-slot" value="${slot.id}" ${isSelectable && isAuthenticated ? '' : 'disabled'} />
                            <span>${timeLabel}</span>
                        </div>
                        <span class="text-xs text-passion-red">${remainingLabel}</span>
                    `;
                    list.appendChild(label);
                });

                timetableElement.appendChild(card);
            });

            bookingButton.disabled = !isAuthenticated || isEnded;
        }

        function updateSelectedSlot() {
            const input = document.querySelector('input[name="allo-slot"]:checked');
            if (!input || !alloData) {
                selectedSlot = null;
                selectedSlotElement.textContent = 'Sélectionne un créneau';
                return;
            }

            selectedSlot = alloData.slots.find((slot) => slot.id === Number(input.value)) || null;
            if (!selectedSlot) {
                selectedSlotElement.textContent = 'Sélectionne un créneau';
                return;
            }

            const label = `${formatTime(new Date(selectedSlot.slot_start_at))} → ${formatTime(new Date(selectedSlot.slot_end_at))}`;
            selectedSlotElement.textContent = label;
        }

        async function bookSelectedSlot() {
            if (!selectedSlot || !alloData) {
                feedbackElement.textContent = 'Choisis un créneau disponible.';
                return;
            }

            bookingButton.disabled = true;
            feedbackElement.textContent = 'Réservation en cours...';

            try {
                const response = await fetch('/api/allos/bookings', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        allo_id: alloData.id,
                        allo_slot_id: selectedSlot.id,
                        user_note: noteInput.value.trim() || null,
                    }),
                });

                const data = await response.json();

                if (!response.ok) {
                    feedbackElement.textContent = data.message || 'Erreur lors de la réservation.';
                    return;
                }

                feedbackElement.textContent = 'Réservation confirmée !';
                await loadAllo();
            } catch (error) {
                feedbackElement.textContent = 'Impossible de réserver pour le moment.';
            } finally {
                if (isAuthenticated) {
                    bookingButton.disabled = false;
                }
            }
        }

        async function loadAllo() {
            try {
                const response = await fetch('/api/allos');
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
                pointsElement.textContent = `${alloData.points_cost} pts`;

                windowCard.innerHTML = formatWindowLabel(alloData.window_start_at, alloData.window_end_at, alloData.time_slots);
                windowCard.classList.remove('hidden');

                renderTimetable();
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
