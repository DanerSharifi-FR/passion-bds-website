@extends('admin.layout')

@section('title', "Gestion des Allos - P'AS'SION BDS")

@section('top_bar_buttons')
    <div class="flex bg-slate-900 p-1 rounded-lg border border-slate-700 ml-4">
        <button onclick="switchView('requests')" id="tabRequests" class="px-4 py-1.5 rounded-md text-sm font-medium transition-all bg-indigo-600 text-white shadow">
            <i class="fa-solid fa-bell mr-2"></i> Demandes <span class="ml-1 bg-red-500 text-white text-[10px] px-1.5 rounded-full" id="pendingCount">3</span>
        </button>
        <button onclick="switchView('catalog')" id="tabCatalog" class="px-4 py-1.5 rounded-md text-sm font-medium text-slate-400 hover:text-white transition-all">
            <i class="fa-solid fa-store mr-2"></i> Catalogue & Créneaux
        </button>
    </div>
@endsection

@section('content')
    <!-- VIEW: REQUESTS -->
    <div id="viewRequests">
        <div class="flex flex-col md:flex-row items-start md:items-center gap-4 mb-6">
            <h2 class="text-xl font-bold text-white min-w-fit">Suivi des Allos</h2>
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
                <button onclick="resetFilters()" class="text-xs text-slate-400 hover:text-white underline px-2">Réinitialiser</button>
            </div>
        </div>
        <div class="space-y-4" id="requestsContainer"></div>
    </div>

    <!-- VIEW: CATALOG -->
    <div id="viewCatalog" class="hidden">
        <div class="flex flex-col gap-4 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h2 class="text-xl font-bold text-white">Catalogue des Allos</h2>
                <button onclick="openAlloModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors shadow">
                    <i class="fa-solid fa-plus mr-2"></i> Nouvel Allo
                </button>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-slate-400 mb-1" for="catalogTitleFilter">Filtrer par titre</label>
                    <input id="catalogTitleFilter" type="text" class="w-full bg-slate-800 border border-slate-600 text-white text-sm rounded-lg p-2 focus:ring-indigo-500" placeholder="Ex: petit dej, massage...">
                </div>
                <div class="sm:w-56">
                    <label class="block text-xs font-medium text-slate-400 mb-1" for="catalogStatusFilter">Statut</label>
                    <select id="catalogStatusFilter" class="w-full bg-slate-800 border border-slate-600 text-white text-sm rounded-lg p-2 focus:ring-indigo-500">
                        <option value="ALL">Tous les statuts</option>
                        <option value="DRAFT">Brouillon</option>
                        <option value="OPEN">Ouvert</option>
                        <option value="CLOSED">Fermé</option>
                        <option value="DISABLED">Désactivé</option>
                    </select>
                </div>
                <div class="sm:w-40 sm:flex sm:items-end">
                    <button id="catalogResetFilters" class="w-full bg-slate-700 hover:bg-slate-600 text-white text-sm rounded-lg px-3 py-2 transition-colors">
                        Réinitialiser
                    </button>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6" id="catalogGrid"></div>
    </div>
@endsection

@push('end_scripts')
    <!-- ALLO MODAL -->
    <div id="alloModal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/80 transition-opacity" onclick="closeAlloModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="relative inline-block align-bottom bg-slate-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-slate-700">
                <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-white mb-4" id="alloModalTitle">Créer un Allo</h3>
                    <form id="alloForm" class="space-y-4">
                        <input type="hidden" id="editAlloId">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Titre</label>
                            <input type="text" id="alloTitle" class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg block p-2.5 focus:ring-yellow-500 focus:border-yellow-500" placeholder="ex: P'tit Dej au lit" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Durée Slot (min)</label>
                            <input type="number" id="alloDuration" class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg block p-2.5" placeholder="15" value="15" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Marge de sécurité (min)</label>
                            <input type="number" id="alloSecurityMargin" class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg block p-2.5" placeholder="0" value="0" min="0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Limite par jour / utilisateur</label>
                            <input type="number" id="alloDailyLimit" class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg block p-2.5" placeholder="Aucune limite" min="1">
                            <p class="text-xs text-slate-500 mt-1">Laisse vide pour aucune limite. Sinon, indique un nombre &gt; 0.</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-slate-300">Capacité par créneau</label>
                            <div class="flex items-center gap-3 text-xs text-slate-300">
                                <label class="inline-flex items-center gap-2">
                                    <input type="radio" name="capacityMode" value="admins" checked class="text-indigo-500 focus:ring-indigo-500">
                                    Auto (nombre d'admins)
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input type="radio" name="capacityMode" value="fixed" class="text-indigo-500 focus:ring-indigo-500">
                                    Fixe
                                </label>
                            </div>
                            <div id="fixedCapacityFields" class="hidden">
                                <input type="number" id="alloFixedCapacity" class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg block p-2.5" placeholder="Ex: 80" min="1">
                                <p class="text-xs text-slate-500 mt-1">Indique le nombre de places disponibles par créneau.</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Statut</label>
                            <select id="alloStatus" class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg block p-2.5">
                                <option value="DRAFT">Brouillon</option>
                                <option value="OPEN">Ouvert</option>
                                <option value="CLOSED">Fermé</option>
                                <option value="DISABLED">Désactivé</option>
                            </select>
                        </div>
                        <div class="p-3 bg-slate-700/30 rounded border border-slate-600 space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-300">Planification</label>
                                <p class="text-xs text-slate-500">Choisis le mode de création des créneaux (fenêtre unique ou créneaux multiples).</p>
                            </div>
                            <div class="flex flex-col gap-2 text-xs text-slate-300">
                                <label class="inline-flex items-center gap-2">
                                    <input type="radio" name="scheduleMode" value="window" checked class="text-indigo-500 focus:ring-indigo-500">
                                    Fenêtre unique
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input type="radio" name="scheduleMode" value="date" class="text-indigo-500 focus:ring-indigo-500">
                                    Créneaux datés (plusieurs dates)
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input type="radio" name="scheduleMode" value="range" class="text-indigo-500 focus:ring-indigo-500">
                                    Plage globale + fenêtres horaires
                                </label>
                            </div>
                            <div id="scheduleWindowFields" class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="text-xs text-slate-500 mb-1 block">Ouverture</label>
                                    <input type="datetime-local" id="alloStart" class="w-full bg-slate-800 border border-slate-600 text-white text-xs rounded p-2">
                                </div>
                                <div>
                                    <label class="text-xs text-slate-500 mb-1 block">Fermeture</label>
                                    <input type="datetime-local" id="alloEnd" class="w-full bg-slate-800 border border-slate-600 text-white text-xs rounded p-2">
                                </div>
                            </div>
                            <div id="scheduleDateFields" class="hidden space-y-2">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs text-slate-400">Ajoute un créneau par date (date + heure de début/fin).</p>
                                    <button type="button" id="addDateSlot" class="text-xs text-indigo-300 hover:text-indigo-200">+ Ajouter un créneau</button>
                                </div>
                                <div id="dateSlotsContainer" class="space-y-2"></div>
                            </div>
                            <div id="scheduleRangeFields" class="hidden space-y-2">
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="text-xs text-slate-500 mb-1 block">Début de période</label>
                                        <input type="date" id="rangeStartDate" class="w-full bg-slate-800 border border-slate-600 text-white text-xs rounded p-2">
                                    </div>
                                    <div>
                                        <label class="text-xs text-slate-500 mb-1 block">Fin de période</label>
                                        <input type="date" id="rangeEndDate" class="w-full bg-slate-800 border border-slate-600 text-white text-xs rounded p-2">
                                    </div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <p class="text-xs text-slate-400">Ajoute plusieurs fenêtres horaires pour cette plage.</p>
                                    <button type="button" id="addRangeSlot" class="text-xs text-indigo-300 hover:text-indigo-200">+ Ajouter une fenêtre</button>
                                </div>
                                <div id="rangeSlotsContainer" class="space-y-2"></div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Attribution Admins</label>
                            <div class="bg-slate-900 border border-slate-600 rounded-lg p-2 max-h-32 overflow-y-auto" id="adminList"></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Description <span class="text-passion-red">*</span></label>
                            <textarea id="alloDesc" rows="2" class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg block p-2.5" placeholder="Détails de l'allo..." required></textarea>
                        </div>
                    </form>
                </div>
                <div class="bg-slate-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-700">
                    <button type="button" onclick="submitAllo()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">Enregistrer</button>
                    <button type="button" onclick="closeAlloModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-slate-600 shadow-sm px-4 py-2 bg-slate-800 text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Annuler</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const API = {
            allos: '/admin/api/allos',
            admins: '/admin/api/allo-admins',
            usages: '/admin/api/allo-usages',
        };

        const isSuperAdmin = @json(auth()->user()->hasRole('ROLE_SUPER_ADMIN'));

        let allos = [];
        let requests = [];
        let admins = [];
        let catalogFilters = {
            title: '',
            status: 'ALL',
        };
        let scheduleMode = 'window';
        let dateSpecificSlots = [];
        let rangeTimeSlots = [];
        let rangeDateStart = '';
        let rangeDateEnd = '';
        let isDraftMode = true;

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

        function formatDateLabel(date) {
            return new Intl.DateTimeFormat('fr-FR', {
                weekday: 'short',
                day: '2-digit',
                month: 'short',
            }).format(date);
        }

        function formatTimeLabel(date) {
            return new Intl.DateTimeFormat('fr-FR', {
                hour: '2-digit',
                minute: '2-digit',
            }).format(date);
        }

        function statusBadgeClasses(status) {
            switch (status) {
                case 'OPEN':
                    return 'bg-emerald-900/20 text-emerald-300 border-emerald-900/30';
                case 'CLOSED':
                    return 'bg-amber-900/20 text-amber-300 border-amber-900/30';
                case 'DISABLED':
                    return 'bg-slate-700/40 text-slate-300 border-slate-600';
                default:
                    return 'bg-indigo-900/20 text-indigo-300 border-indigo-900/30';
            }
        }

        function formatDateOnly(dateString) {
            if (!dateString) return '';
            const [year, month, day] = dateString.split('-');
            if (!year || !month || !day) return dateString;
            return `${day}/${month}/${year}`;
        }

        function formatTimeOnly(timeString) {
            if (!timeString) return '';
            return timeString.slice(0, 5);
        }

        function formatWindowInfo(startAt, endAt) {
            if (!startAt || !endAt) {
                return `<span class="text-slate-500 text-xs">Non planifié</span>`;
            }

            const start = new Date(startAt);
            const end = new Date(endAt);
            const sameDay = start.toDateString() === end.toDateString();
            const dateLabel = formatDateLabel(start);
            const startTime = formatTimeLabel(start);
            const endTime = formatTimeLabel(end);

            if (sameDay) {
                return `
                    <div class="flex items-center gap-2">
                        <i class="fa-regular fa-calendar text-slate-400"></i>
                        <span class="text-indigo-200 text-xs">${dateLabel}</span>
                    </div>
                    <div class="flex items-center gap-2 ml-5 pl-3 border-l border-slate-600/60">
                        <i class="fa-regular fa-clock text-slate-400"></i>
                        <span class="text-indigo-200 text-xs font-mono">de ${startTime} à ${endTime}</span>
                    </div>
                `;
            }

            return `
                <div class="flex items-center gap-2">
                    <i class="fa-regular fa-calendar text-slate-400"></i>
                    <span class="text-indigo-200 text-xs font-mono">du ${formatDateTime(startAt)}</span>
                </div>
                <div class="flex items-center gap-2 ml-5 pl-3 border-l border-slate-600/60">
                    <i class="fa-solid fa-arrow-right text-slate-400 text-[10px]"></i>
                    <span class="text-indigo-200 text-xs font-mono">au ${formatDateTime(endAt)}</span>
                </div>
            `;
        }

        function formatScheduleInfo(allo) {
            if (allo.window_start_at || allo.window_end_at) {
                return formatWindowInfo(allo.window_start_at, allo.window_end_at);
            }

            const slots = Array.isArray(allo.time_slots) ? allo.time_slots : [];
            if (slots.length === 0) {
                return `<span class="text-slate-500 text-xs">Non planifié</span>`;
            }

            const firstSlot = slots[0];
            const sameRange = slots.every(slot => slot.start_date === firstSlot.start_date && slot.end_date === firstSlot.end_date);

            if (sameRange) {
                const startDate = formatDateOnly(firstSlot.start_date);
                const endDate = formatDateOnly(firstSlot.end_date);
                const dateLabel = startDate && endDate
                    ? (startDate === endDate ? `le ${startDate}` : `du ${startDate} au ${endDate}`)
                    : 'Période à définir';
                const timeLines = slots.map(slot => {
                    const startTime = formatTimeOnly(slot.start_time);
                    const endTime = formatTimeOnly(slot.end_time);
                    if (!startTime || !endTime) return '';
                    return `
                        <div class="flex items-center gap-2 ml-5 pl-3 border-l border-slate-600/60">
                            <i class="fa-regular fa-clock text-slate-400"></i>
                            <span class="text-indigo-200 text-xs font-mono">de ${startTime} à ${endTime}</span>
                        </div>
                    `;
                }).join('');

                return `
                    <div class="flex items-center gap-2">
                        <i class="fa-regular fa-calendar text-slate-400"></i>
                        <span class="text-indigo-200 text-xs">${dateLabel}</span>
                    </div>
                    ${timeLines || '<span class="text-slate-500 text-xs">Horaires à définir</span>'}
                `;
            }

            return slots.map(slot => {
                const dateLabel = formatDateOnly(slot.start_date);
                const startTime = formatTimeOnly(slot.start_time);
                const endTime = formatTimeOnly(slot.end_time);
                const dateLine = `
                    <div class="flex items-center gap-2">
                        <i class="fa-regular fa-calendar text-slate-400"></i>
                        <span class="text-indigo-200 text-xs">${dateLabel || 'Date à définir'}</span>
                    </div>
                `;
                if (!startTime || !endTime) {
                    return `
                        ${dateLine}
                        <span class="text-slate-500 text-xs">Horaires à définir</span>
                    `;
                }
                return `
                    ${dateLine}
                    <div class="flex items-center gap-2 ml-5 pl-3 border-l border-slate-600/60">
                        <i class="fa-regular fa-clock text-slate-400"></i>
                        <span class="text-indigo-200 text-xs font-mono">de ${startTime} à ${endTime}</span>
                    </div>
                `;
            }).join('');
        }

        function toInputDateTime(iso) {
            if (!iso) return '';
            const date = new Date(iso);
            const pad = (value) => String(value).padStart(2, '0');
            return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
        }

        function statusLabel(status) {
            switch (status) {
                case 'OPEN':
                    return 'Ouvert';
                case 'CLOSED':
                    return 'Fermé';
                case 'DISABLED':
                    return 'Désactivé';
                default:
                    return 'Brouillon';
            }
        }

        // --- CORE FUNCTIONS ---
        function switchView(view) {
            const v1 = document.getElementById('viewRequests');
            const v2 = document.getElementById('viewCatalog');
            const t1 = document.getElementById('tabRequests');
            const t2 = document.getElementById('tabCatalog');

            if (view === 'requests') {
                v1.classList.remove('hidden'); v2.classList.add('hidden');
                t1.className = "px-4 py-1.5 rounded-md text-sm font-medium transition-all bg-indigo-600 text-white shadow";
                t2.className = "px-4 py-1.5 rounded-md text-sm font-medium text-slate-400 hover:text-white transition-all";
                renderRequests();
            } else {
                v1.classList.add('hidden'); v2.classList.remove('hidden');
                t2.className = "px-4 py-1.5 rounded-md text-sm font-medium transition-all bg-indigo-600 text-white shadow";
                t1.className = "px-4 py-1.5 rounded-md text-sm font-medium text-slate-400 hover:text-white transition-all";
                renderCatalog();
            }
        }

        function toggleSidebar() {
            const sb = document.getElementById('sidebar');
            if (sb.classList.contains('-translate-x-full')) sb.classList.remove('-translate-x-full');
            else sb.classList.add('-translate-x-full');
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
                renderCatalog();
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
            } catch (error) {
                showToast(error.message || 'Erreur lors du chargement des admins.', 'error');
            }
        }

        function populateFilters() {
            const alloSelect = document.getElementById('filterAllo');
            alloSelect.innerHTML = '<option value="">Tous les allos</option>';
            allos.forEach(a => {
                const opt = document.createElement('option');
                opt.value = a.id;
                opt.innerText = a.title;
                alloSelect.appendChild(opt);
            });
        }

        function populateAdminList(selectedAdmins = []) {
            const container = document.getElementById('adminList');
            container.innerHTML = '';
            if (admins.length === 0) {
                container.innerHTML = '<p class="text-xs text-slate-500">Aucun admin disponible.</p>';
                return;
            }

            admins.forEach(admin => {
                const isChecked = selectedAdmins.includes(admin.id);
                const div = document.createElement('div');
                div.className = "flex items-center mb-2 last:mb-0";
                div.innerHTML = `<input type="checkbox" id="admin_${admin.id}" name="alloAdmins" value="${admin.id}" ${isChecked ? 'checked' : ''} class="w-4 h-4 text-indigo-600 bg-slate-800 border-slate-600 rounded focus:ring-indigo-500"><label for="admin_${admin.id}" class="ml-2 text-sm text-slate-300 cursor-pointer select-none">${admin.name}</label>`;
                container.appendChild(div);
            });
        }

        function setScheduleMode(mode) {
            scheduleMode = mode;
            updateScheduleVisibility();
            updateAlloRequirements();
        }

        function updateScheduleVisibility() {
            const windowFields = document.getElementById('scheduleWindowFields');
            const dateFields = document.getElementById('scheduleDateFields');
            const rangeFields = document.getElementById('scheduleRangeFields');

            windowFields.classList.toggle('hidden', scheduleMode !== 'window');
            dateFields.classList.toggle('hidden', scheduleMode !== 'date');
            rangeFields.classList.toggle('hidden', scheduleMode !== 'range');
        }

        function updateAlloRequirements() {
            const statusValue = document.getElementById('alloStatus').value;
            const durationInput = document.getElementById('alloDuration');
            const windowStartInput = document.getElementById('alloStart');
            const windowEndInput = document.getElementById('alloEnd');
            const dateSlotsContainer = document.getElementById('dateSlotsContainer');
            const rangeSlotsContainer = document.getElementById('rangeSlotsContainer');

            isDraftMode = statusValue === 'DRAFT';
            durationInput.required = !isDraftMode;
            windowStartInput.required = !isDraftMode && scheduleMode === 'window';
            windowEndInput.required = !isDraftMode && scheduleMode === 'window';

            dateSlotsContainer.querySelectorAll('input').forEach((input) => {
                input.required = !isDraftMode && scheduleMode === 'date';
            });
            rangeSlotsContainer.querySelectorAll('input').forEach((input) => {
                input.required = !isDraftMode && scheduleMode === 'range';
            });
        }

        function updateCapacityVisibility() {
            const fixedFields = document.getElementById('fixedCapacityFields');
            const fixedCapacityInput = document.getElementById('alloFixedCapacity');
            const isFixed = document.querySelector('input[name="capacityMode"]:checked')?.value === 'fixed';
            fixedFields.classList.toggle('hidden', !isFixed);
            fixedCapacityInput.required = isFixed;
        }

        function renderDateSlots() {
            const container = document.getElementById('dateSlotsContainer');
            container.innerHTML = '';

            if (dateSpecificSlots.length === 0) {
                container.innerHTML = '<p class="text-xs text-slate-500">Aucun créneau ajouté.</p>';
                return;
            }

            dateSpecificSlots.forEach((slot, index) => {
                const row = document.createElement('div');
                row.className = 'grid grid-cols-1 md:grid-cols-[1.1fr_0.9fr_0.9fr_auto] gap-2 items-center';
                row.innerHTML = `
                    <input type="date" class="bg-slate-800 border border-slate-600 text-white text-xs rounded p-2" value="${slot.date || ''}" data-field="date" data-index="${index}">
                    <input type="time" class="bg-slate-800 border border-slate-600 text-white text-xs rounded p-2" value="${slot.start_time || ''}" data-field="start_time" data-index="${index}">
                    <input type="time" class="bg-slate-800 border border-slate-600 text-white text-xs rounded p-2" value="${slot.end_time || ''}" data-field="end_time" data-index="${index}">
                    <button type="button" class="text-xs text-red-300 hover:text-red-200" data-action="remove" data-index="${index}">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                `;
                row.querySelectorAll('input').forEach(input => {
                    input.addEventListener('change', (event) => {
                        const idx = Number(event.target.dataset.index);
                        const field = event.target.dataset.field;
                        dateSpecificSlots[idx][field] = event.target.value;
                    });
                    input.required = !isDraftMode && scheduleMode === 'date';
                });
                row.querySelector('[data-action="remove"]').addEventListener('click', (event) => {
                    const idx = Number(event.currentTarget.dataset.index);
                    dateSpecificSlots.splice(idx, 1);
                    renderDateSlots();
                });
                container.appendChild(row);
            });
        }

        function renderRangeSlots() {
            const container = document.getElementById('rangeSlotsContainer');
            container.innerHTML = '';

            if (rangeTimeSlots.length === 0) {
                container.innerHTML = '<p class="text-xs text-slate-500">Aucune fenêtre horaire ajoutée.</p>';
                return;
            }

            rangeTimeSlots.forEach((slot, index) => {
                const row = document.createElement('div');
                row.className = 'grid grid-cols-1 md:grid-cols-[1fr_1fr_auto] gap-2 items-center';
                row.innerHTML = `
                    <input type="time" class="bg-slate-800 border border-slate-600 text-white text-xs rounded p-2" value="${slot.start_time || ''}" data-field="start_time" data-index="${index}">
                    <input type="time" class="bg-slate-800 border border-slate-600 text-white text-xs rounded p-2" value="${slot.end_time || ''}" data-field="end_time" data-index="${index}">
                    <button type="button" class="text-xs text-red-300 hover:text-red-200" data-action="remove" data-index="${index}">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                `;
                row.querySelectorAll('input').forEach(input => {
                    input.addEventListener('change', (event) => {
                        const idx = Number(event.target.dataset.index);
                        const field = event.target.dataset.field;
                        rangeTimeSlots[idx][field] = event.target.value;
                    });
                    input.required = !isDraftMode && scheduleMode === 'range';
                });
                row.querySelector('[data-action="remove"]').addEventListener('click', (event) => {
                    const idx = Number(event.currentTarget.dataset.index);
                    rangeTimeSlots.splice(idx, 1);
                    renderRangeSlots();
                });
                container.appendChild(row);
            });
        }

        function resetScheduleState() {
            scheduleMode = 'window';
            dateSpecificSlots = [];
            rangeTimeSlots = [];
            rangeDateStart = '';
            rangeDateEnd = '';
            document.querySelectorAll('input[name="scheduleMode"]').forEach((input) => {
                input.checked = input.value === scheduleMode;
            });
            document.getElementById('rangeStartDate').value = '';
            document.getElementById('rangeEndDate').value = '';
            updateScheduleVisibility();
            renderDateSlots();
            renderRangeSlots();
            updateAlloRequirements();
        }

        // --- REQUESTS ---
        function renderRequests() {
            const container = document.getElementById('requestsContainer');
            const statusFilter = document.getElementById('filterStatus').value;
            const alloFilter = document.getElementById('filterAllo').value;
            const userFilter = document.getElementById('filterUser').value.toLowerCase();
            container.innerHTML = '';

            let filtered = requests.filter(r => {
                let statusMatch = true;
                if (statusFilter === 'ACTIVE') statusMatch = (r.status === 'PENDING' || r.status === 'ACCEPTED');
                else if (statusFilter !== 'ALL') statusMatch = (r.status === statusFilter);
                let alloMatch = true;
                if (alloFilter) alloMatch = (r.allo_id == alloFilter);
                let userMatch = true;
                if (userFilter) userMatch = `${r.user_name} ${r.user_email}`.toLowerCase().includes(userFilter);
                return statusMatch && alloMatch && userMatch;
            });

            const pendingCount = requests.filter(r => r.status === 'PENDING').length;
            const badge = document.getElementById('pendingCount');
            badge.innerText = pendingCount;
            badge.classList.toggle('hidden', pendingCount === 0);

            if(filtered.length === 0) { container.innerHTML = `<div class="text-center py-12 text-slate-500"><i class="fa-solid fa-filter text-4xl mb-3 opacity-20"></i><p>Aucun résultat.</p></div>`; return; }

            filtered.forEach(r => {
                let statusBadge = '', actions = '', cardBorder = 'border-slate-700';
            const assignControls = renderAssignControls(r);
                if(r.status === 'PENDING') {
                    statusBadge = `<span class="px-2 py-1 rounded bg-yellow-500/10 text-yellow-400 border border-yellow-500/20 text-xs font-bold">EN ATTENTE</span>`;
                    actions = `${assignControls}<button onclick="updateStatus(${r.id}, 'CANCELLED')" class="text-slate-400 hover:text-red-400 text-sm px-3">Annuler</button><button onclick="updateStatus(${r.id}, 'ACCEPTED')" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-1.5 rounded font-medium shadow">Prendre en charge</button>`;
                    cardBorder = 'border-yellow-500/30';
                } else if (r.status === 'ACCEPTED') {
                    const handlerName = r.handled_by_name || 'Admin';
                    statusBadge = `<span class="px-2 py-1 rounded bg-blue-500/10 text-blue-400 border border-blue-500/20 text-xs font-bold">EN COURS (${handlerName})</span>`;
                    actions = `${assignControls}<button onclick="updateStatus(${r.id}, 'PENDING')" class="text-slate-400 hover:text-yellow-400 text-sm px-3">Relâcher</button><button onclick="updateStatus(${r.id}, 'DONE')" class="bg-green-600 hover:bg-green-700 text-white text-sm px-4 py-1.5 rounded font-medium shadow">Terminer</button>`;
                    cardBorder = 'border-blue-500/30';
                } else if (r.status === 'DONE') {
                    statusBadge = `<span class="px-2 py-1 rounded bg-green-500/10 text-green-400 border border-green-500/20 text-xs font-bold">TERMINÉ</span>`;
                    const doneBy = r.done_by_name || r.handled_by_name || 'Admin';
                    actions = isSuperAdmin
                        ? `<span class="text-xs text-slate-500 mr-3 hidden sm:inline">Géré par ${doneBy}</span><button onclick="updateStatus(${r.id}, 'PENDING')" class="text-slate-400 hover:text-yellow-400 text-sm px-3 flex items-center border-l border-slate-700 ml-2 pl-4"><i class="fa-solid fa-rotate-left mr-1"></i> Rouvrir</button>`
                        : `<span class="text-xs text-slate-500 mr-3">Géré par ${doneBy}</span>`;
                    cardBorder = 'border-green-500/30';
                } else {
                    statusBadge = `<span class="px-2 py-1 rounded bg-red-500/10 text-red-400 border border-red-500/20 text-xs font-bold">ANNULÉ</span>`;
                    actions = `<button onclick="updateStatus(${r.id}, 'PENDING')" class="text-slate-400 hover:text-yellow-400 text-sm px-3 flex items-center"><i class="fa-solid fa-rotate-left mr-1"></i> Remettre en attente</button>`;
                    cardBorder = 'border-red-500/30';
                }
                container.innerHTML += `
                    <div class="bg-slate-800 rounded-lg p-4 border ${cardBorder} flex flex-col gap-4 transition-all">
                        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="h-10 w-10 rounded-full bg-slate-700 flex items-center justify-center text-indigo-400 font-bold">${r.user_name.substring(0,2)}</div>
                                <div>
                                    <div class="flex flex-wrap items-center gap-2 mb-1"><h3 class="font-bold text-white">${r.allo_title}</h3>${statusBadge}</div>
                                    <p class="text-sm text-slate-400">
                                        <i class="fa-solid fa-user mr-1"></i> ${r.user_name}
                                        <span class="mx-2">•</span>
                                        <i class="fa-regular fa-clock mr-1"></i> Créneau : <span class="text-white font-mono">${formatDateTime(r.slot_start_at)}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 w-full md:w-auto justify-end border-t md:border-t-0 border-slate-700 pt-3 md:pt-0">${actions}</div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-slate-400">
                            <div>
                                <p class="uppercase tracking-wide text-[10px] text-slate-500 mb-1">Note étudiant</p>
                                <p class="text-sm text-slate-300">${r.user_note || 'Aucune note.'}</p>
                            </div>
                        </div>
                    </div>`;
            });
        }

        async function updateStatus(id, status) {
            const payload = { status };
            await updateUsage(id, payload);
        }

        function getAssignableAdmins(alloId) {
            const allo = allos.find(item => item.id === alloId);
            const alloAdmins = Array.isArray(allo?.admins) ? allo.admins : [];
            if (alloAdmins.length > 0) {
                return alloAdmins;
            }
            return admins;
        }

        function renderAssignControls(request) {
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
            return `
                <div class="flex items-center gap-2 mr-2">
                    <select id="${selectId}" class="bg-slate-800 border border-slate-600 text-white text-xs rounded-lg p-2">
                        <option value="" ${noneSelected}>Personne</option>
                        ${options}
                    </select>
                    <button onclick="assignUsage(${request.id})" class="bg-slate-700 hover:bg-slate-600 text-white text-xs px-3 py-1.5 rounded">${buttonLabel}</button>
                </div>
            `;
        }

        async function assignUsage(id) {
            const select = document.getElementById(`assignSelect_${id}`);
            if (!select) return;
            const handlerId = select.value ? parseInt(select.value) : null;
            if (!handlerId) {
                await updateUsage(id, { status: 'PENDING', handled_by_id: null });
                return;
            }
            await updateUsage(id, { status: 'ACCEPTED', handled_by_id: handlerId });
        }

        // --- CATALOG ---
        function getFilteredAllos() {
            const titleFilter = catalogFilters.title.trim().toLowerCase();
            const statusFilter = catalogFilters.status;

            return allos.filter((allo) => {
                const matchesTitle = !titleFilter
                    || allo.title.toLowerCase().includes(titleFilter);
                const matchesStatus = statusFilter === 'ALL'
                    || allo.status === statusFilter;
                return matchesTitle && matchesStatus;
            });
        }

        function renderCatalog() {
            const grid = document.getElementById('catalogGrid');
            const filteredAllos = getFilteredAllos();
            if (filteredAllos.length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full text-center py-12 text-slate-500">
                        <i class="fa-solid fa-filter text-3xl mb-3 opacity-30"></i>
                        <p>Aucun allo ne correspond à ces filtres.</p>
                    </div>
                `;
                return;
            }

            const html = filteredAllos.map(a => {
                const windowInfo = formatScheduleInfo(a);
                const adminsStr = a.admins && a.admins.length > 0 ? a.admins.map(admin => admin.name).join(', ') : "Tous";
                return `
                    <div class="bg-slate-800 rounded-xl p-5 border border-slate-700 shadow flex flex-col">
                        <div class="flex justify-between items-start mb-2 gap-2">
                            <div>
                                <h3 class="text-lg font-bold text-white">${a.title}</h3>
                                <span class="inline-flex items-center px-2 py-0.5 text-[10px] uppercase tracking-wide rounded border ${statusBadgeClasses(a.status)}">${statusLabel(a.status)}</span>
                            </div>
                        </div>
                        <p class="text-sm text-slate-400 mb-2 flex-1">${a.description || 'Pas de description.'}</p>
                        <div class="text-xs text-slate-500 mb-3"><i class="fa-solid fa-user-shield mr-1"></i> Géré par: <span class="text-white">${adminsStr}</span></div>
                        <div class="flex items-start justify-between gap-3 mb-4 bg-slate-700/30 p-3 rounded">
                            <div class="flex flex-col gap-1">
                                ${windowInfo}
                            </div>
                            <span class="text-xs bg-slate-700 px-2 py-1 rounded">${a.slot_duration_minutes} min/slot</span>
                        </div>
                        <div class="flex gap-2 mt-auto">
                            <button onclick="window.openEditAllo(${a.id})" class="flex-1 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded text-sm transition-colors"><i class="fa-solid fa-pen mr-2"></i> Modifier</button>
                            <button type="button" onclick="window.deleteAllo(${a.id})" class="px-3 py-2 bg-red-900/20 hover:bg-red-900/40 text-red-400 border border-red-900/30 rounded text-sm transition-colors" title="Supprimer"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </div>`;
            }).join('');
            grid.innerHTML = html;
        }

        async function deleteAllo(id) {
            if(!confirm("Êtes-vous sûr de vouloir supprimer cet Allo ?")) return;
            try {
                const response = await fetch(`${API.allos}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        ...jsonHeaders(),
                        'X-CSRF-TOKEN': csrf(),
                    },
                    credentials: 'same-origin',
                });
                const payload = await parseJsonResponse(response);
                if (!response.ok) throw new Error(payload.message || 'Suppression impossible.');
                showToast("Allo supprimé", "success");
                await loadAllos();
                await loadRequests();
            } catch (error) {
                showToast(error.message || 'Erreur lors de la suppression.', 'error');
            }
        }

        // --- MODALS ---
        function openAlloModal() {
            document.getElementById('alloModalTitle').innerText = "Créer un Allo";
            document.getElementById('editAlloId').value = "";
            document.getElementById('alloTitle').value = "";
            document.getElementById('alloDuration').value = "15";
            document.getElementById('alloSecurityMargin').value = "0";
            document.getElementById('alloDailyLimit').value = "";
            document.querySelectorAll('input[name="capacityMode"]').forEach((input) => {
                input.checked = input.value === 'admins';
            });
            document.getElementById('alloFixedCapacity').value = "";
            document.getElementById('alloStart').value = "";
            document.getElementById('alloEnd').value = "";
            document.getElementById('alloDesc').value = "";
            document.getElementById('alloStatus').value = "DRAFT";
            populateAdminList([]);
            resetScheduleState();
            updateAlloRequirements();
            updateCapacityVisibility();
            document.getElementById('alloModal').classList.remove('hidden');
            document.body.classList.add('modal-active');
        }

        function openEditAllo(id) {
            const a = allos.find(x => x.id === id);
            if(!a) return;
            document.getElementById('alloModalTitle').innerText = "Modifier Allo";
            document.getElementById('editAlloId').value = a.id;
            document.getElementById('alloTitle').value = a.title;
            document.getElementById('alloDuration').value = a.slot_duration_minutes;
            document.getElementById('alloSecurityMargin').value = a.security_margin_minutes ?? 0;
            document.getElementById('alloDailyLimit').value = a.daily_booking_limit ?? "";
            document.querySelectorAll('input[name="capacityMode"]').forEach((input) => {
                input.checked = (input.value === 'fixed') === (a.slot_capacity !== null && a.slot_capacity !== undefined);
            });
            document.getElementById('alloFixedCapacity').value = a.slot_capacity ?? "";
            document.getElementById('alloStart').value = toInputDateTime(a.window_start_at);
            document.getElementById('alloEnd').value = toInputDateTime(a.window_end_at);
            document.getElementById('alloDesc').value = a.description || "";
            document.getElementById('alloStatus').value = a.status || "DRAFT";
            populateAdminList(a.admin_ids || []);
            resetScheduleState();

            if (Array.isArray(a.time_slots) && a.time_slots.length > 0) {
                const first = a.time_slots[0];
                const sameRange = a.time_slots.every(slot => slot.start_date === first.start_date && slot.end_date === first.end_date);
                if (sameRange) {
                    scheduleMode = 'range';
                    rangeDateStart = first.start_date || '';
                    rangeDateEnd = first.end_date || '';
                    rangeTimeSlots = a.time_slots.map(slot => ({
                        start_time: slot.start_time || '',
                        end_time: slot.end_time || '',
                    }));
                    document.getElementById('rangeStartDate').value = rangeDateStart;
                    document.getElementById('rangeEndDate').value = rangeDateEnd;
                    renderRangeSlots();
                } else {
                    scheduleMode = 'date';
                    dateSpecificSlots = a.time_slots.map(slot => ({
                        date: slot.start_date || '',
                        start_time: slot.start_time || '',
                        end_time: slot.end_time || '',
                    }));
                    renderDateSlots();
                }
                document.querySelectorAll('input[name="scheduleMode"]').forEach((input) => {
                    input.checked = input.value === scheduleMode;
                });
                updateScheduleVisibility();
            }
            updateAlloRequirements();
            updateCapacityVisibility();
            document.getElementById('alloModal').classList.remove('hidden');
            document.body.classList.add('modal-active');
        }

        function closeAlloModal() { document.getElementById('alloModal').classList.add('hidden'); document.body.classList.remove('modal-active'); }

        function bindCatalogFilters() {
            const titleFilter = document.getElementById('catalogTitleFilter');
            const statusFilter = document.getElementById('catalogStatusFilter');
            const resetButton = document.getElementById('catalogResetFilters');

            const applyFilters = () => {
                catalogFilters = {
                    title: titleFilter.value,
                    status: statusFilter.value,
                };
                renderCatalog();
            };

            titleFilter.addEventListener('input', applyFilters);
            statusFilter.addEventListener('change', applyFilters);
            resetButton.addEventListener('click', () => {
                titleFilter.value = '';
                statusFilter.value = 'ALL';
                applyFilters();
            });
        }

        async function submitAllo() {
            const id = document.getElementById('editAlloId').value;
            const selectedAdmins = [];
            document.querySelectorAll('input[name="alloAdmins"]:checked').forEach(cb => selectedAdmins.push(cb.value));
            const selectedMode = document.querySelector('input[name="scheduleMode"]:checked')?.value || 'window';
            let timeSlots = null;
            const statusValue = document.getElementById('alloStatus').value;
            const isDraft = statusValue === 'DRAFT';
            const durationValue = document.getElementById('alloDuration').value;
            const durationMinutes = durationValue ? parseInt(durationValue) : null;
            const securityMarginValue = document.getElementById('alloSecurityMargin').value;
            const securityMarginMinutes = securityMarginValue ? parseInt(securityMarginValue) : 0;
            const dailyLimitValue = document.getElementById('alloDailyLimit').value;
            const dailyLimit = dailyLimitValue ? parseInt(dailyLimitValue) : null;
            const capacityMode = document.querySelector('input[name="capacityMode"]:checked')?.value || 'admins';
            const fixedCapacityValue = document.getElementById('alloFixedCapacity').value;
            const fixedCapacity = fixedCapacityValue ? parseInt(fixedCapacityValue) : null;
            const normalizeSlotValue = (value) => (value ? value : null);
            const descriptionValue = document.getElementById('alloDesc').value;
            const data = {
                title: document.getElementById('alloTitle').value,
                slot_duration_minutes: Number.isNaN(durationMinutes) ? null : durationMinutes,
                security_margin_minutes: Number.isNaN(securityMarginMinutes) ? 0 : Math.max(securityMarginMinutes, 0),
                daily_booking_limit: Number.isNaN(dailyLimit) ? null : dailyLimit,
                slot_capacity: capacityMode === 'fixed' ? fixedCapacity : null,
                window_start_at: document.getElementById('alloStart').value,
                window_end_at: document.getElementById('alloEnd').value,
                description: descriptionValue.trim(),
                status: statusValue,
                admin_ids: selectedAdmins.map(id => parseInt(id)),
            };
            if(!data.title) { showToast("Titre requis", 'error'); return; }
            if (!data.description) {
                showToast("Description requise", 'error');
                return;
            }
            if (!isDraft && !data.slot_duration_minutes) {
                showToast("Durée du slot requise", 'error');
                return;
            }
            if (dailyLimitValue && (Number.isNaN(dailyLimit) || dailyLimit <= 0)) {
                showToast("La limite quotidienne doit être un nombre supérieur à 0.", 'error');
                return;
            }
            if (capacityMode === 'fixed' && (fixedCapacity === null || Number.isNaN(fixedCapacity) || fixedCapacity <= 0)) {
                showToast("La capacité fixe doit être un nombre supérieur à 0.", 'error');
                return;
            }
            if (selectedMode === 'window') {
                data.time_slots = null;
            }
            if (selectedMode === 'date') {
                const normalizedSlots = dateSpecificSlots.map(slot => ({
                    start_date: normalizeSlotValue(slot.date),
                    end_date: normalizeSlotValue(slot.date),
                    start_time: normalizeSlotValue(slot.start_time),
                    end_time: normalizeSlotValue(slot.end_time),
                })).filter(slot => slot.start_date || slot.start_time || slot.end_time);
                if (!isDraft && normalizedSlots.length === 0) {
                    showToast("Ajoute au moins un créneau daté.", 'error');
                    return;
                }
                const hasIncomplete = normalizedSlots.some(slot => !slot.start_date || !slot.start_time || !slot.end_time);
                if (!isDraft && hasIncomplete) {
                    showToast("Tous les créneaux datés doivent être complets.", 'error');
                    return;
                }
                timeSlots = normalizedSlots.length ? normalizedSlots : null;
                data.window_start_at = null;
                data.window_end_at = null;
            }

            if (selectedMode === 'range') {
                rangeDateStart = document.getElementById('rangeStartDate').value;
                rangeDateEnd = document.getElementById('rangeEndDate').value;
                if (!isDraft && (!rangeDateStart || !rangeDateEnd)) {
                    showToast("Dates de période requises.", 'error');
                    return;
                }
                const normalizedSlots = rangeTimeSlots.map(slot => ({
                    start_date: rangeDateStart,
                    end_date: rangeDateEnd,
                    start_time: normalizeSlotValue(slot.start_time),
                    end_time: normalizeSlotValue(slot.end_time),
                })).filter(slot => slot.start_time || slot.end_time);
                if (!isDraft && normalizedSlots.length === 0) {
                    showToast("Ajoute au moins une fenêtre horaire.", 'error');
                    return;
                }
                const hasIncomplete = normalizedSlots.some(slot => !slot.start_time || !slot.end_time);
                if (!isDraft && hasIncomplete) {
                    showToast("Toutes les fenêtres horaires doivent être complètes.", 'error');
                    return;
                }
                timeSlots = normalizedSlots.length ? normalizedSlots : null;
                data.window_start_at = null;
                data.window_end_at = null;
            }

            if (!isDraft) {
                if (selectedMode === 'window' && (!data.window_start_at || !data.window_end_at)) {
                    showToast("Dates d'ouverture/fermeture requises", 'error');
                    return;
                }
                if ((selectedMode === 'date' || selectedMode === 'range') && (!timeSlots || timeSlots.length === 0)) {
                    showToast("Créneaux requis", 'error');
                    return;
                }
            }

            if (timeSlots !== null) {
                data.time_slots = timeSlots;
            }

            try {
                const response = await fetch(id ? `${API.allos}/${id}` : API.allos, {
                    method: id ? 'PUT' : 'POST',
                    headers: {
                        ...jsonHeaders(),
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf(),
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(data),
                });
                const payload = await parseJsonResponse(response);
                if (!response.ok) throw new Error(payload.message || 'Sauvegarde impossible.');
                showToast(id ? "Allo mis à jour" : "Allo créé", 'success');
                closeAlloModal();
                await loadAllos();
                await loadRequests();
            } catch (error) {
                showToast(error.message || 'Erreur lors de la sauvegarde.', 'error');
            }
        }

        async function updateUsage(id, payload) {
            try {
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
            }
        }

        // --- FILTER HELPERS ---
        const filterUserInput = document.getElementById('filterUser');
        const userSuggestions = document.getElementById('userSuggestions');

        filterUserInput.addEventListener('input', function() {
            const val = this.value.toLowerCase();
            userSuggestions.innerHTML = '';

            if (val.length < 1) {
                userSuggestions.classList.add('hidden');
                document.getElementById('clearUserFilter').classList.add('hidden');
                renderRequests();
                return;
            }
            document.getElementById('clearUserFilter').classList.remove('hidden');

            const uniqueUsers = [...new Set(requests.map(r => r.user_name))];
            const matches = uniqueUsers.filter(u => u.toLowerCase().includes(val));

            if (matches.length > 0) {
                userSuggestions.classList.remove('hidden');
                matches.forEach(u => {
                    const div = document.createElement('div');
                    div.className = 'px-4 py-2 cursor-pointer hover:bg-slate-700 text-sm text-slate-300 hover:text-white transition-colors';
                    div.innerText = u;
                    div.onclick = () => {
                        filterUserInput.value = u;
                        userSuggestions.classList.add('hidden');
                        renderRequests();
                    };
                    userSuggestions.appendChild(div);
                });
            } else {
                userSuggestions.classList.add('hidden');
            }
            renderRequests();
        });

        document.addEventListener('click', function(e) {
            if (!filterUserInput.contains(e.target) && !userSuggestions.contains(e.target)) {
                userSuggestions.classList.add('hidden');
            }
        });

        function clearUserFilter() {
            filterUserInput.value = '';
            document.getElementById('clearUserFilter').classList.add('hidden');
            renderRequests();
        }

        function resetFilters() {
            document.getElementById('filterStatus').value = 'ACTIVE';
            document.getElementById('filterAllo').value = '';
            clearUserFilter();
        }

        // --- TOAST ---
        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            const bgClass = type === 'success' ? 'bg-green-600' : 'bg-red-600';
            toast.className = `flex items-center p-4 rounded shadow-lg text-white ${bgClass} toast-enter pointer-events-auto`;
            toast.innerHTML = `<i class="fa-solid ${type === 'success' ? 'fa-check' : 'fa-circle-xmark'} text-lg mr-3"></i><span class="text-sm font-medium">${message}</span>`;
            container.appendChild(toast);
            setTimeout(() => { toast.classList.remove('toast-enter'); toast.classList.add('toast-exit'); toast.addEventListener('animationend', () => toast.remove()); }, 3000);
        }

        // Init
        window.deleteAllo = deleteAllo;
        window.openEditAllo = openEditAllo;
        document.querySelectorAll('input[name="scheduleMode"]').forEach(input => {
            input.addEventListener('change', (event) => setScheduleMode(event.target.value));
        });
        document.getElementById('alloStatus').addEventListener('change', () => {
            updateAlloRequirements();
        });
        document.querySelectorAll('input[name="capacityMode"]').forEach(input => {
            input.addEventListener('change', updateCapacityVisibility);
        });
        document.getElementById('addDateSlot').addEventListener('click', () => {
            dateSpecificSlots.push({ date: '', start_time: '', end_time: '' });
            renderDateSlots();
        });
        document.getElementById('addRangeSlot').addEventListener('click', () => {
            rangeTimeSlots.push({ start_time: '', end_time: '' });
            renderRangeSlots();
        });
        document.getElementById('rangeStartDate').addEventListener('change', (event) => {
            rangeDateStart = event.target.value;
        });
        document.getElementById('rangeEndDate').addEventListener('change', (event) => {
            rangeDateEnd = event.target.value;
        });
        updateScheduleVisibility();
        renderDateSlots();
        renderRangeSlots();
        updateCapacityVisibility();
        updateAlloRequirements();
        loadAdmins().then(() => {
            populateAdminList();
            renderRequests();
        });
        bindCatalogFilters();
        loadAllos();
        loadRequests();

    </script>
@endpush
