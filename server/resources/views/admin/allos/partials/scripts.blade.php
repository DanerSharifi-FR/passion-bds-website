    <style>
        .clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
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
        const activeView = @json($activeView ?? 'requests');

        let allos = [];
        let requests = [];
        let admins = [];
        let requestActionState = {};
        let filtersPanelOpen = false;
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

            if (!windowFields || !dateFields || !rangeFields) return;

            windowFields.classList.toggle('hidden', scheduleMode !== 'window');
            dateFields.classList.toggle('hidden', scheduleMode !== 'date');
            rangeFields.classList.toggle('hidden', scheduleMode !== 'range');
        }

        function updateAlloRequirements() {
            const statusElement = document.getElementById('alloStatus');
            const durationInput = document.getElementById('alloDuration');
            const windowStartInput = document.getElementById('alloStart');
            const windowEndInput = document.getElementById('alloEnd');
            const dateSlotsContainer = document.getElementById('dateSlotsContainer');
            const rangeSlotsContainer = document.getElementById('rangeSlotsContainer');

            if (!statusElement || !durationInput || !windowStartInput || !windowEndInput || !dateSlotsContainer || !rangeSlotsContainer) {
                return;
            }

            const statusValue = statusElement.value;

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
            if (!fixedFields || !fixedCapacityInput) return;
            const isFixed = document.querySelector('input[name="capacityMode"]:checked')?.value === 'fixed';
            fixedFields.classList.toggle('hidden', !isFixed);
            fixedCapacityInput.required = isFixed;
        }

        function renderDateSlots() {
            const container = document.getElementById('dateSlotsContainer');
            if (!container) return;
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
            if (!container) return;
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
            const rangeStart = document.getElementById('rangeStartDate');
            const rangeEnd = document.getElementById('rangeEndDate');
            if (!rangeStart || !rangeEnd) return;
            scheduleMode = 'window';
            dateSpecificSlots = [];
            rangeTimeSlots = [];
            rangeDateStart = '';
            rangeDateEnd = '';
            document.querySelectorAll('input[name="scheduleMode"]').forEach((input) => {
                input.checked = input.value === scheduleMode;
            });
            rangeStart.value = '';
            rangeEnd.value = '';
            updateScheduleVisibility();
            renderDateSlots();
            renderRangeSlots();
            updateAlloRequirements();
        }

        // --- REQUESTS ---
        function renderRequests() {
            const container = document.getElementById('requestsContainer');
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
            badge.innerText = pendingCount;
            badge.classList.toggle('hidden', pendingCount === 0);

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

            if (searchInput && clearSearch) {
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
            }
            if (alloSelect) {
                alloSelect.addEventListener('change', () => {
                    requestFilters.alloId = alloSelect.value;
                    updateQueryParams();
                    renderRequests();
                });
            }
            if (assigneeSelect) {
                assigneeSelect.addEventListener('change', () => {
                    requestFilters.assignee = assigneeSelect.value;
                    updateQueryParams();
                    renderRequests();
                });
            }
            if (dateRangeSelect) {
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
            }
            if (dateFrom) {
                dateFrom.addEventListener('change', () => {
                    requestFilters.dateFrom = dateFrom.value;
                    updateQueryParams();
                    renderRequests();
                });
            }
            if (dateTo) {
                dateTo.addEventListener('change', () => {
                    requestFilters.dateTo = dateTo.value;
                    updateQueryParams();
                    renderRequests();
                });
            }
            if (sortSelect) {
                sortSelect.addEventListener('change', () => {
                    requestFilters.sort = sortSelect.value;
                    updateQueryParams();
                    renderRequests();
                });
            }
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
            if (statusButton && statusMenu) {
                statusButton.addEventListener('click', () => {
                    statusMenu.classList.toggle('hidden');
                });
                document.addEventListener('click', (event) => {
                    if (!statusButton.contains(event.target) && !statusMenu.contains(event.target)) {
                        statusMenu.classList.add('hidden');
                    }
                });
            }

            const openFiltersButton = document.getElementById('openFiltersButton');
            if (openFiltersButton) {
                openFiltersButton.addEventListener('click', () => {
                    filtersPanelOpen = !filtersPanelOpen;
                    document.getElementById('filtersPanel').classList.toggle('hidden', !filtersPanelOpen);
                });
            }
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
            const resultsCount = document.getElementById('resultsCount');
            const filteredAllos = getFilteredAllos();
            if (resultsCount) {
                resultsCount.innerText = `${filteredAllos.length} allos`;
            }
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

        // Init
        window.deleteAllo = deleteAllo;
        window.openEditAllo = openEditAllo;
        if (activeView === 'catalog') {
            document.querySelectorAll('input[name="scheduleMode"]').forEach(input => {
                input.addEventListener('change', (event) => setScheduleMode(event.target.value));
            });
            const alloStatus = document.getElementById('alloStatus');
            if (alloStatus) {
                alloStatus.addEventListener('change', () => {
                    updateAlloRequirements();
                });
            }
            document.querySelectorAll('input[name="capacityMode"]').forEach(input => {
                input.addEventListener('change', updateCapacityVisibility);
            });
            const addDateSlot = document.getElementById('addDateSlot');
            if (addDateSlot) {
                addDateSlot.addEventListener('click', () => {
                    dateSpecificSlots.push({ date: '', start_time: '', end_time: '' });
                    renderDateSlots();
                });
            }
            const addRangeSlot = document.getElementById('addRangeSlot');
            if (addRangeSlot) {
                addRangeSlot.addEventListener('click', () => {
                    rangeTimeSlots.push({ start_time: '', end_time: '' });
                    renderRangeSlots();
                });
            }
            const rangeStartInput = document.getElementById('rangeStartDate');
            if (rangeStartInput) {
                rangeStartInput.addEventListener('change', (event) => {
                    rangeDateStart = event.target.value;
                });
            }
            const rangeEndInput = document.getElementById('rangeEndDate');
            if (rangeEndInput) {
                rangeEndInput.addEventListener('change', (event) => {
                    rangeDateEnd = event.target.value;
                });
            }
            updateScheduleVisibility();
            renderDateSlots();
            renderRangeSlots();
            updateCapacityVisibility();
            updateAlloRequirements();
            bindCatalogFilters();
            loadAdmins().then(() => {
                populateAdminList();
                renderCatalog();
            });
            loadAllos();
        } else {
            loadAdmins().then(() => {
                populateAdminList();
                renderRequests();
            });
            loadAllos();
            loadRequests();
        }

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
