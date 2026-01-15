@extends('app')

@section('title', "P'AS'SION BDS - Mes réservations")
@section('meta_description', "Consulte et modifie tes réservations d'allos")

@section('content')
    <div class="w-full max-w-6xl mb-20">
        <div class="flex flex-col gap-2 mb-8">
            <a href="{{ route('allos') }}"
               class="inline-flex items-center gap-2 text-passion-red font-semibold hover:text-passion-fire-orange transition-colors">
                <span aria-hidden="true">←</span>
                Retour au catalogue
            </a>
            <div>
                <h1 class="font-display font-black text-3xl md:text-5xl text-passion-red uppercase tracking-tighter leading-none">
                    Mes <span class="text-passion-fire-orange">réservations</span>
                </h1>
                <p class="text-passion-pink-500 font-medium mt-2 text-sm md:text-base">
                    Retrouve tes allos réservés et leur statut, puis ajuste ton créneau si besoin.
                </p>
            </div>
        </div>

        @guest
            <div class="bg-white border-2 border-passion-red shadow-[6px_6px_0_#000] p-5 text-center mb-8">
                <p class="text-passion-red font-semibold">
                    Connecte-toi pour consulter tes réservations.
                </p>
                <a href="{{ route('login') }}"
                   class="inline-block mt-4 bg-passion-red text-white font-display font-black uppercase px-6 py-3 shadow-[4px_4px_0_#000] hover:bg-passion-fire-orange hover:text-passion-red transition-colors">
                    Se connecter
                </a>
            </div>
        @endguest

        <div id="allo-reservations-status" class="text-center text-sm text-passion-pink-500 font-semibold mb-6">
            Chargement de tes réservations...
        </div>

        <div id="allo-reservations-list" class="grid grid-cols-1 md:grid-cols-2 gap-6"></div>
    </div>
@endsection

@push('end_scripts')
    <script>
        const isAuthenticated = @json(auth()->check());
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const statusElement = document.getElementById('allo-reservations-status');
        const listElement = document.getElementById('allo-reservations-list');

        const statusLabels = {
            PENDING: 'En attente',
            ACCEPTED: 'Acceptée',
            DONE: 'Réalisée',
            CANCELLED: 'Annulée',
        };

        const statusClasses = {
            PENDING: 'bg-yellow-100 text-yellow-700 border-yellow-300',
            ACCEPTED: 'bg-green-100 text-green-700 border-green-300',
            DONE: 'bg-slate-200 text-slate-600 border-slate-300',
            CANCELLED: 'bg-rose-100 text-rose-600 border-rose-300',
        };

        function formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return new Intl.DateTimeFormat('fr-FR', {
                weekday: 'long',
                day: '2-digit',
                month: 'long',
                hour: '2-digit',
                minute: '2-digit',
            }).format(date);
        }

        function highlightFromQuery(card, alloId) {
            const params = new URLSearchParams(window.location.search);
            const targetAlloId = Number(params.get('allo_id'));
            if (!targetAlloId || targetAlloId !== Number(alloId)) return;
            card.classList.add('ring-4', 'ring-passion-fire-orange');
        }

        function renderReservations(reservations) {
            if (!reservations.length) {
                statusElement.textContent = 'Aucune réservation pour le moment.';
                listElement.innerHTML = '';
                return;
            }

            statusElement.textContent = '';
            listElement.innerHTML = '';

            reservations.forEach((reservation) => {
                const statusLabel = statusLabels[reservation.status] || reservation.status;
                const statusClass = statusClasses[reservation.status] || 'bg-slate-100 text-slate-600 border-slate-200';
                const canDesist = reservation.status === 'PENDING';
                const card = document.createElement('div');

                card.className = 'bg-white border-2 border-passion-red shadow-[6px_6px_0_#000] p-6 flex flex-col gap-4';
                card.id = `reservation-${reservation.id}`;

                card.innerHTML = `
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="font-display font-black uppercase text-2xl text-passion-red">${reservation.allo_title || 'Allo'}</h2>
                            <p class="text-sm text-gray-600 mt-2 whitespace-pre-line">${reservation.allo_description ?? ''}</p>
                        </div>
                        <span class="text-xs font-bold uppercase border px-3 py-1 ${statusClass}">
                            ${statusLabel}
                        </span>
                    </div>
                    <div class="bg-passion-pink-100 border border-passion-red text-passion-red px-4 py-3 text-sm font-semibold flex flex-col gap-1">
                        <span>Créneau : ${formatDate(reservation.slot_start_at)}</span>
                        ${reservation.slot_end_at ? `<span>Fin : ${formatDate(reservation.slot_end_at)}</span>` : ''}
                    </div>
                    ${reservation.user_note ? `
                        <div class="text-sm text-slate-600">
                            <span class="font-semibold text-slate-700">Ta note :</span> ${reservation.user_note}
                        </div>
                    ` : ''}
                    <div class="flex flex-wrap items-center gap-3">
                        ${reservation.can_edit ? `
                            <a href="/allos/${reservation.allo_id}/creneaux"
                               class="bg-passion-red text-white font-display font-black uppercase px-5 py-2 shadow-[4px_4px_0_#000] hover:bg-passion-fire-orange hover:text-passion-red transition-colors">
                                Modifier mon créneau
                            </a>
                        ` : canDesist ? `
                            <button type="button"
                                    data-cancel-booking-id="${reservation.id}"
                                    class="bg-white text-passion-red font-display font-black uppercase px-5 py-2 border-2 border-passion-red shadow-[4px_4px_0_#000] hover:bg-passion-pink-100 transition-colors">
                                Se désister
                            </button>
                        ` : `
                            <span class="text-sm font-semibold text-slate-500">Modification indisponible.</span>
                        `}
                    </div>
                `;

                highlightFromQuery(card, reservation.allo_id);
                listElement.appendChild(card);
            });
        }

        async function loadReservations() {
            if (!isAuthenticated) {
                statusElement.textContent = '';
                listElement.innerHTML = '';
                return;
            }

            try {
                const response = await fetch('/api/allos/bookings');
                const data = await response.json();
                const reservations = data.bookings || [];
                renderReservations(reservations);
            } catch (error) {
                statusElement.textContent = 'Impossible de charger tes réservations.';
                listElement.innerHTML = '';
            }
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

                showToast('Réservation supprimée.', 'success');
                await loadReservations();
            } catch (error) {
                showToast('Impossible de se désister.');
            } finally {
                if (button) {
                    button.disabled = false;
                }
            }
        }

        listElement.addEventListener('click', (event) => {
            const button = event.target.closest('[data-cancel-booking-id]');
            if (!button) return;
            event.preventDefault();
            cancelBooking(button.dataset.cancelBookingId, button);
        });

        loadReservations();
    </script>
@endpush
