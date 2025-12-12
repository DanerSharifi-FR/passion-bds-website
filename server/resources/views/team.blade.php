@extends('app')

@section('title', "P'AS'SION BDS - La Team")
@section('meta_description', "Voici la team du P'AS'SION BDS IMT Atlantique Nantes pour l'année 2025-2026.")

@section('content')
    <div class="text-center mb-16">
        <h1 class="font-display font-black text-4xl md:text-6xl uppercase tracking-tighter text-passion-red mb-2 drop-shadow-sm">
            LA&nbsp;&nbsp;TEAM&nbsp;&nbsp;<span class="text-passion-fire-orange">P'AS'SION</span>
        </h1>
        {{--<p class="font-bold text-passion-pink-500 bg-white/60 inline-block px-4 py-1 skew-x-[-6deg]">
            Scrollez pour découvrir les cracks.
        </p>--}}
    </div>

    @foreach(($teamPoles ?? []) as $pole)
        @php
            $poleName = $pole['name'] ?? 'Pôle';
            $members = $pole['members'] ?? [];

            // pas de section vide
            if (count($members) === 0) {
                continue;
            }
        @endphp

        <div class="mb-20">
            <div class="flex items-center gap-4 mb-8">
                <div class="h-1 flex-grow bg-passion-red/20"></div>
                <h2 class="font-display font-black text-3xl uppercase text-passion-red">{{ $poleName }}</h2>
                <div class="h-1 flex-grow bg-passion-red/20"></div>
            </div>

            <div class="flex flex-wrap justify-center gap-4">
                @foreach($members as $member)
                    @php
                        $fullName = $member['full_name'] ?? 'Membre';
                        $nickname = $member['nickname'] ?? null;
                        $photoUrl = $member['photo_url'] ?? '';

                        // injectés par le controller (random par session + change chaque semaine)
                        $rarityClass = $member['rarity_class'] ?? 'rarity-common';
                        $elixirValue = $member['elixir_value'] ?? '1';
                    @endphp

                    <div class="cr-card {{ $rarityClass }}">
                        <div class="cr-elixir"><span>{{ $elixirValue }}</span></div>

                        <div class="cr-card-inner">
                            <img
                                src="{{ $photoUrl }}"
                                class="cr-card-image"
                                alt="{{ $fullName }}"
                                loading="lazy"
                            >
                        </div>

                        <div class="cr-name-overlay">{{ $fullName }}</div>

                        @if(!empty($nickname))
                            <div class="cr-level">{{ $nickname }}</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
@endsection
