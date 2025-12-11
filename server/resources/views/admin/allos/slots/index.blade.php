<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin – Slots de l’allo</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
<header class="bg-slate-900 text-white">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.allos.index') }}" class="text-sm text-slate-300 hover:text-white">
                ← Tous les allos
            </a>
            <a href="{{ route('admin.allos.edit', $allo) }}" class="text-sm text-slate-300 hover:text-white">
                ← Fiche de l’allo
            </a>
            <div class="flex items-center gap-2">
                <span class="font-black tracking-tight text-lg">P'AS'SION BDS – Admin</span>
                <span class="ml-2 text-xs px-2 py-0.5 rounded-full bg-sky-500/20 text-sky-200 border border-sky-400/40">
                    Slots de l’allo
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
    @if(session('status') === 'slots_generated')
        @php
            $count = (int) (session('slots_generated_count') ?? 0);
        @endphp
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg text-sm">
            @if($count > 0)
                {{ $count }} slot(s) ont été générés avec succès.
            @else
                Aucun nouveau slot généré (tout existe déjà ou la fenêtre est invalide).
            @endif
        </div>
    @endif

    <section class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold mb-1">
                    Slots de l’allo : {{ $allo->title }}
                </h1>
                <p class="text-sm text-slate-600">
                    Fenêtre : {{ optional($allo->window_start_at)->format('d/m/Y H:i') }}
                    → {{ optional($allo->window_end_at)->format('d/m/Y H:i') }}
                </p>
                <p class="text-xs text-slate-500 mt-1">
                    Coût : {{ $allo->points_cost }} pts —
                    durée d’un slot : {{ $allo->slot_duration_minutes }} min —
                    statut : <span class="font-mono">{{ $allo->status }}</span>
                </p>
            </div>

            <div class="flex flex-col items-stretch md:items-end gap-2 text-xs text-slate-500">
                <div class="space-y-1 md:text-right">
                    <div>ID allo : {{ $allo->id }}</div>
                    @if($allo->slug)
                        <div>Slug : <code class="bg-slate-100 px-1 rounded">{{ $allo->slug }}</code></div>
                    @endif
                    <div>
                        Créé le : {{ optional($allo->created_at)->format('d/m/Y H:i') ?? 'N/A' }}
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.allos.slots.generate', $allo) }}">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center px-3 py-2 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        @if(!$allo->window_start_at || !$allo->window_end_at || $allo->slot_duration_minutes <= 0) disabled @endif
                    >
                        Générer les slots
                    </button>
                    <p class="mt-1 text-[11px] text-slate-500 max-w-xs">
                        Génère les slots en partant de la fenêtre de l’allo et de la durée.
                        Aucun doublon n’est créé si des slots existent déjà sur les mêmes horaires.
                    </p>
                </form>
            </div>
        </div>
    </section>

    <section class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 space-y-4">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-sm font-semibold text-slate-700">
                Liste des slots
            </h2>
            <p class="text-xs text-slate-500">
                Pour l’instant : affichage uniquement. On ajoutera plus tard la gestion fine (lock, cancel, etc.).
            </p>
        </div>

        @php
            /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\AlloSlot> $slots */
            $slots = $allo->slots;
        @endphp

        @if($slots->isEmpty())
            <div class="text-sm text-slate-500">
                Aucun slot n’a encore été généré pour cet allo.
                Utilise le bouton <span class="font-semibold">“Générer les slots”</span> ci-dessus.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead>
                    <tr class="border-b border-slate-200 text-xs text-slate-500 uppercase">
                        <th class="py-2 pr-4">Début</th>
                        <th class="py-2 pr-4">Fin</th>
                        <th class="py-2 pr-4">Statut</th>
                        <th class="py-2 pr-4 text-right">Usages</th>
                        <th class="py-2 pr-4">Créé le</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    @foreach($slots as $slot)
                        @php
                            /** @var \App\Models\AlloSlot $slot */
                            $status = $slot->status;
                            $statusLabel = match ($status) {
                                'available' => 'Disponible',
                                'booked' => 'Réservé',
                                'locked' => 'Verrouillé',
                                'cancelled' => 'Annulé',
                                default => $status,
                            };
                            $statusClasses = match ($status) {
                                'available' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'booked' => 'bg-sky-50 text-sky-700 border-sky-200',
                                'locked' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'cancelled' => 'bg-rose-50 text-rose-700 border-rose-200',
                                default => 'bg-slate-50 text-slate-700 border-slate-200',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="py-2 pr-4 align-top text-xs text-slate-700">
                                {{ optional($slot->slot_start_at)->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-2 pr-4 align-top text-xs text-slate-700">
                                {{ optional($slot->slot_end_at)->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-2 pr-4 align-top">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border {{ $statusClasses }}">
                                        {{ $statusLabel }}
                                    </span>
                            </td>
                            <td class="py-2 pr-4 align-top text-right text-sm">
                                {{ $slot->usages_count ?? 0 }}
                            </td>
                            <td class="py-2 pr-4 align-top text-xs text-slate-600">
                                {{ optional($slot->created_at)->format('d/m/Y H:i') ?? 'N/A' }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</main>
</body>
</html>
