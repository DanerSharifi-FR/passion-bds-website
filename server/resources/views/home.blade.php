@extends('app')

@section('title', "P'AS'SION BDS - Accueil")
@section('meta_description', "Site de la campagne BDS P'AS'SION IMT Atlantique Nantes.")
@section('og_title', "P'AS'SION : Bureau Des Sports")
@section('og_description', "Site de la campagne BDS P'AS'SION IMT Atlantique Nantes.")

@section('content')
    <!-- Big Sport Title Area -->
    <div class="relative text-center mb-8 skew-box z-10">
        <!-- Reduced Mobile Text Size to avoid overtaking the Header Logo -->
        <h1 class="font-display font-black text-3xl md:text-7xl text-passion-red uppercase tracking-tighter leading-none mb-2 drop-shadow-sm">
            Machine<br><span class="text-passion-fire-orange text-stroke-red">à Allos</span>
        </h1>
        <p class="font-display font-bold text-passion-pink-500 text-sm md:text-xl unskew-text bg-white/50 px-2 inline-block skew-x-6">
            Le sport national : la flemme.
        </p>
    </div>

    <!-- Machine Card Container -->
    <div id="machine-card" class="machine-container machine-state-closed w-full max-w-4xl p-6 md:p-8 rounded-sm relative bg-white z-20 mb-12">

        <!-- Top Bar: Status Board -->
        <div class="flex justify-between items-center mb-6 border-b-2 border-passion-pink-200 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-4 h-4 rounded-full bg-passion-red animate-pulse"></div>
                <span class="font-mono font-bold text-passion-red uppercase tracking-widest text-sm hidden md:block">SYSTEM_READY</span>
                <span class="font-mono font-bold text-passion-red uppercase tracking-widest text-sm md:hidden">RDY</span>
            </div>
            <div class="status-indicator px-4 py-1 skew-box font-display font-black uppercase text-sm tracking-wider shadow-[4px_4px_0px_rgba(0,0,0,0.1)]">
                <span class="block unskew-text status-text-content"></span>
            </div>
        </div>

        <!-- Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <!-- Cards remain same -->
            <div class="relative group h-32 bg-passion-pink-100 border-2 border-passion-pink-300 overflow-hidden hover:border-passion-red transition-colors">
                <div class="allo-real-content p-4 h-full flex flex-col justify-center transition-all duration-300">
                    <div class="flex justify-between items-start mb-1">
                        <h3 class="font-display font-black text-xl text-passion-red uppercase">Petit Déj</h3>
                        <span class="text-xs font-bold bg-white text-passion-red px-2 py-0.5 border border-passion-red rounded-sm">FOOD</span>
                    </div>
                    <p class="text-sm font-medium text-passion-red leading-tight">Viennoiseries livrées direct au lit. 0 effort.</p>
                </div>
                <div class="allo-mystery-overlay absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4IiBoZWlnaHQ9IjgiPgo8cmVjdCB3aWR0aD0iOCIgaGVpZ2h0PSI4IiBmaWxsPSIjRkNFMkVDIi8+CjxwYXRoIGQ9Ik0wIDBMOCA4Wk04IDBMMCA4WiIHN0cm9rZT0iI0U0NDc2QSIgc3Ryb2tlLXdpZHRoPSIxIiBvcGFjaXR5PSIwLjEiLz4KPC9zdmc+')] flex items-center justify-center group-hover:bg-passion-pink-200 transition-colors">
                    <span class="font-display font-black text-2xl text-passion-pink-400 opacity-50 -rotate-6 group-hover:scale-110 transition-transform">LOCKED 🔒</span>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="relative group h-32 bg-passion-pink-100 border-2 border-passion-pink-300 overflow-hidden hover:border-passion-red transition-colors">
                <div class="allo-real-content p-4 h-full flex flex-col justify-center transition-all duration-300">
                    <div class="flex justify-between items-start mb-1">
                        <h3 class="font-display font-black text-xl text-passion-red uppercase">Vaisselle</h3>
                        <span class="text-xs font-bold bg-white text-passion-red px-2 py-0.5 border border-passion-red rounded-sm">CLEAN</span>
                    </div>
                    <p class="text-sm font-medium text-passion-red leading-tight">Ton évier déborde ? On arrive avec des gants.</p>
                </div>
                <div class="allo-mystery-overlay absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4IiBoZWlnaHQ9IjgiPgo8cmVjdCB3aWR0aD0iOCIgaGVpZ2h0PSI4IiBmaWxsPSIjRkNFMkVDIi8+CjxwYXRoIGQ9Ik0wIDBMOCA4Wk04IDBMMCA4WiIHN0cm9rZT0iI0U0NDc2QSIgc3Ryb2tlLXdpZHRoPSIxIiBvcGFjaXR5PSIwLjEiLz4KPC9zdmc+')] flex items-center justify-center group-hover:bg-passion-pink-200 transition-colors">
                    <span class="font-display font-black text-2xl text-passion-pink-400 opacity-50 -rotate-6 group-hover:scale-110 transition-transform">LOCKED 🔒</span>
                </div>
            </div>
        </div>

        <!-- Footer Action -->
        <div class="flex flex-col items-center gap-4 text-center">
            <p id="status-message" class="font-medium text-passion-red text-sm md:text-base px-2"></p>
            <a href="/allos" class="main-btn skew-box px-6 py-3 md:px-10 md:py-4 font-display font-black text-lg md:text-xl uppercase tracking-wider transition-all duration-200 whitespace-nowrap shadow-md">
                <span class="unskew-text btn-text"></span>
            </a>
        </div>

        <div class="absolute -top-3 -right-3 w-8 h-8 bg-passion-fire-yellow border-2 border-passion-red z-10 skew-box"></div>
        <div class="absolute -bottom-3 -left-3 w-8 h-8 bg-passion-pink-500 border-2 border-passion-red z-10 skew-box"></div>
    </div>

    <!-- ARCADE ZONE (Games to Earn Points) -->
    <!-- Toggle user state class here via JS: 'state-guest' or 'state-logged-in' -->
    <section id="arcade-zone" class="state-guest fan-zone-container w-full md:w-11/12 max-w-5xl md:transform md:-skew-x-6 p-1 relative z-10 mb-12 transition-transform duration-300 mx-auto">
        <!-- Increased padding (py-6 md:py-10) to fix tight spacing at top -->
        <div class="bg-[#9B1237] border-4 border-white py-6 px-4 md:p-10 md:transform md:skew-x-6">

            <!-- Section Header -->
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8 border-b-2 border-white/20 pb-4 gap-4">
                <h2 class="font-display font-black text-3xl md:text-5xl uppercase tracking-tighter text-passion-fire-yellow drop-shadow-md break-words">
                    Arcade <span class="text-white">& Games</span>
                </h2>

                <!-- Guest View -->
                <div class="show-if-guest"></div>

                <!-- Logged In View -->
                <div class="show-if-logged-in flex flex-wrap gap-3 items-center w-full lg:w-auto">
                    <div class="bg-black/50 px-4 py-2 rounded border border-passion-fire-yellow flex items-center gap-2 shadow-[2px_2px_0px_#FFC94A] flex-shrink-0 whitespace-nowrap">
                        <span class="text-xs md:text-base">👤 Ton Rang:</span>
                        <span class="font-bold text-white text-base md:text-lg">#4</span>
                    </div>
                    <div class="bg-black/50 px-4 py-2 rounded border border-passion-fire-yellow flex items-center gap-2 shadow-[2px_2px_0px_#FFC94A] flex-shrink-0 whitespace-nowrap">
                        <span class="text-xs md:text-base">💰 Ton Solde:</span>
                        <span class="font-bold text-passion-fire-orange text-base md:text-lg">420 pts</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                <!-- Left: PODIUM Leaderboard -->
                <div class="bg-black/20 p-6 rounded border border-white/10 flex flex-col justify-between">
                    <div class="flex justify-between items-end mb-2">
                        <h3 class="font-display font-bold text-lg md:text-xl uppercase flex items-center gap-2">
                            🏆 Top Joueurs
                        </h3>
                        <div class="show-if-logged-in text-[10px] bg-passion-fire-yellow text-passion-red px-2 py-1 rounded font-bold animate-pulse">
                            💡 Gagne des pts !
                        </div>
                    </div>

                    <!-- PODIUM (Increased margin to mt-20 to fix crown overlap) -->
                    <div class="flex justify-center items-end gap-1 md:gap-2 h-48 mb-6 mt-20">

                        <!-- 2nd Place -->
                        <div class="flex flex-col items-center w-1/3">
                            <!-- Avatar/Name -->
                            <div class="mb-2 text-center text-white/90">
                                <span class="block text-2xl mb-1 filter drop-shadow">🥈</span>
                                <span class="block font-black text-xs md:text-sm uppercase leading-tight truncate w-full">Thomas D.</span>
                                <span class="text-[10px] opacity-80 font-mono">1240 pts</span>
                            </div>
                            <!-- Bar (Silver) -->
                            <div class="w-full bg-gradient-to-t from-gray-500 to-gray-300 rounded-t-lg h-24 border-t-2 border-x-2 border-white/30 relative flex items-end justify-center pb-2 animate-glow-silver">
                                <span class="text-white/30 font-black text-2xl md:text-4xl opacity-50">2</span>
                            </div>
                        </div>

                        <!-- 1st Place -->
                        <div class="flex flex-col items-center w-1/3 z-10 -mt-8">
                            <div class="mb-2 text-center text-passion-fire-yellow">
                                <span class="block text-4xl mb-1 filter drop-shadow-lg">👑</span>
                                <span class="block font-black text-sm md:text-base uppercase leading-tight truncate w-full">Sarah L.</span>
                                <span class="font-bold text-xs">2100 pts</span>
                            </div>
                            <!-- Bar (Gold) -->
                            <div class="w-full bg-gradient-to-t from-yellow-600 to-yellow-300 rounded-t-lg h-36 border-t-2 border-x-2 border-white/50 relative shadow-[0_0_20px_rgba(255,201,74,0.4)] flex items-end justify-center pb-2 animate-glow-gold">
                                <span class="text-white/40 font-black text-4xl md:text-6xl opacity-60">1</span>
                            </div>
                        </div>

                        <!-- 3rd Place -->
                        <div class="flex flex-col items-center w-1/3">
                            <div class="mb-2 text-center text-white/70">
                                <span class="block text-2xl mb-1 filter drop-shadow">🥉</span>
                                <span class="block font-black text-xs md:text-sm uppercase leading-tight truncate w-full">Lucas M.</span>
                                <span class="text-[10px] opacity-80 font-mono">850 pts</span>
                            </div>
                            <!-- Bar (Bronze) -->
                            <div class="w-full bg-gradient-to-t from-amber-800 to-amber-500 rounded-t-lg h-16 border-t-2 border-x-2 border-white/20 relative flex items-end justify-center pb-2 animate-glow-bronze">
                                <span class="text-white/20 font-black text-xl md:text-3xl opacity-50">3</span>
                            </div>
                        </div>
                    </div>

                    <!-- Logged In User Row (Position 4 - Fixed Layout) -->
                    <div class="show-if-logged-in mt-auto">
                        <div class="flex justify-center mb-1">
                                <span class="text-[10px] font-mono text-white/60 bg-black/30 px-2 rounded flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                                    430 pts de retard sur Lucas M.
                                </span>
                        </div>
                        <div class="relative py-2 px-3 bg-white/10 rounded border border-white/30">
                            <div class="flex items-center gap-3">
                                <div class="text-xl animate-bounce-slow flex-shrink-0">👉</div>
                                <div class="flex-grow min-w-0">
                                    <div class="flex justify-between text-sm font-black uppercase text-white mb-1">
                                        <span class="truncate mr-2">#4 TOI (L'Outsider)</span>
                                        <span class="text-passion-fire-yellow flex-shrink-0">420 pts</span>
                                    </div>
                                    <div class="w-full bg-black/40 h-3 rounded-full overflow-hidden border border-white/10">
                                        <div class="h-full bg-passion-fire-yellow progress-bar-striped w-[25%]"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Utility Message Bottom -->
                    <p class="mt-4 text-[10px] md:text-xs text-center text-white/60 border-t border-white/10 pt-2">
                        💡 Les points servent à payer tes Allos.
                    </p>

                    <!-- Bottom of Leaderboard column -->
                    <div class="mt-4 pt-4 border-t border-white/10 text-center">
                        <a href="/classement" class="inline-block px-4 py-2 bg-white/10 hover:bg-white/20 rounded text-xs font-bold uppercase tracking-wider text-white transition-colors">
                            Voir tout le classement +
                        </a>
                    </div>
                </div>

                <!-- Right: Daily Game Card (Modes: Action / Input / QCM) -->
                <div id="game-card" class="game-mode-action relative bg-passion-pink-500/20 p-6 rounded border-2 border-dashed border-passion-fire-yellow flex flex-col items-center justify-center text-center transition-colors hover:bg-passion-pink-500/30 min-h-[300px]">

                    <!-- Header Icon (Added margin bottom) -->
                    <div class="text-4xl mb-1 animate-bounce-slow">🎮</div>

                    <h3 class="font-display font-black text-xl md:text-2xl uppercase text-white mb-4 mt-0">
                        Le Défi du Jour
                    </h3>

                    <!-- MODE 1: ACTION IRL (Replaced old announce) -->
                    <div class="show-if-action w-full flex flex-col items-center">
                        <!-- Added ID for easier JS targeting -->
                        <div id="action-preview-container" class="mb-1 flex items-center justify-center min-h-[40px]">
                            <span class="text-4xl">📸</span>
                        </div>
                        <p class="text-sm text-passion-pink-100 mb-1 font-bold uppercase">
                            "Paparazzi du Bureau"
                        </p>
                        <p class="text-xs text-white/70 mb-6">
                            Prends un selfie avec un membre du bureau (si tu le trouves).
                        </p>

                        <!-- Guest Button (Fixed padding and text size) -->
                        <button class="show-if-guest bg-transparent border-2 border-white text-white font-black uppercase px-4 py-3 md:px-6 md:py-3 rounded text-xs md:text-sm hover:bg-white hover:text-[#9B1237] transition-all whitespace-nowrap">
                            Connecte-toi pour valider
                        </button>

                        <!-- Logged In Button -->
                        <button id="btn-action-upload" onclick="triggerFileSelection()" class="show-if-logged-in bg-passion-fire-yellow text-[#9B1237] font-black uppercase px-6 py-3 rounded shadow-[4px_4px_0px_#000] hover:translate-y-1 hover:shadow-[2px_2px_0px_#000] transition-all flex flex-col items-center leading-none gap-1">
                            <span>J'ai la photo !</span>
                            <span class="text-[10px] opacity-80 font-mono">+100 pts à gagner</span>
                        </button>
                        <!-- Hidden File Input -->
                        <input type="file" id="proof-file" class="hidden" accept="image/*,video/*" onchange="handleFileSelected(this)">

                        <span id="action-status-text" class="text-[10px] mt-3 uppercase tracking-widest opacity-60">Preuve requise (plus tard)</span>
                    </div>

                    <!-- MODE 2: INPUT QUESTION (Type Answer) -->
                    <div class="show-if-input w-full">
                        <div class="text-left w-full mb-4">
                            <span class="text-[10px] uppercase font-bold text-passion-fire-yellow bg-black/40 px-2 py-0.5 rounded">Question 1/3</span>
                            <p class="text-white font-bold text-lg leading-tight mt-2">
                                Quel est le surnom officiel du trésorier cette année ?
                            </p>
                        </div>

                        <input type="text" id="game-input" placeholder="Ta réponse..." class="w-full bg-black/30 border-2 border-white/30 rounded p-3 text-white placeholder-white/50 font-mono text-sm focus:border-passion-fire-yellow focus:outline-none mb-4 transition-colors">

                        <button onclick="validateInput()" class="w-full bg-white text-[#9B1237] font-black uppercase px-4 py-3 rounded hover:bg-passion-pink-100 transition-colors">
                            Valider
                        </button>

                        <div id="input-feedback" class="h-6 mt-2 text-xs font-bold uppercase tracking-wider"></div>
                    </div>

                    <!-- MODE 3: QCM (Multiple Choice) -->
                    <div class="show-if-qcm w-full">
                        <div class="text-left w-full mb-4">
                            <span class="text-[10px] uppercase font-bold text-passion-fire-yellow bg-black/40 px-2 py-0.5 rounded">Question Bonus</span>
                            <p class="text-white font-bold text-lg leading-tight mt-2">
                                Quelle est la capitale du divertissement (selon le BDS) ?
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-2 w-full mb-1">
                            <button onclick="checkQcm(this, false)" class="qcm-option w-full text-left bg-black/30 border-2 border-white/10 hover:border-white/50 rounded p-3 text-sm text-white font-medium transition-all">
                                A. La Bibliothèque
                            </button>
                            <button onclick="checkQcm(this, true)" class="qcm-option w-full text-left bg-black/30 border-2 border-white/10 hover:border-white/50 rounded p-3 text-sm text-white font-medium transition-all">
                                B. Le Foyer
                            </button>
                            <button onclick="checkQcm(this, false)" class="qcm-option w-full text-left bg-black/30 border-2 border-white/10 hover:border-white/50 rounded p-3 text-sm text-white font-medium transition-all">
                                C. L'Amphi A
                            </button>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>
@endsection
