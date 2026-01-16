@extends('admin.layout')

@section('title', "Transactions - P'AS'SION BDS")

@section('content')

    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white">Historique des Points</h2>
            <p class="text-slate-400 mt-1">Suivez les flux de points et attribuez des récompenses manuelles.</p>
        </div>
        <div>
            <button id="givePointsBtn"
                    class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg text-sm font-bold transition-colors shadow-lg shadow-yellow-500/20 flex items-center">
                <i class="fa-solid fa-plus-circle mr-2"></i> Donner des Points
            </button>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-slate-800 rounded-xl p-4 border border-slate-700 shadow-lg mb-6">
        <form id="searchForm" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1 relative">
                <i class="fa-solid fa-search absolute left-3 top-3.5 text-slate-500"></i>
                <input type="text" id="searchInput"
                       class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block pl-10 p-3"
                       placeholder="Rechercher par étudiant ou raison...">
            </div>
            <div class="w-full md:w-48">
                <select id="typeFilter"
                        class="bg-slate-900 border border-slate-600 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3">
                    <option value="">Toutes sources</option>
                    <option value="MANUAL">Manuel (Staff)</option>
                    <option value="CHALLENGE">Défis</option>
                    <option value="ALLO">Allos</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-400">
                <thead class="bg-slate-900/50 text-slate-200 uppercase text-xs font-semibold">
                <tr>
                    <th class="px-6 py-4">Étudiant</th>
                    <th class="px-6 py-4 text-center">Montant</th>
                    <th class="px-6 py-4">Raison & Source</th>
                    <th class="px-6 py-4 text-right">Date & Auteur</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-700" id="transactionsTableBody">
                <!-- JS Injection -->
                </tbody>
            </table>
        </div>
        <!-- Pagination (Static) -->
        <div class="bg-slate-800 px-4 py-3 border-t border-slate-700 flex items-center justify-between sm:px-6">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div><p class="text-sm text-slate-400">
                        Page <span id="paginationCurrentPage" class="font-medium text-white">1</span>
                        sur <span id="paginationLastPage" class="font-medium text-white">1</span>
                    </p></div>

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
@endsection

@push('end_scripts')
    <!-- ============================================== -->
    <!-- GRANT POINTS MODAL                             -->
    <!-- ============================================== -->
    <div id="transactionModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/80 transition-opacity" aria-hidden="true" id="modalBackdrop"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="relative inline-block align-bottom bg-slate-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-slate-700">
                <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-900/50 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fa-solid fa-coins text-yellow-400"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-white" id="modal-title">Attribuer des Points</h3>
                            <div class="mt-2">
                                <p class="text-sm text-slate-400 mb-4">Ajout manuel de points pour une activité IRL ou un bonus.</p>

                                <form id="transactionForm" class="space-y-4">

                                    <!-- Custom "Select2" Search -->
                                    <div class="relative">
                                        <label class="block text-sm font-medium text-slate-300 mb-1">Étudiant</label>
                                        <input type="text" id="userSearchInput"
                                               class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg focus:ring-yellow-500 focus:border-yellow-500 block p-2.5"
                                               placeholder="Rechercher par nom..." autocomplete="off">
                                        <input type="hidden" id="selectedUserId">

                                        <!-- Dropdown Results -->
                                        <div id="searchResults" class="absolute z-50 w-full bg-slate-800 border border-slate-600 rounded-lg mt-1 hidden max-h-48 overflow-y-auto shadow-xl divide-y divide-slate-700">
                                            <!-- Results injected via JS -->
                                        </div>
                                    </div>

                                    <!-- Amount (Buttons Removed) -->
                                    <div>
                                        <label class="block text-sm font-medium text-slate-300 mb-1">Montant</label>
                                        <input type="number" id="pointsAmount" class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg focus:ring-yellow-500 focus:border-yellow-500 block p-2.5" placeholder="ex: 50" required>
                                        <p class="text-xs text-slate-500 mt-1">Utilisez un signe négatif (ex: -20) pour retirer des points.</p>
                                    </div>

                                    <!-- Reason -->
                                    <div>
                                        <label class="block text-sm font-medium text-slate-300 mb-1">Motif</label>
                                        <input type="text" id="transactionReason" class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg focus:ring-yellow-500 focus:border-yellow-500 block p-2.5" placeholder="ex: Victoire Mini-jeu Cafet" required>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-700">
                    <button type="button" id="submitTransactionBtn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Valider
                    </button>
                    <button type="button" id="closeModalBtn" class="mt-3 w-full inline-flex justify-center rounded-md border border-slate-600 shadow-sm px-4 py-2 bg-slate-800 text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // -----------------------------
            // Config
            // -----------------------------
            const transactionsApiUrl = '/admin/api/transactions';
            const createManualTransactionApiUrl = '/admin/api/transactions/manual';
            const studentsSearchApiUrl = '/admin/api/students';

            const perPage = 25;

            // -----------------------------
            // Elements
            // -----------------------------
            const transactionsTableBodyElement = document.getElementById('transactionsTableBody');
            const searchInputElement = document.getElementById('searchInput');
            const typeFilterElement = document.getElementById('typeFilter');

            const paginationCurrentPageElement = document.getElementById('paginationCurrentPage');
            const paginationLastPageElement = document.getElementById('paginationLastPage');
            const paginationPrevButtonElement = document.getElementById('paginationPrevBtn');
            const paginationNextButtonElement = document.getElementById('paginationNextBtn');

            const modalElement = document.getElementById('transactionModal');
            const givePointsButtonElement = document.getElementById('givePointsBtn');
            const closeModalButtonElement = document.getElementById('closeModalBtn');
            const modalBackdropElement = document.getElementById('modalBackdrop');
            const submitTransactionButtonElement = document.getElementById('submitTransactionBtn');

            const userSearchInputElement = document.getElementById('userSearchInput');
            const selectedUserIdElement = document.getElementById('selectedUserId');
            const searchResultsElement = document.getElementById('searchResults');

            const pointsAmountElement = document.getElementById('pointsAmount');
            const transactionReasonElement = document.getElementById('transactionReason');

            // -----------------------------
            // State
            // -----------------------------
            let currentPageNumber = 1;
            let lastPageNumber = 1;

            let pendingTransactionsFetchAbortController = null;
            let pendingStudentsFetchAbortController = null;

            // -----------------------------
            // Utilities
            // -----------------------------
            function getCsrfToken() {
                const csrfMetaElement = document.querySelector('meta[name="csrf-token"]');
                return csrfMetaElement ? csrfMetaElement.getAttribute('content') : null;
            }

            function escapeHtml(value) {
                const div = document.createElement('div');
                div.textContent = value ?? '';
                return div.innerHTML;
            }

            function formatDateFrench(isoString) {
                if (!isoString) return '—';
                const date = new Date(isoString);
                if (Number.isNaN(date.getTime())) return '—';

                // Example: "11 déc., 14:30"
                return new Intl.DateTimeFormat('fr-FR', {
                    day: '2-digit',
                    month: 'short',
                    hour: '2-digit',
                    minute: '2-digit',
                }).format(date);
            }

            function debounce(fn, delayMs) {
                let timeoutId = null;
                return (...args) => {
                    if (timeoutId) window.clearTimeout(timeoutId);
                    timeoutId = window.setTimeout(() => fn(...args), delayMs);
                };
            }

            async function fetchJson(url, options = {}) {
                const response = await fetch(url, {
                    credentials: 'same-origin',
                    ...options,
                });

                const contentType = response.headers.get('content-type') || '';
                const isJson = contentType.includes('application/json');

                if (!response.ok) {
                    const payload = isJson ? await response.json().catch(() => null) : null;
                    const error = new Error('HTTP_ERROR');
                    error.status = response.status;
                    error.payload = payload;
                    throw error;
                }

                return isJson ? response.json() : null;
            }

            // -----------------------------
            // Toasts (robust: auto-create container)
            // -----------------------------
            function ensureToastContainer() {
                let toastContainerElement = document.getElementById('toastContainer');
                if (toastContainerElement) return toastContainerElement;

                toastContainerElement = document.createElement('div');
                toastContainerElement.id = 'toastContainer';
                toastContainerElement.className = 'fixed top-4 right-4 z-[9999] space-y-2 pointer-events-none';
                document.body.appendChild(toastContainerElement);

                return toastContainerElement;
            }

            function showToast(message, type = 'success') {
                const toastContainerElement = ensureToastContainer();

                const toastElement = document.createElement('div');
                const backgroundClass = type === 'success' ? 'bg-green-600' : 'bg-red-600';
                const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-circle-exclamation';

                toastElement.className = `flex items-center p-4 rounded shadow-lg text-white ${backgroundClass} toast-enter pointer-events-auto`;
                toastElement.innerHTML =
                    `<i class="fa-solid ${iconClass} text-lg mr-3"></i>` +
                    `<span class="text-sm font-medium">${escapeHtml(message)}</span>`;

                toastContainerElement.appendChild(toastElement);

                window.setTimeout(() => {
                    toastElement.classList.remove('toast-enter');
                    toastElement.classList.add('toast-exit');
                    toastElement.addEventListener('animationend', () => toastElement.remove(), { once: true });
                }, 3000);
            }

            // -----------------------------
            // Table rendering
            // -----------------------------
            function buildTypeBadgeHtml(type) {
                if (type === 'MANUAL') {
                    return '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">MANUEL</span>';
                }
                if (type === 'CHALLENGE') {
                    return '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-500/10 text-purple-400 border border-purple-500/20">DÉFI</span>';
                }
                if (type === 'ALLO') {
                    return '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20">ALLO</span>';
                }
                return '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-500/10 text-slate-300 border border-slate-500/20">AUTRE</span>';
            }

            function renderTransactionsRows(transactions) {
                transactionsTableBodyElement.innerHTML = '';

                if (!transactions || transactions.length === 0) {
                    const emptyRow = document.createElement('tr');
                    emptyRow.innerHTML = `
                <td class="px-6 py-6 text-center text-slate-500" colspan="4">
                    Aucun résultat.
                </td>
            `;
                    transactionsTableBodyElement.appendChild(emptyRow);
                    return;
                }

                transactions.forEach((transaction) => {
                    const userName = escapeHtml(transaction.user_name);
                    const userEmail = escapeHtml(transaction.user_email || '');
                    const reason = escapeHtml(transaction.reason);
                    const adminName = escapeHtml(transaction.admin_name || 'Auto');
                    const type = transaction.type || 'MANUAL';

                    const amountNumber = Number(transaction.amount || 0);
                    const amountClass = amountNumber > 0 ? 'text-green-400' : 'text-red-400';
                    const amountSign = amountNumber > 0 ? '+' : '';

                    const createdAtLabel = formatDateFrench(transaction.created_at);

                    const rowElement = document.createElement('tr');
                    rowElement.className = 'hover:bg-slate-700/50 transition-colors';

                    rowElement.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-white">${userName}</div>
                    <div class="text-xs text-slate-500">${userEmail}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span class="text-sm font-bold font-mono ${amountClass}">${amountSign}${amountNumber}</span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        ${buildTypeBadgeHtml(type)}
                        <span class="text-sm text-slate-300 truncate max-w-xs">${reason}</span>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                    <div class="text-sm text-slate-400">${escapeHtml(createdAtLabel)}</div>
                    <div class="text-xs text-slate-600">par ${adminName}</div>
                </td>
            `;

                    transactionsTableBodyElement.appendChild(rowElement);
                });
            }

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

            // -----------------------------
            // Transactions API
            // -----------------------------
            async function loadTransactionsPage(pageNumber) {
                currentPageNumber = pageNumber;

                if (pendingTransactionsFetchAbortController) {
                    pendingTransactionsFetchAbortController.abort();
                }
                pendingTransactionsFetchAbortController = new AbortController();

                const searchText = (searchInputElement.value || '').trim();
                const typeValue = (typeFilterElement.value || '').trim();

                const queryParams = new URLSearchParams();
                queryParams.set('page', String(pageNumber));
                queryParams.set('per_page', String(perPage));
                if (searchText !== '') queryParams.set('q', searchText);
                if (typeValue !== '') queryParams.set('type', typeValue);

                try {
                    const response = await fetchJson(`${transactionsApiUrl}?${queryParams.toString()}`, {
                        method: 'GET',
                        signal: pendingTransactionsFetchAbortController.signal,
                        headers: { 'Accept': 'application/json' },
                    });

                    const transactions = response?.data || [];
                    const meta = response?.meta || {};

                    lastPageNumber = Number(meta.last_page || 1);
                    currentPageNumber = Number(meta.current_page || pageNumber);

                    renderTransactionsRows(transactions);
                    updatePaginationUi();
                } catch (error) {
                    if (error.name === 'AbortError') return;

                    renderTransactionsRows([]);
                    updatePaginationUi();

                    // 401 usually means session expired
                    if (error.status === 401) showToast('Session expirée. Reconnecte-toi.', 'error');
                    else showToast('Erreur lors du chargement des transactions.', 'error');
                }
            }

            const debouncedReloadTransactions = debounce(() => loadTransactionsPage(1), 250);

            searchInputElement.addEventListener('input', debouncedReloadTransactions);
            typeFilterElement.addEventListener('change', () => loadTransactionsPage(1));

            paginationPrevButtonElement.addEventListener('click', () => {
                if (currentPageNumber > 1) loadTransactionsPage(currentPageNumber - 1);
            });

            paginationNextButtonElement.addEventListener('click', () => {
                if (currentPageNumber < lastPageNumber) loadTransactionsPage(currentPageNumber + 1);
            });

            // -----------------------------
            // Modal
            // -----------------------------
            function openTransactionModal() {
                modalElement.classList.remove('hidden');
                document.body.classList.add('modal-active');

                // Reset form state
                userSearchInputElement.value = '';
                selectedUserIdElement.value = '';
                pointsAmountElement.value = '';
                transactionReasonElement.value = '';
                searchResultsElement.innerHTML = '';
                searchResultsElement.classList.add('hidden');

                userSearchInputElement.focus();
            }

            function closeTransactionModal() {
                modalElement.classList.add('hidden');
                document.body.classList.remove('modal-active');
                searchResultsElement.classList.add('hidden');
            }

            givePointsButtonElement.addEventListener('click', openTransactionModal);
            closeModalButtonElement.addEventListener('click', closeTransactionModal);
            modalBackdropElement.addEventListener('click', closeTransactionModal);

            // -----------------------------
            // Student search (dropdown)
            // -----------------------------
            function renderStudentsDropdown(students) {
                searchResultsElement.innerHTML = '';

                if (!students || students.length === 0) {
                    searchResultsElement.innerHTML = '<div class="px-4 py-2 text-sm text-slate-500">Aucun résultat</div>';
                    searchResultsElement.classList.remove('hidden');
                    return;
                }

                students.forEach((student) => {
                    const optionElement = document.createElement('div');
                    optionElement.className = 'px-4 py-2 cursor-pointer hover:bg-slate-700 transition-colors';

                    optionElement.innerHTML = `
                <div class="text-sm text-white font-medium">${escapeHtml(student.name)}</div>
                <div class="text-xs text-slate-400">${escapeHtml(student.email)}</div>
            `;

                    optionElement.addEventListener('click', () => {
                        userSearchInputElement.value = student.name;
                        selectedUserIdElement.value = String(student.id);
                        searchResultsElement.classList.add('hidden');
                    });

                    searchResultsElement.appendChild(optionElement);
                });

                searchResultsElement.classList.remove('hidden');
            }

            async function fetchStudents(term) {
                if (pendingStudentsFetchAbortController) {
                    pendingStudentsFetchAbortController.abort();
                }
                pendingStudentsFetchAbortController = new AbortController();

                const queryParams = new URLSearchParams();
                queryParams.set('q', term);

                const response = await fetchJson(`${studentsSearchApiUrl}?${queryParams.toString()}`, {
                    method: 'GET',
                    signal: pendingStudentsFetchAbortController.signal,
                    headers: { 'Accept': 'application/json' },
                });

                return response?.data || [];
            }

            const debouncedStudentSearch = debounce(async () => {
                const term = (userSearchInputElement.value || '').trim().toLowerCase();

                // If user is typing again, invalidate previous selection
                selectedUserIdElement.value = '';

                if (term.length < 2) {
                    searchResultsElement.classList.add('hidden');
                    searchResultsElement.innerHTML = '';
                    return;
                }

                try {
                    const students = await fetchStudents(term);
                    renderStudentsDropdown(students);
                } catch (error) {
                    if (error.name === 'AbortError') return;
                    showToast('Erreur lors de la recherche étudiants.', 'error');
                }
            }, 250);

            userSearchInputElement.addEventListener('input', debouncedStudentSearch);

            document.addEventListener('click', (event) => {
                const clickedInsideInput = userSearchInputElement.contains(event.target);
                const clickedInsideResults = searchResultsElement.contains(event.target);
                if (!clickedInsideInput && !clickedInsideResults) {
                    searchResultsElement.classList.add('hidden');
                }
            });

            // -----------------------------
            // Create manual transaction
            // -----------------------------
            async function submitManualTransaction() {
                const csrfToken = getCsrfToken();
                if (!csrfToken) {
                    showToast("CSRF token introuvable (meta csrf-token manquante).", 'error');
                    return;
                }

                const userId = Number(selectedUserIdElement.value || 0);
                const amount = Number(pointsAmountElement.value);
                const reason = (transactionReasonElement.value || '').trim();

                if (!userId) {
                    showToast('Veuillez sélectionner un étudiant dans la liste.', 'error');
                    return;
                }
                if (!Number.isInteger(amount) || amount === 0) {
                    showToast('Le montant doit être un entier non nul.', 'error');
                    return;
                }
                if (!reason) {
                    showToast('Le motif est obligatoire.', 'error');
                    return;
                }

                const originalButtonHtml = submitTransactionButtonElement.innerHTML;
                submitTransactionButtonElement.disabled = true;
                submitTransactionButtonElement.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';

                try {
                    const response = await fetchJson(createManualTransactionApiUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            user_id: userId,
                            amount,
                            reason,
                        }),
                    });

                    // Refresh list (page 1 so you see it immediately)
                    await loadTransactionsPage(1);

                    closeTransactionModal();
                    showToast('Transaction validée !', 'success');
                } catch (error) {
                    if (error.status === 422 && error.payload?.errors) {
                        // Laravel validation errors
                        const firstError = Object.values(error.payload.errors)[0]?.[0];
                        showToast(firstError || 'Données invalides.', 'error');
                    } else if (error.status === 403) {
                        showToast('Accès refusé.', 'error');
                    } else {
                        showToast('Erreur serveur.', 'error');
                    }
                } finally {
                    submitTransactionButtonElement.disabled = false;
                    submitTransactionButtonElement.innerHTML = originalButtonHtml;
                }
            }

            submitTransactionButtonElement.addEventListener('click', submitManualTransaction);

            // -----------------------------
            // Init
            // -----------------------------
            loadTransactionsPage(1);
        });
    </script>

@endpush
