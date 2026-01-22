@extends('app')

@section('title', 'Paris')

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
    <section class="max-w-6xl mx-auto px-4 py-10">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Paris</h1>
            </div>
            <div class="bg-white shadow-sm border border-gray-200 rounded-xl px-5 py-4 text-right">
                <div class="text-xs uppercase tracking-wide text-gray-500">Mon solde</div>
                <div class="text-2xl font-semibold text-gray-900">{{ number_format($wallet->balance) }} crédits</div>
            </div>
        </div>

        @if(auth()->user()?->hasRole('ROLE_GAMEMASTER') || auth()->user()?->hasRole('ROLE_SUPER_ADMIN'))
            <div class="mb-6 bg-white border border-gray-200 rounded-xl p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="text-sm font-semibold text-gray-900">Réinitialiser les crédits</div>
                    <div class="text-xs text-gray-500 mt-1">Tous les utilisateurs seront remis à 1000 crédits.</div>
                </div>
                <form method="POST" action="{{ route('betting.reset-credits') }}">
                    @csrf
                    <button type="submit"
                            class="px-4 py-2 rounded-full bg-amber-600 text-white font-semibold hover:bg-amber-500 transition"
                            data-confirm="reset-credits"
                            data-confirm-title="Réinitialiser les crédits ?"
                            data-confirm-text="Tous les utilisateurs seront mis à 1000 crédits."
                            data-confirm-require-check="1"
                            data-confirm-check-label="Je confirme la remise à 1000 crédits.">
                        Reset crédits
                    </button>
                </form>
            </div>
        @endif

        @if($matches->count() === 0)
            <div class="bg-white border border-gray-200 rounded-xl p-6 text-gray-600">
                Aucun match disponible pour le moment.
            </div>
        @else
            <div class="grid gap-6">
                @foreach($matches as $match)
                    <article
                        class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm"
                        data-bet-match-id="{{ $match->id }}"
                    >
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">{{ $match->title }}</h2>
                                <p class="text-sm text-gray-500 mt-1">
                                    Ouverture: <span class="human-dt" data-dt="{{ $match->bet_open_at?->toIso8601String() }}"></span>
                                    · Début: <span class="human-dt" data-dt="{{ $match->match_start_at?->toIso8601String() }}"></span>
                                    · Fin: <span class="human-dt" data-dt="{{ $match->match_end_at?->toIso8601String() }}"></span>
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="bet-status-badge text-xs uppercase tracking-wide px-3 py-1 rounded-full"
                                      data-bet-open-at="{{ $match->bet_open_at?->toIso8601String() }}"
                                      data-match-end-at="{{ $match->match_end_at?->toIso8601String() }}"
                                      data-server-status="{{ $match->status->value }}">
                                    —
                                </span>
                                <a
                                    href="{{ route('betting.matches.show', $match) }}"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-red-600 text-white text-sm font-semibold hover:bg-orange-500 transition"
                                >
                                    Parier
                                </a>
                            </div>
                        </div>

                        <div class="mt-6 grid gap-3 md:grid-cols-3">
                            @foreach($match->options as $option)
                                <div class="border border-gray-200 rounded-xl p-4 flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $option->label }}</div>
                                        <div class="text-xs text-gray-500">
                                            Pool: <span data-option-pool="{{ $option->id }}">{{ number_format($option->pool_total) }}</span>
                                        </div>
                                    </div>
                                    <div class="text-lg font-semibold text-gray-900">
                                        <span class="odds" data-match-id="{{ $match->id }}" data-option-id="{{ $option->id }}">{{ number_format((float) $option->current_odds, 2) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $matches->links() }}
            </div>
        @endif
    </section>
@endsection
