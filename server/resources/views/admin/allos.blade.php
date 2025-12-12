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
                    <option value="">Tous les services</option>
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
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-white">Catalogue des Services</h2>
            <button onclick="openAlloModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors shadow">
                <i class="fa-solid fa-plus mr-2"></i> Nouvel Allo
            </button>
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
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Coût (Pts)</label>
                                <input type="number" id="alloCost" class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg block p-2.5 font-mono text-yellow-400" placeholder="200" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Durée Slot (min)</label>
                                <input type="number" id="alloDuration" class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg block p-2.5" placeholder="15" value="15" required>
                            </div>
                        </div>
                        <div class="p-3 bg-slate-700/30 rounded border border-slate-600">
                            <label class="block text-sm font-medium text-slate-300 mb-2">Fenêtre d'ouverture (Obligatoire)</label>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="text-xs text-slate-500 mb-1 block">Ouverture</label>
                                    <input type="datetime-local" id="alloStart" class="w-full bg-slate-800 border border-slate-600 text-white text-xs rounded p-2" required>
                                </div>
                                <div>
                                    <label class="text-xs text-slate-500 mb-1 block">Fermeture</label>
                                    <input type="datetime-local" id="alloEnd" class="w-full bg-slate-800 border border-slate-600 text-white text-xs rounded p-2" required>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Attribution Admins</label>
                            <div class="bg-slate-900 border border-slate-600 rounded-lg p-2 max-h-32 overflow-y-auto" id="adminList"></div>
                            <p class="text-xs text-slate-500 mt-1">Ces admins recevront les notifications.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Description</label>
                            <textarea id="alloDesc" rows="2" class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg block p-2.5" placeholder="Détails du service..."></textarea>
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
        // --- DATA ---
        const mockAdmins = [ { id: 1, name: "Moi (Current)" }, { id: 2, name: "Paul V." }, { id: 3, name: "Marie C." }, { id: 4, name: "Tom R." } ];

        let allos = [
            { id: 1, title: "P'tit Dej au lit", cost: 150, duration: 15, active: true, start: "2023-10-12T08:00", end: "2023-10-12T11:00", desc: "Un croissant et un jus d'orange livrés en chambre.", admins: ["Moi (Current)", "Tom R."] },
            { id: 2, title: "Réveil Fanfare", cost: 300, duration: 10, active: false, start: "2023-10-13T07:00", end: "2023-10-13T09:00", desc: "On vient te réveiller avec des trompettes.", admins: ["Paul V."] }
        ];

        let requests = [
            { id: 101, alloId: 1, alloTitle: "P'tit Dej au lit", user: "Jean Dupont", slot: "08:15", status: "PENDING", handler: null },
            { id: 102, alloId: 1, alloTitle: "P'tit Dej au lit", user: "Marie Curie", slot: "08:30", status: "ACCEPTED", handler: "Moi" },
            { id: 103, alloId: 1, alloTitle: "P'tit Dej au lit", user: "Albert E.", slot: "09:00", status: "DONE", handler: "Tom" },
            { id: 104, alloId: 2, alloTitle: "Réveil Fanfare", user: "Isaac N.", slot: "07:00", status: "PENDING", handler: null },
            { id: 105, alloId: 1, alloTitle: "P'tit Dej au lit", user: "Ada Lovelace", slot: "09:15", status: "CANCELLED", handler: null },
        ];

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

        function populateFilters() {
            const alloSelect = document.getElementById('filterAllo');
            alloSelect.innerHTML = '<option value="">Tous les services</option>';
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
            mockAdmins.forEach(admin => {
                const isChecked = selectedAdmins.includes(admin.name);
                const div = document.createElement('div');
                div.className = "flex items-center mb-2 last:mb-0";
                div.innerHTML = `<input type="checkbox" id="admin_${admin.id}" name="alloAdmins" value="${admin.name}" ${isChecked ? 'checked' : ''} class="w-4 h-4 text-indigo-600 bg-slate-800 border-slate-600 rounded focus:ring-indigo-500"><label for="admin_${admin.id}" class="ml-2 text-sm text-slate-300 cursor-pointer select-none">${admin.name}</label>`;
                container.appendChild(div);
            });
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
                if (alloFilter) alloMatch = (r.alloId == alloFilter);
                let userMatch = true;
                if (userFilter) userMatch = r.user.toLowerCase().includes(userFilter);
                return statusMatch && alloMatch && userMatch;
            });

            const pendingCount = requests.filter(r => r.status === 'PENDING').length;
            const badge = document.getElementById('pendingCount');
            badge.innerText = pendingCount;
            badge.classList.toggle('hidden', pendingCount === 0);

            if(filtered.length === 0) { container.innerHTML = `<div class="text-center py-12 text-slate-500"><i class="fa-solid fa-filter text-4xl mb-3 opacity-20"></i><p>Aucun résultat.</p></div>`; return; }

            filtered.forEach(r => {
                let statusBadge = '', actions = '', cardBorder = 'border-slate-700';
                if(r.status === 'PENDING') {
                    statusBadge = `<span class="px-2 py-1 rounded bg-yellow-500/10 text-yellow-400 border border-yellow-500/20 text-xs font-bold">EN ATTENTE</span>`;
                    actions = `<button onclick="updateStatus(${r.id}, 'CANCELLED')" class="text-slate-400 hover:text-red-400 text-sm px-3">Annuler</button><button onclick="updateStatus(${r.id}, 'ACCEPTED')" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-1.5 rounded font-medium shadow">Prendre en charge</button>`;
                    cardBorder = 'border-yellow-500/30';
                } else if (r.status === 'ACCEPTED') {
                    statusBadge = `<span class="px-2 py-1 rounded bg-blue-500/10 text-blue-400 border border-blue-500/20 text-xs font-bold">EN COURS (${r.handler})</span>`;
                    actions = `<button onclick="updateStatus(${r.id}, 'PENDING')" class="text-slate-400 hover:text-yellow-400 text-sm px-3">Relâcher</button><button onclick="updateStatus(${r.id}, 'DONE')" class="bg-green-600 hover:bg-green-700 text-white text-sm px-4 py-1.5 rounded font-medium shadow">Terminer</button>`;
                    cardBorder = 'border-blue-500/30';
                } else if (r.status === 'DONE') {
                    statusBadge = `<span class="px-2 py-1 rounded bg-green-500/10 text-green-400 border border-green-500/20 text-xs font-bold">TERMINÉ</span>`;
                    actions = `<span class="text-xs text-slate-500 mr-3 hidden sm:inline">Géré par ${r.handler}</span><button onclick="updateStatus(${r.id}, 'PENDING')" class="text-slate-400 hover:text-yellow-400 text-sm px-3 flex items-center border-l border-slate-700 ml-2 pl-4"><i class="fa-solid fa-rotate-left mr-1"></i> Rouvrir</button>`;
                    cardBorder = 'border-green-500/30';
                } else {
                    statusBadge = `<span class="px-2 py-1 rounded bg-red-500/10 text-red-400 border border-red-500/20 text-xs font-bold">ANNULÉ</span>`;
                    actions = `<button onclick="updateStatus(${r.id}, 'PENDING')" class="text-slate-400 hover:text-yellow-400 text-sm px-3 flex items-center"><i class="fa-solid fa-rotate-left mr-1"></i> Remettre en attente</button>`;
                    cardBorder = 'border-red-500/30';
                }
                container.innerHTML += `
                    <div class="bg-slate-800 rounded-lg p-4 border ${cardBorder} flex flex-col md:flex-row items-start md:items-center justify-between gap-4 transition-all">
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 rounded-full bg-slate-700 flex items-center justify-center text-indigo-400 font-bold">${r.user.substring(0,2)}</div>
                            <div>
                                <div class="flex items-center gap-2 mb-1"><h3 class="font-bold text-white">${r.alloTitle}</h3>${statusBadge}</div>
                                <p class="text-sm text-slate-400"><i class="fa-solid fa-user mr-1"></i> ${r.user} <span class="mx-2">•</span> <i class="fa-regular fa-clock mr-1"></i> Créneau : <span class="text-white font-mono">${r.slot}</span></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 w-full md:w-auto justify-end border-t md:border-t-0 border-slate-700 pt-3 md:pt-0">${actions}</div>
                    </div>`;
            });
        }

        async function updateStatus(id, status) {
            const req = requests.find(r => r.id === id);
            if(req) {
                req.status = status;
                req.handler = status === 'ACCEPTED' ? 'Moi' : (status === 'PENDING' ? null : req.handler);
                let msg = "Statut mis à jour";
                if(status === 'ACCEPTED') msg = "Vous avez pris en charge la demande";
                if(status === 'DONE') msg = "Allo terminé !";
                if(status === 'PENDING') msg = "Demande remise en attente";
                showToast(msg, 'success');
                renderRequests();
            }
        }

        // --- CATALOG ---
        function renderCatalog() {
            const grid = document.getElementById('catalogGrid');
            const html = allos.map(a => {
                let statusInfo = '<span class="text-slate-500 text-xs">Non planifié</span>';
                if (a.start && a.end) {
                    const s = new Date(a.start);
                    const e = new Date(a.end);
                    statusInfo = `<span class="text-indigo-400 text-xs font-mono">${s.toLocaleDateString()} ${s.getHours()}h-${e.getHours()}h</span>`;
                }
                const adminsStr = a.admins && a.admins.length > 0 ? a.admins.join(', ') : "Tous";
                return `
                    <div class="bg-slate-800 rounded-xl p-5 border border-slate-700 shadow flex flex-col">
                        <div class="flex justify-between items-start mb-2"><h3 class="text-lg font-bold text-white">${a.title}</h3><span class="text-yellow-400 font-mono font-bold">${a.cost} pts</span></div>
                        <p class="text-sm text-slate-400 mb-2 flex-1">${a.desc}</p>
                        <div class="text-xs text-slate-500 mb-3"><i class="fa-solid fa-user-shield mr-1"></i> Géré par: <span class="text-white">${adminsStr}</span></div>
                        <div class="flex items-center gap-2 mb-4 bg-slate-700/30 p-2 rounded"><i class="fa-regular fa-calendar text-slate-400"></i> ${statusInfo}<span class="ml-auto text-xs bg-slate-700 px-2 py-1 rounded">${a.duration} min/slot</span></div>
                        <div class="flex gap-2 mt-auto">
                            <button onclick="window.openEditAllo(${a.id})" class="flex-1 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded text-sm transition-colors"><i class="fa-solid fa-pen mr-2"></i> Modifier</button>
                            <button type="button" onclick="window.deleteAllo(${a.id})" class="px-3 py-2 bg-red-900/20 hover:bg-red-900/40 text-red-400 border border-red-900/30 rounded text-sm transition-colors" title="Supprimer"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </div>`;
            }).join('');
            grid.innerHTML = html;
        }

        async function deleteAllo(id) {
            console.log("Deleting Allo", id);
            if(!confirm("Êtes-vous sûr de vouloir supprimer cet Allo ?")) return;
            allos = allos.filter(a => a.id != id);
            showToast("Allo supprimé", "success");
            renderCatalog();
            populateFilters();
        }

        // --- MODALS ---
        function openAlloModal() {
            document.getElementById('alloModalTitle').innerText = "Créer un Allo";
            document.getElementById('editAlloId').value = "";
            document.getElementById('alloTitle').value = "";
            document.getElementById('alloCost').value = "100";
            document.getElementById('alloDuration').value = "15";
            document.getElementById('alloStart').value = "";
            document.getElementById('alloEnd').value = "";
            document.getElementById('alloDesc').value = "";
            populateAdminList([]);
            document.getElementById('alloModal').classList.remove('hidden');
            document.body.classList.add('modal-active');
        }

        function openEditAllo(id) {
            const a = allos.find(x => x.id === id);
            if(!a) return;
            document.getElementById('alloModalTitle').innerText = "Modifier Allo";
            document.getElementById('editAlloId').value = a.id;
            document.getElementById('alloTitle').value = a.title;
            document.getElementById('alloCost').value = a.cost;
            document.getElementById('alloDuration').value = a.duration;
            document.getElementById('alloStart').value = a.start || "";
            document.getElementById('alloEnd').value = a.end || "";
            document.getElementById('alloDesc').value = a.desc;
            populateAdminList(a.admins || []);
            document.getElementById('alloModal').classList.remove('hidden');
            document.body.classList.add('modal-active');
        }

        function closeAlloModal() { document.getElementById('alloModal').classList.add('hidden'); document.body.classList.remove('modal-active'); }

        function submitAllo() {
            const id = document.getElementById('editAlloId').value;
            const selectedAdmins = [];
            document.querySelectorAll('input[name="alloAdmins"]:checked').forEach(cb => selectedAdmins.push(cb.value));
            const data = {
                title: document.getElementById('alloTitle').value,
                cost: parseInt(document.getElementById('alloCost').value),
                duration: parseInt(document.getElementById('alloDuration').value),
                start: document.getElementById('alloStart').value,
                end: document.getElementById('alloEnd').value,
                desc: document.getElementById('alloDesc').value,
                admins: selectedAdmins,
                active: true
            };
            if(!data.title) { showToast("Titre requis", 'error'); return; }
            if(!data.start || !data.end) { showToast("Dates d'ouverture/fermeture requises", 'error'); return; }
            if(id) {
                const idx = allos.findIndex(a => a.id == id);
                if(idx > -1) allos[idx] = { ...allos[idx], ...data };
                showToast("Allo mis à jour", 'success');
            } else {
                allos.push({ id: Date.now(), ...data });
                showToast("Allo créé", 'success');
            }
            renderCatalog();
            populateFilters();
            closeAlloModal();
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

            const uniqueUsers = [...new Set(requests.map(r => r.user))];
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
        populateFilters();
        renderRequests();

    </script>
@endpush
