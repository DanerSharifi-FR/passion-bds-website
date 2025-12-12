@extends('app')

@section('title', "P'AS'SION BDS - Activités")
@section('meta_description', "Liste des activités en cours et accès au classement live.")

@section('content')
    <div class="text-center mb-8">
        <h1 class="font-display font-black text-4xl md:text-5xl uppercase tracking-tighter text-passion-red mb-2 drop-shadow-sm">
            APREM&nbsp;&nbsp;BDS&nbsp;&nbsp;EN&nbsp;&nbsp;<span class="text-passion-fire-orange">Live</span>
        </h1>
        <p class="text-passion-pink-500 font-bold text-sm uppercase tracking-widest bg-white/60 inline-block px-4 py-1 rounded-full">
            Pour rejoindre une activité aller voir le stand correspondant.
        </p>
    </div>

    <div id="activities-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 pb-10"></div>
@endsection

@push('end_scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const activitiesApiUrl = '/api/activities';
            const listElement = document.getElementById('activities-list');

            function escapeHtml(v) {
                const d = document.createElement('div');
                d.textContent = v ?? '';
                return d.innerHTML;
            }

            async function fetchJson(url) {
                const res = await fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                if (!res.ok) throw new Error('FETCH_FAILED');
                return await res.json();
            }

            function renderActivities(items) {
                listElement.innerHTML = '';

                if (!items.length) {
                    listElement.innerHTML = `
                <div class="col-span-full bg-white/70 rounded-xl p-6 text-center text-passion-red font-bold">
                    Aucune activité active.
                </div>
            `;
                    return;
                }

                items.forEach((a) => {
                    const isTeam = (a.mode || 'INDIVIDUAL') === 'TEAM';
                    const badge = isTeam
                        ? `<span class="text-[10px] font-black px-2 py-1 rounded-full bg-yellow-300 text-black uppercase tracking-widest">Teams</span>`
                        : `<span class="text-[10px] font-black px-2 py-1 rounded-full bg-passion-pink-200 text-passion-red uppercase tracking-widest">Solo</span>`;

                    const card = document.createElement('a');
                    card.href = `/activities/${encodeURIComponent(a.slug || '')}`;
                    card.className = "block bg-white rounded-2xl border-2 border-passion-pink-300 hover:border-passion-fire-orange transition-all p-5 shadow-sm hover:shadow-md";

                    card.innerHTML = `
                <div class="flex items-center justify-between gap-3 mb-3">
                    <div class="text-passion-red font-black uppercase tracking-tight text-lg">
                        ${escapeHtml(a.title)}
                    </div>
                    ${badge}
                </div>

                <div class="text-xs font-bold uppercase tracking-widest text-passion-pink-500">
                    Comptage: <span class="text-passion-red">${escapeHtml(a.points_label || 'pts')}</span>
                </div>

                <div class="mt-4 inline-flex items-center gap-2 bg-passion-fire-orange text-white font-black uppercase text-xs px-3 py-2 rounded-xl">
                    Voir le classement <i class="fa-solid fa-arrow-right"></i>
                </div>
            `;

                    listElement.appendChild(card);
                });
            }

            (async () => {
                try {
                    const payload = await fetchJson(activitiesApiUrl);
                    renderActivities(Array.isArray(payload?.data) ? payload.data : []);
                } catch (e) {
                    renderActivities([]);
                }
            })();
        });
    </script>
@endpush
