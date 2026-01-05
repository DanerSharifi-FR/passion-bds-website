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
                Des services tentants à réserver contre des points. Choisis ton créneau et laisse-nous un petit mot 💌
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
            const now = new Date();
            const start = new Date(allo.window_start_at);
            const end = new Date(allo.window_end_at);
            return now >= start && now <= end;
        }

        function isWindowEnded(allo) {
            if (!allo.window_end_at) return false;
            const now = new Date();
            const end = new Date(allo.window_end_at);
            return now > end;
        }

        function getVisibleAllos() {
            if (activeFilter === 'all') return allosData;
            return allosData.filter((allo) => isWindowOpen(allo) && allo.status === 'OPEN');
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
                const windowOpen = isWindowOpen(allo);
                const windowEnded = isWindowEnded(allo);
                const isEnded = windowEnded || !windowOpen || allo.status !== 'OPEN';
                const card = document.createElement('div');
                card.className = 'bg-white border-2 border-passion-red shadow-[6px_6px_0_#000] p-6 flex flex-col gap-4';

                card.innerHTML = `
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="font-display font-black uppercase text-2xl text-passion-red">${allo.title}</h2>
                            <p class="text-sm text-gray-600 mt-2">${allo.description ?? ''}</p>
                        </div>
                        <span class="bg-passion-fire-orange text-passion-red font-display font-black text-sm px-3 py-1 shadow-[3px_3px_0_#000]">
                            ${allo.points_cost} pts
                        </span>
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
                    </a>
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
