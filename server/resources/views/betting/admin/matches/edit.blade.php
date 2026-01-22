@extends('admin.layout')

@section('title', 'Modifier un match')

@section('content')
    <main class="flex-1 overflow-y-auto p-6">
        <div class="mb-6">
            <a href="{{ route('admin.betting.matches.show', $match) }}" class="text-sm text-slate-400 hover:text-slate-200">
                ← Retour au match
            </a>
            <h1 class="text-2xl font-semibold text-white mt-2">Modifier le match</h1>
        </div>

        @if($errors->any())
            <div class="mb-4 rounded-lg border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-rose-200">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.betting.matches.update', $match) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 space-y-4">
                <div>
                    <label class="block text-sm text-slate-300">Titre</label>
                    <input
                        type="text"
                        name="title"
                        value="{{ old('title', $match->title) }}"
                        class="mt-2 w-full rounded-lg border border-slate-600 bg-slate-900 text-white px-3 py-2"
                        required
                    >
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label class="block text-sm text-slate-300">Ouverture des paris</label>
                        <input
                            type="datetime-local"
                            name="bet_open_at"
                            value="{{ old('bet_open_at', $match->bet_open_at?->format('Y-m-d\\TH:i')) }}"
                            class="mt-2 w-full rounded-lg border border-slate-600 bg-slate-900 text-white px-3 py-2"
                            required
                        >
                    </div>
                    <div>
                        <label class="block text-sm text-slate-300">Début du match</label>
                        <input
                            type="datetime-local"
                            name="match_start_at"
                            value="{{ old('match_start_at', $match->match_start_at?->format('Y-m-d\\TH:i')) }}"
                            class="mt-2 w-full rounded-lg border border-slate-600 bg-slate-900 text-white px-3 py-2"
                            required
                        >
                    </div>
                    <div>
                        <label class="block text-sm text-slate-300">Fin du match</label>
                        <input
                            type="datetime-local"
                            name="match_end_at"
                            value="{{ old('match_end_at', $match->match_end_at?->format('Y-m-d\\TH:i')) }}"
                            class="mt-2 w-full rounded-lg border border-slate-600 bg-slate-900 text-white px-3 py-2"
                            required
                        >
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <input type="hidden" name="is_visible" value="0">
                    <input
                        type="checkbox"
                        id="is_visible"
                        name="is_visible"
                        value="1"
                        class="h-4 w-4"
                        @checked(old('is_visible', $match->is_visible))
                    >
                    <label for="is_visible" class="text-sm text-slate-300">Visible côté utilisateurs</label>
                </div>
            </div>

            <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-white">Options (3)</h2>
                    @unless($canEditOptions)
                        <span class="text-xs text-amber-200 bg-amber-500/10 border border-amber-500/30 px-2 py-1 rounded-full">
                            Options verrouillées (des paris existent déjà)
                        </span>
                    @endunless
                </div>

                @for($i = 0; $i < 3; $i++)
                    @php
                        $option = $match->options[$i] ?? null;
                    @endphp
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm text-slate-300">Label option {{ $i + 1 }}</label>
                            <input
                                type="text"
                                name="options[{{ $i }}][label]"
                                value="{{ old('options.' . $i . '.label', $option?->label) }}"
                                class="mt-2 w-full rounded-lg border border-slate-600 bg-slate-900 text-white px-3 py-2"
                                @disabled(!$canEditOptions)
                            >
                        </div>
                        <div>
                            <label class="block text-sm text-slate-300">Cote initiale</label>
                            <input
                                type="number"
                                step="0.01"
                                min="1.01"
                                max="50"
                                name="options[{{ $i }}][odds]"
                                value="{{ old('options.' . $i . '.odds', $option?->initial_odds) }}"
                                class="mt-2 w-full rounded-lg border border-slate-600 bg-slate-900 text-white px-3 py-2"
                                @disabled(!$canEditOptions)
                            >
                        </div>
                    </div>
                @endfor
            </div>

            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-500">
                <i class="fa-solid fa-save"></i>
                Enregistrer les modifications
            </button>
        </form>
    </main>
@endsection
