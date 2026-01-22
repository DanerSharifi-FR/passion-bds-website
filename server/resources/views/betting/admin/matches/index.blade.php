@extends('admin.layout')

@section('title', 'Paris - Matches')

@section('content')
    <main class="flex-1 overflow-y-auto p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-white">Matches</h1>
                <p class="text-slate-400">Gestion des matchs de pari.</p>
            </div>
            <a
                href="{{ route('admin.betting.matches.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-500 transition"
            >
                <i class="fa-solid fa-plus"></i>
                Créer un match
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-emerald-200">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 rounded-lg border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-rose-200">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-900 text-slate-300">
                <tr>
                    <th class="text-left px-4 py-3">Match</th>
                    <th class="text-left px-4 py-3">Statut</th>
                    <th class="text-left px-4 py-3">Visibilité</th>
                    <th class="text-left px-4 py-3">Début</th>
                    <th class="text-left px-4 py-3">Fin</th>
                    <th class="text-left px-4 py-3">Bets</th>
                    <th class="text-right px-4 py-3">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                @forelse($matches as $match)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="text-white font-medium">{{ $match->title }}</div>
                            <div class="text-xs text-slate-400">
                                Ouverture: {{ $match->bet_open_at?->format('d/m/Y H:i') ?? '—' }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-slate-300">{{ $match->status->value }}</td>
                        <td class="px-4 py-3 text-slate-300">
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
                        </td>
                        <td class="px-4 py-3 text-slate-300">{{ $match->match_start_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-300">{{ $match->match_end_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-300">{{ $match->bets_count }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.betting.matches.show', $match) }}" class="text-indigo-300 hover:text-indigo-200">Détails</a>
                            <span class="text-slate-600 mx-1">|</span>
                            <a href="{{ route('admin.betting.matches.bets.index', $match) }}" class="text-indigo-300 hover:text-indigo-200">Paris</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-slate-400">Aucun match pour le moment.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $matches->links() }}
        </div>
    </main>
@endsection
