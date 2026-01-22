@extends('app')

@section('title', 'Parier sur un match')

@push('styles')
    <style>
        .odds-up {
            color: #16a34a;
            transform: translateY(-2px);
            transition: color 0.3s ease, transform 0.3s ease, opacity 0.3s ease;
            opacity: 0.9;
        }

        .odds-down {
            color: #dc2626;
            transform: translateY(2px);
            transition: color 0.3s ease, transform 0.3s ease, opacity 0.3s ease;
            opacity: 0.9;
        }
    </style>
@endpush

@section('content')
    <section class="max-w-4xl mx-auto px-4 py-10">
        @php
            $isOpen = $match->isBettingWindowOpen($now);
            $canEdit = $activeBet && $activeBet->editable_until && $now->lte($activeBet->editable_until);
        @endphp
        <div class="mb-6">
            <a href="{{ route('betting.matches.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
                ← Retour aux matchs
            </a>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm" data-bet-match-id="{{ $match->id }}">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">{{ $match->title }}</h1>
                    <p class="text-sm text-gray-500 mt-1">
                        Ouverture: <span class="human-dt" data-dt="{{ $match->bet_open_at?->toIso8601String() }}"></span>
                        · Début: <span class="human-dt" data-dt="{{ $match->match_start_at?->toIso8601String() }}"></span>
                        · Fin: <span class="human-dt" data-dt="{{ $match->match_end_at?->toIso8601String() }}"></span>
                    </p>
                    <p class="text-xs text-gray-500 mt-2">
                        Paris ouverts jusqu’à <span class="human-dt" data-dt="{{ $match->match_end_at?->toIso8601String() }}"></span>.
                    </p>
                </div>
                <div class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-right">
                    <div class="text-xs uppercase tracking-wide text-gray-500">Mon solde</div>
                    <div class="text-xl font-semibold text-gray-900">{{ number_format($wallet->balance) }} crédits</div>
                </div>
            </div>

            @if(!$isOpen)
                <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-amber-700 text-sm">
                    Paris fermés pour ce match.
                </div>
            @endif

            <form method="POST" action="{{ route('betting.bets.store', $match) }}" class="mt-6 space-y-5">
                @csrf
                <div class="grid gap-3">
                    @foreach($match->options as $option)
                        <label class="flex items-center justify-between gap-4 border border-gray-200 rounded-xl p-4 cursor-pointer hover:border-gray-300">
                            <div class="flex items-center gap-3">
                                <input type="radio"
                                       name="option_id"
                                       value="{{ $option->id }}"
                                       class="h-4 w-4"
                                       required
                                       @checked((int) old('option_id', $activeBet?->option_id) === (int) $option->id)
                                       @disabled(!$isOpen)>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $option->label }}</div>
                                    <div class="text-xs text-gray-500">
                                        Pool: <span data-option-pool="{{ $option->id }}">{{ number_format($option->pool_total) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-lg font-semibold text-gray-900">
                                <span class="odds" data-match-id="{{ $match->id }}" data-option-id="{{ $option->id }}">{{ number_format((float) $option->current_odds, 2) }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>

                <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700">Mise (crédits)</label>
                        <input
                            type="number"
                            name="stake"
                            min="1"
                            step="1"
                            value="{{ old('stake', $activeBet?->stake) }}"
                            class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-orange-400 focus:outline-none"
                            required
                            @disabled(!$isOpen)
                        >
                    </div>
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center px-6 py-3 rounded-full bg-red-600 text-white font-semibold hover:bg-orange-500 transition disabled:opacity-60 disabled:cursor-not-allowed"
                        @disabled(!$isOpen)
                    >
                        {{ $activeBet ? 'Modifier mon pari' : 'Valider mon pari' }}
                    </button>
                </div>
            </form>

            @if($activeBet)
                <div class="mt-8 border-t border-gray-100 pt-6">
                    <h2 class="text-lg font-semibold text-gray-900">Mon pari</h2>
                    <p class="text-sm text-gray-600 mt-1">
                        Option: {{ $activeBet->option?->label ?? '—' }}
                        · Mise: {{ number_format($activeBet->stake) }} crédits
                        · Cote: {{ number_format((float) $activeBet->odds_locked, 2) }}
                    </p>
                    <p class="text-xs text-gray-500 mt-2">
                        <span class="bet-edit-countdown"
                              data-editable-until="{{ $activeBet->editable_until?->toIso8601String() }}"></span>
                    </p>

                    @if($canEdit)
                        <div class="mt-4 grid gap-3 md:grid-cols-3 bet-actions">
                            <form method="POST" action="{{ route('betting.bets.update', $activeBet) }}" class="contents">
                                @csrf
                                @method('PUT')
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Option</label>
                                    <select name="option_id" class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2">
                                        @foreach($match->options as $option)
                                            <option value="{{ $option->id }}" @selected($option->id === $activeBet->option_id)>{{ $option->label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Mise (crédits)</label>
                                    <input
                                        type="number"
                                        name="stake"
                                        min="1"
                                        step="1"
                                        value="{{ $activeBet->stake }}"
                                        class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2"
                                    >
                                </div>
                                <div class="flex items-end gap-3">
                                    <button type="submit" class="px-4 py-2 rounded-full bg-slate-900 text-white text-sm font-semibold hover:bg-slate-700 transition" data-action="edit">
                                        Modifier
                                    </button>
                                </div>
                            </form>
                            <form method="POST" action="{{ route('betting.bets.destroy', $activeBet) }}" class="flex items-end">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="px-4 py-2 rounded-full bg-red-600 text-white text-sm font-semibold hover:bg-red-500 transition"
                                        data-action="delete"
                                        data-confirm="delete-bet"
                                        data-confirm-title="Supprimer le pari ?"
                                        data-confirm-text="Tu vas récupérer tes crédits. Cette action est irréversible.">
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </section>
@endsection
