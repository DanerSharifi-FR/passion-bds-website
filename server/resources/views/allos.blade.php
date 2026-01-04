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
        const isAuthenticated = @json(auth()->check());
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

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

        function buildSlotOptions(allo) {
            const now = new Date();
            const slots = allo.slots.filter((slot) => {
                if (slot.status !== 'available') return false;
                if (!slot.slot_start_at) return true;
                return new Date(slot.slot_start_at) >= now;
            });

            return slots.length
                ? slots.map((slot) => `<option value="${slot.id}">${formatDate(slot.slot_start_at)} → ${formatDate(slot.slot_end_at)}</option>`).join('')
                : `<option value="">Plus de créneaux disponibles</option>`;
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
                        <span class="bg-passion-fire-orange text-passion-red font-display font-black text-sm px-3 py-1 shadow-[3px_3px_0_#000]">
                            ${allo.points_cost} pts
                        </span>
                    </div>
                    <div class="bg-passion-pink-100 border border-passion-red px-4 py-3 text-sm font-semibold text-passion-red">
                        Fenêtre: ${formatDate(allo.window_start_at)} → ${formatDate(allo.window_end_at)}
                    </div>
                    ${isEnded ? `
                        <div class="bg-slate-100 border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600">
                            Victime de son succès : créneaux clôturés.
                        </div>
                    ` : ''}
                    <button class="allo-toggle-btn bg-passion-red text-white font-display font-black uppercase py-3 shadow-[4px_4px_0_#000] hover:bg-passion-fire-orange hover:text-passion-red transition-colors">
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

                const toggleButton = card.querySelector('.allo-toggle-btn');
                const formElement = card.querySelector('.allo-form');
                const button = card.querySelector('.allo-book-btn');
                const select = card.querySelector('.allo-slot-select');
                const noteInput = card.querySelector('.allo-note');
                const feedback = card.querySelector('.allo-feedback');

                toggleButton.addEventListener('click', () => {
                    formElement.classList.toggle('hidden');
                    toggleButton.textContent = formElement.classList.contains('hidden')
                        ? 'Voir les créneaux'
                        : 'Masquer le formulaire';
                });

                if (!isAuthenticated || isEnded) {
                    button.disabled = true;
                }

                button.addEventListener('click', async () => {
                    if (!select.value) {
                        feedback.textContent = 'Choisis un créneau disponible.';
                        return;
                    }

                    button.disabled = true;
                    feedback.textContent = 'Réservation en cours...';

                    try {
                        const response = await fetch('/api/allos/bookings', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({
                                allo_id: allo.id,
                                allo_slot_id: Number(select.value),
                                user_note: noteInput.value.trim() || null,
                            }),
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            feedback.textContent = data.message || 'Erreur lors de la réservation.';
                        } else {
                            feedback.textContent = 'Réservation confirmée !';
                            await loadAllos();
                        }
                    } catch (error) {
                        feedback.textContent = 'Impossible de réserver pour le moment.';
                    } finally {
                        if (isAuthenticated && !isEnded) {
                            button.disabled = false;
                        }
                    }
                });

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
