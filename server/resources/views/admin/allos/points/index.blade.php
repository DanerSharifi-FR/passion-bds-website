<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin – Points</title>

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
                <span class="ml-2 text-xs px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-200 border border-amber-400/40">
                    Points
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
    @if(session('status') === 'point_transaction_created')
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg text-sm">
            Transaction de points créée avec succès.
        </div>
    @endif

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold mb-1">Gestion des points</h1>
            <p class="text-sm text-slate-600">
                Vue Gamemaster : historique des mouvements de points + solde par étudiant.
            </p>
        </div>

        <a href="{{ route('admin.points.create') }}"
           class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-amber-600 text-white text-sm font-medium shadow-sm hover:bg-amber-700">
            + Nouvelle transaction
        </a>
    </div>

    <section class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 space-y-4">
        <form method="GET" action="{{ route('admin.points.index') }}" class="flex flex-wrap items-end gap-3 text-sm">
            <div>
                <label for="user_id" class="block text-xs font-semibold text-slate-500 uppercase mb-1">
                    Filtrer par étudiant
                </label>
                <select
                    id="user_id"
                    name="user_id"
                    class="border border-slate-300 rounded-lg px-2 py-1.5 text-sm bg-white min-w-[220px]"
                >
                    <option value="">Tous</option>
                    @foreach($userOptions as $optionUser)
                        @php
                            /** @var \App\Models\User $optionUser */
                            $label = $optionUser->display_name
                                ? $optionUser->display_name . ' (' . $optionUser->university_email . ')'
                                : $optionUser->university_email;
                        @endphp
                        <option value="{{ $optionUser->id }}"
                                @if($currentUserFilter === $optionUser->id) selected @endif>
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

            @if($currentUserFilter)
                <div>
                    <a href="{{ route('admin.points.index') }}"
                       class="inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 text-xs font-semibold border border-slate-200">
                        Réinitialiser
                    </a>
                </div>
            @endif
        </form>

        @if($selectedUser)
            <div class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm flex items-center justify-between gap-3">
                <div class="text-slate-700">
                    <span class="font-semibold">
                        {{ $selectedUser->display_name ?? $selectedUser->university_email }}
                    </span>
                    <span class="text-xs text-slate-500 ml-1">
                        ({{ $selectedUser->university_email }})
                    </span>
                </div>
                <div class="text-right">
                    <div class="text-xs text-slate-500">Solde actuel</div>
                    <div class="font-mono text-base">
                        @php
                            $balance = (int) ($selectedUserBalance ?? 0);
                        @endphp
                        @if($balance > 0)
                            <span class="text-emerald-600">+{{ $balance }}</span>
                        @elseif($balance < 0)
                            <span class="text-rose-600">{{ $balance }}</span>
                        @else
                            <span class="text-slate-700">0</span>
                        @endif
                        <span class="text-xs text-slate-500">pts</span>
                    </div>
                </div>
            </div>
        @endif
    </section>

    <section class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 space-y-4">
        @if($transactions->isEmpty())
            <div class="text-sm text-slate-500">
                Aucune transaction de points trouvée pour ce filtre.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead>
                    <tr class="border-b border-slate-200 text-xs text-slate-500 uppercase">
                        <th class="py-2 pr-4">Date</th>
                        <th class="py-2 pr-4">Étudiant</th>
                        <th class="py-2 pr-4 text-right">Montant</th>
                        <th class="py-2 pr-4">Raison</th>
                        <th class="py-2 pr-4">Contexte</th>
                        <th class="py-2 pr-4">Créé par</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    @foreach($transactions as $transaction)
                        @php
                            /** @var \App\Models\PointTransaction $transaction */
                            $amount = $transaction->amount;
                            $amountClasses = $amount > 0
                                ? 'text-emerald-600'
                                : ($amount < 0 ? 'text-rose-600' : 'text-slate-700');
                        @endphp
                        <tr class="hover:bg-slate-50 align-top">
                            <td class="py-2 pr-4 text-xs text-slate-700">
                                {{ optional($transaction->created_at)->format('d/m/Y H:i') ?? 'N/A' }}
                            </td>

                            <td class="py-2 pr-4 text-xs text-slate-700">
                                @if($transaction->user)
                                    <div class="font-semibold">
                                        {{ $transaction->user->display_name ?? $transaction->user->university_email }}
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        {{ $transaction->user->university_email }}
                                    </div>
                                @else
                                    <span class="text-slate-400">Utilisateur supprimé</span>
                                @endif
                            </td>

                            <td class="py-2 pr-4 text-right">
                                <span class="font-mono text-sm {{ $amountClasses }}">
                                    @if($amount > 0)
                                        +{{ $amount }}
                                    @else
                                        {{ $amount }}
                                    @endif
                                </span>
                                <span class="text-xs text-slate-500">pts</span>
                            </td>

                            <td class="py-2 pr-4 text-xs text-slate-700">
                                {{ $transaction->reason }}
                            </td>

                            <td class="py-2 pr-4 text-xs text-slate-600">
                                @if($transaction->context_type)
                                    <div class="font-mono text-[11px]">
                                        {{ $transaction->context_type }}
                                        @if($transaction->context_id)
                                            #{{ $transaction->context_id }}
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400 text-[11px]">manual</span>
                                @endif
                            </td>

                            <td class="py-2 pr-4 text-xs text-slate-600">
                                @if($transaction->creator)
                                    <div>
                                        {{ $transaction->creator->display_name ?? $transaction->creator->university_email }}
                                    </div>
                                @else
                                    <span class="text-slate-400 text-[11px]">système / inconnu</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $transactions->links() }}
            </div>
        @endif
    </section>
</main>
</body>
</html>
