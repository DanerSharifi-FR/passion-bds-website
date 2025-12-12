// Common JavaScript for P'AS'SION BDS static pages
// Index is considered the reference; other pages add their specific behavior.

(function () {
    const path = (window.location && window.location.pathname) || '';
    const isGallery = path.includes('gallery');
    const isLogin = path.includes('login');
    const isMembers = path.includes('members');
    const isClassement = path.includes('classement');
    const isIndex = !isGallery && !isLogin && !isMembers && !isClassement;

    // --- Tailwind CDN config ---
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: {
                    sans: ['Inter', 'sans-serif'],
                    display: ['Poppins', 'sans-serif'],
                },
                colors: {
                    passion: {
                        pink: {
                            100: '#FFF0F5',
                            200: '#FCE2EC',
                            300: '#F9D7E5',
                            400: '#F4BBD2',
                            500: '#E4476A', // Heart accent
                        },
                        fire: {
                            orange: '#FF914D',
                            yellow: '#FFC94A',
                        },
                        red: '#9B1237', // Main text
                    }
                },
                animation: {
                    'float': 'float 6s ease-in-out infinite',
                    'float-delayed': 'float 7s ease-in-out 2s infinite',
                    'float-delayed-2': 'float 5s ease-in-out 1s infinite',
                    'bounce-slow': 'bounce-slow 3s infinite',
                    'pulse-fast': 'pulse 1s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    'shake': 'shake 0.5s cubic-bezier(.36,.07,.19,.97) both',
                    'confetti': 'confetti 0.5s ease-out forwards',
                    'glow-gold': 'glow-gold 2s ease-in-out infinite alternate',
                    'glow-silver': 'glow-silver 2s ease-in-out infinite alternate',
                    'glow-bronze': 'glow-bronze 2s ease-in-out infinite alternate',
                    'spin-slow': 'spin 3s linear infinite',
                    'pop': 'pop 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275)',
                },
                keyframes: {
                    float: {
                        '0%, 100%': {transform: 'translateY(0)'},
                        '50%': {transform: 'translateY(-6px)'},
                    },
                    'bounce-slow': {
                        '0%, 100%': {
                            transform: 'translateY(-5%)',
                            animationTimingFunction: 'cubic-bezier(0.8, 0, 1, 1)'
                        },
                        '50%': {transform: 'translateY(0)', animationTimingFunction: 'cubic-bezier(0, 0, 0.2, 1)'},
                    },
                    shake: {
                        '10%, 90%': {transform: 'translate3d(-1px, 0, 0)'},
                        '20%, 80%': {transform: 'translate3d(2px, 0, 0)'},
                        '30%, 50%, 70%': {transform: 'translate3d(-4px, 0, 0)'},
                        '40%, 60%': {transform: 'translate3d(4px, 0, 0)'}
                    },
                    'glow-gold': {
                        '0%': {boxShadow: '0 0 5px #CA8A04, 0 0 10px #FACC15'},
                        '100%': {boxShadow: '0 0 20px #CA8A04, 0 0 30px #FACC15'}
                    },
                    'glow-silver': {
                        '0%': {boxShadow: '0 0 5px #6B7280, 0 0 10px #D1D5DB'},
                        '100%': {boxShadow: '0 0 20px #6B7280, 0 0 30px #D1D5DB'}
                    },
                    'glow-bronze': {
                        '0%': {boxShadow: '0 0 5px #92400E, 0 0 10px #D97706'},
                        '100%': {boxShadow: '0 0 20px #92400E, 0 0 30px #D97706'}
                    },
                    pop: {
                        '0%': {transform: 'scale(0.5)', opacity: 0},
                        '100%': {transform: 'scale(1)', opacity: 1}
                    }
                }
            }
        }
    }

    // --- Page interactions ---
    document.addEventListener('DOMContentLoaded', function () {
        const icons = ['⚽', '🏀', '🏉', '🎾', '🎉', '🍻', '🍹', '🎵', '🔥', '💤', '📚', '🍕', '🏆', '🥐'];
        const container = document.getElementById('rain-container');

        function createRain() {
            const count = 15; // Low count for performance
            for (let i = 0; i < count; i++) {
                const icon = document.createElement('div');
                icon.classList.add('rain-icon');
                icon.innerText = icons[Math.floor(Math.random() * icons.length)];
                icon.style.left = Math.random() * 100 + 'vw';
                icon.style.animationDuration = Math.random() * 10 + 10 + 's'; // 10-20s fall
                icon.style.animationDelay = Math.random() * 20 + 's'; // random start
                icon.style.fontSize = Math.random() * 20 + 20 + 'px'; // random size
                container.appendChild(icon);
            }
        }

        createRain();

        if (isIndex) {
            function updateUI(state) {
                const statusBadge = document.querySelector('.status-indicator');
                const statusText = document.querySelector('.status-text-content');
                const msg = document.getElementById('status-message');
                const btnText = document.querySelector('.btn-text');

                if (state === 'closed') {
                    statusBadge.style.backgroundColor = '#9B1237';
                    statusBadge.style.color = 'white';
                    statusText.textContent = "WARM UP";
                    msg.textContent = "On prepare vos allos... RDV janvier 2026 !";
                    btnText.textContent = "J'active la machine !";
                } else {
                    statusBadge.style.backgroundColor = '#FF914D';
                    statusBadge.style.color = '#9B1237';
                    statusText.textContent = "GAME ON";
                    msg.textContent = "C'est parti mon grand. Fais tes choix.";
                    btnText.textContent = "Lancer un Allo";
                }
            }

            function toggleState() {
                const card = document.getElementById('machine-card');
                if (card.classList.contains('machine-state-closed')) {
                    card.classList.remove('machine-state-closed');
                    card.classList.add('machine-state-open');
                    updateUI('open');
                } else {
                    card.classList.remove('machine-state-open');
                    card.classList.add('machine-state-closed');
                    updateUI('closed');
                }
            }

            function toggleUser() {
                const arcadeZone = document.getElementById('arcade-zone');
                if (arcadeZone.classList.contains('state-guest')) {
                    arcadeZone.classList.remove('state-guest');
                    arcadeZone.classList.add('state-logged-in');
                } else {
                    arcadeZone.classList.remove('state-logged-in');
                    arcadeZone.classList.add('state-guest');
                }
            }

            // --- GAME LOGIC ---
            const gameCard = document.getElementById('game-card');
            const modes = ['game-mode-action', 'game-mode-input', 'game-mode-qcm'];
            let currentModeIndex = 0;

            function cycleGameMode() {
                // Remove current mode class
                gameCard.classList.remove(modes[currentModeIndex]);

                // Go to next
                currentModeIndex = (currentModeIndex + 1) % modes.length;

                // Add new mode class
                gameCard.classList.add(modes[currentModeIndex]);

                // Reset UI states if needed
                resetGameUI();
            }

            function resetGameUI() {
                const feedback = document.getElementById('input-feedback');
                const input = document.getElementById('game-input');
                const uploadBtn = document.getElementById('btn-action-upload');
                const previewContainer = document.getElementById('action-preview-container');
                const statusText = document.getElementById('action-status-text');

                if (feedback) feedback.textContent = '';
                if (input) {
                    input.value = '';
                    input.classList.remove('error-border', 'success-pulse', 'shake-element');
                }
                if (uploadBtn) {
                    uploadBtn.className = "show-if-logged-in bg-passion-fire-yellow text-[#9B1237] font-black uppercase px-6 py-3 rounded shadow-[4px_4px_0px_#000] hover:translate-y-1 hover:shadow-[2px_2px_0px_#000] transition-all flex flex-col items-center leading-none gap-1";
                    uploadBtn.innerHTML = '<span>J\'ai la photo !</span><span class="text-[10px] opacity-80 font-mono">+100 pts à gagner</span>';
                }
                if (previewContainer) {
                    previewContainer.innerHTML = '<span class="text-4xl">📸</span>';
                    previewContainer.className = "mb-1 flex items-center justify-center min-h-[40px]"; // Reset height
                }
                if (statusText) {
                    statusText.textContent = "Preuve requise (plus tard)";
                    statusText.className = "text-[10px] mt-3 uppercase tracking-widest opacity-60";
                }

                document.querySelectorAll('.qcm-option').forEach(btn => {
                    btn.classList.remove('bg-green-500', 'bg-red-500', 'border-white');
                    btn.classList.add('bg-black/30', 'border-white/10');
                });
            }

            function triggerFileSelection() {
                document.getElementById('proof-file').click();
            }

            function handleFileSelected(input) {
                if (input.files && input.files[0]) {
                    const file = input.files[0];
                    const btn = document.getElementById('btn-action-upload');
                    const previewContainer = document.getElementById('action-preview-container');
                    const statusText = document.getElementById('action-status-text');

                    // Simulate Upload State
                    const originalContent = btn.innerHTML;
                    btn.innerHTML = '<span class="animate-pulse">Envoi en cours...</span>';

                    setTimeout(() => {
                        // Create Preview URL
                        const url = URL.createObjectURL(file);

                        // Show Preview (Adjusted sizing for mobile)
                        previewContainer.innerHTML = `<img src="${url}" class="h-32 md:h-40 w-auto object-contain rounded border-2 border-white shadow-sm animate-pop" alt="Preview">`;
                        previewContainer.className = "mb-2 flex items-center justify-center"; // Adjust container for image

                        // Update Button to "Change" state
                        btn.className = "show-if-logged-in bg-white/20 border-2 border-white/50 text-white font-black uppercase px-4 py-2 rounded hover:bg-white/30 transition-all flex flex-col items-center leading-none gap-1";
                        btn.innerHTML = '<span>🔄 Changer la photo</span>';

                        // Update Status Message (Playful)
                        statusText.innerHTML = "🏁 Bien reçu ! Le jury BDS va juger ça (sois patient).";
                        statusText.className = "text-[10px] mt-3 uppercase tracking-widest text-passion-fire-yellow font-bold animate-pulse";

                    }, 1500);
                }
            }

            function validateInput() {
                const input = document.getElementById('game-input');
                const feedback = document.getElementById('input-feedback');
                const val = input.value.trim().toLowerCase();

                // Mock correct answer
                if (val === 'picsou' || val === 'radin') {
                    input.classList.remove('error-border', 'shake-element');
                    input.classList.add('success-pulse');
                    feedback.textContent = "✅ Bonne réponse ! (+10 pts)";
                    feedback.className = "h-6 mt-2 text-xs font-bold uppercase tracking-wider text-green-400";
                } else {
                    input.classList.remove('success-pulse');
                    input.classList.add('error-border', 'shake-element');
                    feedback.textContent = "❌ Faux. Essaye 'Picsou'.";
                    feedback.className = "h-6 mt-2 text-xs font-bold uppercase tracking-wider text-red-400";

                    // Re-trigger shake animation hack
                    setTimeout(() => input.classList.remove('shake-element'), 500);
                }
            }

            function checkQcm(btn, isCorrect) {
                // Reset others
                document.querySelectorAll('.qcm-option').forEach(b => {
                    b.classList.remove('bg-green-500', 'bg-red-500', 'border-white');
                    b.classList.add('opacity-50');
                });

                btn.classList.remove('opacity-50', 'bg-black/30', 'border-white/10');
                btn.classList.add('border-white');

                if (isCorrect) {
                    btn.classList.add('bg-green-500');
                    // Trigger Confetti or Points animation here
                } else {
                    btn.classList.add('bg-red-500', 'shake-element');
                    setTimeout(() => btn.classList.remove('shake-element'), 500);
                }
            }

            // Init
            updateUI('closed');
            if (typeof triggerFileSelection === 'function') window.triggerFileSelection = triggerFileSelection;
            if (typeof validateInput === 'function') window.validateInput = validateInput;
            if (typeof checkQcm === 'function') window.checkQcm = checkQcm;
            if (typeof toggleUser === 'function') window.toggleUser = toggleUser;
            if (typeof cycleGameMode === 'function') window.cycleGameMode = cycleGameMode;
            if (typeof toggleState === 'function') window.toggleState = toggleState;
        }

        if (isGallery) {
            // Each object is an Event. Images are placeholders here.
            const eventsData = [
                {
                    id: 1,
                    title: "WEI 2024",
                    date: "Octobre 2024",
                    link: "https://drive.google.com/drive/u/0/my-drive", // Link to specific folder
                    images: [
                        "https://placehold.co/400x400/FF914D/9B1237?text=WEI+1",
                        "https://placehold.co/400x400/9B1237/FFC94A?text=WEI+2",
                        "https://placehold.co/400x400/FCE2EC/E4476A?text=WEI+3"
                    ]
                },
                {
                    id: 2,
                    title: "Afterwork Halloween",
                    date: "31 Octobre 2024",
                    link: "https://drive.google.com",
                    images: [
                        "https://placehold.co/400x400/000000/FFFFFF?text=Spooky",
                        "https://placehold.co/400x400/FF914D/000000?text=Pumpkin",
                        "https://placehold.co/400x400/9B1237/FFFFFF?text=Party"
                    ]
                },
                {
                    id: 3,
                    title: "Match vs Centrale",
                    date: "15 Novembre 2024",
                    link: "https://drive.google.com",
                    images: [
                        "https://placehold.co/400x400/E4476A/FFFFFF?text=Rugby",
                        "https://placehold.co/400x400/9B1237/FFFFFF?text=Score",
                        "https://placehold.co/400x400/FFC94A/9B1237?text=Fans"
                    ]
                },
                {
                    id: 4,
                    title: "Soirée de Noël",
                    date: "Décembre 2024",
                    link: "https://drive.google.com",
                    images: [
                        "https://placehold.co/400x400/166534/FFFFFF?text=Xmas",
                        "https://placehold.co/400x400/dc2626/FFFFFF?text=Santa",
                        "https://placehold.co/400x400/FFFFFF/000000?text=Gift"
                    ]
                },
                {
                    id: 5,
                    title: "Tournoi Futsal",
                    date: "Janvier 2025",
                    link: "https://drive.google.com",
                    images: [
                        "https://placehold.co/400x400/2563eb/FFFFFF?text=Goal",
                        "https://placehold.co/400x400/E4476A/FFFFFF?text=Team",
                        "https://placehold.co/400x400/FFC94A/9B1237?text=Win"
                    ]
                },
                {
                    id: 6,
                    title: "Barbecue de Rentrée",
                    date: "Septembre 2024",
                    link: "https://drive.google.com",
                    images: [
                        "https://placehold.co/400x400/FF914D/FFFFFF?text=Grill",
                        "https://placehold.co/400x400/9B1237/FFFFFF?text=Merguez",
                        "https://placehold.co/400x400/FCE2EC/E4476A?text=Beer"
                    ]
                },
                {
                    id: 7,
                    title: "Voyage au Ski",
                    date: "Février 2025",
                    link: "https://drive.google.com",
                    images: [
                        "https://placehold.co/400x400/bfdbfe/1e40af?text=Snow",
                        "https://placehold.co/400x400/FFFFFF/1e3a8a?text=Ski",
                        "https://placehold.co/400x400/9B1237/FFFFFF?text=Raclette"
                    ]
                }
            ];

            // --- PAGINATION LOGIC ---
            const itemsPerPage = 3;
            let currentPage = 1;

            function renderEvents() {
                const container = document.getElementById('events-container');
                const start = (currentPage - 1) * itemsPerPage;
                const end = start + itemsPerPage;
                const paginatedItems = eventsData.slice(start, end);

                container.innerHTML = ''; // Clear current

                paginatedItems.forEach((event, index) => {
                    // Alternate skew direction for visual rhythm
                    const skewClass = index % 2 === 0 ? 'skew-box' : 'skew-box-r';
                    const unskewClass = index % 2 === 0 ? 'unskew-text' : 'unskew-text-r';

                    // FIXED: Reduced width to 80% (mobile) and 90% (desktop) to prevent corners cutting off
                    const cardHTML = `
                      <div class="gallery-card w-[80%] md:w-[90%] mx-auto p-4 md:p-6 relative ${skewClass} group cursor-pointer" onclick="window.open('${event.link}', '_blank')">

                          <!-- Inner Container to Un-Skew Content at once -->
                          <div class="${unskewClass} h-full w-full">

                              <!-- Header -->
                              <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-4 border-b-2 border-passion-pink-200 pb-2">
                                  <div class="w-full">
                                      <span class="block text-xs font-bold text-passion-fire-orange bg-passion-red px-2 py-0.5 rounded w-fit mb-1">${event.date}</span>
                                      <h2 class="font-display font-black text-2xl md:text-4xl text-passion-red uppercase leading-none break-words">${event.title}</h2>
                                  </div>
                                  <div class="hidden md:block whitespace-nowrap ml-4">
                                      <span class="text-xs font-mono text-passion-pink-500 font-bold group-hover:text-passion-fire-orange transition-colors">OPEN DRIVE ↗</span>
                                  </div>
                              </div>

                              <!-- Photos Grid (Polaroid Style) -->
                              <div class="flex justify-center gap-2 md:gap-8 py-4 relative h-40 md:h-56 items-center">
                                  ${event.images.map((img, i) => `
                                      <div class="polaroid absolute w-24 md:w-40 bg-white border border-gray-200" style="left: ${50 + (i - 1) * 20}%; transform: translateX(-50%) rotate(${(i - 1) * 5}deg);">
                                          <div class="w-full aspect-square bg-gray-100 mb-2 overflow-hidden">
                                              <img src="${img}" class="w-full h-full object-cover mix-blend-multiply opacity-90 hover:opacity-100 transition-opacity" alt="Photo ${event.title}">
                                          </div>
                                          <div class="h-1 md:h-2 w-full bg-gray-100/50"></div>
                                      </div>
                                  `).join('')}
                              </div>

                              <!-- Mobile Action Text -->
                              <div class="md:hidden mt-4 text-center">
                                  <span class="inline-block bg-passion-pink-100 text-passion-red text-[10px] font-bold px-3 py-1 rounded-full border border-passion-red">
                                      Voir l'album ➜
                                  </span>
                              </div>

                          </div>
                          <!-- End Inner Un-Skew -->

                          <!-- Decorative Corner (Outside unskew, so it follows the skew) -->
                          <div class="absolute top-2 right-2 w-2 h-2 md:w-3 md:h-3 bg-passion-fire-yellow rounded-full border border-passion-red"></div>
                      </div>
                      `;
                    container.innerHTML += cardHTML;
                });

                updatePaginationControls();
                window.scrollTo({top: 0, behavior: 'smooth'});
            }

            function updatePaginationControls() {
                const totalPages = Math.ceil(eventsData.length / itemsPerPage);
                document.getElementById('page-indicator').innerText = `${currentPage} / ${totalPages}`;
                document.getElementById('btn-prev').disabled = currentPage === 1;
                document.getElementById('btn-next').disabled = currentPage === totalPages;
            }

            function changePage(delta) {
                const totalPages = Math.ceil(eventsData.length / itemsPerPage);
                const newPage = currentPage + delta;
                if (newPage >= 1 && newPage <= totalPages) {
                    currentPage = newPage;
                    renderEvents();
                }
            }

            // Init
            renderEvents();

            if (typeof changePage === 'function') window.changePage = changePage;
        }

        /*if (isLogin) {
            // Login page security & steps logic
            // Security State
            let lastActionTime = 0;
            let lastEmail = "";
            let cooldownTimer = null;
            const COOLDOWN_SAME_EMAIL = 30000; // 30s
            const COOLDOWN_DIFF_EMAIL = 15000; // 15s

            function trySendCode(isResend = false) {
                const emailInput = document.getElementById('email-input');
                const email = emailInput.value.trim();
                const errorMsg = document.getElementById('error-msg');
                const resendError = document.getElementById('resend-error');
                const feedbackEl = isResend ? resendError : errorMsg;

                // 1. Regex Format Check
                const mailFormat = /^.{2,}\..{2,}@imt-atlantique\.net$/;
                if (!mailFormat.test(email)) {
                    showError("T'es à l'IMT ou pas ? Mets ton mail école (@imt-atlantique.net).", feedbackEl, emailInput);
                    return;
                } else {
                    hideError(feedbackEl, emailInput);
                }

                // 2. Rate Limiting Logic
                const now = Date.now();
                let requiredCooldown = 0;

                if (lastEmail) {
                    if (email === lastEmail) {
                        requiredCooldown = COOLDOWN_SAME_EMAIL;
                    } else {
                        requiredCooldown = COOLDOWN_DIFF_EMAIL;
                    }
                }

                const timeDiff = now - lastActionTime;

                if (lastActionTime > 0 && timeDiff < requiredCooldown) {
                    const remainingMs = requiredCooldown - timeDiff;
                    startRealtimeCooldown(remainingMs, feedbackEl, emailInput);
                    return;
                }

                // 3. Success -> Send Code (Mock)
                lastActionTime = now;
                lastEmail = email;

                if (cooldownTimer) clearInterval(cooldownTimer);
                hideError(feedbackEl, emailInput);

                if (!isResend) {
                    proceedToStep2();
                } else {
                    const btnResend = document.getElementById('btn-resend');
                    const originalText = btnResend.innerText;
                    btnResend.innerText = "Envoyé !";
                    btnResend.classList.add("text-green-500");
                    setTimeout(() => {
                        btnResend.innerText = originalText;
                        btnResend.classList.remove("text-green-500");
                    }, 2000);
                }
            }

            function startRealtimeCooldown(durationMs, element, input) {
                if (cooldownTimer) clearInterval(cooldownTimer);

                let secondsLeft = Math.ceil(durationMs / 1000);

                const updateText = () => {
                    if (secondsLeft <= 0) {
                        clearInterval(cooldownTimer);
                        hideError(element, input);
                        return;
                    }
                    showError(`Doucement l'athlète ! Attends ${secondsLeft}s.`, element, null);
                    secondsLeft--;
                };

                updateText();
                cooldownTimer = setInterval(updateText, 1000);
            }

            function showError(msg, el, input) {
                el.innerText = msg;
                el.classList.remove('hidden');
                if (input) {
                    input.classList.add('border-red-500', 'bg-red-50');
                }
            }

            function hideError(el, input) {
                el.classList.add('hidden');
                if (input) {
                    input.classList.remove('border-red-500', 'bg-red-50');
                }
            }

            function proceedToStep2() {
                const s1 = document.getElementById('step-1');
                const s2 = document.getElementById('step-2');
                const resendError = document.getElementById('resend-error'); // Step 2 error

                // CLEANUP: Ensure Step 2 is clean before showing it
                hideError(resendError, null);

                s1.classList.add('opacity-0');
                setTimeout(() => {
                    s1.classList.add('hidden');
                    s2.classList.remove('hidden');
                    setTimeout(() => {
                        s2.classList.remove('opacity-0');
                        document.querySelector('.code-digit').focus();
                    }, 50);
                }, 300);
            }

            function goToStep1() {
                const s1 = document.getElementById('step-1');
                const s2 = document.getElementById('step-2');
                const errorMsg = document.getElementById('error-msg');
                const resendError = document.getElementById('resend-error');

                hideError(errorMsg, document.getElementById('email-input'));
                hideError(resendError, null);

                if (cooldownTimer) clearInterval(cooldownTimer);

                s2.classList.add('opacity-0');
                setTimeout(() => {
                    s2.classList.add('hidden');
                    s1.classList.remove('hidden');
                    setTimeout(() => s1.classList.remove('opacity-0'), 50);
                }, 300);
            }

            // Bridge for old onClick call
            function goToStep2() {
                trySendCode(false);
            }

            function finishLogin() {
                window.location.href = '/';
            }

            const inputs = document.querySelectorAll('.code-digit');
            inputs.forEach((input, index) => {
                input.addEventListener('input', (e) => {
                    if (e.target.value.length === 1 && index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                });
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && !e.target.value && index > 0) {
                        inputs[index - 1].focus();
                    }
                });
            });

            if (typeof trySendCode === 'function') window.trySendCode = trySendCode;
            if (typeof goToStep1 === 'function') window.goToStep1 = goToStep1;
            if (typeof finishLogin === 'function') window.finishLogin = finishLogin;
        }*/

    });
})();
