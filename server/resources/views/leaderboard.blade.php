@extends('app')

@section('title', "P'AS'SION BDS - Classement")
@section('meta_description', "Classement des meilleurs joueurs : précampagne et campagne confondues.")

@push('styles')
    <style>
        /* Leaderboard Animations */
        .leaderboard-item {
            transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1),
            background 0.4s ease,
            border-color 0.4s ease,
            box-shadow 0.4s ease,
            scale 0.4s ease; /* Smooth morphing */
            will-change: transform, top, left;
        }

        .points-pop-anim {
            animation: floatUpFade 0.8s ease-out forwards;
            pointer-events: none;
            text-shadow: 0 2px 0 rgba(255, 255, 255, 0.8);
        }

        /* New Player Slide In */
        .new-player-anim {
            animation: slideInLeft 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateX(0) scale(1);
            }
        }

        /* Rank Styles */
        .rank-1 {
            background: linear-gradient(135deg, #FACC15, #CA8A04);
            border-color: #FEF08A;
            color: black;
            z-index: 30;
        }

        .rank-2 {
            background: linear-gradient(135deg, #D1D5DB, #9CA3AF);
            border-color: #F3F4F6;
            color: black;
            z-index: 29;
        }

        .rank-3 {
            background: linear-gradient(135deg, #d97706, #92400e);
            border-color: #FFEDD5;
            color: white;
            z-index: 28;
        }

        .rank-default {
            background: white;
            border-color: #F472B6;
            color: #9B1237;
            z-index: 1;
        }

        /* Toast Notifications */
        #toast-container {
            position: fixed;
            bottom: 80px;
            left: 10px;
            z-index: 100;
            display: flex;
            flex-direction: column-reverse;
            gap: 8px;
            pointer-events: none;
        }

        .toast {
            background: #22c55e;
            color: white;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            animation: toastEnter 0.3s ease-out forwards;
            display: flex;
            align-items: center;
            gap: 6px;
            width: fit-content;
        }

        .toast.hiding {
            animation: toastExit 0.3s ease-in forwards;
        }

        @keyframes toastEnter {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.8);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes toastExit {
            to {
                opacity: 0;
                transform: translateX(-20px);
            }
        }

        @keyframes floatUpFade {
            0% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
            50% {
                transform: translateY(-25px) scale(1.4);
            }
            100% {
                opacity: 0;
                transform: translateY(-50px) scale(1);
            }
        }
    </style>
@endpush

@section('content')
    <div class="text-center mb-8">
        <h1 class="font-display font-black text-4xl md:text-5xl uppercase tracking-tighter text-passion-red mb-2 drop-shadow-sm">
            Classement <span class="text-passion-fire-orange">Live</span>
        </h1>
        <p class="text-passion-pink-500 font-bold text-sm uppercase tracking-widest bg-white/60 inline-block px-4 py-1 rounded-full">
            Mise à jour en temps réel
        </p>
    </div>

    <!-- Leaderboard List -->
    <div id="leaderboard-list"
         class="flex flex-col gap-3 relative pb-8 min-h-[400px] lg:min-w-[50%] md:min-w-[70%] min-w-[90%]">
        <!-- Items injected by JS -->
    </div>
@endsection

@push('end_scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // -----------------------------
            // Config
            // -----------------------------
            const leaderboardApiUrl = '/api/leaderboard?limit=200';
            const refreshIntervalMs = 2500;

            // -----------------------------
            // State
            // -----------------------------
            let players = [];
            let lastSnapshotSignature = '';
            let isRefreshing = false;
            let queuedRefresh = false;

            const listContainer = document.getElementById('leaderboard-list');

            // -----------------------------
            // Render
            // -----------------------------
            function renderList(newPlayerIds = []) {
                // Keep deterministic order: API already returns ranked, but we still protect here
                players.sort((a, b) => b.points - a.points);

                listContainer.innerHTML = '';

                players.forEach((player, index) => {
                    const rank = index + 1;

                    let rankClass = "rank-default border";
                    let textClass = "text-passion-red";
                    let rankBadge = `<span class="font-mono opacity-50 font-bold">#${rank}</span>`;
                    let scaleClass = "";

                    if (rank === 1) {
                        rankClass = "rank-1 shadow-[0_0_20px_rgba(250,204,21,0.5)] border-2";
                        textClass = "text-black font-black";
                        rankBadge = "🏆";
                        scaleClass = "scale-[1.03]";
                    } else if (rank === 2) {
                        rankClass = "rank-2 shadow-md border-2";
                        textClass = "text-black font-bold";
                        rankBadge = "🥈";
                        scaleClass = "scale-[1.01]";
                    } else if (rank === 3) {
                        rankClass = "rank-3 shadow-md border-2";
                        textClass = "text-white font-bold";
                        rankBadge = "🥉";
                        scaleClass = "scale-[1.01]";
                    }

                    // Highlight current user row
                    if (player.isUser) {
                        rankClass += " border-4 border-passion-fire-yellow";
                    }

                    const animClass = newPlayerIds.includes(player.id) ? 'new-player-anim' : '';

                    const item = document.createElement('div');
                    item.className = `leaderboard-item relative flex items-center justify-between p-4 rounded-xl ${rankClass} ${scaleClass} ${animClass} transition-all duration-500`;
                    item.setAttribute('data-id', String(player.id));

                    // Keep your transition override (same as your code)
                    item.style.transition =
                        "transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), " +
                        "background 0.4s ease, border-color 0.4s ease, box-shadow 0.4s ease, scale 0.4s ease";

                    item.innerHTML = `
                <div class="flex items-center lg:gap-4 sm:gap-2 gap-0">
                    <div class="w-8 text-center text-xl font-bold">${rankBadge}</div>
                    <div class="text-2xl md:inline d-hidden">${player.avatar ?? '👤'}</div>
                    <div class="flex flex-col">
                        <span class="${textClass} text-lg uppercase tracking-tight font-bold podium-name">${escapeHtml(player.name)}</span>
                        ${player.isUser ? '<span class="text-[10px] uppercase font-bold tracking-widest opacity-70">C\'est toi !</span>' : ''}
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="points-display font-mono font-bold text-xl ${rank === 3 ? 'text-white' : 'text-passion-red'}">${player.points}</span>
                    <span class="text-xs opacity-60 uppercase font-bold ${rank === 3 ? 'text-white' : 'text-passion-red'}">pts</span>
                </div>
            `;

                    listContainer.appendChild(item);
                });
            }

            // -----------------------------
            // Toast
            // -----------------------------
            function showToast(text) {
                const container = document.getElementById('toast-container');
                if (!container) return;

                const toast = document.createElement('div');
                toast.className = 'toast';
                toast.innerHTML = `<span>👋</span> ${escapeHtml(text)}`;
                container.appendChild(toast);

                setTimeout(() => {
                    toast.classList.add('hiding');
                    toast.addEventListener('animationend', () => toast.remove(), {once: true});
                }, 3000);
            }

            // -----------------------------
            // FLIP update (your logic, upgraded for multiple point changes)
            // -----------------------------
            function applyVisualUpdate(previousPlayers, newPlayers) {
                const previousPointsById = new Map(previousPlayers.map(p => [p.id, p.points]));
                const previousIds = new Set(previousPlayers.map(p => p.id));

                const newPlayerIds = newPlayers
                    .filter(p => !previousIds.has(p.id))
                    .map(p => p.id);

                // Capture old positions BEFORE re-render
                const oldPositions = new Map();
                document.querySelectorAll('.leaderboard-item').forEach(item => {
                    oldPositions.set(item.getAttribute('data-id'), item.getBoundingClientRect().top);
                });

                // Replace state + render new list
                players = newPlayers;
                renderList(newPlayerIds);

                // Apply transforms and point pops
                document.querySelectorAll('.leaderboard-item').forEach(item => {
                    const idString = item.getAttribute('data-id');
                    if (!idString) return;

                    const id = Number(idString);
                    if (newPlayerIds.includes(id)) return; // new players already have slide-in anim

                    const oldTop = oldPositions.get(idString);
                    const newTop = item.getBoundingClientRect().top;

                    // Points delta pop
                    const oldPoints = previousPointsById.get(id);
                    const newPoints = newPlayers.find(p => p.id === id)?.points;

                    if (typeof oldPoints === 'number' && typeof newPoints === 'number') {
                        const deltaPoints = newPoints - oldPoints;

                        if (deltaPoints !== 0) {
                            const pop = document.createElement('div');
                            const popColorClass = deltaPoints > 0 ? 'text-green-500' : 'text-red-500';
                            const sign = deltaPoints > 0 ? '+' : '';

                            pop.className = `absolute right-14 top-1 ${popColorClass} font-black text-xl points-pop-anim z-50`;
                            pop.innerText = `${sign}${deltaPoints}`;
                            item.appendChild(pop);

                            item.style.filter = "brightness(1.3)";
                            setTimeout(() => item.style.filter = "", 300);
                        }
                    }

                    // FLIP position animation
                    if (oldTop !== undefined) {
                        const delta = oldTop - newTop;
                        if (delta !== 0) {
                            item.style.transform = `translateY(${delta}px)`;
                            item.style.transition = 'none';
                            void item.offsetHeight;

                            item.style.transition =
                                'transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), ' +
                                'background 0.4s ease, border-color 0.4s ease, box-shadow 0.4s ease, scale 0.4s ease';

                            item.style.transform = '';
                        }
                    }
                });

                // Optional: announce new players (only if you like it)
                newPlayerIds.forEach(id => {
                    const p = newPlayers.find(x => x.id === id);
                    if (p) showToast(`${p.name} a rejoint le game !`);
                });
            }

            // -----------------------------
            // Live fetch
            // -----------------------------
            async function fetchLeaderboard() {
                const response = await fetch(leaderboardApiUrl, {
                    method: 'GET',
                    headers: {'Accept': 'application/json'},
                    credentials: 'same-origin',
                });

                if (!response.ok) throw new Error('LEADERBOARD_FETCH_FAILED');

                const payload = await response.json();
                return Array.isArray(payload?.data) ? payload.data : [];
            }

            function computeSnapshotSignature(rows) {
                // Minimal signature: rank order by id+points
                // (avoid re-render if nothing changed)
                return rows.map(r => `${r.id}:${r.points}`).join('|');
            }

            async function refreshLeaderboard() {
                if (isRefreshing) {
                    queuedRefresh = true;
                    return;
                }

                isRefreshing = true;

                try {
                    const newPlayers = await fetchLeaderboard();
                    const signature = computeSnapshotSignature(newPlayers);

                    if (signature === lastSnapshotSignature) return;
                    lastSnapshotSignature = signature;

                    const previousPlayers = players.slice();
                    if (previousPlayers.length === 0) {
                        players = newPlayers;
                        renderList([]);
                    } else {
                        applyVisualUpdate(previousPlayers, newPlayers);
                    }
                } catch (e) {
                    // Keep silent: it’s a “live” UI; don’t spam users on small hiccups
                    // If you want debug: console.warn(e);
                } finally {
                    isRefreshing = false;
                    if (queuedRefresh) {
                        queuedRefresh = false;
                        await refreshLeaderboard();
                    }
                }
            }

            // -----------------------------
            // Helpers
            // -----------------------------
            function escapeHtml(value) {
                const div = document.createElement('div');
                div.textContent = value ?? '';
                return div.innerHTML;
            }

            // Init + poll
            refreshLeaderboard();
            setInterval(refreshLeaderboard, refreshIntervalMs);
        });
    </script>

@endpush
