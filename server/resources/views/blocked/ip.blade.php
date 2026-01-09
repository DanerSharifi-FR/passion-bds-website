<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès temporairement bloqué</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        .glow { box-shadow: 0 0 0 1px rgba(99,102,241,.25), 0 0 40px rgba(99,102,241,.18); }
        .pulse-soft { animation: pulseSoft 1.6s ease-in-out infinite; }
        @keyframes pulseSoft { 0%,100% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.01); opacity: .92; } }
        .scanline {
            background: linear-gradient(to bottom, transparent, rgba(99,102,241,.12), transparent);
            animation: scan 2.2s linear infinite;
        }
        @keyframes scan { 0% { transform: translateY(-30%); } 100% { transform: translateY(130%); } }

        html, body { overflow: hidden; }
    </style>
</head>

<body class="h-full text-slate-100">
<div class="min-h-full flex items-center justify-center px-6 py-12">
    <div class="w-full max-w-xl relative">
        <div class="absolute inset-0 rounded-3xl scanline opacity-40 pointer-events-none"></div>

        <div class="relative bg-slate-900/60 backdrop-blur rounded-3xl border border-slate-800 p-8 md:p-10 glow">
            <div class="flex items-start gap-4">
                <div class="shrink-0 w-12 h-12 rounded-2xl bg-indigo-500/15 border border-indigo-500/30 flex items-center justify-center">
                    <span class="text-indigo-300 text-xl">⛔</span>
                </div>

                <div class="flex-1">
                    <h1 class="text-2xl md:text-3xl font-bold tracking-tight">Accès temporairement bloqué</h1>
                    <p class="text-slate-300 mt-2 leading-relaxed">
                        Ton IP a été bloquée automatiquement après trop de tentatives.
                        Réessaie quand le compteur arrive à zéro.
                    </p>

                    <div class="mt-7 rounded-2xl border border-indigo-500/25 bg-indigo-500/10 p-5 flex items-center justify-between gap-4 pulse-soft">
                        <div>
                            <p class="text-xs uppercase tracking-wider text-indigo-200/80 font-semibold">Temps restant</p>
                            <p class="text-2xl font-bold text-indigo-100 tabular-nums" id="countdownText">--:--</p>
                        </div>

                        <div class="text-right">
                            <p class="text-xs text-indigo-200/70">Déblocage à</p>
                            <p class="text-sm font-semibold text-indigo-100 tabular-nums" id="unlockAtText">--</p>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col sm:flex-row gap-3">
                        <button id="retryButton"
                                class="w-full sm:w-auto px-5 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 transition-colors font-semibold text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            Réessayer
                        </button>
                    </div>

                    <p class="mt-6 text-xs text-slate-500">
                        Si tu penses que c’est une erreur, attends la fin du timer.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        'use strict';

        const blockedUntilIso = @json($blockedUntilIso);
        const retryButtonElement = document.getElementById('retryButton');
        const countdownTextElement = document.getElementById('countdownText');
        const unlockAtTextElement = document.getElementById('unlockAtText');

        function pad2(n) { return String(n).padStart(2, '0'); }

        function formatCountdown(secondsLeft) {
            const minutes = Math.floor(secondsLeft / 60);
            const seconds = secondsLeft % 60;
            return `${pad2(minutes)}:${pad2(seconds)}`;
        }

        function formatUnlockTime(dateObject) {
            return new Intl.DateTimeFormat('fr-FR', {
                dateStyle: 'short',
                timeStyle: 'short',
            }).format(dateObject);
        }

        const blockedUntilDate = new Date(blockedUntilIso);
        unlockAtTextElement.textContent = formatUnlockTime(blockedUntilDate);

        function tick() {
            const nowMs = Date.now();
            const diffSeconds = Math.max(0, Math.ceil((blockedUntilDate.getTime() - nowMs) / 1000));

            countdownTextElement.textContent = formatCountdown(diffSeconds);

            if (diffSeconds <= 0) {
                retryButtonElement.disabled = false;
                retryButtonElement.textContent = 'Recharger';
                return;
            }

            retryButtonElement.disabled = true;
            retryButtonElement.textContent = 'Réessayer';
        }

        retryButtonElement.addEventListener('click', function () {
            window.location.reload();
        });

        tick();
        window.setInterval(tick, 250);
    })();
</script>
</body>
</html>
