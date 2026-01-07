@extends('app')

@section('title', "P'AS'SION BDS - Activités Live")

@section('content')
    <div class="text-center mb-8">
        <h1 class="font-display font-black text-4xl md:text-5xl uppercase tracking-tighter text-passion-red mb-2 drop-shadow-sm">
            APREM&nbsp;&nbsp;BDS&nbsp;&nbsp;EN&nbsp;&nbsp;<span class="text-passion-fire-orange">Live</span>
        </h1>
    </div>

    {{-- Container for the dynamic grid --}}
    <div id="live-activities-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 pb-10">
        {{-- Loading State --}}
        <div class="col-span-full text-center py-10">
            <div class="inline-block w-8 h-8 border-4 border-passion-red border-t-transparent rounded-full animate-spin"></div>
            <p class="text-passion-red font-bold mt-2">Chargement des scores...</p>
        </div>
    </div>
@endsection

@push('end_scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const grid = document.getElementById('live-activities-grid');
            let isFirstLoad = true;

            // Configuration
            const REFRESH_RATE = 10000; // 10 seconds
            const API_URL = '/api/activities/live'; // Ensure this matches your route

            // --- HELPER: Template for a single Activity Card ---
            function createCardHTML(activity) {
                const isTeam = activity.mode === 'TEAM';
                const badge = isTeam
                    ? `<span class="text-[10px] font-black px-2 py-1 rounded-full bg-yellow-300 text-black uppercase tracking-widest border border-yellow-500">Team</span>`
                    : `<span class="text-[10px] font-black px-2 py-1 rounded-full bg-passion-pink-200 text-passion-red uppercase tracking-widest border border-passion-pink-300">Solo</span>`;

                // We create 3 slots for the podium (2, 1, 3 order)
                // We give them IDs like: act-99-pos-1, act-99-pos-2...
                return `
                <div id="act-${activity.id}" class="relative bg-white rounded-2xl border-2 border-passion-pink-300 overflow-hidden flex flex-col shadow-sm hover:shadow-md transition-all duration-500">

                    <div class="p-5 pb-2 flex justify-between items-start z-10">
                        <h2 class="font-display font-black text-2xl text-passion-red uppercase leading-none break-words w-3/4">
                            ${escapeHtml(activity.title)}
                        </h2>
                        ${badge}
                    </div>

                    <div class="flex-grow flex items-end justify-center px-4 pt-4 pb-0 bg-gradient-to-b from-white to-passion-pink-50 min-h-[180px]">
                        <div class="flex items-end gap-2 w-full max-w-xs relative">

                            <div class="flex-1 flex flex-col items-center transition-all duration-500" id="act-${activity.id}-pos-2">
                                <span class="player-name text-[10px] font-bold text-passion-red truncate w-full text-center mb-1 transition-opacity duration-300">-</span>
                                <div class="bar w-full bg-gray-300 rounded-t border-t-2 border-x-2 border-white/50 relative transition-all duration-700 ease-out" style="height: 0px;">
                                    <span class="player-rank absolute bottom-1 w-full text-center text-[10px] font-black text-black/30">2</span>
                                </div>
                            </div>

                            <div class="flex-1 flex flex-col items-center -mt-4 z-10 transition-all duration-500" id="act-${activity.id}-pos-1">
                                <div class="text-xl mb-1 animate-bounce-slow">👑</div>
                                <span class="player-name text-xs font-black text-passion-red truncate w-full text-center mb-1 transition-opacity duration-300">-</span>
                                <div class="bar w-full bg-yellow-400 rounded-t border-t-2 border-x-2 border-white/50 shadow-sm relative transition-all duration-700 ease-out" style="height: 0px;">
                                    <span class="player-rank absolute bottom-1 w-full text-center text-sm font-black text-white/50">1</span>
                                </div>
                            </div>

                            <div class="flex-1 flex flex-col items-center transition-all duration-500" id="act-${activity.id}-pos-3">
                                <span class="player-name text-[10px] font-bold text-passion-red truncate w-full text-center mb-1 transition-opacity duration-300">-</span>
                                <div class="bar w-full bg-amber-600 rounded-t border-t-2 border-x-2 border-white/50 relative transition-all duration-700 ease-out" style="height: 0px;">
                                    <span class="player-rank absolute bottom-1 w-full text-center text-[10px] font-black text-white/30">3</span>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="p-4 bg-white border-t border-passion-pink-100 text-center z-10">
                         <a href="/activities/${activity.slug}" class="inline-block w-full py-3 bg-passion-fire-orange text-white font-black uppercase text-sm rounded-lg hover:bg-passion-red transition-colors shadow-sm">
                            Classement Complet
                        </a>
                    </div>
                </div>
            `;
            }

            // --- HELPER: Update a specific slot ---
            function updateSlot(actId, pos, playerData, isTeam) {
                const slot = document.getElementById(`act-${actId}-pos-${pos}`);
                if (!slot) return;

                const nameEl = slot.querySelector('.player-name');
                const barEl = slot.querySelector('.bar');

                // Default heights for animation
                const heights = { 1: '80px', 2: '50px', 3: '35px' };
                const emptyHeight = '0px';

                if (!playerData) {
                    // Empty slot
                    nameEl.textContent = '-';
                    nameEl.style.opacity = '0.5';
                    barEl.style.height = emptyHeight;
                    return;
                }

                // Detect change (simple string compare)
                const newName = playerData.name;
                const currentName = nameEl.textContent;

                // Update Text
                if (currentName !== newName) {
                    // Fade out, swap, fade in
                    nameEl.style.opacity = '0';
                    setTimeout(() => {
                        // to upper case
                        nameEl.textContent = newName.toUpperCase();
                        nameEl.style.opacity = '1';
                    }, 150);
                }

                // Update Height (Live animation)
                // We can even scale height slightly based on score if we wanted complex logic,
                // but fixed heights for 1/2/3 is cleaner visually.
                requestAnimationFrame(() => {
                    barEl.style.height = heights[pos];
                });
            }

            // --- MAIN LOOP ---
            async function fetchAndRender() {
                try {
                    const response = await fetch(API_URL, { headers: { 'Accept': 'application/json' } });
                    const json = await response.json();
                    const activities = json.data || [];

                    if (isFirstLoad) {
                        grid.innerHTML = ''; // Clear loading
                        if (activities.length === 0) {
                            grid.innerHTML = '<div class="col-span-full text-center p-10 font-bold text-passion-red">Aucune activité live.</div>';
                            return;
                        }
                        // Generate Cards HTML Once
                        activities.forEach(act => {
                            const tempDiv = document.createElement('div');
                            tempDiv.innerHTML = createCardHTML(act);
                            grid.appendChild(tempDiv.firstElementChild);
                        });
                        isFirstLoad = false;

                        // Small delay to allow DOM to paint before animating bars up
                        setTimeout(() => updateAllPodiums(activities), 100);
                    } else {
                        updateAllPodiums(activities);
                    }

                } catch (error) {
                    console.error("Live fetch error:", error);
                }
            }

            function updateAllPodiums(activities) {
                activities.forEach(act => {
                    const podium = act.podium || [];
                    // Update 1st, 2nd, 3rd
                    // Data comes sorted by points DESC, so index 0 = 1st, 1 = 2nd...

                    // Position 1 (Array index 0)
                    updateSlot(act.id, 1, podium[0], act.mode === 'TEAM');

                    // Position 2 (Array index 1)
                    updateSlot(act.id, 2, podium[1], act.mode === 'TEAM');

                    // Position 3 (Array index 2)
                    updateSlot(act.id, 3, podium[2], act.mode === 'TEAM');
                });
            }

            function escapeHtml(text) {
                if (!text) return '';
                return text
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }

            // Init
            fetchAndRender();

            // Loop
            setInterval(fetchAndRender, REFRESH_RATE);
        });
    </script>
@endpush
