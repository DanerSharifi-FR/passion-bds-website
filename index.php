<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <title>P'AS'SION BDS - Accueil</title>
    <meta name="description" content="Site de la campagne BDS P'AS'SION IMT Atlantique Nantes.">
    <meta name="author" content="Daner Sharifi">
    <meta name="robots" content="index, follow">

    <!-- Mobile Web App -->
    <meta name="theme-color" content="#9B1237">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="format-detection" content="telephone=no">

    <!-- Open Graph / Social Media -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://passion-bds.fr/">
    <meta property="og:title" content="P'AS'SION : Bureau Des Sports">
    <meta property="og:description" content="Site de la campagne BDS P'AS'SION IMT Atlantique Nantes.">
    <meta property="og:image" content="https://placehold.co/1200x630/9B1237/FFC94A?text=PASSION+BDS">

    <!-- Favicons -->
    <link rel="icon" type="image/png" href="https://placehold.co/32x32/FF914D/9B1237?text=P">
    <link rel="apple-touch-icon" href="https://placehold.co/180x180/FF914D/9B1237?text=P">

    <!-- Fonts: Inter & Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Poppins:wght@500;700;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Custom Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        passion: {
                            pink: {
                                100: '#FFF0F5',
                                200: '#FCE2EC',
                                300: '#F9D7E5',
                                400: '#F4BBD2',
                                500: '#E4476A', // Heart accent
                            },
                            fire: {
                                orange: '#FF914D',
                                yellow: '#FFC94A',
                            },
                            red: '#9B1237', // Main text
                        }
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'float-delayed': 'float 7s ease-in-out 2s infinite',
                        'float-delayed-2': 'float 5s ease-in-out 1s infinite',
                        'bounce-slow': 'bounce-slow 3s infinite',
                        'pulse-fast': 'pulse 1s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'shake': 'shake 0.5s cubic-bezier(.36,.07,.19,.97) both',
                        'confetti': 'confetti 0.5s ease-out forwards',
                        'glow-gold': 'glow-gold 2s ease-in-out infinite alternate',
                        'glow-silver': 'glow-silver 2s ease-in-out infinite alternate',
                        'glow-bronze': 'glow-bronze 2s ease-in-out infinite alternate',
                        'spin-slow': 'spin 3s linear infinite',
                        'pop': 'pop 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275)',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-6px)' },
                        },
                        'bounce-slow': {
                            '0%, 100%': { transform: 'translateY(-5%)', animationTimingFunction: 'cubic-bezier(0.8, 0, 1, 1)' },
                            '50%': { transform: 'translateY(0)', animationTimingFunction: 'cubic-bezier(0, 0, 0.2, 1)' },
                        },
                        shake: {
                            '10%, 90%': { transform: 'translate3d(-1px, 0, 0)' },
                            '20%, 80%': { transform: 'translate3d(2px, 0, 0)' },
                            '30%, 50%, 70%': { transform: 'translate3d(-4px, 0, 0)' },
                            '40%, 60%': { transform: 'translate3d(4px, 0, 0)' }
                        },
                        'glow-gold': {
                            '0%': { boxShadow: '0 0 5px #CA8A04, 0 0 10px #FACC15' },
                            '100%': { boxShadow: '0 0 20px #CA8A04, 0 0 30px #FACC15' }
                        },
                        'glow-silver': {
                            '0%': { boxShadow: '0 0 5px #6B7280, 0 0 10px #D1D5DB' },
                            '100%': { boxShadow: '0 0 20px #6B7280, 0 0 30px #D1D5DB' }
                        },
                        'glow-bronze': {
                            '0%': { boxShadow: '0 0 5px #92400E, 0 0 10px #D97706' },
                            '100%': { boxShadow: '0 0 20px #92400E, 0 0 30px #D97706' }
                        },
                        pop: {
                            '0%': { transform: 'scale(0.5)', opacity: 0 },
                            '100%': { transform: 'scale(1)', opacity: 1 }
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* --- GLOBAL TEXTURE --- */
        body {
            background-color: #FFF0F5;
            background-image: radial-gradient(#E4476A 1px, transparent 1px);
            background-size: 30px 30px; /* Jersey mesh vibe */
            color: #9B1237;
            overflow-x: hidden;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* --- DYNAMIC SHAPES --- */
        .skew-box {
            transform: skewX(-6deg);
        }
        .unskew-text {
            transform: skewX(6deg);
        }

        /* --- MACHINE CARD --- */
        .machine-container {
            background: rgba(255, 255, 255, 0.95);
            border: 4px solid #9B1237;
            box-shadow: 12px 12px 0px #FF914D; /* Hard shadow, retro sport style */
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        /* --- FAN ZONE (New) --- */
        .fan-zone-container {
            background: #9B1237;
            color: white;
            box-shadow: -8px 8px 0px #000;
        }

        /* User State Visibility */
        .state-logged-in .show-if-guest { display: none !important; }
        .state-guest .show-if-logged-in { display: none !important; }

        /* Game Modes Visibility */
        .game-mode-action .show-if-input, .game-mode-action .show-if-qcm { display: none !important; }
        .game-mode-input .show-if-action, .game-mode-input .show-if-qcm { display: none !important; }
        .game-mode-qcm .show-if-action, .game-mode-qcm .show-if-input { display: none !important; }

        /* --- RAIN ANIMATION --- */
        #rain-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1; /* Behind everything */
            overflow: hidden;
        }
        .rain-icon {
            position: absolute;
            top: -50px;
            user-select: none;
            animation-name: fall;
            animation-timing-function: linear;
            animation-iteration-count: infinite;
        }
        @keyframes fall {
            0% { transform: translateY(0) rotate(0deg); opacity: 0; }
            10% { opacity: 0.4; }
            90% { opacity: 0.4; }
            100% { transform: translateY(110vh) rotate(360deg); opacity: 0; }
        }

        /* --- STATES --- */
        /* CLOSED STATE */
        .machine-state-closed .allo-real-content {
            opacity: 0.1;
            filter: grayscale(100%) blur(2px);
            pointer-events: none;
        }
        .machine-state-closed .allo-mystery-overlay {
            display: flex;
        }
        .machine-state-closed .status-indicator {
            background-color: #E4476A;
            color: white;
        }
        .machine-state-closed .main-btn {
            background: white;
            color: #9B1237;
            border: 3px solid #9B1237;
        }

        /* OPEN STATE */
        .machine-state-open .allo-real-content {
            opacity: 1;
            filter: none;
        }
        .machine-state-open .allo-mystery-overlay {
            display: none;
        }
        .machine-state-open .status-indicator {
            background-color: #FF914D; /* Fire Orange */
            color: #9B1237;
            animation: pulse 2s infinite;
        }
        .machine-state-open .main-btn {
            background: #FF914D;
            color: #9B1237;
            border: 3px solid #9B1237;
            box-shadow: 4px 4px 0 #9B1237;
        }
        .machine-state-open .main-btn:hover {
            transform: translate(-2px, -2px);
            box-shadow: 6px 6px 0 #9B1237;
        }

        /* --- UTILS --- */
        .text-outline {
            text-shadow:
                    -1px -1px 0 #fff,
                    1px -1px 0 #fff,
                    -1px  1px 0 #fff,
                    1px  1px 0 #fff;
        }

        /* Progress Bar Striped */
        .progress-bar-striped {
            background-image: linear-gradient(45deg,rgba(255,255,255,.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,.15) 50%,rgba(255,255,255,.15) 75%,transparent 75%,transparent);
            background-size: 1rem 1rem;
        }

        /* Feedback Animations */
        .shake-element { animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both; }
        .success-pulse { animation: pulse-fast 0.5s ease-in-out; border-color: #4ade80 !important; }
        .error-border { border-color: #f87171 !important; background-color: rgba(248, 113, 113, 0.1) !important; }

        @media (prefers-reduced-motion: reduce) {
            *, ::before, ::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }
    </style>
</head>
<body class="font-sans antialiased selection:bg-passion-fire-orange selection:text-white pb-20 lg:pb-0">

<!-- Rain Container -->
<div id="rain-container"></div>

<!-- DESKTOP Header (Sticky) -->
<header class="hidden lg:flex fixed top-0 w-full z-50 bg-white/95 backdrop-blur-sm border-b-4 border-passion-red">
    <div class="max-w-7xl mx-auto px-4 h-20 flex items-center justify-between w-full">
        <!-- Logo -->
        <div class="flex items-center gap-3 group cursor-pointer select-none">
            <!-- LOGO IMAGE (Rounded Full) -->
            <img src="logo.png" alt="Logo BDS" class="h-14 w-14 object-contain animate-bounce-slow drop-shadow-md rounded-full">

            <div class="flex flex-col items-end leading-none skew-box">
                <span class="font-display font-black text-2xl text-passion-fire-orange tracking-tighter">P'AS'SION</span>
                <span class="font-display font-black text-xl text-passion-red tracking-tighter -mt-1">BDS</span>
            </div>
        </div>

        <!-- Desktop Nav -->
        <nav class="flex gap-6 items-center">
            <a href="/poles" class="relative group py-2">
                <div class="absolute inset-0 bg-passion-pink-300 skew-box transform scale-y-0 group-hover:scale-y-100 transition-transform origin-bottom"></div>
                <span class="relative font-display font-black text-lg text-passion-red uppercase tracking-wide px-2 group-hover:text-passion-red transition-colors">La team P'AS'SION</span>
            </a>
            <a href="/shop" class="relative group py-2">
                <div class="absolute inset-0 bg-passion-pink-300 skew-box transform scale-y-0 group-hover:scale-y-100 transition-transform origin-bottom"></div>
                <span class="relative font-display font-black text-lg text-passion-red uppercase tracking-wide px-2 group-hover:text-passion-red transition-colors">Le Shop</span>
            </a>
            <a href="/galerie" class="relative group py-2">
                <div class="absolute inset-0 bg-passion-pink-300 skew-box transform scale-y-0 group-hover:scale-y-100 transition-transform origin-bottom"></div>
                <span class="relative font-display font-black text-lg text-passion-red uppercase tracking-wide px-2 group-hover:text-passion-red transition-colors">Galerie</span>
            </a>
            <a href="/allos" class="relative group py-2">
                <div class="absolute inset-0 bg-passion-fire-orange skew-box transform scale-y-0 group-hover:scale-y-100 transition-transform origin-bottom"></div>
                <span class="relative font-display font-black text-lg text-passion-red uppercase tracking-wide px-2 group-hover:text-white transition-colors">Allos</span>
            </a>
        </nav>
    </div>
</header>

<!-- MOBILE/TABLET Header (Not Fixed, Centered) -->
<header class="lg:hidden w-full pt-8 pb-4 flex flex-col items-center justify-center bg-transparent relative z-10">
    <div class="flex items-center gap-3 select-none transform">
        <!-- LOGO IMAGE (Rounded Full) -->
        <img src="logo.png" alt="Logo BDS" class="h-16 w-16 object-contain animate-bounce-slow drop-shadow-lg rounded-full">

        <div class="flex flex-col items-end gap-0 skew-box leading-none">
            <!-- Stacked Layout: BDS Right Aligned -->
            <span class="font-display font-black text-4xl text-passion-fire-orange tracking-tighter drop-shadow-sm">P'AS'SION</span>
            <span class="font-display font-black text-2xl text-passion-red tracking-tighter -mt-1">BDS</span>
        </div>
    </div>

    <!-- Visual Separator (Dashed Line + Icon) -->
    <div class="w-2/3 flex items-center justify-center gap-4 mt-6 opacity-60">
        <div class="h-px bg-passion-red/30 w-full border-b-2 border-dashed border-passion-red/30"></div>
        <div class="text-passion-red text-xs">▼</div>
        <div class="h-px bg-passion-red/30 w-full border-b-2 border-dashed border-passion-red/30"></div>
    </div>
</header>

<!-- Main Content -->
<main class="flex-grow px-4 flex flex-col items-center justify-start relative w-full overflow-hidden lg:pt-32 pt-8">

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
                        💡 Les points servent à ...
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

</main>

<!-- MOBILE BOTTOM NAVIGATION (Fixed) -->
<nav class="lg:hidden fixed bottom-0 w-full bg-white border-t border-passion-pink-200 z-50 flex justify-around items-center pb-safe pt-2 h-16">
    <a href="/" class="flex flex-col items-center p-2 text-passion-red hover:text-passion-fire-orange transition-colors group w-1/5">
        <!-- Icon Home -->
        <svg class="w-6 h-6 mb-1 transition-transform group-hover:-translate-y-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
        <span class="text-[9px] font-bold uppercase tracking-wider">Accueil</span>
    </a>
    <a href="/poles" class="flex flex-col items-center p-2 text-gray-400 hover:text-passion-red transition-colors group w-1/5 text-center">
        <!-- Icon Team -->
        <svg class="w-6 h-6 mb-1 transition-transform group-hover:-translate-y-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        <span class="text-[8px] leading-tight font-bold uppercase tracking-wider">La team P'AS'SION</span>
    </a>
    <a href="/shop" class="flex flex-col items-center p-2 text-gray-400 hover:text-passion-red transition-colors group w-1/5">
        <!-- Icon Shopping Bag -->
        <svg class="w-6 h-6 mb-1 transition-transform group-hover:-translate-y-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
        <span class="text-[9px] font-bold uppercase tracking-wider">Le Shop</span>
    </a>
    <a href="/galerie" class="flex flex-col items-center p-2 text-gray-400 hover:text-passion-red transition-colors group w-1/5">
        <!-- Icon Gallery -->
        <svg class="w-6 h-6 mb-1 transition-transform group-hover:-translate-y-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
        <span class="text-[9px] font-bold uppercase tracking-wider">Galerie</span>
    </a>
    <a href="/allos" class="flex flex-col items-center p-2 text-gray-400 hover:text-passion-red transition-colors group relative w-1/5">
        <!-- Icon Fire -->
        <div class="absolute -top-1 right-1/4 w-3 h-3 bg-passion-fire-orange rounded-full animate-pulse"></div>
        <svg class="w-6 h-6 mb-1 transition-transform group-hover:-translate-y-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"></path></svg>
        <span class="text-[9px] font-bold uppercase tracking-wider">Allos</span>
    </a>
</nav>

<!-- Footer -->
<footer class="bg-passion-red text-white py-8 border-t-4 border-passion-fire-yellow relative z-20">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h2 class="font-display font-black text-2xl uppercase tracking-tighter mb-2 animate-pulse">P'AS'SION BDS</h2>
        <p class="font-mono text-sm opacity-80">Campagne 2025 // Don't Stop The Game</p>
        <p class="text-[10px] mt-2 opacity-50">Si tu lis ça, tu devrais réviser.</p>
    </div>
</footer>

<!-- Dev Tool (Moved up on mobile to avoid overlap) -->
<div class="fixed bottom-24 right-4 z-50 flex flex-col gap-2 items-end opacity-50 hover:opacity-100 transition-opacity">
    <button onclick="toggleUser()" class="bg-blue-600 text-white px-4 py-2 font-mono text-xs border-2 border-white shadow-lg uppercase hover:bg-blue-700 transition-colors">
        👤 Toggle User
    </button>
    <button onclick="cycleGameMode()" class="bg-purple-600 text-white px-4 py-2 font-mono text-xs border-2 border-white shadow-lg uppercase hover:bg-purple-700 transition-colors">
        👀 Toggle Game
    </button>
    <button onclick="toggleState()" class="bg-gray-900 text-white px-4 py-2 font-mono text-xs border-2 border-white shadow-lg uppercase hover:bg-black transition-colors">
        ⚡ Toggle Allos
    </button>
</div>

<script>
    // ... (Rain Scripts remain identical) ...
    const icons = ['⚽', '🏀', '🏉', '🎾', '🎉', '🍻', '🍹', '🎵', '🔥', '💤', '📚', '🍕', '🏆', '🥐'];
    const container = document.getElementById('rain-container');

    function createRain() {
        const count = 15; // Low count for performance
        for(let i=0; i<count; i++) {
            const icon = document.createElement('div');
            icon.classList.add('rain-icon');
            icon.innerText = icons[Math.floor(Math.random() * icons.length)];
            icon.style.left = Math.random() * 100 + 'vw';
            icon.style.animationDuration = Math.random() * 10 + 10 + 's'; // 10-20s fall
            icon.style.animationDelay = Math.random() * 20 + 's'; // random start
            icon.style.fontSize = Math.random() * 20 + 20 + 'px'; // random size
            container.appendChild(icon);
        }
    }
    createRain();

    function updateUI(state) {
        const statusBadge = document.querySelector('.status-indicator');
        const statusText = document.querySelector('.status-text-content');
        const msg = document.getElementById('status-message');
        const btnText = document.querySelector('.btn-text');

        if (state === 'closed') {
            statusBadge.style.backgroundColor = '#9B1237';
            statusBadge.style.color = 'white';
            statusText.textContent = "WARM UP";
            msg.textContent = "L'échauffement est en cours. Reviens plus tard.";
            btnText.textContent = "Voir le stade";
        } else {
            statusBadge.style.backgroundColor = '#FF914D';
            statusBadge.style.color = '#9B1237';
            statusText.textContent = "GAME ON";
            msg.textContent = "C'est parti mon grand. Fais tes choix.";
            btnText.textContent = "Lancer un Allo";
        }
    }

    function toggleState() {
        const card = document.getElementById('machine-card');
        if (card.classList.contains('machine-state-closed')) {
            card.classList.remove('machine-state-closed');
            card.classList.add('machine-state-open');
            updateUI('open');
        } else {
            card.classList.remove('machine-state-open');
            card.classList.add('machine-state-closed');
            updateUI('closed');
        }
    }

    function toggleUser() {
        const arcadeZone = document.getElementById('arcade-zone');
        if (arcadeZone.classList.contains('state-guest')) {
            arcadeZone.classList.remove('state-guest');
            arcadeZone.classList.add('state-logged-in');
        } else {
            arcadeZone.classList.remove('state-logged-in');
            arcadeZone.classList.add('state-guest');
        }
    }

    // --- GAME LOGIC ---
    const gameCard = document.getElementById('game-card');
    const modes = ['game-mode-action', 'game-mode-input', 'game-mode-qcm'];
    let currentModeIndex = 0;

    function cycleGameMode() {
        // Remove current mode class
        gameCard.classList.remove(modes[currentModeIndex]);

        // Go to next
        currentModeIndex = (currentModeIndex + 1) % modes.length;

        // Add new mode class
        gameCard.classList.add(modes[currentModeIndex]);

        // Reset UI states if needed
        resetGameUI();
    }

    function resetGameUI() {
        const feedback = document.getElementById('input-feedback');
        const input = document.getElementById('game-input');
        const uploadBtn = document.getElementById('btn-action-upload');
        const previewContainer = document.getElementById('action-preview-container');
        const statusText = document.getElementById('action-status-text');

        if(feedback) feedback.textContent = '';
        if(input) {
            input.value = '';
            input.classList.remove('error-border', 'success-pulse', 'shake-element');
        }
        if(uploadBtn) {
            uploadBtn.className = "show-if-logged-in bg-passion-fire-yellow text-[#9B1237] font-black uppercase px-6 py-3 rounded shadow-[4px_4px_0px_#000] hover:translate-y-1 hover:shadow-[2px_2px_0px_#000] transition-all flex flex-col items-center leading-none gap-1";
            uploadBtn.innerHTML = '<span>J\'ai la photo !</span><span class="text-[10px] opacity-80 font-mono">+100 pts à gagner</span>';
        }
        if(previewContainer) {
            previewContainer.innerHTML = '<span class="text-4xl">📸</span>';
            previewContainer.className = "mb-1 flex items-center justify-center min-h-[40px]"; // Reset height
        }
        if(statusText) {
            statusText.textContent = "Preuve requise (plus tard)";
            statusText.className = "text-[10px] mt-3 uppercase tracking-widest opacity-60";
        }

        document.querySelectorAll('.qcm-option').forEach(btn => {
            btn.classList.remove('bg-green-500', 'bg-red-500', 'border-white');
            btn.classList.add('bg-black/30', 'border-white/10');
        });
    }

    function triggerFileSelection() {
        document.getElementById('proof-file').click();
    }

    function handleFileSelected(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const btn = document.getElementById('btn-action-upload');
            const previewContainer = document.getElementById('action-preview-container');
            const statusText = document.getElementById('action-status-text');

            // Simulate Upload State
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<span class="animate-pulse">Envoi en cours...</span>';

            setTimeout(() => {
                // Create Preview URL
                const url = URL.createObjectURL(file);

                // Show Preview (Adjusted sizing for mobile)
                previewContainer.innerHTML = `<img src="${url}" class="h-32 md:h-40 w-auto object-contain rounded border-2 border-white shadow-sm animate-pop" alt="Preview">`;
                previewContainer.className = "mb-2 flex items-center justify-center"; // Adjust container for image

                // Update Button to "Change" state
                btn.className = "show-if-logged-in bg-white/20 border-2 border-white/50 text-white font-black uppercase px-4 py-2 rounded hover:bg-white/30 transition-all flex flex-col items-center leading-none gap-1";
                btn.innerHTML = '<span>🔄 Changer la photo</span>';

                // Update Status Message (Playful)
                statusText.innerHTML = "🏁 Bien reçu ! Le jury BDS va juger ça (sois patient).";
                statusText.className = "text-[10px] mt-3 uppercase tracking-widest text-passion-fire-yellow font-bold animate-pulse";

            }, 1500);
        }
    }

    function validateInput() {
        const input = document.getElementById('game-input');
        const feedback = document.getElementById('input-feedback');
        const val = input.value.trim().toLowerCase();

        // Mock correct answer
        if (val === 'picsou' || val === 'radin') {
            input.classList.remove('error-border', 'shake-element');
            input.classList.add('success-pulse');
            feedback.textContent = "✅ Bonne réponse ! (+10 pts)";
            feedback.className = "h-6 mt-2 text-xs font-bold uppercase tracking-wider text-green-400";
        } else {
            input.classList.remove('success-pulse');
            input.classList.add('error-border', 'shake-element');
            feedback.textContent = "❌ Faux. Essaye 'Picsou'.";
            feedback.className = "h-6 mt-2 text-xs font-bold uppercase tracking-wider text-red-400";

            // Re-trigger shake animation hack
            setTimeout(() => input.classList.remove('shake-element'), 500);
        }
    }

    function checkQcm(btn, isCorrect) {
        // Reset others
        document.querySelectorAll('.qcm-option').forEach(b => {
            b.classList.remove('bg-green-500', 'bg-red-500', 'border-white');
            b.classList.add('opacity-50');
        });

        btn.classList.remove('opacity-50', 'bg-black/30', 'border-white/10');
        btn.classList.add('border-white');

        if (isCorrect) {
            btn.classList.add('bg-green-500');
            // Trigger Confetti or Points animation here
        } else {
            btn.classList.add('bg-red-500', 'shake-element');
            setTimeout(() => btn.classList.remove('shake-element'), 500);
        }
    }

    // Init
    updateUI('closed');
</script>
</body>

</html>
