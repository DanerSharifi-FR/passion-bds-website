@extends('admin.layout')

@section('title', "Activité - Joueurs | P'AS'SION BDS")

@php
    /** @var \App\Models\Activity $activity */
    $isTeamMode = ($activity->mode ?? 'INDIVIDUAL') === 'TEAM';
@endphp

@section('content')
    <div class="mb-6 flex flex-col gap-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('admin.activities') }}"
                       class="text-slate-400 hover:text-white transition-colors text-sm">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Retour aux activités
                    </a>

                    <span class="text-slate-600">•</span>

                    <span class="text-xs font-semibold px-2 py-1 rounded border
                        {{ $activity->is_active ? 'bg-green-500/10 text-green-300 border-green-500/20' : 'bg-slate-700 text-slate-300 border-slate-600' }}">
                        {{ $activity->is_active ? 'ACTIF' : 'INACTIF' }}
                    </span>

                    <span class="text-xs font-semibold px-2 py-1 rounded border
                        {{ $isTeamMode ? 'bg-yellow-500/10 text-yellow-300 border-yellow-500/20' : 'bg-indigo-500/10 text-indigo-300 border-indigo-500/20' }}">
                        {{ $isTeamMode ? 'ÉQUIPES' : 'INDIVIDUEL' }}
                    </span>
                </div>

                <h2 class="text-2xl font-bold text-white">{{ $activity->title }}</h2>

                <p class="text-slate-400 mt-1">
                    Gestion des joueurs{{ $isTeamMode ? ' et des équipes' : '' }}
                    — points = <span class="font-semibold text-slate-200">{{ $activity->points_label }}</span>
                    <span class="text-slate-600 mx-2">•</span>
                    <span class="text-slate-500 text-sm">Astuce: double-clic sur un score pour l’éditer.</span>
                </p>
            </div>

            <div class="flex gap-2">
                @if($isTeamMode)
                    <button id="btnOpenCreateTeamModal"
                            class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg text-sm font-bold transition-colors shadow-lg flex items-center">
                        <i class="fa-solid fa-flag mr-2"></i> Créer Équipe
                    </button>
                @endif

                <button id="btnOpenAddPlayerModal"
                        class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg text-sm font-bold transition-colors shadow-lg shadow-yellow-500/20 flex items-center">
                    <i class="fa-solid fa-user-plus mr-2"></i> Ajouter Joueur
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 {{ $isTeamMode ? 'lg:grid-cols-3' : '' }} gap-4">
            <div class="{{ $isTeamMode ? 'lg:col-span-2' : '' }} bg-slate-800 rounded-xl border border-slate-700 shadow-lg overflow-hidden">
                <div class="p-4 border-b border-slate-700 bg-slate-900/30">
                    <div class="flex flex-col md:flex-row gap-3 md:items-center md:justify-between">
                        <div class="flex-1 relative">
                            <i class="fa-solid fa-search absolute left-3 top-3.5 text-slate-500"></i>
                            <input type="text" id="playersSearchInput"
                                   class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg focus:ring-yellow-500 focus:border-yellow-500 block pl-10 p-3"
                                   placeholder="Rechercher un joueur (nom / email)...">
                        </div>

                        @if($isTeamMode)
                            <div class="w-full md:w-56">
                                <select id="teamFilterSelect"
                                        class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg focus:ring-yellow-500 focus:border-yellow-500 block p-3">
                                    <option value="">Toutes équipes</option>
                                    <option value="none">Sans équipe</option>
                                </select>
                            </div>
                        @endif

                        <div class="flex gap-2">
                            <button id="btnReload"
                                    class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg text-sm font-bold transition-colors">
                                <i class="fa-solid fa-rotate mr-2"></i> Rafraîchir
                            </button>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-400">
                        <thead class="bg-slate-900/50 text-slate-200 uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-6 py-4">Joueur</th>
                            <th class="px-6 py-4 text-center">{{ $activity->points_label }}</th>
                            @if($isTeamMode)
                                <th class="px-6 py-4">Équipe</th>
                            @endif
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700" id="playersTableBody"></tbody>
                    </table>
                </div>

                <div class="bg-slate-800 px-4 py-3 border-t border-slate-700 flex items-center justify-between sm:px-6">
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-slate-400">
                                Page <span id="paginationCurrentPage" class="font-medium text-white">1</span>
                                sur <span id="paginationLastPage" class="font-medium text-white">1</span>
                            </p>
                        </div>

                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <button type="button" id="paginationPrevBtn"
                                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-slate-600 bg-slate-800 text-sm font-medium text-slate-400 hover:bg-slate-700">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                          d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                          clip-rule="evenodd"/>
                                </svg>
                            </button>

                            <button type="button" id="paginationNextBtn"
                                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-slate-600 bg-slate-800 text-sm font-medium text-slate-400 hover:bg-slate-700">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                          d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                          clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </nav>
                    </div>
                </div>
            </div>

            @if($isTeamMode)
                <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-lg overflow-hidden">
                    <div class="p-4 border-b border-slate-700 bg-slate-900/30">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-white font-bold">Équipes</div>
                                <div class="text-xs text-slate-400">Liste + stats rapides</div>
                            </div>
                            <span id="teamsApiBadge"
                                  class="text-[10px] font-bold px-2 py-1 rounded border bg-slate-700 text-slate-300 border-slate-600">
                                API TEAMS: ?
                            </span>
                        </div>
                    </div>

                    <div id="teamsPanel" class="p-4 space-y-3">
                        <div class="text-sm text-slate-500">Chargement…</div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('end_scripts')
    <div id="toastContainer" class="fixed top-4 right-4 z-[9999] space-y-2 pointer-events-none"></div>

    {{-- Add player modal --}}
    <div id="addPlayerModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/80 transition-opacity" aria-hidden="true" id="addPlayerBackdrop"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="relative inline-block align-bottom bg-slate-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-slate-700">
                <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-900/50 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fa-solid fa-user-plus text-yellow-400"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-white" id="modal-title">Ajouter un joueur</h3>
                            <div class="mt-2">
                                <p class="text-sm text-slate-400 mb-4">
                                    Cherche un étudiant, sélectionne-le, puis ajoute-le à l’activité.
                                </p>

                                <form id="addPlayerForm" class="space-y-4" onsubmit="return false;">
                                    <div class="relative">
                                        <label class="block text-sm font-medium text-slate-300 mb-1">Étudiant</label>
                                        <input type="text" id="playerSearchInput"
                                               class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg focus:ring-yellow-500 focus:border-yellow-500 block p-2.5"
                                               placeholder="Rechercher par nom..." autocomplete="off">
                                        <input type="hidden" id="selectedPlayerId">

                                        <div id="playerSearchResults"
                                             class="absolute z-50 w-full bg-slate-800 border border-slate-600 rounded-lg mt-1 hidden max-h-56 overflow-y-auto shadow-xl divide-y divide-slate-700">
                                        </div>
                                    </div>

                                    <div id="selectedPlayerInfo" class="hidden p-3 bg-slate-900/40 rounded-lg border border-slate-700">
                                        <div class="flex items-center justify-between">
                                            <div class="flex flex-col">
                                                <span class="text-sm text-white font-semibold" id="selectedPlayerName">—</span>
                                                <span class="text-xs text-slate-400" id="selectedPlayerEmail">—</span>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-[10px] text-slate-500">{{ $activity->points_label }}</div>
                                                <div class="text-sm font-mono font-bold text-yellow-300" id="selectedPlayerPoints">0</div>
                                            </div>
                                        </div>
                                    </div>

                                    @if($isTeamMode)
                                        <div id="addPlayerTeamBlock" class="hidden">
                                            <label class="block text-sm font-medium text-slate-300 mb-1">Équipe</label>
                                            <select id="addPlayerTeamSelect"
                                                    class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg focus:ring-yellow-500 focus:border-yellow-500 block p-2.5">
                                                <option value="">Sans équipe</option>
                                            </select>
                                            <p class="text-xs text-slate-500 mt-1">
                                                Si tu ne vois pas d’équipes, crée-en une d’abord.
                                            </p>
                                        </div>
                                    @endif
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-700">
                    <button type="button" id="btnSubmitAddPlayer"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Ajouter
                    </button>
                    <button type="button" id="btnCloseAddPlayer"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-slate-600 shadow-sm px-4 py-2 bg-slate-800 text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Create team modal --}}
    @if($isTeamMode)
        <div id="createTeamModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/80 transition-opacity" aria-hidden="true" id="createTeamBackdrop"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="relative inline-block align-bottom bg-slate-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-slate-700">
                    <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-slate-700/50 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fa-solid fa-flag text-slate-200"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-white">Créer une équipe</h3>
                                <div class="mt-2">
                                    <form id="createTeamForm" class="space-y-4" onsubmit="return false;">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-300 mb-1">Nom de l’équipe</label>
                                            <input type="text" id="teamTitleInput"
                                                   minlength="2" maxlength="150" required
                                                   class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5"
                                                   placeholder="Ex: Les Titans">
                                            <p class="text-xs text-slate-500 mt-1">2 caractères minimum.</p>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-700">
                        <button type="button" id="btnSubmitCreateTeam"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Créer
                        </button>
                        <button type="button" id="btnCloseCreateTeam"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-slate-600 shadow-sm px-4 py-2 bg-slate-800 text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Annuler
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // ----------------------------------------
            // Config
            // ----------------------------------------
            const ACTIVITY_ID = {{ (int) $activity->id }};
            const IS_TEAM_MODE = @json($isTeamMode);

            const participantsApiUrl = `/admin/api/activities/${ACTIVITY_ID}/participants`;
            const participantsSearchApiUrl = `/admin/api/activities/${ACTIVITY_ID}/participants/search`;
            const studentsSearchApiUrl = `/admin/api/students`;

            // Teams API (optionnel)
            const teamsApiUrl = `/admin/api/activities/${ACTIVITY_ID}/teams`;

            // ⚠️ Endpoint à implémenter côté backend:
            // - crée une point_transaction (delta) pour arriver au total demandé.
            // /admin/api/activities/{activity}/participants/{userId}/points
            const pointsApiUrl = `/admin/api/activities/${ACTIVITY_ID}`;

            const perPage = 25;

            // ----------------------------------------
            // Elements
            // ----------------------------------------
            const playersTableBodyElement = document.getElementById('playersTableBody');
            const playersSearchInputElement = document.getElementById('playersSearchInput');
            const reloadButtonElement = document.getElementById('btnReload');

            const paginationCurrentPageElement = document.getElementById('paginationCurrentPage');
            const paginationLastPageElement = document.getElementById('paginationLastPage');
            const paginationPrevButtonElement = document.getElementById('paginationPrevBtn');
            const paginationNextButtonElement = document.getElementById('paginationNextBtn');

            const addPlayerModalElement = document.getElementById('addPlayerModal');
            const addPlayerBackdropElement = document.getElementById('addPlayerBackdrop');
            const openAddPlayerButtonElement = document.getElementById('btnOpenAddPlayerModal');
            const closeAddPlayerButtonElement = document.getElementById('btnCloseAddPlayer');
            const submitAddPlayerButtonElement = document.getElementById('btnSubmitAddPlayer');

            const playerSearchInputElement = document.getElementById('playerSearchInput');
            const selectedPlayerIdElement = document.getElementById('selectedPlayerId');
            const playerSearchResultsElement = document.getElementById('playerSearchResults');

            const selectedPlayerInfoElement = document.getElementById('selectedPlayerInfo');
            const selectedPlayerNameElement = document.getElementById('selectedPlayerName');
            const selectedPlayerEmailElement = document.getElementById('selectedPlayerEmail');
            const selectedPlayerPointsElement = document.getElementById('selectedPlayerPoints');

            const toastContainerElement = document.getElementById('toastContainer');

            // Team UI (optional)
            const teamsPanelElement = document.getElementById('teamsPanel');
            const teamsApiBadgeElement = document.getElementById('teamsApiBadge');
            const teamFilterSelectElement = document.getElementById('teamFilterSelect');

            const openCreateTeamButtonElement = document.getElementById('btnOpenCreateTeamModal');
            const createTeamModalElement = document.getElementById('createTeamModal');
            const createTeamBackdropElement = document.getElementById('createTeamBackdrop');
            const closeCreateTeamButtonElement = document.getElementById('btnCloseCreateTeam');
            const submitCreateTeamButtonElement = document.getElementById('btnSubmitCreateTeam');
            const teamTitleInputElement = document.getElementById('teamTitleInput');

            const addPlayerTeamBlockElement = document.getElementById('addPlayerTeamBlock');
            const addPlayerTeamSelectElement = document.getElementById('addPlayerTeamSelect');

            // ----------------------------------------
            // State
            // ----------------------------------------
            let currentPageNumber = 1;
            let lastPageNumber = 1;

            let pendingParticipantsFetchAbortController = null;
            let pendingSearchFetchAbortController = null;

            let teamsApiAvailable = false;
            let teamsCache = []; // [{id,title,members_count,points_total}]

            // Filtres
            let currentTeamFilterValue = (teamFilterSelectElement?.value || '').trim(); // '' | 'none' | '123'

            // ----------------------------------------
            // Utilities
            // ----------------------------------------
            function csrfToken() {
                return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            }

            function escapeHtml(value) {
                const div = document.createElement('div');
                div.textContent = value ?? '';
                return div.innerHTML;
            }

            function showToast(message, type = 'success') {
                const toastElement = document.createElement('div');
                const backgroundClass = type === 'success' ? 'bg-green-600' : 'bg-red-600';
                const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-circle-exclamation';

                toastElement.className = `flex items-center p-4 rounded shadow-lg text-white ${backgroundClass} pointer-events-auto opacity-0 translate-y-2 transition-all duration-200`;
                toastElement.innerHTML = `<i class="fa-solid ${iconClass} text-lg mr-3"></i><span class="text-sm font-medium">${escapeHtml(message)}</span>`;
                toastContainerElement.appendChild(toastElement);

                requestAnimationFrame(() => {
                    toastElement.classList.remove('opacity-0', 'translate-y-2');
                    toastElement.classList.add('opacity-100', 'translate-y-0');
                });

                window.setTimeout(() => {
                    toastElement.classList.remove('opacity-100', 'translate-y-0');
                    toastElement.classList.add('opacity-0', 'translate-y-2');
                    window.setTimeout(() => toastElement.remove(), 250);
                }, 3000);
            }

            async function fetchJson(url, options = {}) {
                const response = await fetch(url, {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        ...(options.headers || {}),
                    },
                    ...options,
                });

                const contentType = response.headers.get('content-type') || '';
                const isJson = contentType.includes('application/json');

                const payload = isJson ? await response.json().catch(() => null) : null;

                if (!response.ok) {
                    const error = new Error(payload?.message || 'HTTP_ERROR');
                    error.status = response.status;
                    error.payload = payload;
                    throw error;
                }

                return payload;
            }

            function debounce(fn, delayMs) {
                let timeoutId = null;
                return (...args) => {
                    if (timeoutId) window.clearTimeout(timeoutId);
                    timeoutId = window.setTimeout(() => fn(...args), delayMs);
                };
            }

            // ----------------------------------------
            // Pagination UI
            // ----------------------------------------
            function updatePaginationUi() {
                paginationCurrentPageElement.textContent = String(currentPageNumber);
                paginationLastPageElement.textContent = String(lastPageNumber);

                const prevDisabled = currentPageNumber <= 1;
                const nextDisabled = currentPageNumber >= lastPageNumber;

                paginationPrevButtonElement.disabled = prevDisabled;
                paginationNextButtonElement.disabled = nextDisabled;

                paginationPrevButtonElement.classList.toggle('opacity-50', prevDisabled);
                paginationPrevButtonElement.classList.toggle('cursor-not-allowed', prevDisabled);

                paginationNextButtonElement.classList.toggle('opacity-50', nextDisabled);
                paginationNextButtonElement.classList.toggle('cursor-not-allowed', nextDisabled);
            }

            // ----------------------------------------
            // Teams (optional)
            // ----------------------------------------
            function setTeamsApiBadge(ok) {
                if (!teamsApiBadgeElement) return;
                teamsApiBadgeElement.textContent = ok ? 'API TEAMS: OK' : 'API TEAMS: OFF';
                teamsApiBadgeElement.className = ok
                    ? 'text-[10px] font-bold px-2 py-1 rounded border bg-green-500/10 text-green-300 border-green-500/20'
                    : 'text-[10px] font-bold px-2 py-1 rounded border bg-slate-700 text-slate-300 border-slate-600';
            }

            function fillTeamsSelect() {
                if (!IS_TEAM_MODE || !addPlayerTeamSelectElement) return;

                addPlayerTeamSelectElement.innerHTML = `<option value="">Sans équipe</option>`;
                teamsCache.forEach((t) => {
                    const opt = document.createElement('option');
                    opt.value = String(t.id);
                    opt.textContent = t.title;
                    addPlayerTeamSelectElement.appendChild(opt);
                });

                if (addPlayerTeamBlockElement) {
                    addPlayerTeamBlockElement.classList.toggle('hidden', !teamsApiAvailable);
                }
            }

            function fillTeamFilterSelect() {
                if (!IS_TEAM_MODE || !teamFilterSelectElement) return;

                const keepValue = currentTeamFilterValue;

                // On reset mais on garde les 2 options de base
                teamFilterSelectElement.innerHTML = `
                    <option value="">Toutes équipes</option>
                    <option value="none">Sans équipe</option>
                `;

                if (teamsApiAvailable && teamsCache.length) {
                    teamsCache.forEach((t) => {
                        const opt = document.createElement('option');
                        opt.value = String(t.id);
                        opt.textContent = t.title;
                        teamFilterSelectElement.appendChild(opt);
                    });
                }

                // restore value si possible
                teamFilterSelectElement.value = keepValue;
            }

            function renderTeamsPanel() {
                if (!IS_TEAM_MODE || !teamsPanelElement) return;

                if (!teamsApiAvailable) {
                    teamsPanelElement.innerHTML = `
                        <div class="text-sm text-slate-500">
                            La gestion des équipes n’est pas encore branchée côté API.
                        </div>
                        <div class="text-xs text-slate-600">
                            On l’active ensuite (routes + controller teams).
                        </div>
                    `;
                    return;
                }

                if (!teamsCache.length) {
                    teamsPanelElement.innerHTML = `
                        <div class="text-sm text-slate-500">Aucune équipe.</div>
                        <div class="text-xs text-slate-600">Crée une équipe pour pouvoir assigner des joueurs.</div>
                    `;
                    return;
                }

                teamsPanelElement.innerHTML = '';
                teamsCache.forEach((t) => {
                    const membersCount = Number(t.members_count ?? 0);
                    const pointsTotal = Number(t.points_total ?? 0);

                    const card = document.createElement('div');
                    card.className = 'p-3 rounded-lg border border-slate-700 bg-slate-900/30';

                    card.innerHTML = `
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-white font-bold truncate">${escapeHtml(t.title)}</div>
                                <div class="text-xs text-slate-400 mt-1">
                                    <span class="font-semibold text-slate-200">${membersCount}</span> membre(s)
                                    <span class="text-slate-600 mx-1">•</span>
                                    Total: <span class="font-mono font-bold text-yellow-300">${escapeHtml(String(pointsTotal))}</span>
                                </div>
                            </div>

                            <button class="text-red-300 hover:text-red-200 bg-red-500/10 px-2.5 py-1.5 rounded hover:bg-red-500/20 transition-colors text-xs font-bold"
                                    data-team-delete="${escapeHtml(String(t.id))}">
                                <i class="fa-solid fa-trash mr-1"></i> Suppr
                            </button>
                        </div>
                    `;

                    teamsPanelElement.appendChild(card);
                });

                teamsPanelElement.querySelectorAll('[data-team-delete]').forEach((btn) => {
                    btn.addEventListener('click', async () => {
                        const teamId = btn.getAttribute('data-team-delete');
                        if (!teamId) return;

                        if (!confirm("Supprimer l’équipe ? (les joueurs seront désassignés)")) return;

                        try {
                            await fetchJson(`${teamsApiUrl}/${teamId}`, {
                                method: 'DELETE',
                                headers: {'X-CSRF-TOKEN': csrfToken()},
                            });
                            showToast("Équipe supprimée.", "success");
                            await loadTeams();
                            await loadParticipantsPage(1);
                        } catch (e) {
                            showToast(e.payload?.message || "Erreur suppression équipe.", "error");
                        }
                    });
                });
            }

            async function loadTeams() {
                if (!IS_TEAM_MODE) return;

                try {
                    const res = await fetchJson(teamsApiUrl, { method: 'GET' });
                    teamsApiAvailable = true;
                    setTeamsApiBadge(true);

                    teamsCache = Array.isArray(res?.data) ? res.data : [];
                    fillTeamsSelect();
                    fillTeamFilterSelect();
                    renderTeamsPanel();
                } catch (e) {
                    teamsApiAvailable = false;
                    setTeamsApiBadge(false);
                    teamsCache = [];
                    fillTeamsSelect();
                    fillTeamFilterSelect();
                    renderTeamsPanel();
                }
            }

            // ----------------------------------------
            // Points inline edit
            // ----------------------------------------
            function attachInlinePointsEditorHandlers() {
                playersTableBodyElement.querySelectorAll('[data-points-cell]').forEach((cell) => {
                    cell.addEventListener('dblclick', () => {
                        const userId = Number(cell.getAttribute('data-points-user') || 0);
                        const currentPoints = Number(cell.getAttribute('data-points-value') || 0);
                        if (!userId) return;
                        enterPointsEditMode(cell, userId, currentPoints);
                    });
                });
            }

            function enterPointsEditMode(cellElement, userId, currentPoints) {
                if (cellElement.getAttribute('data-editing') === '1') return;
                cellElement.setAttribute('data-editing', '1');

                const originalHtml = cellElement.innerHTML;

                const wrap = document.createElement('div');
                wrap.className = 'flex items-center justify-center gap-2';

                const input = document.createElement('input');
                input.type = 'number';
                input.step = '1';
                input.className = 'w-24 bg-slate-900 border border-slate-600 text-white text-sm rounded-lg p-2 text-center focus:ring-yellow-500 focus:border-yellow-500';
                input.value = String(currentPoints);

                const btnSave = document.createElement('button');
                btnSave.type = 'button';
                btnSave.className = 'px-2 py-2 rounded bg-green-500/10 hover:bg-green-500/20 text-green-200 border border-green-500/20';
                btnSave.innerHTML = '<i class="fa-solid fa-check"></i>';

                const btnCancel = document.createElement('button');
                btnCancel.type = 'button';
                btnCancel.className = 'px-2 py-2 rounded bg-red-500/10 hover:bg-red-500/20 text-red-200 border border-red-500/20';
                btnCancel.innerHTML = '<i class="fa-solid fa-xmark"></i>';

                wrap.appendChild(input);
                wrap.appendChild(btnSave);
                wrap.appendChild(btnCancel);

                cellElement.innerHTML = '';
                cellElement.appendChild(wrap);

                function exit(restore = true) {
                    cellElement.removeAttribute('data-editing');
                    if (restore) cellElement.innerHTML = originalHtml;
                }

                async function save() {
                    const nextValue = Number((input.value || '').trim());
                    if (!Number.isFinite(nextValue)) {
                        showToast("Valeur invalide.", "error");
                        exit(true);
                        return;
                    }
                    if (nextValue === currentPoints) {
                        exit(true);
                        return;
                    }

                    btnSave.disabled = true;
                    btnCancel.disabled = true;
                    input.disabled = true;
                    btnSave.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';

                    try {
                        // /admin/api/activities/{activity}/participants/{userId}/points
                        const res = await fetchJson(pointsApiUrl + '/participants/' + userId + '/points', {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken(),
                            },
                            body: JSON.stringify({
                                user_id: userId,
                                target_points: nextValue,
                                reason: "Ajustement manuel",
                            }),
                        });

                        const newPoints =
                            Number(res?.data?.points ?? res?.data?.points_total ?? res?.data?.new_points ?? nextValue);

                        cellElement.setAttribute('data-points-value', String(newPoints));
                        cellElement.classList.remove('data-editing');

                        showToast("Score mis à jour.", "success");

                        // reload propre (ça resynchronise aussi les équipes/total)
                        await loadParticipantsPage(currentPageNumber);
                        if (IS_TEAM_MODE) await loadTeams();
                    } catch (e) {
                        if (e.status === 403) showToast("Accès refusé.", "error");
                        else showToast(e.payload?.message || "Erreur update points.", "error");
                        exit(true);
                    }
                }

                btnSave.addEventListener('click', save);
                btnCancel.addEventListener('click', () => exit(true));

                input.addEventListener('keydown', (ev) => {
                    if (ev.key === 'Enter') save();
                    if (ev.key === 'Escape') exit(true);
                });

                // UX: blur => save
                input.addEventListener('blur', () => {
                    // petit timeout pour permettre click sur cancel/save sans déclencher blur-save foireux
                    window.setTimeout(() => {
                        if (cellElement.getAttribute('data-editing') === '1') save();
                    }, 120);
                });

                input.focus();
                input.select();
            }

            // ----------------------------------------
            // Participants table
            // ----------------------------------------
            function renderParticipantsRows(participants) {
                playersTableBodyElement.innerHTML = '';

                if (!participants || participants.length === 0) {
                    const emptyRow = document.createElement('tr');
                    emptyRow.innerHTML = `
                        <td class="px-6 py-6 text-center text-slate-500" colspan="${IS_TEAM_MODE ? 4 : 3}">
                            Aucun joueur.
                        </td>
                    `;
                    playersTableBodyElement.appendChild(emptyRow);
                    return;
                }

                participants.forEach((p) => {
                    const userId = Number(p.user_id ?? p.id ?? 0);
                    const name = p.name ?? p.display_name ?? p.user_name ?? '—';
                    const email = p.email ?? p.university_email ?? p.user_email ?? '—';
                    const points = Number(p.points ?? p.points_total ?? 0);

                    const teamId = p.team_id ?? p.team?.id ?? null;
                    const teamTitle = p.team_title ?? p.team?.title ?? null;

                    const rowElement = document.createElement('tr');
                    rowElement.className = 'hover:bg-slate-700/50 transition-colors';

                    let teamCellHtml = '';
                    if (IS_TEAM_MODE) {
                        if (teamsApiAvailable) {
                            const optionsHtml = [
                                `<option value="">Sans équipe</option>`,
                                ...teamsCache.map(t => {
                                    const selected = String(t.id) === String(teamId) ? 'selected' : '';
                                    return `<option value="${escapeHtml(String(t.id))}" ${selected}>${escapeHtml(t.title)}</option>`;
                                })
                            ].join('');

                            teamCellHtml = `
                                <td class="px-6 py-4">
                                    <select class="bg-slate-900 border border-slate-600 text-white text-sm rounded-lg p-2.5 w-full"
                                            data-team-select="${escapeHtml(String(userId))}">
                                        ${optionsHtml}
                                    </select>
                                    <div class="text-[10px] text-slate-500 mt-1">
                                        Change l’équipe puis ça sauvegarde.
                                    </div>
                                </td>
                            `;
                        } else {
                            teamCellHtml = `
                                <td class="px-6 py-4">
                                    <span class="text-slate-300">${escapeHtml(teamTitle || '—')}</span>
                                    <div class="text-[10px] text-slate-600 mt-1">Teams API off</div>
                                </td>
                            `;
                        }
                    }

                    rowElement.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-white">${escapeHtml(name)}</div>
                            <div class="text-xs text-slate-500">${escapeHtml(email)}</div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div
                                class="inline-flex items-center justify-center px-3 py-2 rounded-lg border border-slate-700 bg-slate-900/30 cursor-text hover:border-yellow-500/30 hover:bg-slate-900/50 transition-colors select-none"
                                title="Double-clic pour modifier"
                                data-points-cell="1"
                                data-points-user="${escapeHtml(String(userId))}"
                                data-points-value="${escapeHtml(String(points))}"
                            >
                                <span class="text-sm font-mono font-bold text-yellow-300">${escapeHtml(String(points))}</span>
                            </div>
                        </td>

                        ${teamCellHtml}

                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <button class="text-red-300 hover:text-red-200 bg-red-500/10 px-3 py-1.5 rounded hover:bg-red-500/20 transition-colors text-sm font-bold"
                                    data-remove-player="${escapeHtml(String(userId))}">
                                <i class="fa-solid fa-user-minus mr-1"></i> Retirer
                            </button>
                        </td>
                    `;

                    playersTableBodyElement.appendChild(rowElement);
                });

                // Retirer joueur
                playersTableBodyElement.querySelectorAll('[data-remove-player]').forEach((btn) => {
                    btn.addEventListener('click', async () => {
                        const userId = btn.getAttribute('data-remove-player');
                        if (!userId) return;

                        if (!confirm("Retirer ce joueur de l’activité ?")) return;

                        try {
                            await fetchJson(`${participantsApiUrl}/${userId}`, {
                                method: 'DELETE',
                                headers: {'X-CSRF-TOKEN': csrfToken()},
                            });

                            showToast("Joueur retiré.", "success");
                            await loadParticipantsPage(1);
                            if (IS_TEAM_MODE) await loadTeams();
                        } catch (e) {
                            if (e.status === 403) showToast("Accès refusé.", "error");
                            else showToast(e.payload?.message || "Erreur retrait joueur.", "error");
                        }
                    });
                });

                // Assign équipe (TEAM mode + API dispo)
                if (IS_TEAM_MODE && teamsApiAvailable) {
                    playersTableBodyElement.querySelectorAll('[data-team-select]').forEach((select) => {
                        select.addEventListener('change', async () => {
                            const userId = select.getAttribute('data-team-select');
                            const teamId = (select.value || '').trim();

                            try {
                                await fetchJson(participantsApiUrl, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken(),
                                    },
                                    body: JSON.stringify({
                                        user_id: Number(userId),
                                        team_id: teamId ? Number(teamId) : null,
                                    }),
                                });

                                showToast("Équipe mise à jour.", "success");
                                await loadParticipantsPage(currentPageNumber);
                                await loadTeams();
                            } catch (e) {
                                showToast(e.payload?.message || "Erreur mise à jour équipe.", "error");
                                await loadParticipantsPage(currentPageNumber);
                            }
                        });
                    });
                }

                // Inline edit points
                attachInlinePointsEditorHandlers();
            }

            async function loadParticipantsPage(pageNumber) {
                currentPageNumber = pageNumber;

                if (pendingParticipantsFetchAbortController) {
                    pendingParticipantsFetchAbortController.abort();
                }
                pendingParticipantsFetchAbortController = new AbortController();

                const q = (playersSearchInputElement.value || '').trim();

                const params = new URLSearchParams();
                params.set('page', String(pageNumber));
                params.set('per_page', String(perPage));
                if (q) params.set('q', q);

                // Filtre équipe (TEAM mode)
                if (IS_TEAM_MODE && currentTeamFilterValue) {
                    params.set('team_id', currentTeamFilterValue);
                }

                try {
                    const res = await fetchJson(`${participantsApiUrl}?${params.toString()}`, {
                        method: 'GET',
                        signal: pendingParticipantsFetchAbortController.signal,
                    });

                    const participants = res?.data || [];
                    const meta = res?.meta || {};

                    lastPageNumber = Number(meta.last_page || 1);
                    currentPageNumber = Number(meta.current_page || pageNumber);

                    renderParticipantsRows(participants);
                    updatePaginationUi();
                } catch (e) {
                    if (e.name === 'AbortError') return;

                    renderParticipantsRows([]);
                    updatePaginationUi();

                    if (e.status === 401) showToast("Session expirée. Reconnecte-toi.", "error");
                    else showToast("Erreur chargement joueurs.", "error");
                }
            }

            const debouncedReloadParticipants = debounce(() => loadParticipantsPage(1), 250);

            // ----------------------------------------
            // Add player modal (inchangé)
            // ----------------------------------------
            function openAddPlayerModal() {
                addPlayerModalElement.classList.remove('hidden');
                document.body.classList.add('modal-active');

                playerSearchInputElement.value = '';
                selectedPlayerIdElement.value = '';
                playerSearchResultsElement.innerHTML = '';
                playerSearchResultsElement.classList.add('hidden');

                selectedPlayerInfoElement.classList.add('hidden');
                selectedPlayerNameElement.textContent = '—';
                selectedPlayerEmailElement.textContent = '—';
                selectedPlayerPointsElement.textContent = '0';

                if (IS_TEAM_MODE && addPlayerTeamSelectElement) {
                    addPlayerTeamSelectElement.value = '';
                    addPlayerTeamBlockElement?.classList.toggle('hidden', !teamsApiAvailable);
                }

                playerSearchInputElement.focus();
            }

            function closeAddPlayerModal() {
                addPlayerModalElement.classList.add('hidden');
                document.body.classList.remove('modal-active');
                playerSearchResultsElement.classList.add('hidden');
            }

            openAddPlayerButtonElement.addEventListener('click', openAddPlayerModal);
            closeAddPlayerButtonElement.addEventListener('click', closeAddPlayerModal);
            addPlayerBackdropElement.addEventListener('click', closeAddPlayerModal);

            function normalizeStudentRow(s) {
                const id = Number(s.id ?? s.user_id ?? 0);
                const name = (s.name ?? s.display_name ?? s.user_name ?? '').trim() || '—';
                const email = (s.email ?? s.university_email ?? s.user_email ?? '').trim() || '—';
                const points = Number(s.points ?? s.points_total ?? 0);
                return { id, name, email, points };
            }

            function renderPlayerSearchResults(students, alreadyInActivityIds = new Set()) {
                playerSearchResultsElement.innerHTML = '';

                if (!students || students.length === 0) {
                    playerSearchResultsElement.innerHTML = `<div class="px-4 py-2 text-sm text-slate-500">Aucun résultat</div>`;
                    playerSearchResultsElement.classList.remove('hidden');
                    return;
                }

                students.forEach((raw) => {
                    const s = normalizeStudentRow(raw);
                    if (!s.id) return;

                    const isAlready = alreadyInActivityIds.has(String(s.id));

                    const opt = document.createElement('div');
                    opt.className = `px-4 py-2 transition-colors ${isAlready ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer hover:bg-slate-700'}`;

                    const rightBadge = isAlready
                        ? `<span class="text-[10px] font-bold px-2 py-1 rounded border bg-slate-700 text-slate-300 border-slate-600">DÉJÀ AJOUTÉ</span>`
                        : `
                            <div class="text-right">
                                <div class="text-[10px] text-slate-500">{{ $activity->points_label }}</div>
                                <div class="text-sm font-mono font-bold text-yellow-300">${escapeHtml(String(s.points))}</div>
                            </div>
                          `;

                    opt.innerHTML = `
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-sm text-white font-medium truncate">${escapeHtml(s.name)}</div>
                                <div class="text-xs text-slate-400 truncate">${escapeHtml(s.email)}</div>
                            </div>
                            ${rightBadge}
                        </div>
                    `;

                    if (!isAlready) {
                        opt.addEventListener('click', () => {
                            playerSearchInputElement.value = s.name;
                            selectedPlayerIdElement.value = String(s.id);

                            selectedPlayerInfoElement.classList.remove('hidden');
                            selectedPlayerNameElement.textContent = s.name;
                            selectedPlayerEmailElement.textContent = s.email;
                            selectedPlayerPointsElement.textContent = String(s.points);

                            playerSearchResultsElement.classList.add('hidden');
                        });
                    }

                    playerSearchResultsElement.appendChild(opt);
                });

                playerSearchResultsElement.classList.remove('hidden');
            }

            async function fetchStudentsGlobal(term) {
                const tryQ = async () => {
                    const p = new URLSearchParams();
                    p.set('q', term);
                    const res = await fetchJson(`${studentsSearchApiUrl}?${p.toString()}`, { method: 'GET' });
                    return Array.isArray(res?.data) ? res.data : [];
                };

                const trySearch = async () => {
                    const p = new URLSearchParams();
                    p.set('search', term);
                    const res = await fetchJson(`${studentsSearchApiUrl}?${p.toString()}`, { method: 'GET' });
                    return Array.isArray(res?.data) ? res.data : [];
                };

                const a = await tryQ().catch(() => []);
                if (a.length) return a;

                const b = await trySearch().catch(() => []);
                return b;
            }

            async function fetchParticipantsMatching(term, signal) {
                const params = new URLSearchParams();
                params.set('q', term);

                const res = await fetchJson(`${participantsSearchApiUrl}?${params.toString()}`, {
                    method: 'GET',
                    signal,
                });

                const rows = Array.isArray(res?.data) ? res.data : [];
                const ids = new Set();
                rows.forEach((r) => {
                    const id = Number(r.id ?? r.user_id ?? 0);
                    if (id) ids.add(String(id));
                });
                return ids;
            }

            const debouncedPlayerSearch = debounce(async () => {
                const term = (playerSearchInputElement.value || '').trim();

                selectedPlayerIdElement.value = '';
                selectedPlayerInfoElement.classList.add('hidden');

                if (term.length < 2) {
                    playerSearchResultsElement.classList.add('hidden');
                    playerSearchResultsElement.innerHTML = '';
                    return;
                }

                if (pendingSearchFetchAbortController) {
                    pendingSearchFetchAbortController.abort();
                }
                pendingSearchFetchAbortController = new AbortController();

                try {
                    const studentsPromise = fetchStudentsGlobal(term);
                    const alreadyPromise = fetchParticipantsMatching(term, pendingSearchFetchAbortController.signal)
                        .catch(() => new Set());

                    const [students, alreadyInActivityIds] = await Promise.all([studentsPromise, alreadyPromise]);

                    renderPlayerSearchResults(students, alreadyInActivityIds);
                } catch (e) {
                    if (e.name === 'AbortError') return;
                    showToast("Erreur recherche étudiants.", "error");
                }
            }, 250);

            playerSearchInputElement.addEventListener('input', debouncedPlayerSearch);

            document.addEventListener('click', (event) => {
                const clickedInsideInput = playerSearchInputElement.contains(event.target);
                const clickedInsideResults = playerSearchResultsElement.contains(event.target);
                if (!clickedInsideInput && !clickedInsideResults) {
                    playerSearchResultsElement.classList.add('hidden');
                }
            });

            async function submitAddPlayer() {
                const userId = Number(selectedPlayerIdElement.value || 0);
                if (!userId) {
                    showToast("Sélectionne un étudiant dans la liste.", "error");
                    return;
                }

                const payload = { user_id: userId };

                if (IS_TEAM_MODE && teamsApiAvailable && addPlayerTeamSelectElement) {
                    const teamId = (addPlayerTeamSelectElement.value || '').trim();
                    payload.team_id = teamId ? Number(teamId) : null;
                }

                const original = submitAddPlayerButtonElement.innerHTML;
                submitAddPlayerButtonElement.disabled = true;
                submitAddPlayerButtonElement.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin"></i>`;

                try {
                    const res = await fetchJson(participantsApiUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken(),
                        },
                        body: JSON.stringify(payload),
                    });

                    const inserted = res?.data?.inserted;
                    if (inserted === false) showToast("Déjà dans l’activité.", "success");
                    else showToast("Joueur ajouté.", "success");

                    closeAddPlayerModal();

                    await loadParticipantsPage(1);
                    if (IS_TEAM_MODE) await loadTeams();
                } catch (e) {
                    if (e.status === 422 && e.payload?.errors) {
                        const first = Object.values(e.payload.errors)[0]?.[0];
                        showToast(first || "Données invalides.", "error");
                    } else if (e.status === 403) {
                        showToast("Accès refusé.", "error");
                    } else {
                        showToast(e.payload?.message || "Erreur serveur.", "error");
                    }
                } finally {
                    submitAddPlayerButtonElement.disabled = false;
                    submitAddPlayerButtonElement.innerHTML = original;
                }
            }

            submitAddPlayerButtonElement.addEventListener('click', submitAddPlayer);

            // ----------------------------------------
            // Create team modal (optionnel)
            // ----------------------------------------
            if (IS_TEAM_MODE && openCreateTeamButtonElement && createTeamModalElement) {
                function openCreateTeamModal() {
                    if (!teamsApiAvailable) {
                        showToast("Teams API pas encore branchée.", "error");
                        return;
                    }

                    createTeamModalElement.classList.remove('hidden');
                    document.body.classList.add('modal-active');

                    if (teamTitleInputElement) {
                        teamTitleInputElement.value = '';
                        teamTitleInputElement.focus();
                    }
                }

                function closeCreateTeamModal() {
                    createTeamModalElement.classList.add('hidden');
                    document.body.classList.remove('modal-active');
                }

                openCreateTeamButtonElement.addEventListener('click', openCreateTeamModal);
                closeCreateTeamButtonElement?.addEventListener('click', closeCreateTeamModal);
                createTeamBackdropElement?.addEventListener('click', closeCreateTeamModal);

                submitCreateTeamButtonElement?.addEventListener('click', async () => {
                    const title = (teamTitleInputElement?.value || '').trim();
                    if (title.length < 2) {
                        showToast("Nom d’équipe requis (2 caractères minimum).", "error");
                        return;
                    }

                    const original = submitCreateTeamButtonElement.innerHTML;
                    submitCreateTeamButtonElement.disabled = true;
                    submitCreateTeamButtonElement.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin"></i>`;

                    try {
                        await fetchJson(teamsApiUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken(),
                            },
                            body: JSON.stringify({ title }),
                        });

                        showToast("Équipe créée.", "success");
                        closeCreateTeamModal();
                        await loadTeams();
                        await loadParticipantsPage(currentPageNumber);
                    } catch (e) {
                        if (e.status === 422 && e.payload?.errors) {
                            const first = Object.values(e.payload.errors)[0]?.[0];
                            showToast(first || "Données invalides.", "error");
                        } else {
                            showToast(e.payload?.message || "Erreur création équipe.", "error");
                        }
                    } finally {
                        submitCreateTeamButtonElement.disabled = false;
                        submitCreateTeamButtonElement.innerHTML = original;
                    }
                });
            }

            // ----------------------------------------
            // Wire search + filters + pagination
            // ----------------------------------------
            playersSearchInputElement.addEventListener('input', debouncedReloadParticipants);

            teamFilterSelectElement?.addEventListener('change', () => {
                currentTeamFilterValue = (teamFilterSelectElement.value || '').trim();
                loadParticipantsPage(1);
            });

            reloadButtonElement.addEventListener('click', () => loadParticipantsPage(currentPageNumber));

            paginationPrevButtonElement.addEventListener('click', () => {
                if (currentPageNumber > 1) loadParticipantsPage(currentPageNumber - 1);
            });

            paginationNextButtonElement.addEventListener('click', () => {
                if (currentPageNumber < lastPageNumber) loadParticipantsPage(currentPageNumber + 1);
            });

            // ----------------------------------------
            // Init
            // ----------------------------------------
            if (IS_TEAM_MODE) loadTeams();
            loadParticipantsPage(1);
        });
    </script>
@endpush
