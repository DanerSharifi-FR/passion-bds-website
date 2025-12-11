<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin – Usages de l’allo</title>

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
            <a href="{{ route('admin.allos.slots.index', $allo) }}" class="text-sm text-slate-300 hover:text-white">
                ← Slots
            </a>
            <div class="flex items-center gap-2">
                <span class="font-black tracking-tight text-lg">P'AS'SION BDS – Admin</span>
                <span class="ml-2 text-xs px-2 py-0.5 rounded-full bg-purple-500/20 text-purple-200 border border-purple-400/40">
                    Usages de l’allo
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
    @php
        use App\Services\AlloUsageService;
    @endphp

    @if(session('status') === 'allo_usage_accepted')
        @php $changed = (bool) (session('allo_usage_changed') ?? false); @endphp
        <div class="bg-sky-50 border border-sky-200 text-sky-800 px-4 py-3 rounded-lg text-sm">
            @if($changed)
                La demande a été marquée comme <strong>acceptée</strong>.
            @else
                Aucun changement : la demande n’était pas en état "en attente".
            @endif
        </div>
    @endif

    @if(session('status') === 'allo_usage_done')
        @php $changed = (bool) (session('allo_usage_changed') ?? false); @endphp
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg text-sm">
            @if($changed)
                La demande a été marquée comme <strong>terminée</strong>.
            @else
                Aucun changement : seule une demande "acceptée" peut être terminée.
            @endif
        </div>
    @endif

    @if(session('status') === 'allo_usage_cancelled')
        @php $changed = (bool) (session('allo_usage_changed') ?? false); @endphp
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-lg text-sm">
            @if($changed)
                La demande a été <strong>annulée</strong>.
            @else
                Aucun changement : la demande était déjà terminée ou annulée.
            @endif
        </div>
    @endif

    <section class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold mb-1">
                    Usages de l’allo : {{ $allo->title }}
                </h1>
                <p class="text-sm text-slate-600">
                    Fenêtre : {{ optional($allo->window_start_at)->format('d/m/Y H:i') }}
                    → {{ optional($allo->window_end_at)->format('d/m/Y H:i') }}
                </p>
                <p class="text-xs text-slate-500 mt-1">
                    Coût : {{ $allo->points_cost }} pts —
                    durée d’un slot : {{ $allo->slot_duration_minutes }} min —
                    statut allo : <span class="font-mono">{{ $allo->status }}</span>
                </p>
            </div>

            <div class="text-xs text-slate-500 space-y-1 md:text-right">
                <div>ID allo : {{ $allo->id }}</div>
                @if($allo->slug)
                    <div>Slug : <code class="bg-slate-100 px-1 rounded">{{ $allo->slug }}</code></div>
                @endif
                <div>
                    Créé le : {{ optional($allo->created_at)->format('d/m/Y H:i') ?? 'N/A' }}
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 space-y-4">
        <form method="GET" action="{{ route('admin.allos.usages.index', $allo) }}" class="flex flex-wrap items-end gap-3 text-sm">
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
                    <a href="{{ route('admin.allos.usages.index', $allo) }}"
                       class="inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 text-xs font-semibold border border-slate-200">
                        Réinitialiser
                    </a>
                </div>
            @endif
        </form>

        @if($usages->isEmpty())
            <div class="text-sm text-slate-500">
                Aucun usage (réservation) pour cet allo pour l’instant.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead>
                    <tr class="border-b border-slate-200 text-xs text-slate-500 uppercase">
                        <th class="py-2 pr-4">Étudiant</th>
                        <th class="py-2 pr-4">Slot</th>
                        <th class="py-2 pr-4">Statut</th>
                        <th class="py-2 pr-4 text-right">Points</th>
                        <th class="py-2 pr-4">Timeline</th>
                        <th class="py-2 pr-4">Géré par</th>
                        <th class="py-2 pr-4 text-right">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    @foreach($usages as $usage)
                        @php
                            /** @var \App\Models\AlloUsage $usage */
                            $status = $usage->status;
                            $statusLabel = match ($status) {
                                AlloUsageService::STATUS_PENDING => 'En attente',
                                AlloUsageService::STATUS_ACCEPTED => 'Accepté',
                                AlloUsageService::STATUS_DONE => 'Terminé',
                                AlloUsageService::STATUS_CANCELLED => 'Annulé',
                                default => $status,
                            };
                            $statusClasses = match ($status) {
                                AlloUsageService::STATUS_PENDING => 'bg-slate-50 text-slate-700 border-slate-200',
                                AlloUsageService::STATUS_ACCEPTED => 'bg-sky-50 text-sky-700 border-sky-200',
                                AlloUsageService::STATUS_DONE => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                AlloUsageService::STATUS_CANCELLED => 'bg-rose-50 text-rose-700 border-rose-200',
                                default => 'bg-slate-50 text-slate-700 border-slate-200',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50 align-top">
                            <td class="py-2 pr-4 text-xs text-slate-700">
                                @if($usage->user)
                                    <div class="font-semibold">
                                        {{ $usage->user->display_name ?? $usage->user->university_email }}
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        {{ $usage->user->university_email }}
                                    </div>
                                @else
                                    <span class="text-slate-400">Utilisateur supprimé</span>
                                @endif
                            </td>

                            <td class="py-2 pr-4 text-xs text-slate-700">
                                @if($usage->slot)
                                    <div>
                                        {{ optional($usage->slot->slot_start_at)->format('d/m H:i') }}
                                    </div>
                                    <div class="text-[11px] text-slate-500">
                                        → {{ optional($usage->slot->slot_end_at)->format('H:i') }}
                                    </div>
                                @else
                                    <span class="text-slate-400">Slot supprimé</span>
                                @endif
                            </td>

                            <td class="py-2 pr-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border {{ $statusClasses }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>

                            <td class="py-2 pr-4 text-right">
                                <span class="font-mono text-sm">
                                    {{ $usage->points_spent }}
                                </span>
                                <span class="text-xs text-slate-500">pts</span>
                            </td>

                            <td class="py-2 pr-4 text-xs text-slate-600">
                                <div>
                                    Créé : {{ optional($usage->created_at)->format('d/m H:i') ?? 'N/A' }}
                                </div>
                                @if($usage->accepted_at)
                                    <div>
                                        Accepté : {{ $usage->accepted_at->format('d/m H:i') }}
                                    </div>
                                @endif
                                @if($usage->done_at)
                                    <div>
                                        Terminé : {{ $usage->done_at->format('d/m H:i') }}
                                    </div>
                                @endif
                                @if($usage->cancelled_at)
                                    <div>
                                        Annulé : {{ $usage->cancelled_at->format('d/m H:i') }}
                                    </div>
                                @endif
                            </td>

                            <td class="py-2 pr-4 text-xs text-slate-600">
                                @if($usage->handledBy)
                                    <div>
                                        Pris en charge par :
                                    </div>
                                    <div class="font-semibold">
                                        {{ $usage->handledBy->display_name ?? $usage->handledBy->university_email }}
                                    </div>
                                @else
                                    <span class="text-slate-400">Non affecté</span>
                                @endif

                                @if($usage->doneBy && $usage->doneBy->id !== ($usage->handledBy->id ?? null))
                                    <div class="mt-1">
                                        Validé par :
                                    </div>
                                    <div class="font-semibold">
                                        {{ $usage->doneBy->display_name ?? $usage->doneBy->university_email }}
                                    </div>
                                @endif
                            </td>

                            <td class="py-2 pr-0 text-right text-xs">
                                <div class="flex flex-col items-end gap-1">
                                    @if($usage->status === AlloUsageService::STATUS_PENDING)
                                        <form method="POST" action="{{ route('admin.allos.usages.accept', [$allo, $usage]) }}">
                                            @csrf
                                            <button
                                                type="submit"
                                                class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-sky-600 text-white text-xs font-semibold hover:bg-sky-700"
                                            >
                                                Accepter
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.allos.usages.cancel', [$allo, $usage]) }}">
                                            @csrf
                                            <button
                                                type="submit"
                                                class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700"
                                            >
                                                Annuler
                                            </button>
                                        </form>
                                    @elseif($usage->status === AlloUsageService::STATUS_ACCEPTED)
                                        <form method="POST" action="{{ route('admin.allos.usages.done', [$allo, $usage]) }}">
                                            @csrf
                                            <button
                                                type="submit"
                                                class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700"
                                            >
                                                Terminer
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.allos.usages.cancel', [$allo, $usage]) }}">
                                            @csrf
                                            <button
                                                type="submit"
                                                class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700"
                                            >
                                                Annuler
                                            </button>
                                        </form>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-lg bg-slate-100 text-slate-500 border border-slate-200">
                                            Aucune action
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $usages->links() }}
            </div>
        @endif
    </section>
</main>
</body>
</html>
