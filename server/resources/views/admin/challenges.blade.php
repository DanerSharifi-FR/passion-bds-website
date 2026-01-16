@extends('admin.layout')

@section('title', "Gestion des Défis - P'AS'SION BDS")

@section('content')
    <!-- VIEW 1: CHALLENGES LIST (Default) -->
    <div id="viewChallengesList">
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
            <h2 class="text-xl font-bold text-white">Défis Actifs</h2>
            <button onclick="openCreateModal()"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors shadow-lg shadow-indigo-500/20 flex items-center">
                <i class="fa-solid fa-plus mr-2"></i> Nouveau Défi
            </button>
        </div>

        <!-- Challenges Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6" id="challengesGrid">
            <!-- JS Injected -->
        </div>
    </div>

    <!-- VIEW 2: CHALLENGE DETAIL (Validation Queue) -->
    <div id="viewChallengeDetail" class="hidden h-full flex flex-col">
        <div class="flex items-center gap-4 mb-6 pb-4 border-b border-slate-700">
            <button onclick="closeChallengeDetail()"
                    class="p-2 bg-slate-800 hover:bg-slate-700 rounded-full text-slate-400 hover:text-white transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
            </button>
            <div>
                <h2 class="text-xl font-bold text-white" id="detailTitle">Titre du Défi</h2>
                <p class="text-sm text-slate-400">File de validation</p>
            </div>
            <div class="ml-auto flex gap-2">
                <select id="reviewFilter"
                        class="bg-slate-800 border border-slate-600 text-white text-xs rounded-lg p-2 focus:ring-indigo-500"
                        onchange="renderDetailReviews()">
                    <option value="PENDING" selected>⏳ En attente</option>
                    <option value="APPROVED">✅ Validés</option>
                    <option value="REJECTED">❌ Refusés</option>
                    <option value="ALL">Tout voir</option>
                </select>
            </div>
        </div>

        <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-lg overflow-hidden flex-1 overflow-y-auto">
            <table class="w-full text-left text-sm text-slate-400">
                <thead class="bg-slate-900/50 text-slate-200 uppercase text-xs font-semibold sticky top-0 z-10">
                <tr>
                    <th class="px-6 py-4">Étudiant</th>
                    <th class="px-6 py-4">Contenu / Preuve</th>
                    <th class="px-6 py-4">Statut</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-700" id="detailReviewsBody"></tbody>
            </table>
            <div id="emptyState" class="hidden flex flex-col items-center justify-center p-12 text-slate-500">
                <i class="fa-solid fa-check-double text-4xl mb-3 opacity-20"></i>
                <p>Aucune demande dans cette catégorie.</p>
            </div>
        </div>
    </div>
@endsection

@push('end_scripts')
    <!-- CREATE/EDIT MODAL -->
    <div id="modalForm" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
         aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/80 transition-opacity" onclick="closeModalForm()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="relative inline-block align-bottom bg-slate-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-slate-700">
                <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-white mb-4" id="modalFormTitle">Créer un Défi</h3>
                    <form id="challengeForm" class="space-y-4">
                        <input type="hidden" id="editId">

                        <!-- Row 1: Title, Points -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-300 mb-1">Titre</label>
                                <input type="text" id="inpTitle"
                                       class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5"
                                       placeholder="ex: Selfie avec le Prez" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Récompense (Pts)</label>
                                <input type="number" id="inpPoints"
                                       class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg focus:ring-yellow-500 focus:border-yellow-500 block p-2.5 font-mono font-bold text-yellow-400"
                                       placeholder="50" required>
                            </div>
                        </div>

                        <!-- Row 2: Dates & Status -->
                        <div
                            class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-slate-700/20 p-3 rounded-lg border border-slate-600/50">
                            <!-- Toggle Active -->
                            <div class="flex items-center justify-between md:flex-col md:items-start md:justify-center">
                                <label class="text-sm font-medium text-slate-300" id="activeLabel">Activer le
                                    défi</label>
                                <label class="relative inline-flex items-center cursor-pointer mt-1">
                                    <input type="checkbox" id="inpIsActive" class="sr-only peer" checked>
                                    <div
                                        class="w-11 h-6 bg-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                </label>
                            </div>
                            <!-- Start Date -->
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">Début (Optionnel)</label>
                                <input type="datetime-local" id="inpStartsAt"
                                       class="w-full bg-slate-800 border border-slate-600 text-white text-xs rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2">
                            </div>
                            <!-- End Date -->
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">Fin (Optionnel)</label>
                                <input type="datetime-local" id="inpEndsAt"
                                       class="w-full bg-slate-800 border border-slate-600 text-white text-xs rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2">
                            </div>
                        </div>

                        <!-- Type Selector -->
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Type de Défi</label>
                            <select id="inpType" onchange="toggleFormFields()"
                                    class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5">
                                <option value="ACTION_PHOTO">📸 Photo (Preuve image)</option>
                                <option value="QUESTION_MCQ">✅ QCM (Choix unique)</option>
                                <option value="QUESTION_TEXT">📝 Question (Réponse libre)</option>
                                <option value="ACTION_VISIT">📍 Visite (Flash QR code)</option>
                            </select>
                        </div>

                        <!-- Description -->
                        <div id="fieldDescription">
                            <label class="block text-sm font-medium text-slate-300 mb-1">Description / Question</label>
                            <textarea id="inpDescription" rows="2"
                                      class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5"
                                      placeholder="Expliquez ce qu'il faut faire..."></textarea>
                        </div>

                        <!-- Expected Text Answer -->
                        <div id="fieldExpectedAnswer"
                             class="hidden p-3 bg-slate-700/30 rounded border border-slate-600">
                            <label class="block text-sm font-medium text-indigo-300 mb-1">Réponse attendue</label>
                            <input type="text" id="inpExpected"
                                   class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg block p-2.5"
                                   placeholder="Laisser vide pour correction manuelle">
                        </div>

                        <!-- MCQ Builder -->
                        <div id="fieldMCQ" class="hidden p-3 bg-slate-700/30 rounded border border-slate-600 space-y-3">
                            <label class="block text-sm font-medium text-indigo-300 mb-1">Options du QCM (Max 4)</label>
                            <div id="mcqOptionsContainer" class="space-y-2">
                                <!-- JS Injected Options -->
                            </div>
                            <button type="button" id="btnAddMcqOption" onclick="addMcqOption()"
                                    class="text-xs text-indigo-400 hover:text-indigo-300 font-medium flex items-center mt-2">
                                <i class="fa-solid fa-plus-circle mr-1"></i> Ajouter une option
                            </button>
                        </div>

                        <!-- Visit Label -->
                        <div id="fieldVisit" class="hidden p-3 bg-slate-700/30 rounded border border-slate-600">
                            <label class="block text-sm font-medium text-indigo-300 mb-1">Label QR Code</label>
                            <input type="text" id="inpVisitLabel"
                                   class="w-full bg-slate-900 border border-slate-600 text-white text-sm rounded-lg block p-2.5"
                                   placeholder="ex: Stand_Crepes">
                        </div>
                    </form>
                </div>
                <div class="bg-slate-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-700">
                    <button type="button" id="btnSubmitForm" onclick="submitForm()"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">
                        Enregistrer
                    </button>
                    <button type="button" onclick="closeModalForm()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-slate-600 shadow-sm px-4 py-2 bg-slate-800 text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- IMAGE PREVIEW MODAL -->
    <div id="imageModal" class="fixed inset-0 z-[60] hidden flex items-center justify-center bg-black/90 p-4"
         onclick="closeImageModal()">
        <img id="previewImage" src="" class="max-h-full max-w-full rounded shadow-2xl">
    </div>
    <script>
        // --- DATA ---
        let challenges = [
            {
                id: 1,
                title: "Selfie avec le Prez",
                type: "ACTION_PHOTO",
                points: 50,
                active: true,
                desc: "Faites un selfie avec le président du BDE.",
                expected: "",
                visitLabel: "",
                starts_at: "2023-10-01T08:00",
                ends_at: null,
                mcqOptions: []
            },
            {
                id: 2,
                title: "Quelle est la mascotte ?",
                type: "QUESTION_MCQ",
                points: 10,
                active: true,
                desc: "Regardez bien le logo de la liste !",
                expected: "",
                visitLabel: "",
                starts_at: null,
                ends_at: null,
                mcqOptions: [
                    {text:"Un Chat", correct:false},
                    {text:"Un Aigle", correct:true},
                    {text:"Un Panda", correct:false}
                ]
            },
            {
                id: 3,
                title: "Le Mot de Passe",
                type: "QUESTION_TEXT",
                points: 20,
                active: true,
                desc: "Quel est le mot secret caché sous la table du stand ?",
                expected: "Chocolatine",
                visitLabel: "",
                starts_at: null,
                ends_at: null,
                mcqOptions: []
            },
            {
                id: 4,
                title: "Visite le QG",
                type: "ACTION_VISIT",
                points: 15,
                active: false,
                desc: "Rends-toi au local BDE et flashe le QR code sur la porte.",
                expected: "",
                visitLabel: "LOCAL_BDE_DOOR",
                starts_at: "2023-10-05T12:00",
                ends_at: "2023-10-05T14:00",
                mcqOptions: []
            }
        ];

        let reviews = [
            { id: 101, challengeId: 1, user: "Jean Dupont", type: "PHOTO", content: "https://images.unsplash.com/photo-1544502062-f82887f03d1c?auto=format&fit=crop&q=80&w=200", submitted: "10:30", status: "PENDING" },
            { id: 102, challengeId: 3, user: "Marie Curie", type: "TEXT", content: "Pain au chocolat", submitted: "11:15", status: "PENDING" },
            { id: 105, challengeId: 1, user: "Paul V.", type: "PHOTO", content: "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=200", submitted: "09:00", status: "APPROVED" },
        ];

        let activeChallengeId = null;

        // --- LISTENERS FOR AUTO ACTIVE ---
        document.getElementById('inpStartsAt').addEventListener('input', checkDateAutoActive);
        document.getElementById('inpEndsAt').addEventListener('input', checkDateAutoActive);

        function checkDateAutoActive() {
            const startVal = document.getElementById('inpStartsAt').value;
            const endVal = document.getElementById('inpEndsAt').value;
            const activeToggle = document.getElementById('inpIsActive');
            const activeLabel = document.getElementById('activeLabel');

            if (startVal || endVal) {
                const now = new Date();
                const start = startVal ? new Date(startVal) : new Date('1970-01-01');
                const end = endVal ? new Date(endVal) : new Date('2099-12-31');

                const shouldBeActive = now >= start && now <= end;

                activeToggle.checked = shouldBeActive;
                activeToggle.disabled = true;
                activeToggle.parentElement.classList.add('opacity-50', 'cursor-not-allowed');

                // Detailed label
                const statusText = shouldBeActive ? "(Actif maintenant)" : "(Inactif maintenant)";
                activeLabel.innerHTML = `Géré par les dates <span class="text-xs ml-1 ${shouldBeActive ? 'text-green-400' : 'text-red-400'}">${statusText}</span>`;
                activeLabel.classList.add('text-indigo-400');
            } else {
                activeToggle.disabled = false;
                activeToggle.parentElement.classList.remove('opacity-50', 'cursor-not-allowed');
                activeLabel.innerText = "Activer le défi";
                activeLabel.classList.remove('text-indigo-400');
            }
        }

        // --- RENDER CHALLENGES ---
        function renderChallenges() {
            const grid = document.getElementById('challengesGrid');
            grid.innerHTML = '';
            const now = new Date();

            challenges.forEach(c => {
                let isActive = c.active;

                // If dates are present, override active status for display (Simulation of server-side logic)
                if (c.starts_at || c.ends_at) {
                    const start = c.starts_at ? new Date(c.starts_at) : new Date('1970-01-01');
                    const end = c.ends_at ? new Date(c.ends_at) : new Date('2099-12-31');
                    isActive = now >= start && now <= end;
                }

                const statusColor = isActive ? 'bg-green-500/10 text-green-400 border-green-500/20' : 'bg-slate-700/50 text-slate-400 border-slate-600';
                const statusText = isActive ? 'ACTIF' : 'INACTIF';

                const pendingCount = reviews.filter(r => r.challengeId === c.id && r.status === 'PENDING').length;
                let pendingBadge = '';
                if(pendingCount > 0 && (c.type === 'ACTION_PHOTO' || c.type === 'QUESTION_TEXT')) {
                    pendingBadge = `<span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold w-6 h-6 flex items-center justify-center rounded-full shadow-lg border border-slate-900">${pendingCount}</span>`;
                }

                let icon = 'fa-camera';
                if(c.type === 'QUESTION_MCQ') icon = 'fa-list-check';
                if(c.type === 'ACTION_VISIT') icon = 'fa-location-dot';
                if(c.type === 'QUESTION_TEXT') icon = 'fa-pen';

                let actionBtn = '';
                if(c.type === 'ACTION_PHOTO' || c.type === 'QUESTION_TEXT') {
                    actionBtn = `<button onclick="openChallengeDetail(${c.id})" class="relative w-full mt-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors shadow"><i class="fa-solid fa-check-to-slot mr-2"></i> Gérer les preuves ${pendingBadge}</button>`;
                } else {
                    actionBtn = `<button class="w-full mt-4 py-2 bg-slate-700 text-slate-500 rounded-lg text-sm font-medium cursor-not-allowed"><i class="fa-solid fa-robot mr-2"></i> Validé Automatiquement</button>`;
                }

                // Date logic display
                let dateDisplay = "";
                if(c.starts_at || c.ends_at) {
                    dateDisplay = `<div class="text-[10px] text-slate-500 mt-2 flex gap-2">`;
                    if(c.starts_at) dateDisplay += `<span><i class="fa-regular fa-clock mr-1"></i> Début: ${new Date(c.starts_at).toLocaleDateString('fr-FR', {month:'short', day:'numeric', hour:'2-digit', minute:'2-digit'})}</span>`;
                    if(c.ends_at) dateDisplay += `<span><i class="fa-solid fa-hourglass-end mr-1"></i> Fin: ${new Date(c.ends_at).toLocaleDateString('fr-FR', {month:'short', day:'numeric', hour:'2-digit', minute:'2-digit'})}</span>`;
                    dateDisplay += `</div>`;
                }

                grid.innerHTML += `
                    <div class="bg-slate-800 rounded-xl p-5 border border-slate-700 shadow hover:border-slate-600 transition-all flex flex-col">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-slate-700 rounded-lg text-indigo-400"><i class="fa-solid ${icon} text-xl"></i></div>
                            <span class="px-2 py-1 rounded text-[10px] font-bold border ${statusColor}">${statusText}</span>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-1">${c.title}</h3>
                        <p class="text-sm text-slate-400 mb-1 flex-1">${c.type.replace('ACTION_', '').replace('QUESTION_', '')}</p>
                        ${dateDisplay}
                        <div class="flex items-center justify-between py-2 border-t border-slate-700 mt-3 mb-2">
                            <span class="text-yellow-400 font-mono font-bold">+${c.points} pts</span>
                            <div class="space-x-2">
                                <button onclick="openEditModal(${c.id})" class="text-slate-400 hover:text-white text-sm bg-slate-700/50 hover:bg-slate-700 px-3 py-1.5 rounded transition-colors flex items-center">
                                    <i class="fa-solid fa-pen mr-2"></i> Modifier
                                </button>
                            </div>
                        </div>
                        ${actionBtn}
                    </div>
                `;
            });
        }

        // --- MCQ BUILDER LOGIC ---
        let currentMcqCount = 0;

        function addMcqOption(text = '', isCorrect = false) {
            if (currentMcqCount >= 4) return;

            const container = document.getElementById('mcqOptionsContainer');
            const div = document.createElement('div');
            div.className = "flex gap-2 items-center mcq-option-row";
            div.innerHTML = `
                <input type="radio" name="mcq_correct" ${isCorrect ? 'checked' : ''} class="accent-green-500 w-4 h-4 cursor-pointer" title="Cocher si c'est la bonne réponse">
                <input type="text" value="${text}" class="flex-1 bg-slate-900 border border-slate-600 text-white text-sm rounded-lg block p-2 placeholder-slate-500 focus:ring-indigo-500" placeholder="Texte de l'option" required>
                <button type="button" onclick="removeMcqOption(this)" class="text-red-400 hover:text-red-300 px-1"><i class="fa-solid fa-xmark"></i></button>
            `;
            container.appendChild(div);
            currentMcqCount++;
            updateMcqAddButton();
        }

        function removeMcqOption(btn) {
            btn.parentElement.remove();
            currentMcqCount--;
            updateMcqAddButton();
        }

        function updateMcqAddButton() {
            const btn = document.getElementById('btnAddMcqOption');
            if(currentMcqCount >= 4) btn.classList.add('hidden');
            else btn.classList.remove('hidden');
        }

        function getMcqDataFromForm() {
            const rows = document.querySelectorAll('.mcq-option-row');
            const options = [];
            rows.forEach(row => {
                const text = row.querySelector('input[type="text"]').value.trim();
                const correct = row.querySelector('input[type="radio"]').checked;
                if(text) options.push({ text, correct });
            });
            return options;
        }

        // --- FORM LOGIC ---
        function toggleFormFields() {
            const type = document.getElementById('inpType').value;
            document.getElementById('fieldExpectedAnswer').classList.toggle('hidden', type !== 'QUESTION_TEXT');
            document.getElementById('fieldMCQ').classList.toggle('hidden', type !== 'QUESTION_MCQ');
            document.getElementById('fieldVisit').classList.toggle('hidden', type !== 'ACTION_VISIT');
        }

        function openCreateModal() {
            document.getElementById('editId').value = "";
            document.getElementById('modalFormTitle').innerText = "Créer un Défi";
            document.getElementById('inpTitle').value = "";
            document.getElementById('inpPoints').value = "50";
            document.getElementById('inpType').value = "ACTION_PHOTO";
            document.getElementById('inpDescription').value = "";
            document.getElementById('inpExpected').value = "";
            document.getElementById('inpVisitLabel').value = "";

            // New Date/Status Fields
            document.getElementById('inpIsActive').checked = true;
            document.getElementById('inpStartsAt').value = "";
            document.getElementById('inpEndsAt').value = "";
            checkDateAutoActive();

            // Reset MCQ
            document.getElementById('mcqOptionsContainer').innerHTML = '';
            currentMcqCount = 0;
            addMcqOption(); // Add 2 default empty
            addMcqOption();
            updateMcqAddButton();

            toggleFormFields();
            document.getElementById('modalForm').classList.remove('hidden');
            document.body.classList.add('modal-active');
        }

        function openEditModal(id) {
            const c = challenges.find(x => x.id === id);
            if(!c) return;

            document.getElementById('editId').value = c.id;
            document.getElementById('modalFormTitle').innerText = "Modifier le Défi";
            document.getElementById('inpTitle').value = c.title;
            document.getElementById('inpPoints').value = c.points;
            document.getElementById('inpType').value = c.type;
            document.getElementById('inpDescription').value = c.desc || "";
            document.getElementById('inpExpected').value = c.expected || "";
            document.getElementById('inpVisitLabel').value = c.visitLabel || "";

            // Date/Status Fields
            document.getElementById('inpIsActive').checked = c.active;
            document.getElementById('inpStartsAt').value = c.starts_at || "";
            document.getElementById('inpEndsAt').value = c.ends_at || "";
            checkDateAutoActive();

            // Populate MCQ
            document.getElementById('mcqOptionsContainer').innerHTML = '';
            currentMcqCount = 0;
            if (c.mcqOptions && c.mcqOptions.length > 0) {
                c.mcqOptions.forEach(opt => addMcqOption(opt.text, opt.correct));
            } else {
                addMcqOption(); addMcqOption(); // Fallback defaults
            }
            updateMcqAddButton();

            toggleFormFields();
            document.getElementById('modalForm').classList.remove('hidden');
            document.body.classList.add('modal-active');
        }

        function closeModalForm() {
            document.getElementById('modalForm').classList.add('hidden');
            document.body.classList.remove('modal-active');
        }

        function submitForm() {
            const id = document.getElementById('editId').value;
            const title = document.getElementById('inpTitle').value;
            const points = document.getElementById('inpPoints').value;
            const type = document.getElementById('inpType').value;
            const desc = document.getElementById('inpDescription').value;
            const expected = document.getElementById('inpExpected').value;
            const visitLabel = document.getElementById('inpVisitLabel').value;

            // New Fields
            const isActive = document.getElementById('inpIsActive').checked;
            const startsAt = document.getElementById('inpStartsAt').value;
            const endsAt = document.getElementById('inpEndsAt').value;

            if(!title) { showToast('Titre requis', 'error'); return; }
            if(!points || points < 0) { showToast('Points invalides', 'error'); return; }
            if(!desc) { showToast('Description requise', 'error'); return; }

            // MCQ Validation
            let mcqOptions = [];
            if (type === 'QUESTION_MCQ') {
                mcqOptions = getMcqDataFromForm();
                if (mcqOptions.length < 2) { showToast('Il faut au moins 2 options pour un QCM', 'error'); return; }
                const hasCorrect = mcqOptions.some(o => o.correct);
                if (!hasCorrect) { showToast('Veuillez sélectionner la bonne réponse', 'error'); return; }
            }

            const data = {
                title: title,
                type: type,
                points: parseInt(points),
                desc: desc,
                expected: expected,
                visitLabel: visitLabel,
                active: isActive,
                starts_at: startsAt || null,
                ends_at: endsAt || null,
                mcqOptions: mcqOptions
            };

            if (id) {
                const idx = challenges.findIndex(c => c.id == id);
                if(idx > -1) {
                    challenges[idx] = { ...challenges[idx], ...data };
                    showToast('Défi modifié !', 'success');
                }
            } else {
                challenges.push({ id: Date.now(), ...data });
                showToast('Défi créé !', 'success');
            }

            renderChallenges();
            closeModalForm();
        }

        // --- REVIEW DETAIL VIEW & UTILS ---
        function openChallengeDetail(id) {
            const challenge = challenges.find(c => c.id === id);
            if(!challenge) return;
            activeChallengeId = id;
            document.getElementById('detailTitle').innerText = challenge.title;
            document.getElementById('reviewFilter').value = 'PENDING';
            document.getElementById('viewChallengesList').classList.add('hidden');
            document.getElementById('viewChallengeDetail').classList.remove('hidden');
            renderDetailReviews();
        }

        function closeChallengeDetail() {
            activeChallengeId = null;
            document.getElementById('viewChallengeDetail').classList.add('hidden');
            document.getElementById('viewChallengesList').classList.remove('hidden');
            renderChallenges();
        }

        function renderDetailReviews() {
            const tbody = document.getElementById('detailReviewsBody');
            const emptyState = document.getElementById('emptyState');
            const filter = document.getElementById('reviewFilter').value;
            tbody.innerHTML = '';

            const filteredReviews = reviews.filter(r => {
                if (r.challengeId !== activeChallengeId) return false;
                if (filter === 'ALL') return true;
                return r.status === filter;
            });

            if(filteredReviews.length === 0) {
                emptyState.classList.remove('hidden');
                return;
            }
            emptyState.classList.add('hidden');

            filteredReviews.forEach(r => {
                let contentDisplay = '';
                if (r.type === 'PHOTO') {
                    contentDisplay = `
                        <div class="group relative w-16 h-16 rounded overflow-hidden cursor-pointer border border-slate-600" onclick="openImageModal('${r.content}')">
                            <img src="${r.content}" class="w-full h-full object-cover transition-transform group-hover:scale-110">
                            <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"><i class="fa-solid fa-eye text-white"></i></div>
                        </div>`;
                } else {
                    contentDisplay = `<div class="p-2 bg-slate-700/50 rounded border border-slate-600 text-sm text-white italic">"${r.content}"</div>`;
                }

                let actionColumn = '';
                if(r.status === 'PENDING') {
                    actionColumn = `<div class="flex justify-end gap-2"><button onclick="handleReview(${r.id}, 'REJECTED')" class="px-3 py-1.5 bg-slate-700 hover:bg-red-900/50 text-slate-300 hover:text-red-300 rounded text-xs">Refuser</button><button onclick="handleReview(${r.id}, 'APPROVED')" class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded text-xs font-bold shadow">Valider</button></div>`;
                } else if (r.status === 'APPROVED') {
                    actionColumn = `<div class="flex items-center justify-end gap-3"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-900 text-green-200 border border-green-700"><i class="fa-solid fa-check mr-1"></i> Validé</span><button onclick="handleReview(${r.id}, 'REJECTED')" class="text-slate-500 hover:text-red-400 text-xs flex items-center"><i class="fa-solid fa-arrow-right-arrow-left mr-1"></i> Changer</button></div>`;
                } else {
                    actionColumn = `<div class="flex items-center justify-end gap-3"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-900 text-red-200 border border-red-700"><i class="fa-solid fa-xmark mr-1"></i> Refusé</span><button onclick="handleReview(${r.id}, 'APPROVED')" class="text-slate-500 hover:text-green-400 text-xs flex items-center"><i class="fa-solid fa-arrow-right-arrow-left mr-1"></i> Changer</button></div>`;
                }

                let rowClass = "hover:bg-slate-700/50 transition-colors border-b border-slate-700 last:border-0";
                if(r.status === 'APPROVED') rowClass = "bg-green-900/5 hover:bg-green-900/10 border-b border-slate-700";
                if(r.status === 'REJECTED') rowClass = "opacity-75 hover:opacity-100 border-b border-slate-700";

                tbody.innerHTML += `<tr class="${rowClass}" id="review-${r.id}"><td class="px-6 py-4"><div class="text-sm font-medium text-white">${r.user}</div><div class="text-xs text-slate-500">${r.submitted}</div></td><td class="px-6 py-4">${contentDisplay}</td><td class="px-6 py-4 text-xs font-mono text-slate-400">${r.status}</td><td class="px-6 py-4 text-right">${actionColumn}</td></tr>`;
            });
        }

        async function handleReview(id, newStatus) {
            const reviewIndex = reviews.findIndex(r => r.id === id);
            if (reviewIndex > -1) {
                reviews[reviewIndex].status = newStatus;
                const msg = newStatus === 'APPROVED' ? 'Preuve validée !' : 'Preuve refusée.';
                const type = newStatus === 'APPROVED' ? 'success' : 'error';
                showToast(msg, type);
                renderDetailReviews();
            }
        }

        function openImageModal(src) { document.getElementById('previewImage').src = src; document.getElementById('imageModal').classList.remove('hidden'); }
        function closeImageModal() { document.getElementById('imageModal').classList.add('hidden'); }

        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            const bgClass = type === 'success' ? 'bg-green-600' : 'bg-red-600';
            toast.className = `flex items-center p-4 rounded shadow-lg text-white ${bgClass} toast-enter pointer-events-auto`;
            toast.innerHTML = `<i class="fa-solid ${type === 'success' ? 'fa-check' : 'fa-circle-xmark'} text-lg mr-3"></i><span class="text-sm font-medium">${message}</span>`;
            container.appendChild(toast);
            setTimeout(() => { toast.classList.remove('toast-enter'); toast.classList.add('toast-exit'); toast.addEventListener('animationend', () => toast.remove()); }, 3000);
        }

        renderChallenges();
    </script>
@endpush
