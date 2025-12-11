<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin – Allos</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
<header class="bg-slate-900 text-white">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.dashboard') }}" class="text-sm text-slate-300 hover:text-white">
                ← Dashboard
            </a>
            <div class="flex items-center gap-2">
                <span class="font-black tracking-tight text-lg">P'AS'SION BDS – Admin</span>
                <span class="ml-2 text-xs px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/40">
                    Allos
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

<main class="max-w-6xl mx-auto px-4 py-6 space-y-4">
    @if(session('status') === 'allo_created')
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg text-sm">
            Nouvel allo créé avec succès.
        </div>
    @endif

    @if(session('status') === 'allo_updated')
        <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg text-sm">
            Allo mis à jour avec succès.
        </div>
    @endif

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold mb-1">Gestion des allos</h1>
            <p class="text-sm text-slate-600">
                Crée, ouvre, ferme et surveille les allos disponibles pour les étudiants.
            </p>
        </div>

        <a href="{{ route('admin.allos.create') }}"
           class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-medium shadow-sm hover:bg-emerald-700">
            + Nouvel allo
        </a>
    </div>

    <section class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 space-y-4">
        <form method="GET" action="{{ route('admin.allos.index') }}" class="flex flex-wrap items-end gap-3 text-sm">
            <div>
                <label for="status" class="block text-xs font-semibold text-slate-500 uppercase mb-1">
                    Filtrer par statut
                </label>
                <select
                    id="status"
                    name="status"
                    class="border border-slate-300 rounded-lg px-2 py-1.5 text-sm bg-white"
                >
                    <option value="">Tous</option>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}"
                                @if($currentStatusFilter === $value) selected @endif>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <button type="submit"
                        class="inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-900 text-white text-xs font-semibold">
                    Appliquer
                </button>
            </div>

            @if($currentStatusFilter)
                <div>
                    <a href="{{ route('admin.allos.index') }}"
                       class="inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 text-xs font-semibold border border-slate-200">
                        Réinitialiser
                    </a>
                </div>
            @endif
        </form>

        @if($allos->isEmpty())
            <div class="text-sm text-slate-500">
                Aucun allo trouvé. Tu peux en créer un avec le bouton
                <span class="font-semibold">"Nouvel allo"</span> en haut à droite.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead>
                    <tr class="border-b border-slate-200 text-xs text-slate-500 uppercase">
                        <th class="py-2 pr-4">Titre</th>
                        <th class="py-2 pr-4">Statut</th>
                        <th class="py-2 pr-4 text-right">Coût</th>
                        <th class="py-2 pr-4">Fenêtre</th>
                        <th class="py-2 pr-4 text-right">Durée slot</th>
                        <th class="py-2 pr-4 text-right">Slots</th>
                        <th class="py-2 pr-4 text-right">Usages</th>
                        <th class="py-2 pr-4">Créé par</th>
                        <th class="py-2 pr-4 text-right">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    @foreach($allos as $allo)
                        @php
                            /** @var \App\Models\Allo $allo */
                            $status = $allo->status;
                            $statusLabel = $statusOptions[$status] ?? $status;
                            $statusClasses = match ($status) {
                                'open' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'draft' => 'bg-slate-50 text-slate-700 border-slate-200',
                                'closed' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'archived' => 'bg-slate-100 text-slate-500 border-slate-200',
                                default => 'bg-slate-50 text-slate-700 border-slate-200',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="py-2 pr-4 align-top">
                                <div class="font-semibold text-slate-900">
                                    <a href="{{ route('admin.allos.edit', $allo) }}"
                                       class="hover:underline">
                                        {{ $allo->title }}
                                    </a>
                                </div>
                                @if($allo->slug)
                                    <div class="text-xs text-slate-400">
                                        slug: {{ $allo->slug }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-2 pr-4 align-top">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border {{ $statusClasses }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="py-2 pr-4 align-top text-right">
                                <span class="font-mono text-sm">
                                    {{ $allo->points_cost }}
                                </span>
                                <span class="text-xs text-slate-500">pts</span>
                            </td>
                            <td class="py-2 pr-4 align-top text-xs text-slate-600">
                                <div>
                                    {{ optional($allo->window_start_at)->format('d/m/Y H:i') }}
                                </div>
                                <div>
                                    → {{ optional($allo->window_end_at)->format('d/m/Y H:i') }}
                                </div>
                            </td>
                            <td class="py-2 pr-4 align-top text-right text-sm">
                                {{ $allo->slot_duration_minutes }} min
                            </td>
                            <td class="py-2 pr-4 align-top text-right text-sm">
                                {{ $allo->slots_count }}
                            </td>
                            <td class="py-2 pr-4 align-top text-right text-sm">
                                {{ $allo->usages_count }}
                            </td>
                            <td class="py-2 pr-4 align-top text-xs text-slate-600">
                                @if($allo->creator)
                                    {{ $allo->creator->display_name ?? $allo->creator->university_email }}
                                @else
                                    <span class="text-slate-400">N/A</span>
                                @endif
                            </td>
                            <td class="py-2 pr-4 align-top text-right text-xs">
                                <div class="flex flex-col items-end gap-1">
                                    <a href="{{ route('admin.allos.edit', $allo) }}"
                                       class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100">
                                        Éditer
                                    </a>

                                    <a href="{{ route('admin.allos.slots.index', $allo) }}"
                                       class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-sky-600 text-white hover:bg-sky-700">
                                        Slots
                                    </a>

                                    <a href="{{ route('admin.allos.usages.index', $allo) }}"
                                       class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-purple-600 text-white hover:bg-purple-700">
                                        Usages
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $allos->links() }}
            </div>
        @endif
    </section>
</main>
</body>
</html>
