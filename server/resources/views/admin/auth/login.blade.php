<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Admin – P'AS'SION BDS</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-slate-50">
<div class="min-h-screen flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-md">
        <div class="mb-6 text-center">
            <div
                class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-sky-500/10 border border-sky-500/40 text-[11px] uppercase tracking-wide text-sky-100">
                Admin Panel
            </div>
            <h1 class="mt-3 text-2xl font-bold tracking-tight">
                Connexion admin P'AS'SION BDS
            </h1>
            <p class="mt-2 text-sm text-slate-300">
                Accès réservé aux rôles&nbsp;:
                <span class="font-mono text-xs bg-slate-800/70 px-1.5 py-0.5 rounded border border-slate-700/60">ROLE_GAMEMASTER</span>,
                <span class="font-mono text-xs bg-slate-800/70 px-1.5 py-0.5 rounded border border-slate-700/60">ROLE_BLOGGER</span>,
                <span class="font-mono text-xs bg-slate-800/70 px-1.5 py-0.5 rounded border border-slate-700/60">ROLE_TEAM</span>,
                <span class="font-mono text-xs bg-slate-800/70 px-1.5 py-0.5 rounded border border-slate-700/60">ROLE_SHOP</span>,
                <span class="font-mono text-xs bg-slate-800/70 px-1.5 py-0.5 rounded border border-slate-700/60">ROLE_SUPER_ADMIN</span>.
            </p>
        </div>

        @if(session('status') === 'admin_login_code_sent')
            <div
                class="mb-4 bg-emerald-500/10 border border-emerald-500/60 text-emerald-100 px-4 py-3 rounded-lg text-sm">
                Un code à 4 chiffres a été généré pour cet email (en dev, vérifie les logs).
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 bg-red-500/10 border border-red-500/60 text-red-100 px-4 py-3 rounded-lg text-sm">
                <p class="font-semibold mb-1">Erreur dans le formulaire :</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            /** @var string|null $prefilledEmail */
            $emailValue = old('email', $prefilledEmail ?? '');
        @endphp

        <div
            class="bg-slate-900/60 border border-slate-700/80 rounded-2xl shadow-xl shadow-slate-950/60 p-5 space-y-5 backdrop-blur">
            {{-- Étape 1 : demander un code --}}
            <form method="POST" action="{{ route('admin.login.requestCode') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 uppercase mb-1 tracking-wide">
                        Email universitaire
                    </label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        required
                        autocomplete="email"
                        value="{{ $emailValue }}"
                        class="w-full rounded-lg border border-slate-600 bg-slate-900/80 px-3 py-2 text-sm text-slate-50 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                        placeholder="prenom.nom@imt-atlantique.fr"
                    >
                    <p class="mt-1 text-[11px] text-slate-400">
                        On utilise uniquement l’email de l’école pour identifier les membres de la liste.
                    </p>
                </div>

                <button
                    type="submit"
                    class="w-full inline-flex items-center justify-center rounded-lg bg-sky-600 hover:bg-sky-500 text-sm font-semibold py-2.5 shadow-sm shadow-sky-900/60 transition"
                >
                    Recevoir un code
                </button>
            </form>

            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-700/70"></div>
                </div>
                <div class="relative flex justify-center text-xs">
                    <span class="px-2 bg-slate-900/80 text-slate-400">Ou si tu as déjà le code</span>
                </div>
            </div>

            {{-- Étape 2 : saisir le code --}}
            <form method="POST" action="{{ route('admin.login.verifyCode') }}" class="space-y-4">
                @csrf

                <input type="hidden" name="email" value="{{ $emailValue }}">

                <div>
                    <label for="code" class="block text-xs font-semibold text-slate-300 uppercase mb-1 tracking-wide">
                        Code à 4 chiffres
                    </label>
                    <input
                        id="code"
                        name="code"
                        type="text"
                        inputmode="numeric"
                        pattern="\d{4}"
                        maxlength="4"
                        class="w-full rounded-lg border border-slate-600 bg-slate-900/80 px-3 py-2 text-sm text-slate-50 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                        placeholder="1234"
                    >
                    <p class="mt-1 text-[11px] text-slate-400">
                        Valable quelques minutes seulement. Nombre de tentatives limité.
                    </p>
                </div>

                <button
                    type="submit"
                    class="w-full inline-flex items-center justify-center rounded-lg bg-emerald-600 hover:bg-emerald-500 text-sm font-semibold py-2.5 shadow-sm shadow-emerald-900/60 transition"
                >
                    Se connecter à l’admin
                </button>
            </form>
        </div>

        <p class="mt-4 text-[11px] text-slate-500 text-center">
            Si tu n’as aucun rôle admin, tu pourras te connecter à la plateforme mais pas accéder au panel
            <span class="font-mono">/admin</span>. Dans ce cas, vois avec la tête de liste / super admin.
        </p>
    </div>
</div>
</body>
</html>
