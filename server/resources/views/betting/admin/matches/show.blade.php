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
    </main>
@endsection
