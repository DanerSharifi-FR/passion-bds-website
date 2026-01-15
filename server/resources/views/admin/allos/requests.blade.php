@extends('admin.layout')

@section('title', "Demandes Allos - P'AS'SION BDS")

@section('top_bar_buttons')
    <div class="flex bg-slate-900 p-1 rounded-lg border border-slate-700 ml-4">
        <a href="{{ route('admin.allos.requests') }}" id="tabRequests" class="px-4 py-1.5 rounded-md text-sm font-medium transition-all bg-indigo-600 text-white shadow">
            <i class="fa-solid fa-bell mr-2"></i> Demandes <span class="ml-1 bg-red-500 text-white text-[10px] px-1.5 rounded-full" id="pendingCount">0</span>
        </a>
        <a href="{{ route('admin.allos.catalog') }}" id="tabCatalog" class="px-4 py-1.5 rounded-md text-sm font-medium text-slate-400 hover:text-white transition-all">
            <i class="fa-solid fa-store mr-2"></i> Catalogue & Créneaux
        </a>
    </div>
@endsection

@section('content')
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
@endsection

@push('end_scripts')
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

            if(filtered.length === 0) {
                container.innerHTML = `<div class="text-center py-12 text-slate-500"><i class="fa-solid fa-filter text-4xl mb-3 opacity-20"></i><p>Aucun résultat.</p></div>`;
                return;
            }

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

        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            const bgClass = type === 'success' ? 'bg-green-600' : 'bg-red-600';
            toast.className = `flex items-center p-4 rounded shadow-lg text-white ${bgClass} toast-enter pointer-events-auto`;
            toast.innerHTML = `<i class="fa-solid ${type === 'success' ? 'fa-check' : 'fa-circle-xmark'} text-lg mr-3"></i><span class="text-sm font-medium">${message}</span>`;
            container.appendChild(toast);
            setTimeout(() => {
                toast.classList.remove('toast-enter');
                toast.classList.add('toast-exit');
                toast.addEventListener('animationend', () => toast.remove());
            }, 3000);
        }

        loadAdmins().then(() => {
            renderRequests();
        });
        loadAllos();
        loadRequests();
    </script>
@endpush
