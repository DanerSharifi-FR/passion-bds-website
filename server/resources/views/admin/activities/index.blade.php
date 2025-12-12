@extends('admin.layout')

@section('title', "Activités - P'AS'SION BDS")

@php
    $uid = auth()->id();

    $actorRoleNames = $uid
        ? \Illuminate\Support\Facades\DB::table('user_roles')
            ->join('roles', 'roles.id', '=', 'user_roles.role_id')
            ->where('user_roles.user_id', $uid)
            ->pluck('roles.name')
            ->all()
        : [];

    $isSuperAdmin = in_array('ROLE_SUPER_ADMIN', $actorRoleNames, true);
    $isGameMaster = in_array('ROLE_GAMEMASTER', $actorRoleNames, true);
@endphp

@section('content')
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white">Gestion des Activités</h2>
            <p class="text-slate-400 mt-1">Crée des activités, définis l’unité de points, et invite d’autres
                Gamemasters.</p>
        </div>
        <div>
            @if($isSuperAdmin || $isGameMaster)
                <button onclick="openCreateModal()"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors shadow-lg shadow-indigo-500/20 flex items-center">
                    <i class="fa-solid fa-plus mr-2"></i> Créer une activité
                </button>
            @endif
        </div>
    </div>

    <div class="bg-slate-800 rounded-xl p-4 border border-slate-700 shadow-lg mb-6">
        <form id="searchForm" class="flex flex-col md:flex-row gap-4" onsubmit="return false;">
            <div class="flex-1 relative">
                <i class="fa-solid fa-search absolute left-3 top-3.5 text-slate-500"></i>
                <input type="text" id="searchInput"
                       class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block pl-10 p-3"
                       placeholder="Rechercher une activité (titre / slug)...">
            </div>
            <div class="w-full md:w-48">
                <select id="statusFilter"
                        class="bg-slate-900 border border-slate-600 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3">
                    <option value="">Tous statuts</option>
                    <option value="1">Actives</option>
                    <option value="0">Inactives</option>
                </select>
            </div>
        </form>
    </div>

    <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-400">
                <thead class="bg-slate-900/50 text-slate-200 uppercase text-xs font-semibold">
                <tr>
                    <th class="px-6 py-4">Activité</th>
                    <th class="px-6 py-4">Mode</th>
                    <th class="px-6 py-4">Unité</th>
                    <th class="px-6 py-4 text-center">Participants</th>
                    <th class="px-6 py-4 text-center">Admins</th>
                    <th class="px-6 py-4 text-center">Statut</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-700" id="activitiesTableBody"></tbody>
            </table>
        </div>

        <div class="bg-slate-800 px-4 py-3 border-t border-slate-700 flex items-center justify-between sm:px-6">
            <div class="text-sm text-slate-400">
                Liste simple (pagination plus tard).
            </div>
        </div>
    </div>
@endsection

@push('end_scripts')
    <div id="toastContainer" class="fixed top-4 right-4 z-[9999] space-y-2 pointer-events-none"></div>

    {{-- CREATE MODAL --}}
    <div id="createActivityModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/80 transition-opacity" aria-hidden="true"
                 onclick="closeCreateModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div
                class="relative inline-block align-bottom bg-slate-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-slate-700">
                <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-start gap-3">
                        <div
                            class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-900/50 flex items-center justify-center">
                            <i class="fa-solid fa-flag-checkered text-indigo-400"></i>
                        </div>
                        <div class="w-full">
                            <h3 class="text-lg leading-6 font-medium text-white">Créer une activité</h3>
                            <p class="text-sm text-slate-400 mt-1">Ex: “Babyfoot”, unité = “wins”, mode = individuel ou
                                équipes.</p>

                            <form id="createActivityForm" class="space-y-4 mt-4" onsubmit="return false;">
                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-1">Titre</label>
                                    <input type="text" id="createTitle"
                                           minlength="2" required
                                           class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5"
                                           placeholder="Ex: Babyfoot">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-1">Unité de points</label>
                                    <input type="text" id="createPointsLabel"
                                           maxlength="50" required
                                           class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5"
                                           placeholder="Ex: wins / seconds / kills">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-300 mb-1">Mode</label>
                                    <select id="createMode"
                                            class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5">
                                        <option value="INDIVIDUAL">Individuel</option>
                                        <option value="TEAM">Équipes</option>
                                    </select>
                                </div>

                                <div
                                    class="flex items-center justify-between p-3 bg-slate-900/40 rounded-lg border border-slate-700">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-white">Activité active</span>
                                        <span
                                            class="text-xs text-slate-400">Si désactivée, elle reste visible en admin.</span>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" id="createIsActive" class="sr-only peer" checked>
                                        <div class="w-11 h-6 bg-slate-600 peer-focus:outline-none rounded-full peer
                                            peer-checked:after:translate-x-full peer-checked:after:border-white
                                            after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white
                                            after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all
                                            peer-checked:bg-indigo-600"></div>
                                    </label>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-700">
                    <button type="button" id="btnCreateActivity" onclick="submitCreateActivity()"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">
                        Créer
                    </button>
                    <button type="button" onclick="closeCreateModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-slate-600 shadow-sm px-4 py-2 bg-slate-800 text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MANAGE MODAL --}}
    <div id="manageActivityModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/80 transition-opacity" aria-hidden="true"
                 onclick="closeManageModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div
                class="relative inline-block align-bottom bg-slate-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl w-full border border-slate-700">
                <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-start gap-3">
                        <div
                            class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-900/50 flex items-center justify-center">
                            <i class="fa-solid fa-gear text-indigo-400"></i>
                        </div>

                        <div class="w-full">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                <div>
                                    <h3 class="text-lg leading-6 font-medium text-white">Gérer l’activité</h3>
                                    <p class="text-sm text-slate-400 mt-1">
                                        <span class="font-mono text-indigo-300" id="manageSlug">—</span>
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span id="manageStatusBadge"
                                          class="px-2 py-1 rounded text-xs font-bold bg-slate-700 text-slate-200 border border-slate-600">—</span>
                                </div>
                            </div>

                            <input type="hidden" id="manageActivityId">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                {{-- Settings --}}
                                <div class="p-4 bg-slate-900/30 rounded-xl border border-slate-700">
                                    <h4 class="text-sm font-bold text-white mb-3">
                                        <i class="fa-solid fa-sliders mr-2 text-slate-400"></i> Paramètres
                                    </h4>

                                    <form id="manageForm" class="space-y-3" onsubmit="return false;">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-300 mb-1">Titre</label>
                                            <input type="text" id="manageTitle" minlength="2" required
                                                   class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-slate-300 mb-1">Unité</label>
                                            <input type="text" id="managePointsLabel" maxlength="50" required
                                                   class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-slate-300 mb-1">Mode</label>
                                            <select id="manageMode"
                                                    class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5">
                                                <option value="INDIVIDUAL">Individuel</option>
                                                <option value="TEAM">Équipes</option>
                                            </select>
                                        </div>

                                        <div
                                            class="flex items-center justify-between p-3 bg-slate-900/40 rounded-lg border border-slate-700">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-white">Actif</span>
                                                <span class="text-xs text-slate-400">Désactiver = stop usage côté public.</span>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" id="manageIsActive" class="sr-only peer">
                                                <div class="w-11 h-6 bg-slate-600 peer-focus:outline-none rounded-full peer
                                                    peer-checked:after:translate-x-full peer-checked:after:border-white
                                                    after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white
                                                    after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all
                                                    peer-checked:bg-indigo-600"></div>
                                            </label>
                                        </div>

                                        <button type="button" id="btnSaveActivity" onclick="saveActivity()"
                                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:w-auto sm:text-sm">
                                            Enregistrer
                                        </button>
                                    </form>
                                </div>

                                {{-- Admins --}}
                                <div class="p-4 bg-slate-900/30 rounded-xl border border-slate-700">
                                    <h4 class="text-sm font-bold text-white mb-3">
                                        <i class="fa-solid fa-user-shield mr-2 text-slate-400"></i> Admins (Gamemasters)
                                    </h4>

                                    <div class="space-y-3">
                                        <div class="relative">
                                            <label class="block text-sm font-medium text-slate-300 mb-1">Inviter un
                                                Gamemaster</label>
                                            <input type="text" id="inviteSearchInput"
                                                   class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5"
                                                   placeholder="Rechercher par nom / email..." autocomplete="off">
                                            <div id="inviteResults"
                                                 class="absolute z-50 w-full bg-slate-800 border border-slate-600 rounded-lg mt-1 hidden max-h-56 overflow-y-auto shadow-xl divide-y divide-slate-700">
                                            </div>
                                        </div>

                                        <div class="rounded-lg border border-slate-700 overflow-hidden">
                                            <div
                                                class="px-3 py-2 bg-slate-900/40 text-xs font-bold text-slate-300 uppercase">
                                                Admins actuels
                                            </div>
                                            <div id="adminsList" class="divide-y divide-slate-700">
                                                {{-- injected --}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 text-xs text-slate-500">
                                Participants + transactions d’activité: on branche ça juste après (API déjà routée).
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-700">
                    <button type="button" onclick="closeManageModal()"
                            class="w-full inline-flex justify-center rounded-md border border-slate-600 shadow-sm px-4 py-2 bg-slate-800 text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 sm:w-auto sm:text-sm">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const API_ACTIVITIES = '/admin/api/activities';
            const API_ACTIVITY = (id) => `/admin/api/activities/${id}`;

            const API_ADMINS_LIST = (id) => `/admin/api/activities/${id}/admins`;
            const API_ADMINS_ADD = (id) => `/admin/api/activities/${id}/admins`;
            const API_ADMINS_DEL = (id, adminId) => `/admin/api/activities/${id}/admins/${adminId}`;

            const API_INVITABLE_GAMEMASTERS = (id, q) => {
                const params = new URLSearchParams();
                if (q) params.set('q', q);
                return `/admin/api/activities/${id}/invitable-gamemasters?${params.toString()}`;
            };

            const IS_SUPER_ADMIN = @json($isSuperAdmin);
            const IS_GAMEMASTER = @json($isGameMaster);

            const tbody = document.getElementById('activitiesTableBody');
            const searchInput = document.getElementById('searchInput');
            const statusFilter = document.getElementById('statusFilter');

            const toastContainer = document.getElementById('toastContainer');

            // Create modal elements
            const createModal = document.getElementById('createActivityModal');
            const createTitle = document.getElementById('createTitle');
            const createPointsLabel = document.getElementById('createPointsLabel');
            const createMode = document.getElementById('createMode');
            const createIsActive = document.getElementById('createIsActive');
            const btnCreateActivity = document.getElementById('btnCreateActivity');

            // Manage modal elements
            const manageModal = document.getElementById('manageActivityModal');
            const manageActivityId = document.getElementById('manageActivityId');
            const manageSlug = document.getElementById('manageSlug');
            const manageStatusBadge = document.getElementById('manageStatusBadge');

            const manageTitle = document.getElementById('manageTitle');
            const managePointsLabel = document.getElementById('managePointsLabel');
            const manageMode = document.getElementById('manageMode');
            const manageIsActive = document.getElementById('manageIsActive');
            const btnSaveActivity = document.getElementById('btnSaveActivity');

            const inviteSearchInput = document.getElementById('inviteSearchInput');
            const inviteResults = document.getElementById('inviteResults');
            const adminsList = document.getElementById('adminsList');

            let activitiesCache = [];
            let currentManaging = null;
            let pendingInviteAbort = null;

            function csrf() {
                return document.querySelector('meta[name="csrf-token"]')?.content || '';
            }

            function escapeHtml(str) {
                return String(str ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            function showToast(message, type = 'success') {
                const toast = document.createElement('div');
                const bgClass = type === 'success' ? 'bg-green-600' : 'bg-red-600';
                const icon = type === 'success' ? 'fa-check-circle' : 'fa-circle-exclamation';

                toast.className = `flex items-center p-4 rounded shadow-lg text-white ${bgClass} pointer-events-auto opacity-0 translate-y-2 transition-all duration-200`;
                toast.innerHTML = `<i class="fa-solid ${icon} text-lg mr-3"></i><span class="text-sm font-medium">${escapeHtml(message)}</span>`;
                toastContainer.appendChild(toast);

                requestAnimationFrame(() => {
                    toast.classList.remove('opacity-0', 'translate-y-2');
                    toast.classList.add('opacity-100', 'translate-y-0');
                });

                setTimeout(() => {
                    toast.classList.remove('opacity-100', 'translate-y-0');
                    toast.classList.add('opacity-0', 'translate-y-2');
                    setTimeout(() => toast.remove(), 250);
                }, 3000);
            }

            async function httpJson(url, method = 'GET', body = null) {
                const res = await fetch(url, {
                    method,
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf(),
                    },
                    body: body ? JSON.stringify(body) : null,
                });

                const text = await res.text();
                let payload = null;
                try {
                    payload = text ? JSON.parse(text) : null;
                } catch {
                }

                if (!res.ok) {
                    const msg =
                        payload?.message ||
                        payload?.errors?.title?.[0] ||
                        payload?.errors?.points_label?.[0] ||
                        payload?.errors?.mode?.[0] ||
                        'Erreur serveur';
                    const err = new Error(msg);
                    err.status = res.status;
                    err.payload = payload;
                    throw err;
                }

                return payload;
            }

            function badgeMode(mode) {
                if (mode === 'TEAM') {
                    return `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-pink-500/10 text-pink-300 border border-pink-500/20">TEAM</span>`;
                }
                return `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-500/10 text-indigo-300 border border-indigo-500/20">SOLO</span>`;
            }

            function badgeStatus(active) {
                if (active) {
                    return `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-500/10 text-green-300 border border-green-500/20">ACTIVE</span>`;
                }
                return `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-500/10 text-slate-300 border border-slate-500/20">INACTIVE</span>`;
            }

            function renderActivities(list) {
                tbody.innerHTML = '';

                if (!list.length) {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td class="px-6 py-6 text-slate-500" colspan="7">Aucune activité</td>`;
                    tbody.appendChild(tr);
                    return;
                }

                list.forEach(a => {
                    const canManage = !!a.can_manage;

                    const actions = canManage
                        ? `
                          <button onclick="openManageModal(${a.id})"
                                  class="text-indigo-400 hover:text-indigo-300 bg-indigo-500/10 px-3 py-1.5 rounded hover:bg-indigo-500/20 transition-colors">
                              <i class="fa-solid fa-gear mr-1"></i> Gérer
                          </button>

                          <a href="/admin/activities/${a.id}/players"
                             class="ml-2 text-yellow-300 hover:text-yellow-200 bg-yellow-500/10 px-3 py-1.5 rounded hover:bg-yellow-500/20 transition-colors">
                              <i class="fa-solid fa-users mr-1"></i> ${a.mode === 'TEAM' ? 'Joueurs & Équipes' : 'Joueurs'}
                          </a>
                        `
                        : `<span class="text-slate-600">—</span>`;


                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-700/50 transition-colors';
                    tr.innerHTML = `
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-white">${escapeHtml(a.title)}</div>
                            <div class="text-xs text-slate-500 font-mono">${escapeHtml(a.slug)}</div>
                        </td>
                        <td class="px-6 py-4">${badgeMode(a.mode)}</td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-slate-200 font-semibold">${escapeHtml(a.points_label)}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-sm font-mono text-yellow-400 font-bold">${escapeHtml(String(a.participants_count ?? 0))}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-sm font-mono text-slate-200 font-bold">${escapeHtml(String(a.admins_count ?? 0))}</span>
                        </td>
                        <td class="px-6 py-4 text-center">${badgeStatus(!!a.is_active)}</td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">${actions}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }

            async function loadActivities() {
                const q = (searchInput.value || '').trim();
                const status = (statusFilter.value || '').trim();

                const params = new URLSearchParams();
                if (q) params.set('q', q);

                const res = await httpJson(params.toString() ? `${API_ACTIVITIES}?${params}` : API_ACTIVITIES, 'GET');
                let data = res?.data || [];

                if (status !== '') {
                    const activeWanted = status === '1';
                    data = data.filter(a => !!a.is_active === activeWanted);
                }

                activitiesCache = data;
                renderActivities(data);
            }

            function debounce(fn, ms) {
                let t = null;
                return (...args) => {
                    clearTimeout(t);
                    t = setTimeout(() => fn(...args), ms);
                };
            }

            const debouncedReload = debounce(() => loadActivities().catch(e => showToast(e.message || 'Erreur', 'error')), 250);

            searchInput.addEventListener('input', debouncedReload);
            statusFilter.addEventListener('change', debouncedReload);

            // ----------------------------
            // Create modal
            // ----------------------------
            window.openCreateModal = function () {
                if (!IS_SUPER_ADMIN && !IS_GAMEMASTER) {
                    showToast("Non autorisé.", "error");
                    return;
                }
                createTitle.value = '';
                createPointsLabel.value = '';
                createMode.value = 'INDIVIDUAL';
                createIsActive.checked = true;

                createModal.classList.remove('hidden');
                document.body.classList.add('modal-active');
                createTitle.focus();
            };

            window.closeCreateModal = function () {
                createModal.classList.add('hidden');
                document.body.classList.remove('modal-active');
            };

            window.submitCreateActivity = async function () {
                const title = (createTitle.value || '').trim();
                const pointsLabel = (createPointsLabel.value || '').trim();
                const mode = (createMode.value || 'INDIVIDUAL').trim();
                const isActive = !!createIsActive.checked;

                if (title.length < 2) return showToast("Titre requis (2 caractères minimum).", "error");
                if (!pointsLabel) return showToast("Unité requise.", "error");

                const original = btnCreateActivity.innerHTML;
                btnCreateActivity.disabled = true;
                btnCreateActivity.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin"></i>`;

                try {
                    await httpJson(API_ACTIVITIES, 'POST', {
                        title,
                        points_label: pointsLabel,
                        mode,
                        is_active: isActive,
                    });

                    showToast("Activité créée.", "success");
                    window.closeCreateModal();
                    await loadActivities();
                } catch (e) {
                    showToast(e.message || "Erreur", "error");
                } finally {
                    btnCreateActivity.disabled = false;
                    btnCreateActivity.innerHTML = original;
                }
            };

            // ----------------------------
            // Manage modal
            // ----------------------------
            function setManageStatus(active) {
                if (active) {
                    manageStatusBadge.textContent = 'ACTIVE';
                    manageStatusBadge.className = 'px-2 py-1 rounded text-xs font-bold bg-green-500/10 text-green-300 border border-green-500/20';
                } else {
                    manageStatusBadge.textContent = 'INACTIVE';
                    manageStatusBadge.className = 'px-2 py-1 rounded text-xs font-bold bg-slate-500/10 text-slate-300 border border-slate-500/20';
                }
            }

            window.openManageModal = async function (id) {
                const a = activitiesCache.find(x => Number(x.id) === Number(id));
                if (!a) return;

                if (!a.can_manage) {
                    showToast("Non autorisé.", "error");
                    return;
                }

                currentManaging = a;
                manageActivityId.value = String(a.id);
                manageSlug.textContent = a.slug || '—';

                manageTitle.value = (a.title || '').trim();
                managePointsLabel.value = (a.points_label || '').trim();
                manageMode.value = (a.mode || 'INDIVIDUAL').trim();
                manageIsActive.checked = !!a.is_active;
                setManageStatus(!!a.is_active);

                inviteSearchInput.value = '';
                inviteResults.classList.add('hidden');
                inviteResults.innerHTML = '';
                adminsList.innerHTML = `<div class="px-3 py-3 text-sm text-slate-500">Chargement…</div>`;

                manageModal.classList.remove('hidden');
                document.body.classList.add('modal-active');

                try {
                    await loadAdminsList();
                } catch (e) {
                    showToast(e.message || 'Erreur chargement admins', 'error');
                }
            };

            window.closeManageModal = function () {
                manageModal.classList.add('hidden');
                document.body.classList.remove('modal-active');
                currentManaging = null;
            };

            window.saveActivity = async function () {
                if (!currentManaging) return;

                const id = Number(manageActivityId.value || 0);
                const title = (manageTitle.value || '').trim();
                const pointsLabel = (managePointsLabel.value || '').trim();
                const mode = (manageMode.value || 'INDIVIDUAL').trim();
                const isActive = !!manageIsActive.checked;

                if (!id) return;
                if (title.length < 2) return showToast("Titre requis (2 caractères minimum).", "error");
                if (!pointsLabel) return showToast("Unité requise.", "error");

                const original = btnSaveActivity.innerHTML;
                btnSaveActivity.disabled = true;
                btnSaveActivity.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin"></i>`;

                try {
                    await httpJson(API_ACTIVITY(id), 'PUT', {
                        title,
                        points_label: pointsLabel,
                        mode,
                        is_active: isActive,
                    });

                    showToast("Activité mise à jour.", "success");
                    setManageStatus(isActive);
                    await loadActivities();

                    // Refresh currentManaging cache
                    currentManaging = activitiesCache.find(x => Number(x.id) === id) || currentManaging;
                } catch (e) {
                    showToast(e.message || "Erreur", "error");
                } finally {
                    btnSaveActivity.disabled = false;
                    btnSaveActivity.innerHTML = original;
                }
            };

            // ----------------------------
            // Admins list + invite
            // ----------------------------
            function renderAdmins(admins) {
                adminsList.innerHTML = '';

                if (!admins.length) {
                    adminsList.innerHTML = `<div class="px-3 py-3 text-sm text-slate-500">Aucun admin</div>`;
                    return;
                }

                admins.forEach(a => {
                    const row = document.createElement('div');
                    row.className = 'px-3 py-2 flex items-center justify-between';

                    const name = escapeHtml(a.name || a.display_name || a.email || '—');
                    const email = escapeHtml(a.email || '—');

                    row.innerHTML = `
                        <div class="flex flex-col">
                            <span class="text-sm text-white font-medium">${name}</span>
                            <span class="text-xs text-slate-500 font-mono">${email}</span>
                        </div>
                        ${a.id !== {{ auth()->id() }} ? `
                        <div>
                            <button class="text-red-300 hover:text-red-200 bg-red-500/10 px-3 py-1.5 rounded hover:bg-red-500/20 transition-colors">
                                <i class="fa-solid fa-user-minus mr-1"></i> Retirer
                            </button>
                        </div>` : ''}
                    `;

                    row.querySelector('button')?.addEventListener('click', async () => {
                        const activityId = Number(manageActivityId.value || 0);
                        if (!activityId) return;

                        try {
                            await httpJson(API_ADMINS_DEL(activityId, a.id), 'DELETE');
                            showToast("Admin retiré.", "success");
                            await loadAdminsList();
                            await loadActivities();
                        } catch (e) {
                            showToast(e.message || "Erreur", "error");
                        }
                    });

                    adminsList.appendChild(row);
                });
            }

            async function loadAdminsList() {
                const activityId = Number(manageActivityId.value || 0);
                if (!activityId) return;

                const res = await httpJson(API_ADMINS_LIST(activityId), 'GET');
                const admins = res?.data || [];
                renderAdmins(admins);
            }

            function renderInviteResults(list) {
                inviteResults.innerHTML = '';

                if (!list.length) {
                    inviteResults.innerHTML = `<div class="px-4 py-2 text-sm text-slate-500">Aucun résultat</div>`;
                    inviteResults.classList.remove('hidden');
                    return;
                }

                list.forEach(u => {
                    const item = document.createElement('div');
                    item.className = 'px-4 py-2 cursor-pointer hover:bg-slate-700 transition-colors';

                    item.innerHTML = `
                        <div class="text-sm text-white font-medium">${escapeHtml(u.name || u.display_name || u.email || '—')}</div>
                        <div class="text-xs text-slate-400 font-mono">${escapeHtml(u.email || '—')}</div>
                    `;

                    item.addEventListener('click', async () => {
                        const activityId = Number(manageActivityId.value || 0);
                        if (!activityId) return;

                        inviteResults.classList.add('hidden');
                        inviteResults.innerHTML = '';
                        inviteSearchInput.value = '';

                        try {
                            await httpJson(API_ADMINS_ADD(activityId), 'POST', {admin_id: Number(u.id)});
                            showToast("Gamemaster ajouté.", "success");
                            await loadAdminsList();
                            await loadActivities();
                        } catch (e) {
                            showToast(e.message || "Erreur", "error");
                        }
                    });

                    inviteResults.appendChild(item);
                });

                inviteResults.classList.remove('hidden');
            }

            const debouncedInviteSearch = debounce(async () => {
                const activityId = Number(manageActivityId.value || 0);
                const term = (inviteSearchInput.value || '').trim();

                if (!activityId || term.length < 2) {
                    inviteResults.classList.add('hidden');
                    inviteResults.innerHTML = '';
                    return;
                }

                if (pendingInviteAbort) pendingInviteAbort.abort();
                pendingInviteAbort = new AbortController();

                try {
                    const res = await fetch(API_INVITABLE_GAMEMASTERS(activityId, term), {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf(),
                        },
                        signal: pendingInviteAbort.signal,
                    });

                    const payload = await res.json().catch(() => null);
                    if (!res.ok) throw new Error(payload?.message || 'Erreur recherche');

                    renderInviteResults(payload?.data || []);
                } catch (e) {
                    if (e.name === 'AbortError') return;
                    showToast(e.message || "Erreur", "error");
                }
            }, 250);

            inviteSearchInput.addEventListener('input', debouncedInviteSearch);

            document.addEventListener('click', (e) => {
                const inside = inviteSearchInput.contains(e.target) || inviteResults.contains(e.target);
                if (!inside) inviteResults.classList.add('hidden');
            });

            // Init
            loadActivities().catch(e => showToast(e.message || 'Erreur', 'error'));
        })();
    </script>
@endpush
