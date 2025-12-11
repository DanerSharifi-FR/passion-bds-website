<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin – Nouvelle transaction de points</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
<header class="bg-slate-900 text-white">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.points.index') }}" class="text-sm text-slate-300 hover:text-white">
                ← Retour aux points
            </a>
            <div class="flex items-center gap-2">
                <span class="font-black tracking-tight text-lg">P'AS'SION BDS – Admin</span>
                <span class="ml-2 text-xs px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-200 border border-amber-400/40">
                    Nouvelle transaction
                </span>
            </div>
        </div>

        @php
            /** @var \App\Models\User|null $user */
            $user = auth()->user();
        @endphp

        @if($user)
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <div class="text-sm font-semibold">
                        {{ $user->display_name ?? $user->university_email }}
                    </div>
                    <div class="text-xs text-slate-300">
                        @php
                            $roleNames = $user->roles->pluck('name')->all();
                        @endphp
                        @if(empty($roleNames))
                            Aucun rôle admin
                        @else
                            {{ implode(' · ', $roleNames) }}
                        @endif
                    </div>
                </div>
                @if($user->avatar_url)
                    <img
                        src="{{ $user->avatar_url }}"
                        alt="Avatar"
                        class="w-8 h-8 rounded-full object-cover border border-slate-700"
                    >
                @else
                    <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-xs font-bold">
                        {{ strtoupper(mb_substr($user->display_name ?? $user->university_email, 0, 2)) }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</header>

<main class="max-w-3xl mx-auto px-4 py-6 space-y-4">
    <div>
        <h1 class="text-xl font-semibold mb-1">
            Nouvelle transaction de points
        </h1>
        <p class="text-sm text-slate-600">
            Interface Gamemaster pour donner / retirer des points à un étudiant avec une raison claire.
        </p>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">
            <p class="font-semibold mb-1">Il y a des erreurs dans le formulaire :</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.points.store') }}"
          class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 space-y-5">
        @csrf

        <div>
            <label for="user_id" class="block text-xs font-semibold text-slate-500 uppercase mb-1">
                Étudiant concerné <span class="text-red-500">*</span>
            </label>
            @php
                /** @var \Illuminate\Support\Collection<int, \App\Models\User> $userOptions */
                /** @var int|null $prefilledUserId */
                $selectedUserId = old('user_id');
                if ($selectedUserId === null && isset($prefilledUserId) && $prefilledUserId !== null) {
                    $selectedUserId = (string) $prefilledUserId;
                }
            @endphp
            <select
                id="user_id"
                name="user_id"
                required
                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900"
            >
                <option value="">— Choisir un étudiant —</option>
                @foreach($userOptions as $optionUser)
                    @php
                        /** @var \App\Models\User $optionUser */
                        $label = $optionUser->display_name
                            ? $optionUser->display_name . ' (' . $optionUser->university_email . ')'
                            : $optionUser->university_email;
                    @endphp
                    <option value="{{ $optionUser->id }}"
                            @if((string) $optionUser->id === (string) $selectedUserId) selected @endif>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="amount" class="block text-xs font-semibold text-slate-500 uppercase mb-1">
                    Montant de points <span class="text-red-500">*</span>
                </label>
                <input
                    id="amount"
                    name="amount"
                    type="number"
                    step="1"
                    required
                    value="{{ old('amount') }}"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900"
                >
                <p class="mt-1 text-xs text-slate-500">
                    Positif = gain de points, négatif = retrait / pénalité. Exemple : +10, -5.
                </p>
            </div>

            <div>
                <label for="reason" class="block text-xs font-semibold text-slate-500 uppercase mb-1">
                    Raison <span class="text-red-500">*</span>
                </label>
                <input
                    id="reason"
                    name="reason"
                    type="text"
                    maxlength="255"
                    required
                    value="{{ old('reason') }}"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900"
                >
                <p class="mt-1 text-xs text-slate-500">
                    Exemple : “Défi du jour – QCM sport”, “Ambiance BDS en soirée”, “Retard important”.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="context_type" class="block text-xs font-semibold text-slate-500 uppercase mb-1">
                    Contexte (type)
                </label>
                <input
                    id="context_type"
                    name="context_type"
                    type="text"
                    maxlength="50"
                    value="{{ old('context_type') }}"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900"
                >
                <p class="mt-1 text-xs text-slate-500">
                    Optionnel. Exemple : <code class="bg-slate-100 px-1 rounded">challenge</code>,
                    <code class="bg-slate-100 px-1 rounded">allo</code>,
                    <code class="bg-slate-100 px-1 rounded">manual</code>.
                </p>
            </div>

            <div>
                <label for="context_id" class="block text-xs font-semibold text-slate-500 uppercase mb-1">
                    Contexte (ID)
                </label>
                <input
                    id="context_id"
                    name="context_id"
                    type="number"
                    step="1"
                    value="{{ old('context_id') }}"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900"
                >
                <p class="mt-1 text-xs text-slate-500">
                    Optionnel. Ex : ID du challenge, de l’allo, etc.
                </p>
            </div>
        </div>

        <div class="flex items-center justify-between gap-2 pt-3 border-t border-slate-100">
            <p class="text-xs text-slate-500">
                Cette opération sera historisée et visible dans l’onglet “Points”.
            </p>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.points.index') }}"
                   class="inline-flex items-center px-3 py-2 rounded-lg border border-slate-300 text-slate-700 text-sm">
                    Annuler
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 rounded-lg bg-amber-600 text-white text-sm font-semibold hover:bg-amber-700"
                >
                    Enregistrer la transaction
                </button>
            </div>
        </div>
    </form>
</main>
</body>
</html>
