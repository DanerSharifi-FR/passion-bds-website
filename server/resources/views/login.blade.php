@extends('app')

@section('title', "P'AS'SION BDS - Connexion")
@section('meta_description', "Connexion à l'espace membre de P'AS'SION BDS.")

@section('content')
    <!-- Background Decors (Repositioned to avoid overlap) -->
    <div class="sticker text-8xl top-24 -left-4 animate-float lg:left-10 lg:top-32">🔒</div>
    <div class="sticker text-9xl bottom-32 -right-4 animate-float lg:right-10 lg:bottom-10"
         style="animation-delay: 2s;">🔑
    </div>

    <div class="w-full max-w-md mb-20">

        <!-- Login Header -->
        <div class="text-center mb-8">
            <h1 class="font-display font-black text-3xl md:text-4xl text-passion-red uppercase tracking-tighter leading-none">
                Espace <span class="text-passion-fire-orange">Allos</span>
            </h1>
            <p class="text-passion-pink-500 font-medium mt-2 text-sm">Pas de mot de passe. Juste ta flemme.</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white border-4 border-passion-red md:p-8 px-4 py-12 shadow-[8px_8px_0px_#FF914D] relative">

            <!-- STEP 1: EMAIL -->
            <div id="step-1" class="transition-opacity duration-300">
                <label class="block text-xs font-bold uppercase tracking-widest text-passion-red mb-2">Email de
                    l'école</label>
                <input type="email" id="email-input" placeholder="prenom.nom@imt-atlantique.net"
                       class="w-full bg-passion-pink-100 border-2 border-passion-pink-400 p-4 text-passion-red placeholder-passion-red/40 font-bold focus:outline-none focus:border-passion-fire-orange transition-colors mb-2 rounded-none">

                <!-- Error Message Container -->
                <p id="error-msg"
                   class="text-[10px] text-red-500 font-bold uppercase tracking-wide hidden mb-4 bg-red-100 px-2 py-1 border-l-2 border-red-500">
                    <!-- JS fills this -->
                </p>

                <button id="btn-send-code" onclick="trySendCode()"
                        class="w-full bg-passion-red text-white font-display font-black uppercase text-xl py-4 mt-2 hover:bg-passion-fire-orange hover:text-passion-red transition-colors shadow-[4px_4px_0px_#000] active:translate-y-1 active:shadow-none disabled:opacity-50 disabled:cursor-not-allowed">
                    Envoyer le code
                </button>
            </div>

            <!-- STEP 2: CODE (Hidden initially) -->
            <div id="step-2" class="hidden opacity-0 transition-opacity duration-300">
                <div class="flex justify-between items-center mb-4">
                    <label class="block text-xs font-bold uppercase tracking-widest text-passion-red">Code reçu par
                        mail</label>
                    <button onclick="goToStep1()"
                            class="text-[10px] text-gray-400 hover:text-passion-red uppercase underline">Modifier
                        l'email
                    </button>
                </div>

                <div class="flex gap-2 mb-6 justify-between">
                    <input type="text" maxlength="1"
                           class="code-digit w-14 h-16 text-center text-2xl font-black bg-passion-pink-100 border-2 border-passion-pink-400 focus:border-passion-fire-orange focus:outline-none text-passion-red">
                    <input type="text" maxlength="1"
                           class="code-digit w-14 h-16 text-center text-2xl font-black bg-passion-pink-100 border-2 border-passion-pink-400 focus:border-passion-fire-orange focus:outline-none text-passion-red">
                    <input type="text" maxlength="1"
                           class="code-digit w-14 h-16 text-center text-2xl font-black bg-passion-pink-100 border-2 border-passion-pink-400 focus:border-passion-fire-orange focus:outline-none text-passion-red">
                    <input type="text" maxlength="1"
                           class="code-digit w-14 h-16 text-center text-2xl font-black bg-passion-pink-100 border-2 border-passion-pink-400 focus:border-passion-fire-orange focus:outline-none text-passion-red">
                </div>

                <button onclick="finishLogin()"
                        class="w-full bg-passion-fire-yellow text-passion-red font-display font-black uppercase text-xl py-4 hover:bg-passion-fire-orange transition-colors shadow-[4px_4px_0px_#000] active:translate-y-1 active:shadow-none mb-3">
                    Valider
                </button>

                <div class="text-center">
                    <button id="btn-resend" onclick="trySendCode(true)"
                            class="text-[10px] text-passion-pink-500 font-bold uppercase hover:text-passion-red underline disabled:opacity-50 disabled:no-underline disabled:cursor-not-allowed">
                        Renvoyer le code
                    </button>
                    <p class="mt-1 text-[9px] text-gray-400">Ça arrive pas ? Check tes spams ou crie très fort.</p>
                    <!-- Resend Feedback -->
                    <p id="resend-error"
                       class="text-[10px] text-red-500 font-bold uppercase tracking-wide hidden mt-2">
                        <!-- JS fills this -->
                    </p>
                </div>
            </div>

        </div>

    </div>
@endsection

@push('end_scripts')
    <script src="{{ asset('assets/auth.js') }}"></script>
    <script>
        // Security State
        let lastActionTime = 0;
        let lastEmail = "";
        let cooldownTimer = null;
        const COOLDOWN_SAME_EMAIL = 30000; // 30s
        const COOLDOWN_DIFF_EMAIL = 15000; // 15s

        async function trySendCode(isResend = false) {
            const emailInput = document.getElementById('email-input');
            const email = emailInput.value.trim().toLowerCase();
            const errorMsg = document.getElementById('error-msg');
            const resendError = document.getElementById('resend-error');
            const feedbackEl = isResend ? resendError : errorMsg;

            // 1. Regex Format Check
            const mailFormat = /^[a-z0-9][a-z0-9-]*\.[a-z0-9][a-z0-9-]*@imt-atlantique\.net$/i;
            if (!mailFormat.test(email)) {
                showError("T'es à l'IMT ou pas ? Mets ton mail de l'école.", feedbackEl, emailInput);
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

            // 3. Send Code (REAL)
            lastActionTime = now;
            lastEmail = email;

            if (cooldownTimer) clearInterval(cooldownTimer);
            hideError(feedbackEl, emailInput);

            const btn = document.getElementById('btn-send-code');
            btn.disabled = true;

            try {
                await postJson('/auth/request-code', {email});

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
            } catch (e) {
                // if backend says wait Xs, show it
                showError(e.message || "Erreur", feedbackEl, emailInput);
            } finally {
                btn.disabled = false;
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
            if(input) {
                input.classList.add('border-red-500', 'bg-red-50');
            }
        }

        function hideError(el, input) {
            el.classList.add('hidden');
            if(input) {
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

        async function finishLogin() {
            const email = document.getElementById('email-input').value.trim().toLowerCase();
            const digits = Array.from(document.querySelectorAll('.code-digit')).map(i => i.value.trim()).join('');

            // Basic UI validation
            if (digits.length !== 4) {
                const resendError = document.getElementById('resend-error');
                showError("4 chiffres requis.", resendError, null);
                return;
            }

            try {
                await postJson('/auth/verify-code', { email, code: digits });

                // REDIRECT FOR THIS PAGE:
                window.location.href = '/'; // change if needed
            } catch (e) {
                const resendError = document.getElementById('resend-error');
                showError(e.message || "Code incorrect.", resendError, null);
            }
        }


        const inputs = document.querySelectorAll('.code-digit');
        inputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                if(e.target.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });
            input.addEventListener('keydown', (e) => {
                if(e.key === 'Backspace' && !e.target.value && index > 0) {
                    inputs[index - 1].focus();
                }
            });
        });
    </script>
@endpush
