@extends('app')

@section('title', "P'AS'SION BDS - La Team")
@section('meta_description', "Voici la team du P'AS'SION BDS IMT Atlantique Nantes pour l'année 2025-2026.")

@section('content')
    <div class="text-center mb-16">
        <h1 class="font-display font-black text-4xl md:text-6xl uppercase tracking-tighter text-passion-red mb-2 drop-shadow-sm">
            LA&nbsp;&nbsp;TEAM&nbsp;&nbsp;<span class="text-passion-fire-orange">P'AS'SION</span>
        </h1>
        <p class="font-bold text-passion-pink-500 bg-white/60 inline-block px-4 py-1 skew-x-[-6deg]">
            Scrollez pour découvrir les cracks.
        </p>
    </div>

    <!-- SECTION: BUREAU -->
    <div class="mb-20">
        <div class="flex items-center gap-4 mb-8">
            <div class="h-1 flex-grow bg-passion-red/20"></div>
            <h2 class="font-display font-black text-3xl uppercase text-passion-red">Bureau restreint</h2>
            <div class="h-1 flex-grow bg-passion-red/20"></div>
        </div>

        <div class="flex flex-wrap justify-center gap-4">
            <div class="cr-card rarity-champion">
                <div class="cr-elixir"><span>5</span></div>
                <div class="cr-card-inner">
                    <img src="{{ asset('assets/members/tiago.jpg') }}" class="cr-card-image" alt="Prez">
                </div>
                <div class="cr-name-overlay">Tiago</div>
                <div class="cr-level">Prez</div>
            </div>

            <div class="cr-card rarity-champion">
                <div class="cr-elixir"><span>9</span></div>
                <div class="cr-card-inner">
                    <img src="{{ asset('assets/members/antoine.jpg') }}" class="cr-card-image" alt="Vice-prez">
                </div>
                <div class="cr-name-overlay">Antoine</div>
                <div class="cr-level">Vice-prez</div>
            </div>

            <div class="cr-card rarity-epic">
                <div class="cr-elixir"><span>7</span></div>
                <div class="cr-card-inner">
                    <img src="{{ asset('assets/members/hugo.jpg') }}" class="cr-card-image" alt="Trez">
                </div>
                <div class="cr-name-overlay">Hugo</div>
                <div class="cr-level">Trez</div>
            </div>

            <div class="cr-card rarity-epic">
                <div class="cr-elixir"><span>5</span></div>
                <div class="cr-card-inner">
                    <img src="{{ asset('assets/members/barnabe.jpg') }}" class="cr-card-image" alt="Vice-trez">
                </div>
                <div class="cr-name-overlay">Barnabé</div>
                <div class="cr-level">Vice-trez</div>
            </div>

            <div class="cr-card rarity-legendary">
                <div class="cr-elixir"><span>99</span></div>
                <div class="cr-card-inner">
                    <img src="{{ asset('assets/members/arthur.jpg') }}" class="cr-card-image" alt="Secrétaire">
                </div>
                <div class="cr-name-overlay">Arthur</div>
                <div class="cr-level">Secrétaire</div>
            </div>
        </div>
    </div>

    <div class="mb-20">
        <div class="flex items-center gap-4 mb-8">
            <div class="h-1 flex-grow bg-passion-red/20"></div>
            <h2 class="font-display font-black text-3xl uppercase text-passion-red">Pôle Compet</h2>
            <div class="h-1 flex-grow bg-passion-red/20"></div>
        </div>

        <div class="flex flex-wrap justify-center gap-4">

            <div class="cr-card rarity-legendary">
                <div class="cr-elixir"><span>∞</span></div>
                <div class="cr-card-inner">
                    <img src="{{ asset('assets/members/daner.jpg') }}" class="cr-card-image" alt="compet">
                </div>
                <div class="cr-name-overlay">Daner</div>
                <div class="cr-level">Space cake</div>
            </div>

            <div class="cr-card rarity-legendary">
                <div class="cr-elixir"><span>∞</span></div>
                <div class="cr-card-inner">
                    <img src="{{ asset('assets/members/timothe.jpg') }}" class="cr-card-image" alt="compet">
                </div>
                <div class="cr-name-overlay">Timothé</div>
                <div class="cr-level">Ceinture noire</div>
            </div>

            <div class="cr-card rarity-legendary">
                <div class="cr-elixir"><span>∞</span></div>
                <div class="cr-card-inner">
                    <img src="{{ asset('assets/members/gabriel.jpg') }}" class="cr-card-image" alt="compet">
                </div>
                <div class="cr-name-overlay">Gabriel</div>
                <div class="cr-level">MrOlympia</div>
            </div>

        </div>
    </div>

    <!-- SECTION: PÔLE SPORT -->
    <div class="mb-20">
        <div class="flex items-center gap-4 mb-8">
            <div class="h-1 flex-grow bg-passion-red/20"></div>
            <h2 class="font-display font-black text-3xl uppercase text-passion-red">Pôle Com</h2>
            <div class="h-1 flex-grow bg-passion-red/20"></div>
        </div>

        <div class="flex flex-wrap justify-center gap-4">

            <!-- FOOT: RARE -->
            <div class="cr-card rarity-rare">
                <div class="cr-elixir"><span>4</span></div>
                <div class="cr-card-inner">
                    <img src="https://placehold.co/200x250/png?text=Karim" class="cr-card-image">
                </div>
                <div class="cr-name-overlay">Karim</div>
                <div class="cr-level">Niveau 10</div>
            </div>

            <!-- RUGBY: COMMON -->
            <div class="cr-card rarity-common">
                <div class="cr-elixir"><span>6</span></div>
                <div class="cr-card-inner">
                    <img src="https://placehold.co/200x250/png?text=Theo" class="cr-card-image">
                </div>
                <div class="cr-name-overlay">Théo</div>
                <div class="cr-level">Niveau 10</div>
            </div>

        </div>
    </div>

    <!-- SECTION: PÔLE SPORT -->
    <div class="mb-20">
        <div class="flex items-center gap-4 mb-8">
            <div class="h-1 flex-grow bg-passion-red/20"></div>
            <h2 class="font-display font-black text-3xl uppercase text-passion-red">Pôle Sponso</h2>
            <div class="h-1 flex-grow bg-passion-red/20"></div>
        </div>

        <div class="flex flex-wrap justify-center gap-4">

            <!-- FOOT: RARE -->
            <div class="cr-card rarity-rare">
                <div class="cr-elixir"><span>4</span></div>
                <div class="cr-card-inner">
                    <img src="https://placehold.co/200x250/png?text=Karim" class="cr-card-image">
                </div>
                <div class="cr-name-overlay">Karim</div>
                <div class="cr-level">Niveau 10</div>
            </div>

            <!-- RUGBY: COMMON -->
            <div class="cr-card rarity-common">
                <div class="cr-elixir"><span>6</span></div>
                <div class="cr-card-inner">
                    <img src="https://placehold.co/200x250/png?text=Theo" class="cr-card-image">
                </div>
                <div class="cr-name-overlay">Théo</div>
                <div class="cr-level">Niveau 10</div>
            </div>

        </div>
    </div>

    <!-- SECTION: PÔLE SPORT -->
    <div class="mb-20">
        <div class="flex items-center gap-4 mb-8">
            <div class="h-1 flex-grow bg-passion-red/20"></div>
            <h2 class="font-display font-black text-3xl uppercase text-passion-red">Pôle Sport</h2>
            <div class="h-1 flex-grow bg-passion-red/20"></div>
        </div>

        <div class="flex flex-wrap justify-center gap-4">

            <!-- FOOT: RARE -->
            <div class="cr-card rarity-rare">
                <div class="cr-elixir"><span>4</span></div>
                <div class="cr-card-inner">
                    <img src="https://placehold.co/200x250/png?text=Karim" class="cr-card-image">
                </div>
                <div class="cr-name-overlay">Karim</div>
                <div class="cr-level">Niveau 10</div>
            </div>

            <!-- RUGBY: COMMON -->
            <div class="cr-card rarity-common">
                <div class="cr-elixir"><span>6</span></div>
                <div class="cr-card-inner">
                    <img src="https://placehold.co/200x250/png?text=Theo" class="cr-card-image">
                </div>
                <div class="cr-name-overlay">Théo</div>
                <div class="cr-level">Niveau 10</div>
            </div>

        </div>
    </div>


    <!-- SECTION: PÔLE SPORT -->
    <div class="mb-20">
        <div class="flex items-center gap-4 mb-8">
            <div class="h-1 flex-grow bg-passion-red/20"></div>
            <h2 class="font-display font-black text-3xl uppercase text-passion-red">Pôle Inté/Event</h2>
            <div class="h-1 flex-grow bg-passion-red/20"></div>
        </div>

        <div class="flex flex-wrap justify-center gap-4">

            <!-- FOOT: RARE -->
            <div class="cr-card rarity-rare">
                <div class="cr-elixir"><span>4</span></div>
                <div class="cr-card-inner">
                    <img src="https://placehold.co/200x250/png?text=Karim" class="cr-card-image">
                </div>
                <div class="cr-name-overlay">Karim</div>
                <div class="cr-level">Niveau 10</div>
            </div>

            <!-- RUGBY: COMMON -->
            <div class="cr-card rarity-common">
                <div class="cr-elixir"><span>6</span></div>
                <div class="cr-card-inner">
                    <img src="https://placehold.co/200x250/png?text=Theo" class="cr-card-image">
                </div>
                <div class="cr-name-overlay">Théo</div>
                <div class="cr-level">Niveau 10</div>
            </div>

        </div>
    </div>


    <!-- SECTION: PÔLE SPORT -->
    <div class="mb-20">
        <div class="flex items-center gap-4 mb-8">
            <div class="h-1 flex-grow bg-passion-red/20"></div>
            <h2 class="font-display font-black text-3xl uppercase text-passion-red">Membres actifs</h2>
            <div class="h-1 flex-grow bg-passion-red/20"></div>
        </div>

        <div class="flex flex-wrap justify-center gap-4">

            <!-- FOOT: RARE -->
            <div class="cr-card rarity-rare">
                <div class="cr-elixir"><span>4</span></div>
                <div class="cr-card-inner">
                    <img src="https://placehold.co/200x250/png?text=Karim" class="cr-card-image">
                </div>
                <div class="cr-name-overlay">Karim</div>
                <div class="cr-level">Niveau 10</div>
            </div>

            <!-- RUGBY: COMMON -->
            <div class="cr-card rarity-common">
                <div class="cr-elixir"><span>6</span></div>
                <div class="cr-card-inner">
                    <img src="https://placehold.co/200x250/png?text=Theo" class="cr-card-image">
                </div>
                <div class="cr-name-overlay">Théo</div>
                <div class="cr-level">Niveau 10</div>
            </div>

        </div>
    </div>

@endsection
