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
                Des services tentants à réserver. Choisis ton créneau et laisse-nous un petit mot 💌
            </p>
        </div>

        @guest
            <div class="bg-white border-2 border-passion-red shadow-[6px_6px_0_#000] p-5 text-center mb-8">
                <p class="text-passion-red font-semibold">
                    Connecte-toi pour réserver un allo et voir les notes des admins.
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
        const catalogElement = document.getElementById('allos-catalog');
        const statusElement = document.getElementById('allos-status');
        const filterButtons = document.querySelectorAll('.allo-filter-btn');

        let allosData = [];
        let activeFilter = 'active';

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

        function buildSlotOptions(allo) {
            if (!allo?.slots?.length) {
                return '<option value="">Aucun créneau disponible</option>';
            }

            const now = new Date();
            const slots = allo.slots.filter((slot) => {
                if (!slot.slot_start_at || !slot.slot_end_at) return false;
                const slotStart = new Date(slot.slot_start_at);
                const slotEnd = new Date(slot.slot_end_at);
                if (!isSlotWithinTimeSlots(slotStart, slotEnd, allo.time_slots)) {
                    return false;
                }
                return slotStart >= now;
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
                    && slotStart >= now;
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
            if (!allo.window_start_at || !allo.window_end_at) return false;
            const now = new Date().getTime();
            const start = new Date(allo.window_start_at).getTime();
            const end = new Date(allo.window_end_at).getTime();
            if (Number.isNaN(start) || Number.isNaN(end)) return false;
            return now >= start && now <= end;
        }

        function isWindowEnded(allo) {
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

        function isWindowEnded(allo) {
            if (!allo.window_end_at) return false;
            const now = new Date();
            const end = new Date(allo.window_end_at);
            return now > end;
        }

        function getVisibleAllos() {
            if (activeFilter === 'all') return allosData;
            return allosData.filter((allo) => {
                const windowEnded = allo.is_window_ended ?? isWindowEnded(allo);
                return allo.status === 'OPEN' && !windowEnded;
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
                const isEnded = windowEnded || !windowOpen || status !== 'OPEN';
                const userBookings = allo.slots
                    .map((slot) => slot.user_booking)
                    .filter((booking) => booking !== null);

                const card = document.createElement('div');
                card.className = 'bg-white border-2 border-passion-red shadow-[6px_6px_0_#000] p-6 flex flex-col gap-4';

                card.innerHTML = `
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="font-display font-black uppercase text-2xl text-passion-red">${allo.title}</h2>
                            <p class="text-sm text-gray-600 mt-2">${allo.description ?? ''}</p>
                        </div>
                    </div>
                    <div class="bg-passion-pink-100 border border-passion-red px-4 py-3 text-sm font-semibold text-passion-red flex flex-col gap-1">
                        ${formatWindowLabel(allo.window_start_at, allo.window_end_at, allo.time_slots)}
                    </div>
                    ${isEnded ? `
                        <div class="bg-slate-100 border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600">
                            Victime de son succès : créneaux clôturés.
                        </div>
                    ` : ''}
                    <a href="/allos/${allo.id}/creneaux"
                       class="text-center bg-passion-red text-white font-display font-black uppercase py-3 shadow-[4px_4px_0_#000] hover:bg-passion-fire-orange hover:text-passion-red transition-colors">
                        Voir les créneaux
                    </button>
                    <div class="allo-form hidden space-y-4 border-t border-passion-red/30 pt-4">
                        <div class="space-y-3">
                            <label class="text-xs font-bold uppercase text-passion-red">Choisis un créneau</label>
                            <select class="allo-slot-select w-full border-2 border-passion-red px-3 py-2 text-sm" ${isEnded ? 'disabled' : ''}>
                                ${buildSlotOptions(allo)}
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-bold uppercase text-passion-red">Ton note pour nous</label>
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

        filterButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                activeFilter = btn.dataset.filter;
                setFilterButtons();
                renderAllos();
            });
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
