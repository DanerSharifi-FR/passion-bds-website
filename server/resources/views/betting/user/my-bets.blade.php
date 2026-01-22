@extends('app')

@section('title', 'Mes paris')

@section('content')
    <section class="max-w-6xl mx-auto px-4 py-10">
        <div class="flex items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Mes paris</h1>
                <p class="text-gray-600 mt-1">Gère tes paris dans la fenêtre de 2 minutes.</p>
            </div>
            <a href="{{ route('betting.matches.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
                ← Retour aux matchs
            </a>
        </div>

        @if($bets->count() === 0)
            <div class="bg-white border border-gray-200 rounded-xl p-6 text-gray-600">
                Aucun pari pour le moment.
            </div>
        @else
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left px-4 py-3">Date</th>
                        <th class="text-left px-4 py-3">Match</th>
                        <th class="text-left px-4 py-3">Option</th>
                        <th class="text-left px-4 py-3">Mise</th>
                        <th class="text-left px-4 py-3">Cote</th>
                        <th class="text-left px-4 py-3">Statut</th>
                        <th class="text-left px-4 py-3">Modifiable jusqu’à</th>
                        <th class="text-left px-4 py-3">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    @foreach($bets as $bet)
                        @php
                            $canEdit = $bet->status === \App\Enums\BetBetStatus::ACTIVE && $now->lte($bet->editable_until);
                            $match = $bet->match;
                        @endphp
                        <tr>
                            <td class="px-4 py-3 text-gray-500">{{ $bet->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-900">{{ $match?->title ?? 'Match supprimé' }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $bet->option?->label ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ number_format($bet->stake) }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ number_format((float) $bet->odds_locked, 2) }}</td>
                            <td class="px-4 py-3 text-gray-700">
                                {{ $bet->status->value }}
                                @if($bet->result)
                                    <div class="text-xs mt-1">
                                        @switch($bet->result->value)
                                            @case('WON')
                                                <span class="text-emerald-600">Gagné</span>
                                                @break
                                            @case('LOST')
                                                <span class="text-rose-600">Perdu</span>
                                                @break
                                            @case('REFUNDED')
                                                <span class="text-slate-500">Remboursé</span>
                                                @break
                                        @endswitch
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-500">
                                <span class="bet-edit-countdown"
                                      data-editable-until="{{ $bet->editable_until?->toIso8601String() }}"></span>
                            </td>
                            <td class="px-4 py-3">
                                @if($canEdit && $match)
                                    <div class="flex flex-col gap-2">
                                        <form method="POST" action="{{ route('betting.bets.update', $bet) }}" class="grid gap-2">
                                            @csrf
                                            @method('PUT')
                                            <select name="option_id" class="w-full rounded-lg border border-gray-300 px-2 py-1">
                                                @foreach($match->options as $option)
                                                    <option value="{{ $option->id }}" @selected($option->id === $bet->option_id)>{{ $option->label }}</option>
                                                @endforeach
                                            </select>
                                            <input
                                                type="number"
                                                name="stake"
                                                min="1"
                                                step="1"
                                                value="{{ $bet->stake }}"
                                                class="w-full rounded-lg border border-gray-300 px-2 py-1"
                                            >
                                            <button type="submit" class="px-3 py-1 rounded-full bg-slate-900 text-white text-xs font-semibold hover:bg-slate-700 transition" data-action="edit">
                                                Modifier
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('betting.bets.destroy', $bet) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="px-3 py-1 rounded-full bg-red-600 text-white text-xs font-semibold hover:bg-red-500 transition"
                                                    data-action="delete"
                                                    data-confirm="delete-bet"
                                                    data-confirm-title="Supprimer le pari ?"
                                                    data-confirm-text="Tu vas récupérer tes crédits. Cette action est irréversible.">
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $bets->links() }}
            </div>
        @endif
    </section>
@endsection
