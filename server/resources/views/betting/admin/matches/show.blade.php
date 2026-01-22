@extends('admin.layout')

@section('title', 'Détails du match')

@section('content')
    <main class="flex-1 overflow-y-auto p-6">
        <div class="mb-6">
            <a href="{{ route('admin.betting.matches.index') }}" class="text-sm text-slate-400 hover:text-slate-200">
                ← Retour aux matchs
            </a>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mt-2">
                <h1 class="text-2xl font-semibold text-white">{{ $match->title }}</h1>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('admin.betting.matches.edit', $match) }}" class="px-4 py-2 rounded-lg bg-slate-700 text-white hover:bg-slate-600">
                        Modifier
                    </a>
                    <form method="POST" action="{{ route('admin.betting.matches.destroy', $match) }}"
                          onsubmit="return confirm('Supprimer ce match ? Tous les paris seront supprimés et remboursés.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 rounded-lg bg-rose-600 text-white hover:bg-rose-500">
                            Supprimer le match
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="bg-slate-800 border border-slate-700 rounded-xl p-5 space-y-2">
                <div class="text-sm text-slate-400">Statut</div>
                <div class="text-white font-semibold">{{ $match->status->value }}</div>
                <div class="text-sm text-slate-400 mt-4">Visibilité</div>
                <div class="flex items-center gap-3">
                    <span class="text-xs uppercase tracking-wide px-2 py-1 rounded-full {{ $match->is_visible ? 'bg-emerald-500/20 text-emerald-200' : 'bg-rose-500/20 text-rose-200' }}">
                        {{ $match->is_visible ? 'Visible' : 'Invisible' }}
                    </span>
                    <form method="POST" action="{{ route('admin.betting.matches.visibility', $match) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="text-xs text-indigo-300 hover:text-indigo-200">
                            {{ $match->is_visible ? 'Masquer' : 'Afficher' }}
                        </button>
                    </form>
                </div>
                <div class="text-sm text-slate-400 mt-4">Ouverture</div>
                <div class="text-white">{{ $match->bet_open_at?->format('d/m/Y H:i') ?? '—' }}</div>
                <div class="text-sm text-slate-400 mt-4">Début</div>
                <div class="text-white">{{ $match->match_start_at?->format('d/m/Y H:i') ?? '—' }}</div>
                <div class="text-sm text-slate-400 mt-4">Fin</div>
                <div class="text-white">{{ $match->match_end_at?->format('d/m/Y H:i') ?? '—' }}</div>
                <div class="text-sm text-slate-400 mt-4">Réglé le</div>
                <div class="text-white">{{ $match->settled_at?->format('d/m/Y H:i') ?? '—' }}</div>
                <div class="text-sm text-slate-400 mt-4">Réglé par</div>
                <div class="text-white">{{ $match->settled_by ?? '—' }}</div>
                <div class="text-sm text-slate-400 mt-4">Version règlement</div>
                <div class="text-white">{{ $match->settlement_version }}</div>
            </div>

            <div class="lg:col-span-2 bg-slate-800 border border-slate-700 rounded-xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-white">Options</h2>
                    <a href="{{ route('admin.betting.matches.bets.index', $match) }}" class="text-indigo-300 hover:text-indigo-200">
                        Voir les paris utilisateurs
                    </a>
                </div>
                <div class="space-y-3">
                    @foreach($match->options as $option)
                        <div class="flex items-center justify-between border border-slate-700 rounded-lg px-4 py-3">
                            <div>
                                <div class="text-white font-medium">{{ $option->label }}</div>
                                <div class="text-xs text-slate-400">Pool: {{ number_format($option->pool_total) }}</div>
                            </div>
                            <div class="text-right text-sm text-slate-300">
                                <div>Cote initiale: {{ number_format((float) $option->initial_odds, 2) }}</div>
                                <div>Cote actuelle: {{ number_format((float) $option->current_odds, 2) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-6 bg-slate-800 border border-slate-700 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Résultat</h2>

            @if($match->winnerOption)
                <div class="mb-4 text-sm text-slate-300">
                    Gagnant actuel :
                    <span class="text-white font-semibold">{{ $match->winnerOption->label }}</span>
                    @if($match->score_a !== null && $match->score_b !== null)
                        · Score: {{ $match->score_a }} - {{ $match->score_b }}
                    @endif
                </div>
            @else
                <div class="mb-4 text-sm text-slate-300">Aucun gagnant déclaré.</div>
            @endif

            <form method="POST" action="{{ route('admin.betting.matches.winner', $match) }}" class="grid gap-4 lg:grid-cols-4 items-end">
                @csrf
                <div class="lg:col-span-2">
                    <label class="block text-sm text-slate-300">Option gagnante</label>
                    <select name="winner_option_id" class="mt-2 w-full rounded-lg border border-slate-600 bg-slate-900 text-white px-3 py-2" required>
                        @foreach($match->options as $option)
                            <option value="{{ $option->id }}" @selected($match->winner_option_id === $option->id)>{{ $option->label ?? 'Option sans libellé' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-slate-300">Score A (optionnel)</label>
                    <input type="number" min="0" name="score_a" value="{{ old('score_a') }}" class="mt-2 w-full rounded-lg border border-slate-600 bg-slate-900 text-white px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm text-slate-300">Score B (optionnel)</label>
                    <input type="number" min="0" name="score_b" value="{{ old('score_b') }}" class="mt-2 w-full rounded-lg border border-slate-600 bg-slate-900 text-white px-3 py-2">
                </div>
                <div class="lg:col-span-4 flex flex-wrap gap-3">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-500">
                        Déclarer / Refaire le gagnant
                    </button>
                </div>
            </form>

            @if($match->winner_option_id)
                <form method="POST" action="{{ route('admin.betting.matches.winner.undo', $match) }}" class="mt-3">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-lg bg-amber-600 text-white hover:bg-amber-500">
                        Annuler la déclaration
                    </button>
                </form>
            @endif
        </div>
    </main>
@endsection
