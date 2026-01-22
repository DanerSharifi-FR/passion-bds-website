@extends('admin.layout')

@section('title', 'Créer un match')

@section('content')
    <main class="flex-1 overflow-y-auto p-6">
        <div class="mb-6">
            <a href="{{ route('admin.betting.matches.index') }}" class="text-sm text-slate-400 hover:text-slate-200">
                ← Retour aux matchs
            </a>
            <h1 class="text-2xl font-semibold text-white mt-2">Créer un match</h1>
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

        <form method="POST" action="{{ route('admin.betting.matches.store') }}" class="space-y-6">
            @csrf

            <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 space-y-4">
                <div>
                    <label class="block text-sm text-slate-300">Titre</label>
                    <input
                        type="text"
                        name="title"
                        value="{{ old('title') }}"
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
                            value="{{ old('bet_open_at') }}"
                            class="mt-2 w-full rounded-lg border border-slate-600 bg-slate-900 text-white px-3 py-2"
                            required
                        >
                    </div>
                    <div>
                        <label class="block text-sm text-slate-300">Début du match</label>
                        <input
                            type="datetime-local"
                            name="match_start_at"
                            value="{{ old('match_start_at') }}"
                            class="mt-2 w-full rounded-lg border border-slate-600 bg-slate-900 text-white px-3 py-2"
                            required
                        >
                    </div>
                    <div>
                        <label class="block text-sm text-slate-300">Fin du match</label>
                        <input
                            type="datetime-local"
                            name="match_end_at"
                            value="{{ old('match_end_at') }}"
                            class="mt-2 w-full rounded-lg border border-slate-600 bg-slate-900 text-white px-3 py-2"
                            required
                        >
                    </div>
                </div>
            </div>

            <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 space-y-4">
                <h2 class="text-lg font-semibold text-white">Options (3)</h2>
                @for($i = 0; $i < 3; $i++)
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm text-slate-300">Label option {{ $i + 1 }}</label>
                            <input
                                type="text"
                                name="options[{{ $i }}][label]"
                                value="{{ old('options.' . $i . '.label') }}"
                                class="mt-2 w-full rounded-lg border border-slate-600 bg-slate-900 text-white px-3 py-2"
                                required
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
                                value="{{ old('options.' . $i . '.odds') }}"
                                class="mt-2 w-full rounded-lg border border-slate-600 bg-slate-900 text-white px-3 py-2"
                                required
                            >
                        </div>
                    </div>
                @endfor
            </div>

            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-500">
                <i class="fa-solid fa-check"></i>
                Créer le match
            </button>
        </form>
    </main>
@endsection
