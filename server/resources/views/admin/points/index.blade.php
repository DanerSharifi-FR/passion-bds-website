{{-- resources/views/admin/points/index.blade.php --}}
@extends('admin.layout')

@section('title', "Admin – Points / Transactions")

@section('header-tag', 'Points / Transactions')

@section('content')
    @php
        /**
         * @var \Illuminate\Pagination\LengthAwarePaginator<\App\Models\PointTransaction> $transactions
         * @var \Illuminate\Support\Collection<int,\App\Models\User> $users
         */
    @endphp

    @if ($errors->any())
        <div class="mb-4 bg-red-500/10 border border-red-500/60 text-red-100 px-4 py-3 rounded-lg text-sm">
            <p class="font-semibold mb-1">Erreur dans le formulaire :</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('status') === 'points_manual_created')
        <div class="mb-4 bg-emerald-500/10 border border-emerald-500/60 text-emerald-100 px-4 py-3 rounded-lg text-sm">
            Transaction créée avec succès.
        </div>
    @endif

    <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold mb-1">
                Points & transactions
            </h1>
            <p class="text-sm text-slate-600">
                Suivi des mouvements de points et attribution manuelle (IRL, jeux, ambiance, etc.).
            </p>
        </div>
    </div>

    {{-- Formulaire d'ajout / retrait manuel --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="md:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <div class="text-xs font-semibold text-slate-400 uppercase mb-2">
                Nouvelle transaction manuelle
            </div>

            <form method="POST" action="{{ route('admin.points.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="user_id" class="block text-xs font-semibold text-slate-500 uppercase mb-1">
                        Étudiant
                    </label>
                    <select
                        id="user_id"
                        name="user_id"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                        required
                    >
                        <option value="">— Sélectionner un étudiant —</option>
                        @foreach($users as $user)
                            <option
                                value="{{ $user->id }}"
                                @selected((int) old('user_id') === $user->id)
                            >
                                {{ $user->display_name ?? $user->university_email }}
                                ({{ $user->university_email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label for="amount" class="block text-xs font-semibold text-slate-500 uppercase mb-1">
                            Montant (points)
                        </label>
                        <input
                            id="amount"
                            name="amount"
                            type="number"
                            step="1"
                            value="{{ old('amount', 10) }}"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                            required
                        >
                        <p class="mt-1 text-[11px] text-slate-500">
                            Positif = ajout de points, négatif = retrait.
                        </p>
                    </div>

                    <div>
                        <label for="reason" class="block text-xs font-semibold text-slate-500 uppercase mb-1">
                            Raison
                        </label>
                        <input
                            id="reason"
                            name="reason"
                            type="text"
                            value="{{ old('reason') }}"
                            maxlength="255"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                            placeholder="Mini-jeu, ambiance, participation, etc."
                            required
                        >
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-1">
                    <button
                        type="submit"
                        class="px-3 py-1.5 rounded-lg bg-slate-900 text-sm text-white hover:bg-slate-800"
                    >
                        Enregistrer la transaction
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 text-sm text-slate-600">
            <div class="text-xs font-semibold text-slate-400 uppercase mb-2">
                Rappel
            </div>
            <ul class="list-disc list-inside space-y-1 text-xs">
                <li>Les transactions manuelles sont visibles dans l’historique pour audit.</li>
                <li>Utilise un libellé de raison clair (jeu, événement, pénalité…).</li>
                <li>Les gros mouvements de points sont à éviter pour garder le jeu équilibré.</li>
            </ul>
        </div>
    </div>

    {{-- Liste des transactions --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
            <div class="text-sm font-semibold text-slate-700">
                Derniers mouvements de points
            </div>
            <div class="text-xs text-slate-500">
                Triés du plus récent au plus ancien.
            </div>
        </div>

        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase text-slate-500">
            <tr>
                <th class="px-4 py-2 text-left">Date</th>
                <th class="px-4 py-2 text-left">Étudiant</th>
                <th class="px-4 py-2 text-left">Montant</th>
                <th class="px-4 py-2 text-left">Raison</th>
                <th class="px-4 py-2 text-left">Contexte</th>
                <th class="px-4 py-2 text-left">Attribué par</th>
            </tr>
            </thead>
            <tbody>
            @forelse($transactions as $tx)
                @php
                    /** @var \App\Models\PointTransaction $tx */
                    $user = $tx->user;
                    $createdBy = $tx->createdBy;
                    $amount = (int) $tx->amount;
                    $isPositive = $amount > 0;
                @endphp
                <tr class="border-b border-slate-100 hover:bg-slate-50/60">
                    <td class="px-4 py-2 align-top text-xs text-slate-600 whitespace-nowrap">
                        {{ $tx->created_at?->format('d/m/Y H:i') ?? '—' }}
                    </td>
                    <td class="px-4 py-2 align-top">
                        @if($user)
                            <div class="text-xs font-medium">
                                {{ $user->display_name ?? $user->university_email }}
                            </div>
                            <div class="text-[11px] text-slate-500 font-mono">
                                {{ $user->university_email }}
                            </div>
                        @else
                            <span class="text-[11px] text-slate-400 italic">
                                    Utilisateur inconnu (#{{ $tx->user_id }})
                                </span>
                        @endif
                    </td>
                    <td class="px-4 py-2 align-top">
                            <span class="inline-flex items-center text-xs px-2 py-0.5 rounded-full border
                                {{ $isPositive ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                               : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                                {{ $isPositive ? '+' : '' }}{{ $amount }} pts
                            </span>
                    </td>
                    <td class="px-4 py-2 align-top text-xs text-slate-700">
                        {{ $tx->reason }}
                    </td>
                    <td class="px-4 py-2 align-top text-xs text-slate-500">
                        @if($tx->context_type)
                            <div class="font-mono text-[11px]">
                                {{ $tx->context_type }}
                                @if($tx->context_id)
                                    <span class="text-slate-400">#{{ $tx->context_id }}</span>
                                @endif
                            </div>
                        @else
                            <span class="text-[11px] text-slate-400 italic">
                                    Manuel / sans contexte
                                </span>
                        @endif
                    </td>
                    <td class="px-4 py-2 align-top text-xs text-slate-600">
                        @if($createdBy)
                            <div>{{ $createdBy->display_name ?? $createdBy->university_email }}</div>
                            <div class="text-[11px] text-slate-500 font-mono">
                                {{ $createdBy->university_email }}
                            </div>
                        @else
                            <span class="text-[11px] text-slate-400 italic">
                                    Système / inconnu
                                </span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-4 text-center text-sm text-slate-500">
                        Aucune transaction de points pour l’instant.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>

        @if($transactions->hasPages())
            <div class="px-4 py-3 border-t border-slate-200">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
@endsection
