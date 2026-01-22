@extends('admin.layout')

@section('title', 'Paris utilisateurs')

@section('content')
    <main class="flex-1 overflow-y-auto p-6">
        <div class="mb-6">
            <a href="{{ route('admin.betting.matches.show', $match) }}" class="text-sm text-slate-400 hover:text-slate-200">
                ← Retour au match
            </a>
            <h1 class="text-2xl font-semibold text-white mt-2">Paris utilisateurs</h1>
            <p class="text-slate-400">Match: {{ $match->title }}</p>
        </div>

        <form method="GET" class="bg-slate-800 border border-slate-700 rounded-xl p-4 flex flex-col md:flex-row gap-4 mb-6">
            <div>
                <label class="block text-xs uppercase tracking-wide text-slate-400">Statut</label>
                <select name="status" class="mt-2 w-full rounded-lg border border-slate-600 bg-slate-900 text-white px-3 py-2">
                    <option value="">Tous</option>
                    @foreach(['ACTIVE', 'CANCELLED', 'SETTLED'] as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-xs uppercase tracking-wide text-slate-400">Utilisateur</label>
                <input
                    type="text"
                    name="user"
                    value="{{ $filters['user'] ?? '' }}"
                    placeholder="Nom ou email"
                    class="mt-2 w-full rounded-lg border border-slate-600 bg-slate-900 text-white px-3 py-2"
                >
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-500">Filtrer</button>
                <a href="{{ route('admin.betting.matches.bets.index', $match) }}" class="px-4 py-2 rounded-lg bg-slate-700 text-slate-200 hover:bg-slate-600">Réinitialiser</a>
            </div>
        </form>

        <div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-900 text-slate-300">
                <tr>
                    <th class="text-left px-4 py-3">Utilisateur</th>
                    <th class="text-left px-4 py-3">Option</th>
                    <th class="text-left px-4 py-3">Mise</th>
                    <th class="text-left px-4 py-3">Cote</th>
                    <th class="text-left px-4 py-3">Statut</th>
                    <th class="text-left px-4 py-3">Créé le</th>
                    <th class="text-left px-4 py-3">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                @forelse($bets as $bet)
                    <tr>
                        <td class="px-4 py-3 text-slate-200">
                            <div class="font-medium">{{ $bet->user?->display_name ?: $bet->user?->university_email }}</div>
                            <div class="text-xs text-slate-400">{{ $bet->user?->university_email }}</div>
                        </td>
                        <td class="px-4 py-3 text-slate-300">{{ $bet->option?->label ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-300">{{ number_format($bet->stake) }}</td>
                        <td class="px-4 py-3 text-slate-300">{{ number_format((float) $bet->odds_locked, 2) }}</td>
                        <td class="px-4 py-3 text-slate-300">{{ $bet->status->value }}</td>
                        <td class="px-4 py-3 text-slate-300">{{ $bet->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-300">
                            <div class="space-y-2">
                                <form method="POST" action="{{ route('admin.betting.bets.update', $bet) }}" class="grid gap-2">
                                    @csrf
                                    @method('PUT')
                                    <select name="option_id" class="w-full rounded-lg border border-slate-600 bg-slate-900 text-white px-2 py-1">
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
                                        class="w-full rounded-lg border border-slate-600 bg-slate-900 text-white px-2 py-1"
                                    >
                                    <select name="status" class="w-full rounded-lg border border-slate-600 bg-slate-900 text-white px-2 py-1">
                                        @foreach(['ACTIVE', 'CANCELLED', 'SETTLED'] as $status)
                                            <option value="{{ $status }}" @selected($status === $bet->status->value)>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="px-3 py-1 rounded-lg bg-indigo-600 text-white hover:bg-indigo-500 text-xs">
                                        Mettre à jour
                                    </button>
                                </form>
                                <div class="flex items-center gap-2">
                                    <form method="POST" action="{{ route('admin.betting.bets.destroy', $bet) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1 rounded-lg bg-amber-600 text-white hover:bg-amber-500 text-xs">
                                            Annuler
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.betting.bets.destroy', $bet) }}">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="hard" value="1">
                                        <button type="submit" class="px-3 py-1 rounded-lg bg-rose-600 text-white hover:bg-rose-500 text-xs">
                                            Supprimer
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-slate-400">Aucun pari trouvé.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between text-sm text-slate-400 mt-4">
            <div>
                Total: {{ $bets->total() }} · Page {{ $bets->currentPage() }} / {{ $bets->lastPage() }}
            </div>
            <div>
                {{ $bets->links() }}
            </div>
        </div>
    </main>
@endsection
