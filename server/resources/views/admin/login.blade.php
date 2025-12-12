<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campagne BDS - Connexion</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        .fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .shake { animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both; }
        @keyframes shake {
            10%, 90% { transform: translate3d(-1px, 0, 0); }
            20%, 80% { transform: translate3d(2px, 0, 0); }
            30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
            40%, 60% { transform: translate3d(4px, 0, 0); }
        }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4">

<div class="w-full max-w-md bg-slate-800 rounded-xl shadow-2xl border border-slate-700 overflow-hidden fade-in">

    <!-- Header -->
    <div class="bg-indigo-600 p-8 text-center relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 to-purple-700 opacity-90"></div>
        <div class="absolute -top-12 -right-12 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl"></div>

        <div class="relative z-10">
            <div class="mx-auto bg-white/20 w-16 h-16 rounded-full flex items-center justify-center backdrop-blur-sm mb-4">
                <i id="headerIcon" class="fa-solid fa-bolt text-3xl text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Campagne BDS 2025</h1>
            <p class="text-indigo-200 text-sm mt-1">Plateforme Centralisée</p>
        </div>
    </div>

    <div class="p-8">

        <!-- STEP 1: EMAIL -->
        <div id="stepEmail">
            <form id="formEmail" class="space-y-6" novalidate>
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-2">Email IMT Atlantique</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-envelope text-slate-500"></i>
                        </div>
                        <input type="email" name="email" id="email"
                               class="block w-full pl-10 pr-3 py-3 bg-slate-900 border border-slate-600 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                               placeholder="prenom.nom@imt-atlantique.net"
                               required
                               autocomplete="username">
                    </div>
                    <p id="emailError" class="mt-2 text-sm text-red-400 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i> <span>Erreur</span></p>
                </div>

                <button type="submit" id="btnSendCode" class="mt-6 w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-800 focus:ring-indigo-500 transition-all">
                    Recevoir le code
                </button>
            </form>
        </div>


        <!-- STEP 2: CODE -->
        <div id="stepCode" class="hidden">
            <form id="formCode" class="space-y-6" novalidate>

                <div class="flex justify-between items-center mb-2">
                    <label for="code" class="block text-sm font-medium text-slate-300">Code à 4 chiffres</label>
                    <button type="button" id="btnChangeEmail" class="text-xs text-indigo-400 hover:text-indigo-300">Changer d'email</button>
                </div>

                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa-solid fa-key text-slate-500"></i>
                    </div>
                    <input type="text" name="code" id="code" maxlength="4" inputmode="numeric" pattern="[0-9]*"
                           class="block w-full pl-10 pr-3 py-3 bg-slate-900 border border-slate-600 rounded-lg text-white text-xl tracking-widest placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all font-mono"
                           placeholder="0000"
                           required>
                </div>
                <p id="codeError" class="mt-2 text-sm text-red-400 hidden"><i class="fa-solid fa-circle-exclamation mr-1"></i> <span>Code incorrect</span></p>

                <button type="submit" id="btnLogin" class="mt-6 w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-800 focus:ring-green-500 transition-all">
                    Se connecter
                </button>
            </form>

            <!-- Resend Button -->
            <div class="mt-4 text-center">
                <form id="formResend">
                    <button type="submit" id="btnResend" class="text-sm text-indigo-400 hover:text-indigo-300 transition-colors bg-transparent border-none cursor-pointer">
                        Code non reçu ? Renvoyer
                    </button>
                </form>
                <p id="resendMsg" class="text-xs text-amber-400 mt-1 hidden"></p>
            </div>
        </div>

    </div>

    <div class="bg-slate-900/50 p-4 border-t border-slate-700 text-center">
        <p class="text-xs text-slate-500">Authentification Étudiante Sécurisée</p>
    </div>
</div>

<script src="{{ asset('assets/auth.js') }}"></script>
<script>
    const DEFAULT_COOLDOWN_SECONDS = 30;
    const STORAGE_KEY = 'admin_login_cooldown_until_ms_v1';

    const AUTH_PREFIX = '/admin/auth';
    const REDIRECT_URL = '/admin';

    const stepEmail = document.getElementById('stepEmail');
    const stepCode = document.getElementById('stepCode');
    const headerIcon = document.getElementById('headerIcon');

    const formEmail = document.getElementById('formEmail');
    const formCode = document.getElementById('formCode');
    const formResend = document.getElementById('formResend');

    const emailInputElement = document.getElementById('email');
    const codeInputElement = document.getElementById('code');

    const emailErrorElement = document.getElementById('emailError');
    const codeErrorElement = document.getElementById('codeError');

    const sendCodeButtonElement = document.getElementById('btnSendCode');
    const loginButtonElement = document.getElementById('btnLogin');
    const resendButtonElement = document.getElementById('btnResend');
    const resendMessageElement = document.getElementById('resendMsg');
    const changeEmailButton = document.getElementById('btnChangeEmail');

    let currentUniversityEmail = '';
    let cooldownInterval = null;

    function getCsrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    function extractWaitSeconds(msg) {
        const m = String(msg || '').match(/(\d+)\s*s/i);
        return m ? parseInt(m[1], 10) : null;
    }

    function validateEmailFormat(email) {
        return /^[a-z0-9][a-z0-9-]*\.[a-z0-9][a-z0-9-]*@imt-atlantique\.net$/i.test(email);
    }

    function normalizeEmail() {
        return emailInputElement.value.trim().toLowerCase();
    }

    function normalizeCode() {
        return codeInputElement.value.replace(/[^0-9]/g, '').trim();
    }

    function setButtonLoading(btn, isLoading, text) {
        if (isLoading) {
            btn.disabled = true;
            btn.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin"></i> ${text}`;
            btn.classList.add('opacity-75', 'cursor-not-allowed');
        } else {
            btn.disabled = false;
            btn.innerHTML = text;
            btn.classList.remove('opacity-75', 'cursor-not-allowed');
        }
    }

    function showFieldError(input, errorEl, show, msg) {
        if (!errorEl) return;

        if (show) {
            if (input) {
                input.classList.add('border-red-500', 'focus:ring-red-500');
                input.classList.remove('border-slate-600', 'focus:ring-indigo-500');
                input.parentElement?.classList.add('shake');
                setTimeout(() => input.parentElement?.classList.remove('shake'), 500);
            }

            errorEl.classList.remove('hidden');
            const span = errorEl.querySelector('span');
            if (span) span.innerText = msg || 'Erreur';
        } else {
            if (input) {
                input.classList.remove('border-red-500', 'focus:ring-red-500');
                input.classList.add('border-slate-600', 'focus:ring-indigo-500');
            }
            errorEl.classList.add('hidden');
        }
    }

    function showResendMsg(msg) {
        resendMessageElement.classList.remove('hidden');
        resendMessageElement.innerText = msg;
    }

    function hideResendMsg() {
        resendMessageElement.classList.add('hidden');
        resendMessageElement.innerText = '';
    }

    function switchToCodeStep() {
        stepEmail.classList.add('hidden');
        stepCode.classList.remove('hidden');
        stepCode.classList.add('fade-in');
        headerIcon.className = 'fa-solid fa-lock text-3xl text-white';
        codeInputElement.focus();
    }

    function switchToEmailStep() {
        stepCode.classList.add('hidden');
        stepEmail.classList.remove('hidden');
        stepEmail.classList.add('fade-in');
        headerIcon.className = 'fa-solid fa-bolt text-3xl text-white';
        emailInputElement.focus();
    }

    function getCooldownUntil() {
        return parseInt(localStorage.getItem(STORAGE_KEY) || '0', 10);
    }

    function checkCooldown() {
        const until = getCooldownUntil();
        const now = Date.now();
        const remaining = Math.ceil((until - now) / 1000);
        if (remaining > 0) {
            applyCooldownUI(remaining);
            return true;
        }
        return false;
    }

    function startCooldown(seconds) {
        const until = Date.now() + seconds * 1000;
        localStorage.setItem(STORAGE_KEY, String(until));
        applyCooldownUI(seconds);
    }

    function applyCooldownUI(seconds) {
        if (cooldownInterval) clearInterval(cooldownInterval);

        const update = (remaining) => {
            if (sendCodeButtonElement) {
                sendCodeButtonElement.disabled = true;
                sendCodeButtonElement.classList.add('opacity-50', 'cursor-not-allowed');
                sendCodeButtonElement.innerText = `Attends ${remaining}s...`;
            }
            if (resendButtonElement) {
                resendButtonElement.disabled = true;
                resendButtonElement.classList.add('opacity-50', 'cursor-not-allowed');
                resendButtonElement.innerText = `Attends ${remaining}s...`;
            }
        };

        let remaining = seconds;
        update(remaining);

        cooldownInterval = setInterval(() => {
            remaining--;
            if (remaining <= 0) {
                clearInterval(cooldownInterval);
                resetCooldownUI();
                return;
            }
            update(remaining);
        }, 1000);
    }

    function resetCooldownUI() {
        if (sendCodeButtonElement) {
            sendCodeButtonElement.disabled = false;
            sendCodeButtonElement.classList.remove('opacity-50', 'cursor-not-allowed');
            sendCodeButtonElement.innerText = 'Recevoir le code';
        }
        if (resendButtonElement) {
            resendButtonElement.disabled = false;
            resendButtonElement.classList.remove('opacity-50', 'cursor-not-allowed');
            resendButtonElement.innerText = 'Code non reçu ? Renvoyer';
        }
    }

    async function postJson(url, data) {
        const res = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrf(),
            },
            body: JSON.stringify(data),
        });

        const text = await res.text();
        let payload = null;
        try {
            payload = text ? JSON.parse(text) : null;
        } catch {}

        if (!res.ok) {
            const msg =
                payload?.errors?.email?.[0] ||
                payload?.errors?.code?.[0] ||
                payload?.message ||
                'Erreur serveur';

            const err = new Error(msg);
            err.payload = payload;
            err.status = res.status;
            throw err;
        }

        return payload ?? { ok: true };
    }

    async function authApiRequest(action, data) {
        if (action === 'send-code') {
            await postJson(`${AUTH_PREFIX}/request-code`, { email: data.email });
            return { success: true };
        }

        if (action === 'verify') {
            await postJson(`${AUTH_PREFIX}/verify-code`, { email: data.email, code: data.code });
            return { success: true };
        }

        if (action === 'resend') {
            await postJson(`${AUTH_PREFIX}/request-code`, { email: data.email });
            return { success: true };
        }

        return { success: false, message: 'Unknown action' };
    }

    formEmail.addEventListener('submit', async (e) => {
        e.preventDefault();

        const submittedUniversityEmail = normalizeEmail();

        showFieldError(emailInputElement, emailErrorElement, false, '');
        hideResendMsg();

        if (!validateEmailFormat(submittedUniversityEmail)) {
            showFieldError(emailInputElement, emailErrorElement, true, 'Format invalide (prenom.nom@imt-atlantique.net)');
            return;
        }

        if (checkCooldown()) return;

        setButtonLoading(sendCodeButtonElement, true, 'Envoi...');

        try {
            const serverResponse = await authApiRequest('send-code', { email: submittedUniversityEmail });

            if (serverResponse?.success) {
                currentUniversityEmail = submittedUniversityEmail;
                startCooldown(DEFAULT_COOLDOWN_SECONDS);
                switchToCodeStep();
                return;
            }

            const msg = serverResponse?.message || 'Erreur';
            const wait = extractWaitSeconds(msg);
            if (wait) startCooldown(wait);

            showFieldError(emailInputElement, emailErrorElement, true, msg);

        } catch (err) {
            const msg =
                err?.payload?.errors?.email?.[0] ||
                err?.message ||
                'Erreur';

            const wait = extractWaitSeconds(msg);
            if (wait) startCooldown(wait);

            showFieldError(emailInputElement, emailErrorElement, true, msg);

        } finally {
            setButtonLoading(sendCodeButtonElement, false, 'Recevoir le code');
            checkCooldown();
        }
    });

    formCode.addEventListener('submit', async (e) => {
        e.preventDefault();

        const email = currentUniversityEmail || normalizeEmail();
        const code = normalizeCode();

        showFieldError(codeInputElement, codeErrorElement, false, '');

        if (!email || !validateEmailFormat(email)) {
            switchToEmailStep();
            showFieldError(emailInputElement, emailErrorElement, true, 'Email invalide.');
            return;
        }

        if (code.length !== 4) {
            showFieldError(codeInputElement, codeErrorElement, true, '4 chiffres requis');
            return;
        }

        setButtonLoading(loginButtonElement, true, 'Vérification...');

        try {
            const serverResponse = await authApiRequest('verify', { email, code });

            if (serverResponse?.success) {
                window.location.href = REDIRECT_URL;
                return;
            }

            showFieldError(codeInputElement, codeErrorElement, true, serverResponse?.message || 'Code incorrect');

        } catch (err) {
            const msg =
                err?.payload?.errors?.code?.[0] ||
                err?.message ||
                'Code incorrect';

            showFieldError(codeInputElement, codeErrorElement, true, msg);

        } finally {
            setButtonLoading(loginButtonElement, false, 'Se connecter');
        }
    });

    formResend.addEventListener('submit', async (e) => {
        e.preventDefault();

        const resendUniversityEmail = currentUniversityEmail || normalizeEmail();

        hideResendMsg();

        if (!validateEmailFormat(resendUniversityEmail)) {
            showResendMsg('Email invalide.');
            switchToEmailStep();
            return;
        }

        if (checkCooldown()) {
            showResendMsg('Veuillez patienter...');
            return;
        }

        resendButtonElement.disabled = true;
        resendButtonElement.innerText = 'Envoi...';

        try {
            const serverResponse = await authApiRequest('resend', { email: resendUniversityEmail });

            if (serverResponse?.success) {
                currentUniversityEmail = resendUniversityEmail;
                startCooldown(DEFAULT_COOLDOWN_SECONDS);
                return;
            }

            const msg = serverResponse?.message || "Erreur lors de l'envoi";
            const wait = extractWaitSeconds(msg);
            if (wait) startCooldown(wait);

            showResendMsg(msg);

        } catch (err) {
            const msg =
                err?.payload?.errors?.email?.[0] ||
                err?.message ||
                "Erreur lors de l'envoi";

            const wait = extractWaitSeconds(msg);
            if (wait) startCooldown(wait);

            showResendMsg(msg);

        } finally {
            checkCooldown();
            if (!checkCooldown()) {
                resendButtonElement.disabled = false;
                resendButtonElement.innerText = 'Code non reçu ? Renvoyer';
            }
        }
    });

    changeEmailButton?.addEventListener('click', () => {
        showFieldError(emailInputElement, emailErrorElement, false, '');
        showFieldError(codeInputElement, codeErrorElement, false, '');
        hideResendMsg();
        switchToEmailStep();
    });

    emailInputElement.addEventListener('input', () => showFieldError(emailInputElement, emailErrorElement, false, ''));
    codeInputElement.addEventListener('input', (e) => {
        e.target.value = e.target.value.replace(/[^0-9]/g, '');
        showFieldError(codeInputElement, codeErrorElement, false, '');
    });

    checkCooldown();
</script>


</body>
</html>
