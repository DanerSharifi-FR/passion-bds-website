<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin – Éditer un allo</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
<header class="bg-slate-900 text-white">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.allos.index') }}" class="text-sm text-slate-300 hover:text-white">
                ← Retour aux allos
            </a>
            <div class="flex items-center gap-2">
                <span class="font-black tracking-tight text-lg">P'AS'SION BDS – Admin</span>
                <span class="ml-2 text-xs px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-200 border border-amber-400/40">
                    Édition d’un allo
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
            Éditer l’allo : {{ $allo->title }}
        </h1>
        <p class="text-sm text-slate-600">
            Modifie les paramètres de cet allo : titre, coût, fenêtre, statut, admins responsables…
        </p>
        <p class="mt-1 text-xs text-slate-500">
            Créé le {{ optional($allo->created_at)->format('d/m/Y H:i') ?? 'N/A' }} —
            Dernière maj : {{ optional($allo->updated_at)->format('d/m/Y H:i') ?? 'N/A' }}
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

    <form method="POST" action="{{ route('admin.allos.update', $allo) }}" class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 space-y-5">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="title" class="block text-xs font-semibold text-slate-500 uppercase mb-1">
                    Titre de l’allo <span class="text-red-500">*</span>
                </label>
                <input
                    id="title"
                    name="title"
                    type="text"
                    required
                    maxlength="255"
                    value="{{ old('title', $allo->title) }}"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900"
                >
                <p class="mt-1 text-xs text-slate-500">
                    Exemple : “Livraison de café en amphi”, “Serenade devant le RU”, etc.
                </p>
            </div>

            <div>
                <label for="slug" class="block text-xs font-semibold text-slate-500 uppercase mb-1">
                    Slug (optionnel)
                </label>
                <input
                    id="slug"
                    name="slug"
                    type="text"
                    maxlength="255"
                    value="{{ old('slug', $allo->slug) }}"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900"
                >
                <p class="mt-1 text-xs text-slate-500">
                    Laisse vide pour régénérer automatiquement à partir du titre.
                </p>
            </div>
        </div>

        <div>
            <label for="description" class="block text-xs font-semibold text-slate-500 uppercase mb-1">
                Description
            </label>
            <textarea
                id="description"
                name="description"
                rows="4"
                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900"
            >{{ old('description', $allo->description) }}</textarea>
            <p class="mt-1 text-xs text-slate-500">
                Explique clairement ce que l’allo implique, les limites, les conditions, etc.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="points_cost" class="block text-xs font-semibold text-slate-500 uppercase mb-1">
                    Coût en points <span class="text-red-500">*</span>
                </label>
                <input
                    id="points_cost"
                    name="points_cost"
                    type="number"
                    min="0"
                    step="1"
                    required
                    value="{{ old('points_cost', $allo->points_cost) }}"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900"
                >
            </div>

            <div>
                <label for="slot_duration_minutes" class="block text-xs font-semibold text-slate-500 uppercase mb-1">
                    Durée d’un slot (min) <span class="text-red-500">*</span>
                </label>
                <input
                    id="slot_duration_minutes"
                    name="slot_duration_minutes"
                    type="number"
                    min="5"
                    max="1440"
                    step="5"
                    required
                    value="{{ old('slot_duration_minutes', $allo->slot_duration_minutes) }}"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900"
                >
            </div>

            <div>
                <label for="status" class="block text-xs font-semibold text-slate-500 uppercase mb-1">
                    Statut <span class="text-red-500">*</span>
                </label>
                <select
                    id="status"
                    name="status"
                    required
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900"
                >
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}"
                                @if(old('status', $allo->status) === $value) selected @endif>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @php
                $startValue = $allo->window_start_at
                    ? $allo->window_start_at->format('Y-m-d\TH:i')
                    : null;
                $endValue = $allo->window_end_at
                    ? $allo->window_end_at->format('Y-m-d\TH:i')
                    : null;
            @endphp

            <div>
                <label for="window_start_at" class="block text-xs font-semibold text-slate-500 uppercase mb-1">
                    Début de la fenêtre <span class="text-red-500">*</span>
                </label>
                <input
                    id="window_start_at"
                    name="window_start_at"
                    type="datetime-local"
                    required
                    value="{{ old('window_start_at', $startValue) }}"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900"
                >
            </div>

            <div>
                <label for="window_end_at" class="block text-xs font-semibold text-slate-500 uppercase mb-1">
                    Fin de la fenêtre <span class="text-red-500">*</span>
                </label>
                <input
                    id="window_end_at"
                    name="window_end_at"
                    type="datetime-local"
                    required
                    value="{{ old('window_end_at', $endValue) }}"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900"
                >
            </div>
        </div>

        <div>
            <label for="admin_ids" class="block text-xs font-semibold text-slate-500 uppercase mb-1">
                Admins responsables (optionnel)
            </label>
            @php
                /** @var \Illuminate\Support\Collection<int, \App\Models\User> $adminCandidates */
                /** @var array<int, int> $currentAdminIds */
                $selectedAdminIds = collect(
                    old('admin_ids', $currentAdminIds)
                )->map(fn($v) => (int) $v)->all();
            @endphp

            <select
                id="admin_ids"
                name="admin_ids[]"
                multiple
                size="5"
                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900"
            >
                @foreach($adminCandidates as $candidate)
                    <option value="{{ $candidate->id }}"
                            @if(in_array($candidate->id, $selectedAdminIds, true)) selected @endif>
                        {{ $candidate->display_name ?? $candidate->university_email }}
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-slate-500">
                Les admins sélectionnés reçoivent les demandes pour cet allo.
            </p>
        </div>

        <div class="flex items-center justify-between gap-2 pt-3 border-t border-slate-100">
            <div class="text-xs text-slate-500">
                ID : {{ $allo->id }} – Slug actuel : <code class="bg-slate-100 px-1 rounded">{{ $allo->slug ?? 'N/A' }}</code>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.allos.index') }}"
                   class="inline-flex items-center px-3 py-2 rounded-lg border border-slate-300 text-slate-700 text-sm">
                    Annuler
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 rounded-lg bg-amber-600 text-white text-sm font-semibold hover:bg-amber-700"
                >
                    Enregistrer les modifications
                </button>
            </div>
        </div>
    </form>
</main>
</body>
</html>
