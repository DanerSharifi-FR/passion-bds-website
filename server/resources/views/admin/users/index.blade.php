@extends('admin.layout')

@section('title', "Utilsateurs - P'AS'SION BDS")

@section('content')
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white">Gestion des Utilisateurs</h2>
            <p class="text-slate-400 mt-1">Gérez les permissions et les accès des organisateurs.</p>
        </div>
        <div>
            <button onclick="openCreateModal()"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors shadow-lg shadow-indigo-500/20 flex items-center">
                <i class="fa-solid fa-plus mr-2"></i> Créer Utilisateur
            </button>
        </div>
    </div>

    <div class="bg-slate-800 rounded-xl p-4 border border-slate-700 shadow-lg mb-6">
        <form id="searchForm" class="flex flex-col md:flex-row gap-4" onsubmit="return false;">
            <div class="flex-1 relative">
                <i class="fa-solid fa-search absolute left-3 top-3.5 text-slate-500"></i>
                <input type="text" id="searchInput"
                       class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block pl-10 p-3"
                       placeholder="Rechercher par nom, email...">
            </div>
            <div class="w-full md:w-48">
                <select id="roleFilter"
                        class="bg-slate-900 border border-slate-600 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3">
                    <option value="">Tous les rôles</option>
                    <option value="ADMIN">Admins Uniquement</option>
                    <option value="ROLE_GAMEMASTER">Game Masters</option>
                    <option value="ROLE_BLOGGER">Bloggers</option>
                    <option value="ROLE_USER">Étudiants</option>
                </select>
            </div>
        </form>
    </div>

    <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-400">
                <thead class="bg-slate-900/50 text-slate-200 uppercase text-xs font-semibold">
                <tr>
                    <th class="px-6 py-4">Utilisateur</th>
                    <th class="px-6 py-4">Rôles (Permissions)</th>
                    <th class="px-6 py-4 text-center">Points</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-700" id="usersTableBody">
                </tbody>
            </table>
        </div>

        <div class="bg-slate-800 px-4 py-3 border-t border-slate-700 flex items-center justify-between sm:px-6">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-slate-400">
                        Affichage (pagination à faire plus tard)
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <span class="relative inline-flex items-center px-4 py-2 border border-slate-600 bg-slate-800 text-sm font-medium text-slate-400">
                            1
                        </span>
                    </nav>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('end_scripts')
    <div id="toastContainer" class="fixed top-4 right-4 z-[9999] space-y-2 pointer-events-none"></div>

    <div id="roleModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/80 transition-opacity" aria-hidden="true" onclick="closeModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="relative inline-block align-bottom bg-slate-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-slate-700">
                <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-900/50 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fa-solid fa-user-shield text-indigo-400"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-white" id="modal-title">Gérer les Rôles</h3>
                            <div class="mt-2">
                                <p class="text-sm text-slate-400 mb-4">
                                    Modification des permissions pour <span id="modalUserEmail" class="font-mono text-indigo-300"></span>
                                </p>

                                <form id="rolesForm" class="space-y-3">
                                    <input type="hidden" id="editUserId">

                                    <div class="flex items-center justify-between p-3 bg-slate-700/50 rounded-lg border border-slate-600">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-white">GAMEMASTER</span>
                                            <span class="text-xs text-slate-400">Gère les points, allos et défis.</span>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="roles" value="ROLE_GAMEMASTER" class="sr-only peer">
                                            <div class="w-11 h-6 bg-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                        </label>
                                    </div>

                                    <div class="flex items-center justify-between p-3 bg-slate-700/50 rounded-lg border border-slate-600">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-white">BLOGGER</span>
                                            <span class="text-xs text-slate-400">Gère événements et photos.</span>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="roles" value="ROLE_BLOGGER" class="sr-only peer">
                                            <div class="w-11 h-6 bg-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                                        </label>
                                    </div>

                                    <div class="flex items-center justify-between p-3 bg-slate-700/50 rounded-lg border border-slate-600">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-white">TEAM</span>
                                            <span class="text-xs text-slate-400">Gère la page équipe/pôles.</span>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="roles" value="ROLE_TEAM" class="sr-only peer">
                                            <div class="w-11 h-6 bg-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-600"></div>
                                        </label>
                                    </div>

                                    <div class="flex items-center justify-between p-3 bg-slate-700/50 rounded-lg border border-slate-600">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-white">SHOP</span>
                                            <span class="text-xs text-slate-400">Gère la boutique goodies.</span>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="roles" value="ROLE_SHOP" class="sr-only peer">
                                            <div class="w-11 h-6 bg-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                        </label>
                                    </div>

                                    <div id="superAdminEditBadge" class="hidden flex items-center justify-between p-3 bg-red-900/10 rounded-lg border border-red-900/30 mt-4">
                                        <div class="flex items-center">
                                            <div class="bg-red-900/50 p-2 rounded-full mr-3">
                                                <i class="fa-solid fa-crown text-red-400"></i>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-sm font-bold text-red-400">SUPER ADMIN</span>
                                                <span class="text-xs text-red-300/50">Géré uniquement en base de données.</span>
                                            </div>
                                        </div>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-700">
                    <button type="button" id="btnSaveRoles" onclick="saveRoles()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Enregistrer
                    </button>
                    <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-slate-600 shadow-sm px-4 py-2 bg-slate-800 text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="createUserModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/80 transition-opacity" aria-hidden="true" onclick="closeCreateModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="relative inline-block align-bottom bg-slate-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-slate-700">
                <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-white mb-4">Ajouter un utilisateur</h3>
                    <form id="createUserForm" class="space-y-4" onsubmit="return false;">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Email IMT</label>
                            <input type="email" id="newEmail" class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5 transition-colors" placeholder="prenom.nom@imt-atlantique.net" required>
                            <p id="createEmailError" class="text-xs text-red-400 mt-1 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i> Format requis: prenom.nom@imt-atlantique.net</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Nom Complet <span class="text-slate-500 text-xs">(Auto)</span></label>
                            <input type="text" id="newName" class="w-full bg-slate-800 border border-slate-700 text-slate-400 text-sm rounded-lg cursor-not-allowed block p-2.5" placeholder="Généré automatiquement..." readonly>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Rôles Initiaux</label>
                            <div class="space-y-2 bg-slate-900/50 p-3 rounded-lg border border-slate-600 max-h-60 overflow-y-auto">

                                <div class="flex items-center justify-between py-2 border-b border-slate-700 last:border-0">
                                    <span class="text-sm text-white">Gamemaster</span>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="newRoles" value="ROLE_GAMEMASTER" class="sr-only peer">
                                        <div class="w-9 h-5 bg-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                                    </label>
                                </div>

                                <div class="flex items-center justify-between py-2 border-b border-slate-700 last:border-0">
                                    <span class="text-sm text-white">Blogger</span>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="newRoles" value="ROLE_BLOGGER" class="sr-only peer">
                                        <div class="w-9 h-5 bg-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-purple-600"></div>
                                    </label>
                                </div>

                                <div class="flex items-center justify-between py-2 border-b border-slate-700 last:border-0">
                                    <span class="text-sm text-white">Team Manager</span>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="newRoles" value="ROLE_TEAM" class="sr-only peer">
                                        <div class="w-9 h-5 bg-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-pink-600"></div>
                                    </label>
                                </div>

                                <div class="flex items-center justify-between py-2 border-b border-slate-700 last:border-0">
                                    <span class="text-sm text-white">Shop Manager</span>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="newRoles" value="ROLE_SHOP" class="sr-only peer">
                                        <div class="w-9 h-5 bg-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-600"></div>
                                    </label>
                                </div>

                            </div>
                            <p id="createRolesError" class="text-xs text-red-400 mt-2 hidden">
                                <i class="fa-solid fa-circle-exclamation mr-1"></i> Choisis au moins un rôle.
                            </p>
                        </div>
                    </form>
                </div>

                <div class="bg-slate-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-700">
                    <button type="button" id="btnCreateUser" onclick="submitCreateUser()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">
                        Créer
                    </button>
                    <button type="button" onclick="closeCreateModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-slate-600 shadow-sm px-4 py-2 bg-slate-800 text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const API_LIST = '/admin/api/users';
            const API_CREATE = '/admin/api/users';
            const API_UPDATE_ROLES = (id) => `/admin/api/users/${id}/roles`;

            const tbody = document.getElementById('usersTableBody');
            const searchInput = document.getElementById('searchInput');
            const roleFilter = document.getElementById('roleFilter');

            const toastContainer = document.getElementById('toastContainer');

            const modal = document.getElementById('roleModal');
            const modalEmail = document.getElementById('modalUserEmail');
            const rolesForm = document.getElementById('rolesForm');
            const superAdminEditBadge = document.getElementById('superAdminEditBadge');
            const btnSaveRoles = document.getElementById('btnSaveRoles');

            const createModal = document.getElementById('createUserModal');
            const newEmailInput = document.getElementById('newEmail');
            const newNameInput = document.getElementById('newName');
            const createEmailError = document.getElementById('createEmailError');
            const createRolesError = document.getElementById('createRolesError');
            const btnCreateUser = document.getElementById('btnCreateUser');

            let usersCache = [];
            let currentEditingUserId = null;

            function csrf() {
                return document.querySelector('meta[name="csrf-token"]')?.content || '';
            }

            function emailRegexOk(email) {
                return /^[a-z0-9][a-z0-9-]*\.[a-z0-9][a-z0-9-]*@imt-atlantique\.net$/i.test(email);
            }

            function displayNameFromEmail(email) {
                const local = (email.split('@')[0] || '').trim();
                const parts = local.split('.');
                const fmt = (s) => {
                    s = String(s || '').trim().replace(/[-_]+/g, ' ');
                    if (!s) return '';
                    return s.split(/\s+/).map(w => w ? (w[0].toUpperCase() + w.slice(1).toLowerCase()) : '').join(' ').trim();
                };
                const first = fmt(parts[0] || '');
                const last = fmt(parts[1] || '');
                const name = (first + ' ' + last).trim();
                return name || '';
            }

            function initialsFromUser(u) {
                const base = (u.display_name || '').trim();
                if (base) {
                    const parts = base.split(/\s+/).filter(Boolean);
                    const a = (parts[0]?.[0] || '').toUpperCase();
                    const b = (parts[1]?.[0] || parts[0]?.[1] || '').toUpperCase();
                    return (a + b).slice(0, 2) || '??';
                }
                const local = (u.email || '').split('@')[0] || '';
                const p = local.split('.');
                const a = (p[0]?.[0] || '').toUpperCase();
                const b = (p[1]?.[0] || p[0]?.[1] || '').toUpperCase();
                return (a + b).slice(0, 2) || '??';
            }

            function escapeHtml(str) {
                return String(str ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            function roleBadge(role) {
                let color = 'bg-slate-600 text-slate-200';
                let label = role.replace('ROLE_', '');
                if (role === 'ROLE_SUPER_ADMIN') { color = 'bg-red-900 text-red-200 border border-red-800'; label = 'SUPER ADMIN'; }
                else if (role === 'ROLE_GAMEMASTER') { color = 'bg-indigo-900 text-indigo-200 border border-indigo-800'; label = 'GAMEMASTER'; }
                else if (role === 'ROLE_BLOGGER') { color = 'bg-purple-900 text-purple-200 border border-purple-800'; label = 'BLOGGER'; }
                else if (role === 'ROLE_SHOP') { color = 'bg-green-900 text-green-200 border border-green-800'; label = 'SHOP'; }
                else if (role === 'ROLE_TEAM') { color = 'bg-pink-900 text-pink-200 border border-pink-800'; label = 'TEAM'; }

                return `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${color} mr-1 mb-1">${escapeHtml(label)}</span>`;
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
                try { payload = text ? JSON.parse(text) : null; } catch {}

                if (!res.ok) {
                    const msg =
                        payload?.errors?.email?.[0] ||
                        payload?.errors?.roles?.[0] ||
                        payload?.message ||
                        'Erreur serveur';
                    const err = new Error(msg);
                    err.payload = payload;
                    err.status = res.status;
                    throw err;
                }

                return payload;
            }

            function renderTable(list) {
                tbody.innerHTML = '';

                if (!list.length) {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td class="px-6 py-6 text-slate-400" colspan="4">Aucun utilisateur</td>`;
                    tbody.appendChild(tr);
                    return;
                }

                list.forEach(u => {
                    const roles = Array.isArray(u.roles) ? u.roles : [];
                    const adminRoles = roles.filter(r => r !== 'ROLE_USER');

                    let roleBadges = '';
                    if (adminRoles.length === 0) {
                        roleBadges = `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-700 text-slate-400 border border-slate-600">STUDENT</span>`;
                    } else {
                        roleBadges = adminRoles.map(roleBadge).join('');
                    }

                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-700/50 transition-colors';

                    tr.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-slate-700 flex items-center justify-center text-sm font-bold text-slate-300">
                                    ${escapeHtml(initialsFromUser(u))}
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-white">${escapeHtml(u.display_name || '—')}</div>
                                    <div class="text-sm text-slate-500">${escapeHtml(u.email || '—')}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap max-w-xs">${roleBadges}</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-sm font-mono text-yellow-400 font-bold">${escapeHtml(String(u.points ?? 0))}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button onclick="openModal(${u.id})" class="text-indigo-400 hover:text-indigo-300 bg-indigo-500/10 px-3 py-1.5 rounded hover:bg-indigo-500/20 transition-colors">
                                <i class="fa-solid fa-pen-to-square mr-1"></i> Gérer
                            </button>
                            <button onclick="deleteUser(${u.id})"
                                    class="ml-2 text-red-300 hover:text-red-200 bg-red-500/10 px-3 py-1.5 rounded hover:bg-red-500/20 transition-colors">
                                <i class="fa-solid fa-trash mr-1"></i> Supprimer
                            </button>
                        </td>

                    `;

                    tbody.appendChild(tr);
                });
            }

            async function loadUsers() {
                const search = (searchInput.value || '').trim();
                const role = (roleFilter.value || '').trim();

                let queryRole = role;
                let filterStudentsClientSide = false;

                if (role === 'ROLE_USER') {
                    queryRole = '';
                    filterStudentsClientSide = true;
                }

                const params = new URLSearchParams();
                if (search) params.set('search', search);
                if (queryRole) params.set('role', queryRole);

                const url = params.toString() ? `${API_LIST}?${params.toString()}` : API_LIST;

                const res = await httpJson(url, 'GET');
                const data = res?.data || [];
                usersCache = data;

                let list = data;

                if (filterStudentsClientSide) {
                    list = data.filter(u => Array.isArray(u.roles) && u.roles.length === 0);
                }

                renderTable(list);
            }

            function debounce(fn, ms) {
                let t = null;
                return function (...args) {
                    clearTimeout(t);
                    t = setTimeout(() => fn.apply(this, args), ms);
                };
            }

            window.openModal = function (id) {
                currentEditingUserId = id;

                const user = usersCache.find(u => u.id === id);
                if (!user) return;

                modalEmail.textContent = user.email || '';
                document.getElementById('editUserId').value = String(id);

                const roles = Array.isArray(user.roles) ? user.roles : [];

                const checkboxes = rolesForm.querySelectorAll('input[type="checkbox"][name="roles"]');
                checkboxes.forEach(cb => {
                    cb.checked = roles.includes(cb.value);
                });

                if (roles.includes('ROLE_SUPER_ADMIN')) {
                    superAdminEditBadge.classList.remove('hidden');
                } else {
                    superAdminEditBadge.classList.add('hidden');
                }

                modal.classList.remove('hidden');
                document.body.classList.add('modal-active');
            };

            window.closeModal = function () {
                modal.classList.add('hidden');
                document.body.classList.remove('modal-active');
                currentEditingUserId = null;
            };

            window.saveRoles = async function () {
                if (!currentEditingUserId) return;

                const formData = new FormData(rolesForm);
                const selected = [];

                for (const [k, v] of formData.entries()) {
                    if (k === 'roles') selected.push(v);
                }

                const original = btnSaveRoles.innerHTML;
                btnSaveRoles.disabled = true;
                btnSaveRoles.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin"></i>`;

                try {
                    await httpJson(API_UPDATE_ROLES(currentEditingUserId), 'PUT', { roles: selected });
                    showToast('Rôles mis à jour', 'success');
                    window.closeModal();
                    await loadUsers();
                } catch (e) {
                    showToast(e.message || 'Erreur', 'error');
                } finally {
                    btnSaveRoles.disabled = false;
                    btnSaveRoles.innerHTML = original;
                }
            }

            window.deleteUser = async function (id) {
                if (!confirm("Supprimer = désactiver le compte + retirer ses rôles. Continuer ?")) return;

                try {
                    await httpJson(`/admin/api/users/${id}`, 'DELETE');
                    showToast("Utilisateur supprimé (désactivé).", "success");
                    await loadUsers();
                } catch (e) {
                    showToast(e.message || "Erreur", "error");
                }
            };

            window.openCreateModal = function () {
                createModal.classList.remove('hidden');
                document.body.classList.add('modal-active');
                newEmailInput.focus();
            };

            window.closeCreateModal = function () {
                createModal.classList.add('hidden');
                document.body.classList.remove('modal-active');

                newEmailInput.value = '';
                newNameInput.value = '';
                createEmailError.classList.add('hidden');
                createRolesError.classList.add('hidden');

                newEmailInput.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
                newEmailInput.classList.add('border-slate-600', 'focus:border-indigo-500', 'focus:ring-indigo-500');

                document.querySelectorAll('input[name="newRoles"]').forEach(cb => cb.checked = false);
            };

            newEmailInput.addEventListener('input', function () {
                const email = this.value.trim().toLowerCase();

                if (email.length > 0 && !emailRegexOk(email)) {
                    this.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
                    this.classList.remove('border-slate-600', 'focus:border-indigo-500', 'focus:ring-indigo-500');
                    createEmailError.classList.remove('hidden');
                    newNameInput.value = '';
                } else {
                    this.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
                    this.classList.add('border-slate-600', 'focus:border-indigo-500', 'focus:ring-indigo-500');
                    createEmailError.classList.add('hidden');
                }

                if (emailRegexOk(email)) {
                    newNameInput.value = displayNameFromEmail(email);
                }
            });

            window.submitCreateUser = async function () {
                const email = (newEmailInput.value || '').trim().toLowerCase();

                createRolesError.classList.add('hidden');

                if (!emailRegexOk(email)) {
                    showToast("Format d'email invalide", 'error');
                    return;
                }

                const selectedRoles = [];
                document.querySelectorAll('input[name="newRoles"]:checked').forEach(cb => selectedRoles.push(cb.value));

                const original = btnCreateUser.innerHTML;
                btnCreateUser.disabled = true;
                btnCreateUser.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin"></i>`;

                try {
                    await httpJson(API_CREATE, 'POST', { email, roles: selectedRoles });
                    showToast('Utilisateur créé', 'success');
                    window.closeCreateModal();
                    await loadUsers();
                } catch (e) {
                    showToast(e.message || 'Erreur', 'error');
                } finally {
                    btnCreateUser.disabled = false;
                    btnCreateUser.innerHTML = original;
                }
            };

            const debouncedReload = debounce(() => {
                loadUsers().catch(e => showToast(e.message || 'Erreur', 'error'));
            }, 250);

            searchInput.addEventListener('input', debouncedReload);
            roleFilter.addEventListener('change', debouncedReload);

            loadUsers().catch(e => showToast(e.message || 'Erreur', 'error'));
        })();
    </script>
@endpush
