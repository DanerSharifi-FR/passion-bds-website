@extends('admin.layout')

@section('title', "Gestion des Allos - P'AS'SION BDS")

@php($activeView = 'requests')

@section('top_bar_buttons')
    <div class="flex bg-slate-900 p-1 rounded-lg border border-slate-700 ml-4">
        <a href="{{ route('admin.allos.requests') }}" id="tabRequests" class="px-4 py-1.5 rounded-md text-sm font-medium transition-all {{ $activeView === 'requests' ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-white' }}">
            <i class="fa-solid fa-bell mr-2"></i> Demandes <span class="ml-1 bg-red-500 text-white text-[10px] px-1.5 rounded-full" id="pendingCount">3</span>
        </a>
        <a href="{{ route('admin.allos.catalog') }}" id="tabCatalog" class="px-4 py-1.5 rounded-md text-sm font-medium transition-all {{ $activeView === 'catalog' ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-white' }}">
            <i class="fa-solid fa-store mr-2"></i> Catalogue & Créneaux
        </a>
    </div>
@endsection

@section('content')
    <div id="viewRequests">
        <div class="flex flex-col md:flex-row items-start md:items-center gap-4 mb-6">
            <div class="min-w-fit">
                <h2 class="text-xl font-bold text-white">Suivi des Allos</h2>
                <p id="resultsCount" class="text-xs text-slate-400 mt-1">0 allos</p>
            </div>
            <div class="flex flex-wrap gap-2 w-full">
                <select id="filterStatus" class="bg-slate-800 border border-slate-600 text-white text-xs rounded-lg p-2 focus:ring-indigo-500" onchange="renderRequests()">
                    <option value="ACTIVE" selected>En cours</option>
                    <option value="ALL">Tout l'historique</option>
                    <option value="PENDING">En attente</option>
                    <option value="ACCEPTED">Acceptés</option>
                    <option value="DONE">Terminés</option>
                    <option value="CANCELLED">Annulés</option>
                </select>
                <select id="filterAllo" class="bg-slate-800 border border-slate-600 text-white text-xs rounded-lg p-2 focus:ring-indigo-500 max-w-[200px]" onchange="renderRequests()">
                    <option value="">Tous les allos</option>
                </select>
                <div class="relative w-full md:w-64">
                    <input type="text" id="filterUser" class="w-full bg-slate-800 border border-slate-600 text-white text-xs rounded-lg p-2 pl-8 focus:ring-indigo-500" placeholder="Chercher un étudiant..." autocomplete="off">
                    <i class="fa-solid fa-search absolute left-2.5 top-2.5 text-slate-500 text-xs"></i>
                    <button id="clearUserFilter" onclick="clearUserFilter()" class="absolute right-2 top-2 text-slate-500 hover:text-white hidden"><i class="fa-solid fa-xmark"></i></button>
                    <div id="userSuggestions" class="absolute z-10 w-full bg-slate-800 border border-slate-600 rounded-lg mt-1 hidden max-h-48 overflow-y-auto shadow-xl"></div>
                </div>
            </div>
            <div class="bg-slate-900/70 border border-slate-700 rounded-2xl p-4 flex flex-col gap-4 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center gap-3">
                    <div class="relative flex-1">
                        <input type="text" id="filterSearch" class="w-full bg-slate-800 border border-slate-600 text-white text-sm rounded-lg p-2.5 pl-9 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Chercher un étudiant, un allo, une note..." autocomplete="off">
                        <i class="fa-solid fa-search absolute left-3 top-3.5 text-slate-500 text-sm"></i>
                        <button id="clearSearch" type="button" class="absolute right-3 top-3 text-slate-500 hover:text-white hidden focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="md:w-64">
                        <label class="sr-only" for="filterSort">Trier par</label>
                        <select id="filterSort" class="w-full bg-slate-800 border border-slate-600 text-white text-sm rounded-lg p-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="slot_soon">Créneau le plus proche</option>
                            <option value="recent">Plus récent</option>
                            <option value="status">Statut</option>
                            <option value="student">Étudiant</option>
                        </select>
                    </div>
                </div>
                <div id="filtersPanel" class="hidden md:flex flex-col gap-4">
                    <div class="flex flex-wrap gap-3">
                        <div class="relative">
                            <button id="statusFilterButton" type="button" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-slate-800 border border-slate-600 text-sm text-white hover:border-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                                Statuts
                                <span id="statusFilterCount" class="text-[10px] uppercase tracking-wide bg-indigo-500/20 text-indigo-200 px-2 py-0.5 rounded-full">2</span>
                                <i class="fa-solid fa-chevron-down text-xs text-slate-400"></i>
                            </button>
                            <div id="statusFilterMenu" class="absolute z-20 mt-2 w-56 rounded-xl bg-slate-900 border border-slate-700 shadow-xl p-2 hidden">
                                <div class="flex items-center justify-between px-2 py-1 text-xs text-slate-400">
                                    <span>Sélection rapide</span>
                                    <button id="statusSelectAll" type="button" class="text-indigo-300 hover:text-indigo-200">Tout</button>
                                </div>
                                <div class="flex flex-col gap-1 p-2">
                                    <label class="flex items-center gap-2 text-sm text-slate-200">
                                        <input type="checkbox" value="PENDING" class="status-checkbox text-indigo-500 focus:ring-indigo-500 rounded">
                                        En attente
                                    </label>
                                    <label class="flex items-center gap-2 text-sm text-slate-200">
                                        <input type="checkbox" value="ACCEPTED" class="status-checkbox text-indigo-500 focus:ring-indigo-500 rounded">
                                        En cours
                                    </label>
                                    <label class="flex items-center gap-2 text-sm text-slate-200">
                                        <input type="checkbox" value="DONE" class="status-checkbox text-indigo-500 focus:ring-indigo-500 rounded">
                                        Terminés
                                    </label>
                                    <label class="flex items-center gap-2 text-sm text-slate-200">
                                        <input type="checkbox" value="CANCELLED" class="status-checkbox text-indigo-500 focus:ring-indigo-500 rounded">
                                        Annulés
                                    </label>
                                </div>
                                <div class="px-2 py-1 text-xs text-slate-500 border-t border-slate-700">
                                    Sélection multiple autorisée
                                </div>
                            </div>
                        </div>
                        <select id="filterAllo" class="bg-slate-800 border border-slate-600 text-white text-sm rounded-lg p-2.5 focus:ring-indigo-500 focus:border-indigo-500 max-w-[220px]">
                            <option value="">Tous les allos</option>
                        </select>
                        <select id="filterAssignee" class="bg-slate-800 border border-slate-600 text-white text-sm rounded-lg p-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="all">Tous les agents</option>
                            <option value="unassigned">Non attribués</option>
                            <option value="me">Moi</option>
                        </select>
                        <select id="filterDateRange" class="bg-slate-800 border border-slate-600 text-white text-sm rounded-lg p-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="all">Tous les créneaux</option>
                            <option value="today">Aujourd’hui</option>
                            <option value="tomorrow">Demain</option>
                            <option value="week">Semaine</option>
                            <option value="custom">Plage personnalisée</option>
                        </select>
                        <div id="customDateRange" class="hidden flex items-center gap-2">
                            <input type="date" id="filterDateFrom" class="bg-slate-800 border border-slate-600 text-white text-xs rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <span class="text-slate-500 text-xs">→</span>
                            <input type="date" id="filterDateTo" class="bg-slate-800 border border-slate-600 text-white text-xs rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="quick-filter inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-slate-700 text-xs text-slate-300 hover:text-white hover:border-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500" data-quick="mine">
                            <i class="fa-solid fa-user-check text-indigo-300"></i> Mes allos
                        </button>
                        <button type="button" class="quick-filter inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-slate-700 text-xs text-slate-300 hover:text-white hover:border-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500" data-quick="unassigned">
                            <i class="fa-solid fa-user-slash text-amber-300"></i> Non attribués
                        </button>
                        <button type="button" class="quick-filter inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-slate-700 text-xs text-slate-300 hover:text-white hover:border-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500" data-quick="upcoming">
                            <i class="fa-regular fa-calendar text-emerald-300"></i> À venir (aujourd’hui/demain)
                        </button>
                    </div>
                </div>
                <div id="activeFilters" class="flex flex-wrap gap-2"></div>
            </div>
        </div>
        <div class="space-y-4" id="requestsContainer"></div>
    </div>
@endsection

@push('end_scripts')
    <style>
        .clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
    <script>
        const API = {
            allos: '/admin/api/allos',
            admins: '/admin/api/allo-admins',
            usages: '/admin/api/allo-usages',
        };

        const isSuperAdmin = @json(auth()->user()->hasRole('ROLE_SUPER_ADMIN'));
        const currentUserId = @json(auth()->id());

        let allos = [];
        let requests = [];
        let admins = [];
        let requestActionState = {};
        let filtersPanelOpen = false;

        const defaultRequestFilters = {
            search: '',
            alloId: '',
            statuses: ['PENDING', 'ACCEPTED'],
            assignee: 'all',
            dateRange: 'all',
            dateFrom: '',
            dateTo: '',
            sort: 'slot_soon',
            quickMine: false,
            quickUnassigned: false,
            quickUpcoming: false,
        };
        let requestFilters = { ...defaultRequestFilters };

        function csrf() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        }

        function jsonHeaders() {
            return { 'Accept': 'application/json' };
        }

        async function parseJsonResponse(response) {
            const contentType = response.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                const isLoginRedirect = response.redirected
                    && response.url
                    && (response.url.includes('/login') || response.url.includes('/admin/login'));
                const message = isLoginRedirect
                    ? 'Session expirée. Merci de te reconnecter.'
                    : `Réponse inattendue du serveur. (HTTP ${response.status})`;
                throw new Error(message);
            }
            return response.json();
        }

        function formatDateTime(iso) {
            if (!iso) return '-';
            const date = new Date(iso);
            return date.toLocaleString('fr-FR', { dateStyle: 'short', timeStyle: 'short' });
        }

        function formatRelativeTime(iso) {
            if (!iso) return '';
            const date = new Date(iso);
            const now = new Date();
            const diffMs = date.getTime() - now.getTime();
            const diffMinutes = Math.round(diffMs / 60000);
            const diffHours = Math.round(diffMs / 3600000);
            const diffDays = Math.round(diffMs / 86400000);

            if (Math.abs(diffMinutes) < 60) {
                return diffMinutes >= 0 ? `dans ${diffMinutes} min` : `il y a ${Math.abs(diffMinutes)} min`;
            }
            if (Math.abs(diffHours) < 24) {
                return diffHours >= 0 ? `dans ${diffHours} h` : `il y a ${Math.abs(diffHours)} h`;
            }
            return diffDays >= 0 ? `dans ${diffDays} j` : `il y a ${Math.abs(diffDays)} j`;
        }

        function debounce(fn, delayMs) {
            let timer;
            return (...args) => {
                if (timer) clearTimeout(timer);
                timer = setTimeout(() => fn(...args), delayMs);
            };
        }

        function requestStatusConfig(status) {
            switch (status) {
                case 'PENDING':
                    return {
                        label: 'En attente',
                        icon: 'fa-hourglass-half',
                        classes: 'bg-amber-900/20 text-amber-300 border-amber-900/30',
                    };
                case 'ACCEPTED':
                    return {
                        label: 'En cours',
                        icon: 'fa-circle-play',
                        classes: 'bg-sky-900/20 text-sky-300 border-sky-900/30',
                    };
                case 'DONE':
                    return {
                        label: 'Terminé',
                        icon: 'fa-circle-check',
                        classes: 'bg-emerald-900/20 text-emerald-300 border-emerald-900/30',
                    };
                case 'CANCELLED':
                    return {
                        label: 'Annulé',
                        icon: 'fa-circle-xmark',
                        classes: 'bg-rose-900/20 text-rose-300 border-rose-900/30',
                    };
                default:
                    return {
                        label: status || 'Inconnu',
                        icon: 'fa-circle-question',
                        classes: 'bg-slate-700/40 text-slate-300 border-slate-600',
                    };
            }
        }

        function isWithinUpcomingWindow(date) {
            const start = new Date();
            start.setHours(0, 0, 0, 0);
            const end = new Date(start);
            end.setDate(end.getDate() + 2);
            return date >= start && date < end;
        }

        function updateQueryParams() {
            const params = new URLSearchParams();
            if (requestFilters.search) params.set('q', requestFilters.search);
            if (requestFilters.alloId) params.set('allo', requestFilters.alloId);
            if (requestFilters.statuses.length && requestFilters.statuses.length !== defaultRequestFilters.statuses.length) {
                params.set('statuses', requestFilters.statuses.join(','));
            }
            if (requestFilters.assignee !== defaultRequestFilters.assignee) params.set('assignee', requestFilters.assignee);
            if (requestFilters.dateRange !== defaultRequestFilters.dateRange) params.set('dateRange', requestFilters.dateRange);
            if (requestFilters.dateFrom) params.set('dateFrom', requestFilters.dateFrom);
            if (requestFilters.dateTo) params.set('dateTo', requestFilters.dateTo);
            if (requestFilters.sort !== defaultRequestFilters.sort) params.set('sort', requestFilters.sort);
            if (requestFilters.quickMine) params.set('mine', '1');
            if (requestFilters.quickUnassigned) params.set('unassigned', '1');
            if (requestFilters.quickUpcoming) params.set('upcoming', '1');
            const query = params.toString();
            const url = query ? `${window.location.pathname}?${query}` : window.location.pathname;
            window.history.replaceState({}, '', url);
        }

        async function loadAllos() {
            try {
                const response = await fetch(API.allos, {
                    credentials: 'same-origin',
                    headers: jsonHeaders(),
                });
                const payload = await parseJsonResponse(response);
                if (!response.ok) throw new Error(payload.message || 'Impossible de charger les allos.');
                allos = payload.data || [];
                populateFilters();
                renderRequests();
            } catch (error) {
                showToast(error.message || 'Erreur lors du chargement des allos.', 'error');
            }
        }

        async function loadRequests() {
            try {
                const response = await fetch(`${API.usages}?status=ALL`, {
                    credentials: 'same-origin',
                    headers: jsonHeaders(),
                });
                const payload = await parseJsonResponse(response);
                if (!response.ok) throw new Error(payload.message || 'Impossible de charger les demandes.');
                requests = payload.data || [];
                renderRequests();
            } catch (error) {
                showToast(error.message || 'Erreur lors du chargement des demandes.', 'error');
            }
        }

        async function loadAdmins() {
            try {
                const response = await fetch(API.admins, {
                    credentials: 'same-origin',
                    headers: jsonHeaders(),
                });
                const payload = await parseJsonResponse(response);
                if (!response.ok) throw new Error(payload.message || 'Impossible de charger la liste des admins.');
                admins = payload.data || [];
                populateAssignees();
            } catch (error) {
                showToast(error.message || 'Erreur lors du chargement des admins.', 'error');
            }
        }

        function populateFilters() {
            const alloSelect = document.getElementById('filterAllo');
            if (!alloSelect) return;
            alloSelect.innerHTML = '<option value="">Tous les allos</option>';
            allos.forEach(a => {
                const opt = document.createElement('option');
                opt.value = a.id;
                opt.innerText = a.title;
                alloSelect.appendChild(opt);
            });
        }

        function populateAssignees() {
            const assigneeSelect = document.getElementById('filterAssignee');
            if (!assigneeSelect) return;
            const currentValue = assigneeSelect.value || 'all';
            const existing = Array.from(assigneeSelect.querySelectorAll('option[data-admin="true"]'));
            existing.forEach(opt => opt.remove());
            admins.forEach(admin => {
                const opt = document.createElement('option');
                opt.value = `admin:${admin.id}`;
                opt.innerText = admin.name;
                opt.dataset.admin = 'true';
                assigneeSelect.appendChild(opt);
            });
            assigneeSelect.value = currentValue;
        }

        function renderRequests() {
            const container = document.getElementById('requestsContainer');
            if (!container) return;
            container.innerHTML = '';

            let filtered = requests.filter(r => {
                const statusMatch = requestFilters.statuses.length === 0
                    || requestFilters.statuses.includes(r.status);
                const alloMatch = !requestFilters.alloId || r.allo_id == requestFilters.alloId;
                const assigneeMatch = (() => {
                    if (requestFilters.assignee === 'all') return true;
                    if (requestFilters.assignee === 'unassigned') return !r.handled_by_id;
                    if (requestFilters.assignee === 'me') return r.handled_by_id === currentUserId;
                    if (requestFilters.assignee.startsWith('admin:')) {
                        const id = parseInt(requestFilters.assignee.split(':')[1]);
                        return r.handled_by_id === id;
                    }
                    return true;
                })();
                const searchText = requestFilters.search.trim().toLowerCase();
                const searchMatch = !searchText
                    || `${r.user_name} ${r.user_email} ${r.allo_title} ${r.user_note || ''}`.toLowerCase().includes(searchText);
                const slotDate = r.slot_start_at ? new Date(r.slot_start_at) : null;
                const dateMatch = (() => {
                    if (!slotDate) return true;
                    if (requestFilters.dateRange === 'today') {
                        const today = new Date();
                        return slotDate.toDateString() === today.toDateString();
                    }
                    if (requestFilters.dateRange === 'tomorrow') {
                        const tomorrow = new Date();
                        tomorrow.setDate(tomorrow.getDate() + 1);
                        return slotDate.toDateString() === tomorrow.toDateString();
                    }
                    if (requestFilters.dateRange === 'week') {
                        const now = new Date();
                        const weekEnd = new Date(now);
                        weekEnd.setDate(weekEnd.getDate() + 7);
                        return slotDate >= now && slotDate <= weekEnd;
                    }
                    if (requestFilters.dateRange === 'custom') {
                        const from = requestFilters.dateFrom ? new Date(requestFilters.dateFrom) : null;
                        const to = requestFilters.dateTo ? new Date(requestFilters.dateTo) : null;
                        if (from && slotDate < from) return false;
                        if (to) {
                            const end = new Date(to);
                            end.setHours(23, 59, 59, 999);
                            if (slotDate > end) return false;
                        }
                    }
                    return true;
                })();
                const quickMineMatch = !requestFilters.quickMine || r.handled_by_id === currentUserId;
                const quickUnassignedMatch = !requestFilters.quickUnassigned || !r.handled_by_id;
                const quickUpcomingMatch = !requestFilters.quickUpcoming || (slotDate && isWithinUpcomingWindow(slotDate));

                return statusMatch && alloMatch && assigneeMatch && searchMatch && dateMatch && quickMineMatch && quickUnassignedMatch && quickUpcomingMatch;
            });

            const pendingCount = requests.filter(r => r.status === 'PENDING').length;
            const badge = document.getElementById('pendingCount');
            if (badge) {
                badge.innerText = pendingCount;
                badge.classList.toggle('hidden', pendingCount === 0);
            }

            const resultsCount = document.getElementById('resultsCount');
            if (resultsCount) {
                resultsCount.innerText = `${filtered.length} allos`;
            }

            filtered = sortRequests(filtered);

            if (filtered.length === 0) {
                const hasActiveFilters = !isDefaultFilters();
                container.innerHTML = `
                    <div class="text-center py-16 text-slate-500 border border-dashed border-slate-700 rounded-2xl">
                        <i class="fa-solid fa-filter text-4xl mb-4 opacity-30"></i>
                        <p class="text-sm">${hasActiveFilters ? 'Aucun résultat — retirez un filtre pour élargir la recherche.' : 'Aucune demande pour le moment.'}</p>
                    </div>
                `;
                renderActiveFilters();
                updateResetButtonState();
                return;
            }

            filtered.forEach(r => {
                const statusConfig = requestStatusConfig(r.status);
                const isLoading = !!requestActionState[r.id];
                const cardBorder = r.status === 'PENDING'
                    ? 'border-amber-500/30'
                    : r.status === 'ACCEPTED'
                        ? 'border-sky-500/30'
                        : r.status === 'DONE'
                            ? 'border-emerald-500/30'
                            : 'border-rose-500/30';
                const assignControls = renderAssignControls(r, isLoading);
                const handlerLine = r.handled_by_name ? `Assigné à ${r.handled_by_name}` : 'Non attribué';
                const relativeTime = formatRelativeTime(r.slot_start_at);
                const noteText = r.user_note || 'Aucune note.';
                const noteId = `note_${r.id}`;
                const canReopen = isSuperAdmin && r.status === 'DONE';

                const actions = renderActions(r, isLoading, canReopen);

                container.innerHTML += `
                    <div class="bg-slate-800 rounded-2xl p-4 border ${cardBorder} flex flex-col gap-4 transition-all">
                        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                            <div class="flex items-start gap-4">
                                <div class="h-11 w-11 rounded-full bg-slate-700 flex items-center justify-center text-indigo-300 font-bold uppercase">${r.user_name.substring(0,2)}</div>
                                <div class="space-y-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="font-bold text-white text-lg">${r.allo_title}</h3>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full border text-xs font-semibold uppercase tracking-wide ${statusConfig.classes}">
                                            <i class="fa-solid ${statusConfig.icon}"></i> ${statusConfig.label}
                                        </span>
                                    </div>
                                    <p class="text-sm text-slate-400 flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center gap-1"><i class="fa-solid fa-user text-slate-500"></i> ${r.user_name}</span>
                                        <span class="text-slate-500">•</span>
                                        <span class="inline-flex items-center gap-1"><i class="fa-regular fa-clock text-slate-500"></i> <span class="text-white font-mono">${formatDateTime(r.slot_start_at)}</span></span>
                                        <span class="text-xs text-slate-400 bg-slate-700/60 px-2 py-0.5 rounded-full">${relativeTime}</span>
                                    </p>
                                    <p class="text-xs uppercase tracking-wide text-slate-500">${handlerLine}</p>
                                </div>
                            </div>
                            <div class="flex flex-col gap-2 w-full lg:w-auto border-t lg:border-t-0 border-slate-700 pt-3 lg:pt-0">
                                ${assignControls}
                                <div class="flex flex-wrap items-center gap-2 justify-end">${actions}</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-slate-400">
                            <div class="space-y-2">
                                <p class="uppercase tracking-wide text-[10px] text-slate-500">Note étudiant</p>
                                <p id="${noteId}" class="text-sm text-slate-200 clamp-3">${noteText}</p>
                                ${noteText.length > 140 ? `<button type="button" class="toggle-note text-xs text-indigo-300 hover:text-indigo-200" data-note="${noteId}">Voir plus</button>` : ''}
                            </div>
                        </div>
                    </div>`;
            });

            bindNoteToggles();
            bindAssignControls();
            renderActiveFilters();
            updateResetButtonState();
        }

        async function updateStatus(id, status) {
            if (requestActionState[id]) return;
            const payload = { status };
            await updateUsage(id, payload, status);
        }

        function getAssignableAdmins(alloId) {
            const allo = allos.find(item => item.id === alloId);
            const alloAdmins = Array.isArray(allo?.admins) ? allo.admins : [];
            if (alloAdmins.length > 0) {
                return alloAdmins;
            }
            return admins;
        }

        function renderAssignControls(request, isLoading) {
            if (request.status !== 'PENDING' && request.status !== 'ACCEPTED') {
                return '';
            }

            const availableAdmins = getAssignableAdmins(request.allo_id);
            if (!availableAdmins || availableAdmins.length === 0) {
                return '<span class="text-xs text-slate-500 mr-2">Aucun responsable.</span>';
            }

            const selectId = `assignSelect_${request.id}`;
            const options = availableAdmins.map(admin => {
                const selected = admin.id === request.handled_by_id ? 'selected' : '';
                return `<option value="${admin.id}" ${selected}>${admin.name}</option>`;
            }).join('');
            const noneSelected = request.handled_by_id ? '' : 'selected';
            const buttonLabel = request.status === 'PENDING' ? 'Attribuer' : 'Réattribuer';
            const isDisabled = isLoading;
            const disabledClass = isDisabled ? 'opacity-50 cursor-not-allowed' : '';
            return `
                <div class="flex flex-wrap items-center gap-2">
                    <select id="${selectId}" data-assign-select="${request.id}" class="bg-slate-800 border border-slate-600 text-white text-xs rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="" ${noneSelected}>Personne</option>
                        ${options}
                    </select>
                    <button onclick="assignUsage(${request.id})" data-assign-button="${request.id}" data-current-handler="${request.handled_by_id || ''}" class="bg-slate-700 hover:bg-slate-600 text-white text-xs px-3 py-1.5 rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 ${disabledClass}" ${isDisabled ? 'disabled' : ''} title="${isDisabled ? 'Action en cours' : 'Sélectionnez un agent pour attribuer'}">${buttonLabel}</button>
                    ${request.status === 'ACCEPTED' ? `<button onclick="updateStatus(${request.id}, 'PENDING')" class="text-xs px-3 py-1.5 rounded border border-slate-600 text-slate-200 hover:text-yellow-200 hover:border-yellow-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 ${disabledClass}" ${isDisabled ? 'disabled' : ''} title="${isDisabled ? 'Action en cours' : 'Relâcher la demande'}">Relâcher</button>` : ''}
                </div>
            `;
        }

        async function assignUsage(id) {
            if (requestActionState[id]) return;
            const select = document.getElementById(`assignSelect_${id}`);
            if (!select) return;
            const handlerId = select.value ? parseInt(select.value) : null;
            if (!handlerId) {
                await updateUsage(id, { status: 'PENDING', handled_by_id: null }, 'PENDING');
                return;
            }
            await updateUsage(id, { status: 'ACCEPTED', handled_by_id: handlerId });
        }

        function renderActions(request, isLoading, canReopen) {
            const disabledAttrs = isLoading ? 'disabled' : '';
            const disabledClass = isLoading ? 'opacity-50 cursor-not-allowed' : '';
            const loadingLabel = isLoading ? '<i class="fa-solid fa-spinner fa-spin mr-2"></i> En cours...' : '';
            if (request.status === 'PENDING') {
                return `
                    <button onclick="updateStatus(${request.id}, 'CANCELLED')" class="text-xs px-3 py-1.5 rounded border border-slate-600 text-slate-300 hover:text-rose-200 hover:border-rose-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 ${disabledClass}" ${disabledAttrs} title="${isLoading ? 'Action en cours' : 'Annuler la demande'}">Annuler</button>
                    <button onclick="updateStatus(${request.id}, 'ACCEPTED')" class="text-xs px-4 py-2 rounded bg-indigo-600 hover:bg-indigo-500 text-white font-semibold shadow focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 ${disabledClass}" ${disabledAttrs}>${loadingLabel || 'Prendre en charge'}</button>
                `;
            }
            if (request.status === 'ACCEPTED') {
                return `
                    <button onclick="updateStatus(${request.id}, 'DONE')" class="text-xs px-4 py-2 rounded bg-emerald-600 hover:bg-emerald-500 text-white font-semibold shadow focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 ${disabledClass}" ${disabledAttrs}>${loadingLabel || 'Terminer'}</button>
                `;
            }
            if (request.status === 'DONE') {
                const doneBy = request.done_by_name || request.handled_by_name || 'Admin';
                return `
                    <span class="text-xs text-slate-500 mr-3">Géré par ${doneBy}</span>
                    ${canReopen ? `<button onclick="updateStatus(${request.id}, 'PENDING')" class="text-xs px-3 py-1.5 rounded border border-slate-600 text-slate-200 hover:text-yellow-200 hover:border-yellow-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 ${disabledClass}" ${disabledAttrs}><i class="fa-solid fa-rotate-left mr-1"></i> Rouvrir</button>` : ''}
                `;
            }
            return `
                <button onclick="updateStatus(${request.id}, 'PENDING')" class="text-xs px-3 py-1.5 rounded border border-slate-600 text-slate-200 hover:text-yellow-200 hover:border-yellow-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 ${disabledClass}" ${disabledAttrs}><i class="fa-solid fa-rotate-left mr-1"></i> Remettre en attente</button>
            `;
        }

        function bindNoteToggles() {
            document.querySelectorAll('.toggle-note').forEach(button => {
                button.addEventListener('click', (event) => {
                    const targetId = event.currentTarget.dataset.note;
                    const note = document.getElementById(targetId);
                    if (!note) return;
                    const isClamped = note.classList.contains('clamp-3');
                    note.classList.toggle('clamp-3', !isClamped);
                    event.currentTarget.innerText = isClamped ? 'Voir moins' : 'Voir plus';
                });
            });
        }

        function bindAssignControls() {
            document.querySelectorAll('[data-assign-select]').forEach(select => {
                const requestId = select.dataset.assignSelect;
                const button = document.querySelector(`[data-assign-button="${requestId}"]`);
                if (!button) return;
                const currentHandler = button.dataset.currentHandler;
                const updateState = () => {
                    const selected = select.value;
                    const isLoading = button.hasAttribute('disabled') && button.classList.contains('cursor-not-allowed');
                    const isNoChange = selected === currentHandler || (!selected && !currentHandler);
                    const shouldDisable = isLoading || isNoChange;
                    button.disabled = shouldDisable;
                    button.classList.toggle('opacity-50', shouldDisable);
                    button.classList.toggle('cursor-not-allowed', shouldDisable);
                    button.title = shouldDisable
                        ? (isLoading ? 'Action en cours' : 'Sélectionnez un agent différent pour attribuer')
                        : 'Attribuer cet agent';
                };
                updateState();
                select.addEventListener('change', updateState);
            });
        }

        function sortRequests(list) {
            const statusOrder = { PENDING: 1, ACCEPTED: 2, DONE: 3, CANCELLED: 4 };
            return [...list].sort((a, b) => {
                if (requestFilters.sort === 'recent') {
                    return new Date(b.created_at || b.slot_start_at) - new Date(a.created_at || a.slot_start_at);
                }
                if (requestFilters.sort === 'status') {
                    return (statusOrder[a.status] || 99) - (statusOrder[b.status] || 99);
                }
                if (requestFilters.sort === 'student') {
                    return (a.user_name || '').localeCompare(b.user_name || '');
                }
                const aDate = a.slot_start_at ? new Date(a.slot_start_at) : new Date(8640000000000000);
                const bDate = b.slot_start_at ? new Date(b.slot_start_at) : new Date(8640000000000000);
                return aDate - bDate;
            });
        }

        function renderActiveFilters() {
            const container = document.getElementById('activeFilters');
            if (!container) return;
            container.innerHTML = '';
            const chips = [];

            if (requestFilters.search) chips.push({ label: `Recherche : ${requestFilters.search}`, key: 'search' });
            if (requestFilters.alloId) {
                const allo = allos.find(item => String(item.id) === String(requestFilters.alloId));
                chips.push({ label: `Allo : ${allo?.title || 'Filtre'}`, key: 'alloId' });
            }
            const isDefaultStatuses = JSON.stringify(requestFilters.statuses) === JSON.stringify(defaultRequestFilters.statuses);
            if (!isDefaultStatuses) {
                chips.push({ label: `Statuts : ${requestFilters.statuses.join(', ')}`, key: 'statuses' });
            }
            if (requestFilters.assignee !== 'all') {
                const label = requestFilters.assignee === 'unassigned'
                    ? 'Non attribués'
                    : requestFilters.assignee === 'me'
                        ? 'Moi'
                        : admins.find(a => `admin:${a.id}` === requestFilters.assignee)?.name || 'Agent';
                chips.push({ label: `Assigné : ${label}`, key: 'assignee' });
            }
            if (requestFilters.dateRange !== 'all') {
                const label = requestFilters.dateRange === 'today'
                    ? 'Créneau : aujourd’hui'
                    : requestFilters.dateRange === 'tomorrow'
                        ? 'Créneau : demain'
                        : requestFilters.dateRange === 'week'
                            ? 'Créneau : semaine'
                            : `Créneau : ${requestFilters.dateFrom || '...'} → ${requestFilters.dateTo || '...'}`;
                chips.push({ label, key: 'dateRange' });
            }
            if (requestFilters.quickMine) chips.push({ label: 'Mes allos', key: 'quickMine' });
            if (requestFilters.quickUnassigned) chips.push({ label: 'Non attribués', key: 'quickUnassigned' });
            if (requestFilters.quickUpcoming) chips.push({ label: 'À venir', key: 'quickUpcoming' });

            chips.forEach(chip => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800 border border-slate-700 text-xs text-slate-200 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500';
                button.innerHTML = `${chip.label} <i class="fa-solid fa-xmark text-[10px]"></i>`;
                button.addEventListener('click', () => removeFilterChip(chip.key));
                container.appendChild(button);
            });
        }

        function removeFilterChip(key) {
            if (key === 'search') requestFilters.search = '';
            if (key === 'alloId') requestFilters.alloId = '';
            if (key === 'statuses') requestFilters.statuses = [...defaultRequestFilters.statuses];
            if (key === 'assignee') requestFilters.assignee = 'all';
            if (key === 'dateRange') {
                requestFilters.dateRange = 'all';
                requestFilters.dateFrom = '';
                requestFilters.dateTo = '';
            }
            if (key === 'quickMine') requestFilters.quickMine = false;
            if (key === 'quickUnassigned') requestFilters.quickUnassigned = false;
            if (key === 'quickUpcoming') requestFilters.quickUpcoming = false;
            syncFilterInputs();
            updateQueryParams();
            renderRequests();
        }

        function updateResetButtonState() {
            const resetButton = document.getElementById('resetFiltersButton');
            if (!resetButton) return;
            resetButton.disabled = isDefaultFilters();
        }

        function isDefaultFilters() {
            return JSON.stringify(requestFilters) === JSON.stringify(defaultRequestFilters);
        }

        function syncFilterInputs() {
            document.getElementById('filterSearch').value = requestFilters.search;
            document.getElementById('filterAllo').value = requestFilters.alloId;
            document.getElementById('filterAssignee').value = requestFilters.assignee;
            document.getElementById('filterDateRange').value = requestFilters.dateRange;
            document.getElementById('filterDateFrom').value = requestFilters.dateFrom;
            document.getElementById('filterDateTo').value = requestFilters.dateTo;
            document.getElementById('filterSort').value = requestFilters.sort;
            document.getElementById('customDateRange').classList.toggle('hidden', requestFilters.dateRange !== 'custom');
            document.getElementById('clearSearch').classList.toggle('hidden', !requestFilters.search);
            document.querySelectorAll('.status-checkbox').forEach(cb => {
                cb.checked = requestFilters.statuses.includes(cb.value);
            });
            document.getElementById('statusFilterCount').innerText = `${requestFilters.statuses.length}`;
            document.querySelectorAll('.quick-filter').forEach(button => {
                const quick = button.dataset.quick;
                const isActive = (quick === 'mine' && requestFilters.quickMine)
                    || (quick === 'unassigned' && requestFilters.quickUnassigned)
                    || (quick === 'upcoming' && requestFilters.quickUpcoming);
                button.classList.toggle('bg-indigo-500/20', isActive);
                button.classList.toggle('border-indigo-400/50', isActive);
                button.classList.toggle('text-indigo-100', isActive);
            });
        }

        function bindFilterControls() {
            const searchInput = document.getElementById('filterSearch');
            const clearSearch = document.getElementById('clearSearch');
            const alloSelect = document.getElementById('filterAllo');
            const assigneeSelect = document.getElementById('filterAssignee');
            const dateRangeSelect = document.getElementById('filterDateRange');
            const dateFrom = document.getElementById('filterDateFrom');
            const dateTo = document.getElementById('filterDateTo');
            const sortSelect = document.getElementById('filterSort');
            const resetButton = document.getElementById('resetFiltersButton');

            const applySearch = debounce(() => {
                requestFilters.search = searchInput.value.trim();
                syncFilterInputs();
                updateQueryParams();
                renderRequests();
            }, 300);

            searchInput.addEventListener('input', applySearch);
            clearSearch.addEventListener('click', () => {
                requestFilters.search = '';
                syncFilterInputs();
                updateQueryParams();
                renderRequests();
            });
            alloSelect.addEventListener('change', () => {
                requestFilters.alloId = alloSelect.value;
                updateQueryParams();
                renderRequests();
            });
            assigneeSelect.addEventListener('change', () => {
                requestFilters.assignee = assigneeSelect.value;
                updateQueryParams();
                renderRequests();
            });
            dateRangeSelect.addEventListener('change', () => {
                requestFilters.dateRange = dateRangeSelect.value;
                if (requestFilters.dateRange !== 'custom') {
                    requestFilters.dateFrom = '';
                    requestFilters.dateTo = '';
                }
                syncFilterInputs();
                updateQueryParams();
                renderRequests();
            });
            dateFrom.addEventListener('change', () => {
                requestFilters.dateFrom = dateFrom.value;
                updateQueryParams();
                renderRequests();
            });
            dateTo.addEventListener('change', () => {
                requestFilters.dateTo = dateTo.value;
                updateQueryParams();
                renderRequests();
            });
            sortSelect.addEventListener('change', () => {
                requestFilters.sort = sortSelect.value;
                updateQueryParams();
                renderRequests();
            });
            if (resetButton) {
                resetButton.addEventListener('click', () => {
                    requestFilters = { ...defaultRequestFilters };
                    syncFilterInputs();
                    updateQueryParams();
                    renderRequests();
                });
            }

            document.querySelectorAll('.status-checkbox').forEach(cb => {
                cb.addEventListener('change', () => {
                    requestFilters.statuses = Array.from(document.querySelectorAll('.status-checkbox:checked')).map(input => input.value);
                    syncFilterInputs();
                    updateQueryParams();
                    renderRequests();
                });
            });

            document.getElementById('statusSelectAll').addEventListener('click', () => {
                requestFilters.statuses = ['PENDING', 'ACCEPTED', 'DONE', 'CANCELLED'];
                syncFilterInputs();
                updateQueryParams();
                renderRequests();
            });

            document.querySelectorAll('.quick-filter').forEach(button => {
                button.addEventListener('click', () => {
                    const quick = button.dataset.quick;
                    if (quick === 'mine') requestFilters.quickMine = !requestFilters.quickMine;
                    if (quick === 'unassigned') requestFilters.quickUnassigned = !requestFilters.quickUnassigned;
                    if (quick === 'upcoming') requestFilters.quickUpcoming = !requestFilters.quickUpcoming;
                    syncFilterInputs();
                    updateQueryParams();
                    renderRequests();
                });
            });

            const statusButton = document.getElementById('statusFilterButton');
            const statusMenu = document.getElementById('statusFilterMenu');
            statusButton.addEventListener('click', () => {
                statusMenu.classList.toggle('hidden');
            });
            document.addEventListener('click', (event) => {
                if (!statusButton.contains(event.target) && !statusMenu.contains(event.target)) {
                    statusMenu.classList.add('hidden');
                }
            });

            const openFiltersButton = document.getElementById('openFiltersButton');
            if (openFiltersButton) {
                openFiltersButton.addEventListener('click', () => {
                    filtersPanelOpen = !filtersPanelOpen;
                    document.getElementById('filtersPanel').classList.toggle('hidden', !filtersPanelOpen);
                });
            }
        }

        async function updateUsage(id, payload, actionLabel = '') {
            try {
                requestActionState[id] = true;
                renderRequests();
                const requiresConfirm = actionLabel === 'CANCELLED' || actionLabel === 'DONE' || actionLabel === 'PENDING';
                if (requiresConfirm && payload.status) {
                    const confirmed = await confirmAction(id, payload.status, true);
                    if (!confirmed) {
                        requestActionState[id] = false;
                        renderRequests();
                        return;
                    }
                }
                const response = await fetch(`${API.usages}/${id}`, {
                    method: 'PUT',
                    headers: {
                        ...jsonHeaders(),
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf(),
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload),
                });
                const result = await parseJsonResponse(response);
                if (!response.ok) throw new Error(result.message || 'Mise à jour impossible.');
                showToast('Demande mise à jour.', 'success');
                await loadRequests();
            } catch (error) {
                showToast(error.message || 'Erreur lors de la mise à jour.', 'error');
            } finally {
                requestActionState[id] = false;
                renderRequests();
            }
        }

        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            if (!container) return;
            const toast = document.createElement('div');
            const bgClass = type === 'success' ? 'bg-green-600' : 'bg-red-600';
            toast.className = `flex items-center p-4 rounded shadow-lg text-white ${bgClass} toast-enter pointer-events-auto`;
            toast.innerHTML = `<i class="fa-solid ${type === 'success' ? 'fa-check' : 'fa-circle-xmark'} text-lg mr-3"></i><span class="text-sm font-medium">${message}</span>`;
            container.appendChild(toast);
            setTimeout(() => { toast.classList.remove('toast-enter'); toast.classList.add('toast-exit'); toast.addEventListener('animationend', () => toast.remove()); }, 3000);
        }

        function confirmAction(id, status, isBlocking = false) {
            const messages = {
                CANCELLED: 'Voulez-vous annuler cette demande ?',
                DONE: 'Confirmer la fin de cette demande ?',
                PENDING: 'Relâcher cette demande ?',
            };
            return new Promise((resolve) => {
                const modal = document.getElementById('confirmModal');
                const text = document.getElementById('confirmModalText');
                const confirmButton = document.getElementById('confirmModalConfirm');
                const cancelButton = document.getElementById('confirmModalCancel');
                if (!modal || !text || !confirmButton || !cancelButton) {
                    resolve(true);
                    return;
                }
                text.innerText = messages[status] || 'Confirmer cette action ?';
                modal.classList.remove('hidden');
                const closeModal = (value) => {
                    modal.classList.add('hidden');
                    confirmButton.onclick = null;
                    cancelButton.onclick = null;
                    resolve(value);
                };
                confirmButton.onclick = () => closeModal(true);
                cancelButton.onclick = () => closeModal(false);
                if (!isBlocking) {
                    confirmButton.focus();
                }
            });
        }

        syncFilterInputs();
        bindFilterControls();
        loadAdmins();
        loadAllos();
        loadRequests();
    </script>
    <div id="confirmModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-slate-900/80"></div>
            <div class="relative bg-slate-800 border border-slate-700 rounded-2xl shadow-xl w-full max-w-md p-6">
                <div class="flex items-center gap-3 text-slate-200">
                    <i class="fa-solid fa-triangle-exclamation text-amber-400 text-lg"></i>
                    <h3 class="text-lg font-semibold">Confirmer l’action</h3>
                </div>
                <p id="confirmModalText" class="text-sm text-slate-400 mt-3">Confirmer cette action ?</p>
                <div class="mt-6 flex justify-end gap-3">
                    <button id="confirmModalCancel" type="button" class="px-4 py-2 rounded-lg border border-slate-600 text-slate-200 hover:text-white hover:border-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">Annuler</button>
                    <button id="confirmModalConfirm" type="button" class="px-4 py-2 rounded-lg bg-rose-600 text-white hover:bg-rose-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-400">Confirmer</button>
                </div>
            </div>
        </div>
    </div>
@endpush
