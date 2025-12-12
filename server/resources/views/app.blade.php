<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"
    >

    <title>@yield('title', "P'AS'SION BDS - Accueil")</title>
    <meta name="description"
          content="@yield('meta_description', "Site de la campagne BDS P'AS'SION IMT Atlantique Nantes.")">
    <meta name="author" content="Daner Sharifi">
    <meta name="robots" content="index, follow">

    {{-- Mobile Web App --}}
    <meta name="theme-color" content="#9B1237">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="format-detection" content="telephone=no">

    {{-- Open Graph / Social Media --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="@yield('og_url', 'https://passion-bds.fr/')">
    <meta property="og:title" content="@yield('title', "P'AS'SION BDS - Accueil")">
    <meta property="og:description"
          content="@yield('meta_description', "Site de la campagne BDS P'AS'SION IMT Atlantique Nantes.")">
    <meta property="og:image"
          content="@yield('og_image', 'https://placehold.co/1200x630/9B1237/FFC94A?text=PASSION+BDS')">

    {{-- Favicons (change later if you want real files) --}}
    <link rel="icon" type="image/png" href="https://placehold.co/32x32/FF914D/9B1237?text=P">
    <link rel="apple-touch-icon" href="https://placehold.co/180x180/FF914D/9B1237?text=P">

    <style>
        :root{
            --passion-red:#9B1237;
            --passion-fire-orange:#FF914D;
            --passion-fire-yellow:#FFC94A;
            --passion-pink-300:#E7A3AB;
        }

        #pbds-loader-overlay {
            position: fixed;
            inset: 0;
            z-index: 2147483647;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(1200px 700px at 50% 18%, color-mix(in oklab, var(--passion-fire-orange, #FF914D) 22%, transparent), transparent 60%),
            radial-gradient(900px 520px at 70% 82%, color-mix(in oklab, var(--passion-fire-yellow, #FFC94A) 18%, transparent), transparent 55%),
            linear-gradient(#050206, #0b0610);
            opacity: 1;
            transform: scale(1);
            transition: opacity .22s ease, transform .22s ease;
        }

        #pbds-loader-overlay[data-hidden="1"] {
            opacity: 0;
            transform: scale(1.02);
            pointer-events: none;
        }

        #pbds-loader-overlay::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: repeating-linear-gradient(
                to bottom,
                rgba(255, 255, 255, .06) 0px,
                rgba(255, 255, 255, .06) 1px,
                transparent 2px,
                transparent 7px
            );
            opacity: .14;
        }

        #pbds-loader-overlay::after {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: radial-gradient(circle at 50% 50%, transparent 0%, transparent 55%, rgba(0,0,0,.45) 80%);
        }

        #pbds-loader-overlay .pbds-loader-card {
            position: relative;
            width: min(460px, calc(100vw - 32px));
            padding: 22px 22px 18px;
            border-radius: 18px;
            border: 2px solid color-mix(in oklab, var(--passion-fire-yellow, #FFC94A) 70%, transparent);
            background: rgba(0, 0, 0, .58);
            box-shadow: 0 18px 70px rgba(0, 0, 0, .6), 6px 6px 0 color-mix(in oklab, var(--passion-fire-yellow, #FFC94A) 90%, transparent);
            backdrop-filter: blur(6px);
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
        }

        #pbds-loader-overlay .pbds-loader-header {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        #pbds-loader-overlay .pbds-loader-logo {
            width: 54px;
            height: 54px;
            object-fit: contain;
            border-radius: 999px; /* 100% */
            filter: drop-shadow(0 10px 18px rgba(0, 0, 0, .55));
        }

        #pbds-loader-overlay .pbds-loader-title {
            display: flex;
            flex-direction: column;
            gap: 6px;
            line-height: 1.1;
        }

        #pbds-loader-overlay .pbds-loader-title b {
            letter-spacing: .12em;
            text-transform: uppercase;
            font-weight: 800;
            font-size: 14px;
            color: rgba(255, 255, 255, .92);
        }

        #pbds-loader-overlay .pbds-loader-title span {
            font-weight: 700;
            font-size: 12px;
            color: rgba(255, 255, 255, .68);
        }

        #pbds-loader-overlay .pbds-loader-row {
            margin-top: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }

        #pbds-loader-overlay .pbds-loader-spinner {
            width: 34px;
            height: 34px;
            border-radius: 999px;
            border: 3px solid rgba(255, 255, 255, .10);
            border-top-color: var(--passion-fire-yellow, #FFC94A);
            border-right-color: var(--passion-fire-orange, #FF914D);
            box-shadow: 0 0 0 2px rgba(0, 0, 0, .35) inset,
            0 0 22px color-mix(in oklab, var(--passion-fire-orange, #FF914D) 35%, transparent);
            animation: pbds-spin .9s linear infinite;
        }

        @keyframes pbds-spin {
            to {
                transform: rotate(360deg);
            }
        }

        #pbds-loader-overlay .pbds-loader-badge {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, .12);
            background: rgba(255, 255, 255, .05);
            box-shadow: 2px 2px 0 color-mix(in oklab, var(--passion-red, #9B1237) 65%, transparent);
            color: rgba(255, 255, 255, .86);
            font-weight: 700;
            font-size: 12px;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        #pbds-loader-overlay .pbds-loader-progress-track {
            margin-top: 14px;
            height: 10px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .08);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .10);
        }

        #pbds-loader-overlay .pbds-loader-progress-bar {
            display: block;
            height: 100%;
            width: 42%;
            background: linear-gradient(90deg, var(--passion-red, #9B1237), var(--passion-fire-orange, #FF914D), var(--passion-fire-yellow, #FFC94A));
            filter: saturate(1.15);
            animation: pbds-bar 1.05s ease-in-out infinite;
        }

        @keyframes pbds-bar {
            0% {
                transform: translateX(-120%);
            }
            50% {
                transform: translateX(40%);
            }
            100% {
                transform: translateX(220%);
            }
        }

        #pbds-loader-overlay .pbds-loader-footer {
            margin-top: 10px;
            color: rgba(255, 255, 255, .70);
            font-size: 12px;
            text-align: center;
        }

        @media (prefers-reduced-motion: reduce) {
            #pbds-loader-overlay .pbds-loader-spinner,
            #pbds-loader-overlay .pbds-loader-progress-bar {
                animation: none !important;
            }
        }
    </style>

    {{-- Fonts: Inter & Poppins --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Poppins:wght@500;700;900&display=swap"
        rel="stylesheet"
    >

    {{-- Tailwind CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    {{-- Your consolidated CSS --}}
    <link rel="stylesheet" href="{{ asset('assets/passion-common.css') }}">

    {{-- Page-specific extra CSS if needed --}}
    @stack('styles')

    {{-- Page-specific extra JS in <head> if really needed --}}
    @stack('head_scripts')
</head>
<body class="font-sans antialiased selection:bg-passion-fire-orange selection:text-white pb-20 lg:pb-0">

<!-- Rain Container -->
<div id="rain-container"></div>

<!-- DESKTOP Header (Sticky) -->
<header class="hidden lg:flex fixed top-0 w-full z-50 bg-white/95 backdrop-blur-sm border-b-4 border-passion-red">
    <div class="max-w-7xl mx-auto px-4 h-20 flex items-center justify-between w-full">
        <!-- Logo -->
        <div class="flex items-center gap-3 group cursor-pointer select-none">
            <a href="{{ route('home') }}"
               class="flex items-center gap-3 transform group-hover:scale-105 transition-transform">
                <!-- LOGO IMAGE (Rounded Full) -->
                <img src="{{ asset('logo.png') }}" alt="Logo BDS"
                     class="h-14 w-14 object-contain animate-bounce-slow drop-shadow-md rounded-full">

                <div class="flex flex-col items-end leading-none skew-box">
                    <span
                        class="font-display font-black text-2xl text-passion-fire-orange tracking-tighter">P'AS'SION</span>
                    <span class="font-display font-black text-xl text-passion-red tracking-tighter -mt-1">BDS</span>
                </div>
            </a>
        </div>

        <!-- Desktop Nav -->
        <nav class="flex gap-6 items-center">
            <a href="{{ route('home') }}" class="relative group py-2">
                <div
                    class="absolute inset-0 bg-passion-pink-300 skew-box @if(request()->routeIs('home')) scale-y-100 @else transform scale-y-0 group-hover:scale-y-100 transition-transform origin-bottom @endif"></div>
                <span
                    class="relative font-display font-black text-lg text-passion-red uppercase tracking-wide px-2 group-hover:text-passion-red transition-colors">Accueil</span>
            </a>
            <a href="{{ route('team') }}" class="relative group py-2">
                <div
                    class="absolute inset-0 bg-passion-pink-300 skew-box @if(request()->routeIs('team')) scale-y-100 @else transform scale-y-0 group-hover:scale-y-100 transition-transform origin-bottom @endif"></div>
                <span
                    class="relative font-display font-black text-lg text-passion-red uppercase tracking-wide px-2 group-hover:text-passion-red transition-colors">La team P'AS'SION</span>
            </a>
            <a href="/shop" class="relative group py-2">
                <div
                    class="absolute inset-0 bg-passion-pink-300 skew-box transform scale-y-0 group-hover:scale-y-100 transition-transform origin-bottom"></div>
                <span
                    class="relative font-display font-black text-lg text-passion-red uppercase tracking-wide px-2 group-hover:text-passion-red transition-colors">Le Shop</span>
            </a>
            <a href="{{ route('gallery') }}" class="relative group py-2">
                <div
                    class="absolute inset-0 bg-passion-pink-300 skew-box @if(request()->routeIs('gallery')) scale-y-100 @else transform scale-y-0 group-hover:scale-y-100 transition-transform origin-bottom @endif"></div>
                <span
                    class="relative font-display font-black text-lg text-passion-red uppercase tracking-wide px-2 group-hover:text-passion-red transition-colors">Galerie</span>
            </a>
            @auth
                <a href="/allos" class="relative group py-2">
                    <div
                        class="absolute inset-0 bg-passion-fire-orange skew-box transform scale-y-0 group-hover:scale-y-100 transition-transform origin-bottom"></div>
                    <span
                        class="relative font-display font-black text-lg text-passion-red uppercase tracking-wide px-2 group-hover:text-white transition-colors">Allos</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="relative group py-2">
                    <div
                        class="absolute inset-0 bg-passion-fire-orange skew-box @if(request()->routeIs('login')) scale-y-100 @else transform scale-y-0 group-hover:scale-y-100 transition-transform origin-bottom @endif"></div>
                    <span
                        class="relative font-display font-black text-lg uppercase tracking-wide px-2 @if(request()->routeIs('login')) text-white @else text-passion-red group-hover:text-white transition-colors @endif">Allos</span>
                </a>
            @endauth
        </nav>
    </div>
</header>

<!-- MOBILE/TABLET Header (Not Fixed, Centered) -->
<header class="lg:hidden w-full pt-8 pb-4 flex flex-col items-center justify-center bg-transparent relative z-10">
    <div class="flex items-center gap-3 select-none transform">
        <!-- LOGO IMAGE (Rounded Full) -->
        <img src="logo.png" alt="Logo BDS"
             class="h-16 w-16 object-contain animate-bounce-slow drop-shadow-lg rounded-full">

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
    @yield('content')
</main>

<!-- MOBILE BOTTOM NAVIGATION (Fixed) -->
<nav
    class="lg:hidden fixed bottom-0 w-full bg-white border-t border-passion-pink-200 z-50 flex justify-around items-center pb-4 pt-3 h-20">
    <a href="/shop" class="flex flex-col items-center p-2 text-gray-400 transition-colors group w-1/5">
        <!-- Icon Shopping Bag -->
        <svg class="w-6 h-6 mb-1 transition-transform group-hover:-translate-y-1" fill="none" stroke="currentColor"
             viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
        </svg>
        <span class="text-[9px] font-bold uppercase tracking-wider">Le Shop</span>
    </a>
    <a href="{{ route('team') }}"
       class="flex flex-col items-center p-2 transition-colors group w-1/5 text-center @if(request()->routeIs('team')) text-passion-red hover:text-passion-fire-orange @else text-gray-400  hover:text-passion-red @endif">
        <!-- Icon Team -->
        <svg class="w-6 h-6 mb-1 transition-transform group-hover:-translate-y-1" fill="none" stroke="currentColor"
             viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <span class="text-[8px] leading-tight font-bold uppercase tracking-wider">La team P'AS'SION</span>
    </a>
    <a href="{{ route('home') }}"
       class="flex flex-col items-center p-2 transition-colors group w-1/5 @if(request()->routeIs('home')) text-passion-red hover:text-passion-fire-orange @else text-gray-400  hover:text-passion-red @endif">
        <!-- Icon Home -->
        <svg class="w-6 h-6 mb-1 transition-transform group-hover:-translate-y-1" fill="none" stroke="currentColor"
             viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
        </svg>
        <span class="text-[9px] font-bold uppercase tracking-wider">Accueil</span>
    </a>
    <a href="{{ route('gallery') }}"
       class="flex flex-col items-center p-2 transition-colors group w-1/5 @if(request()->routeIs('gallery')) text-passion-red hover:text-passion-fire-orange @else text-gray-400  hover:text-passion-red @endif">
        <!-- Icon Gallery -->
        <svg class="w-6 h-6 mb-1 transition-transform group-hover:-translate-y-1" fill="none" stroke="currentColor"
             viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        <span class="text-[9px] font-bold uppercase tracking-wider">Galerie</span>
    </a>
    @auth
        <a href="/allos"
           class="flex flex-col items-center p-2 transition-colors group relative w-1/5 text-gray-400  hover:text-passion-red">
            <!-- Icon Fire -->
            <div
                class="absolute -top-0 md:right-[35%] right-1/4 w-3 h-3 bg-passion-fire-orange rounded-full animate-pulse"></div>
            <svg class="w-6 h-6 mb-1 transition-transform group-hover:-translate-y-1" fill="none" stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"></path>
            </svg>
            <span class="text-[9px] font-bold uppercase tracking-wider">Allos</span>
        </a>
    @else
        <a href="{{ route('login') }}"
           class="flex flex-col items-center p-2 transition-colors group relative w-1/5 @if(request()->routeIs('login')) text-passion-red hover:text-passion-fire-orange @else text-gray-400 hover:text-passion-red @endif">
            <!-- Icon Fire -->
            <div
                class="absolute -top-0 md:right-[35%] right-1/4 w-3 h-3 bg-passion-fire-orange rounded-full animate-pulse"></div>
            <svg class="w-6 h-6 mb-1 transition-transform group-hover:-translate-y-1" fill="none" stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"></path>
            </svg>
            <span class="text-[9px] font-bold uppercase tracking-wider">Allos</span>
        </a>
    @endauth
</nav>

<!-- Footer -->
<footer class="bg-passion-red text-white py-8 border-t-4 border-passion-fire-yellow relative z-20">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h2 class="font-display font-black text-2xl uppercase tracking-tighter mb-2 animate-pulse">P'AS'SION BDS</h2>
        <p class="font-mono text-sm opacity-80">Campagne 2025 // Don't Stop The Game</p>
        <p class="text-[10px] mt-2 opacity-50">Si tu lis ça, tu devrais réviser.</p>
    </div>
</footer>

<script>
    (() => {
        if (window.__PBDS_LOADER_INSTALLED__) return;
        window.__PBDS_LOADER_INSTALLED__ = true;

        const LOADER_CONFIG = {
            overlayId: "pbds-loader-overlay",
            messageId: "pbds-loader-message",
            logoSrc: "{{ asset("logo.png") }}", // change if needed
            defaultMessage: "",
            hideDelayMsAfterLoad: 120,
        };

        let loaderOverlayElement = null;

        // -------- Scroll lock (prevents page move / jump) --------
        let isScrollLocked = false;
        let savedScrollY = 0;
        let savedBodyStyles = null;

        const lockPageScroll = () => {
            if (isScrollLocked) return;
            isScrollLocked = true;

            savedScrollY = window.scrollY || document.documentElement.scrollTop || 0;
            const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;

            savedBodyStyles = {
                position: document.body.style.position,
                top: document.body.style.top,
                left: document.body.style.left,
                right: document.body.style.right,
                width: document.body.style.width,
                paddingRight: document.body.style.paddingRight,
                overflow: document.body.style.overflow,
                htmlOverflow: document.documentElement.style.overflow,
            };

            document.documentElement.style.overflow = "hidden";
            document.body.style.overflow = "hidden";

            document.body.style.position = "fixed";
            document.body.style.top = `-${savedScrollY}px`;
            document.body.style.left = "0";
            document.body.style.right = "0";
            document.body.style.width = "100%";

            if (scrollbarWidth > 0) {
                document.body.style.paddingRight = `${scrollbarWidth}px`;
            }
        };

        const unlockPageScroll = () => {
            if (!isScrollLocked) return;
            isScrollLocked = false;

            if (savedBodyStyles) {
                document.body.style.position = savedBodyStyles.position;
                document.body.style.top = savedBodyStyles.top;
                document.body.style.left = savedBodyStyles.left;
                document.body.style.right = savedBodyStyles.right;
                document.body.style.width = savedBodyStyles.width;
                document.body.style.paddingRight = savedBodyStyles.paddingRight;
                document.body.style.overflow = savedBodyStyles.overflow;
                document.documentElement.style.overflow = savedBodyStyles.htmlOverflow;
            } else {
                document.body.style.position = "";
                document.body.style.top = "";
                document.body.style.left = "";
                document.body.style.right = "";
                document.body.style.width = "";
                document.body.style.paddingRight = "";
                document.body.style.overflow = "";
                document.documentElement.style.overflow = "";
            }

            window.scrollTo(0, savedScrollY);
        };
        // ---------------------------------------------------------

        const getLoaderMessageNode = () => document.getElementById(LOADER_CONFIG.messageId);

        const ensureLoaderOverlayExists = () => {
            if (loaderOverlayElement && document.getElementById(LOADER_CONFIG.overlayId)) return;

            const existing = document.getElementById(LOADER_CONFIG.overlayId);
            if (existing) {
                loaderOverlayElement = existing;
                return;
            }

            loaderOverlayElement = document.createElement("div");
            loaderOverlayElement.id = LOADER_CONFIG.overlayId;

            loaderOverlayElement.innerHTML = `
      <div class="pbds-loader-card" role="status" aria-live="polite" aria-busy="true">
        <div class="pbds-loader-header">
          <img class="pbds-loader-logo" src="${LOADER_CONFIG.logoSrc}" alt="P'AS'SION BDS">
          <div class="pbds-loader-title">
            <b>P'AS'SION BDS</b>
            <span id="${LOADER_CONFIG.messageId}">${LOADER_CONFIG.defaultMessage}</span>
          </div>
        </div>

        <div class="pbds-loader-row">
          <div class="pbds-loader-spinner" aria-hidden="true"></div>
          <div class="pbds-loader-badge">SYSTEM LOADING...</div>
        </div>

        <div class="pbds-loader-progress-track" aria-hidden="true">
          <i class="pbds-loader-progress-bar"></i>
        </div>

        <div class="pbds-loader-footer">Si ça prend trop longtemps, c’est pas toi : c’est le réseau.</div>
      </div>
    `.trim();

            const appendOverlay = () => {
                if (!document.body) return;
                document.body.appendChild(loaderOverlayElement);
            };

            if (document.body) appendOverlay();
            else window.addEventListener("DOMContentLoaded", appendOverlay, {once: true});
        };

        const showLoaderOverlay = (messageText) => {
            ensureLoaderOverlayExists();

            const messageNode = getLoaderMessageNode();
            if (messageText && messageNode) messageNode.textContent = messageText;

            loaderOverlayElement.dataset.hidden = "0";
            lockPageScroll();
        };

        const hideLoaderOverlay = () => {
            if (!loaderOverlayElement) return;
            loaderOverlayElement.dataset.hidden = "1";
            unlockPageScroll();
        };

        // Public API (optional)
        window.PBDSLoader = {
            show: showLoaderOverlay,
            hide: hideLoaderOverlay,
            setMessage: (text) => {
                const node = getLoaderMessageNode();
                if (node) node.textContent = text;
            },
        };

        // Initial load behavior
        showLoaderOverlay();
        window.addEventListener(
            "load",
            () => setTimeout(hideLoaderOverlay, LOADER_CONFIG.hideDelayMsAfterLoad),
            {once: true}
        );

        // BFCache restore
        window.addEventListener("pageshow", (event) => {
            if (event.persisted) hideLoaderOverlay();
        });

        // Classic multi-page Laravel: show loader on internal navigations + form submits
        const isInternalUrl = (urlObject) => urlObject && urlObject.origin === location.origin;

        document.addEventListener(
            "click",
            (event) => {
                const anchor = event.target.closest && event.target.closest("a[href]");
                if (!anchor) return;

                if (anchor.target === "_blank" || anchor.hasAttribute("download")) return;
                if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) return;

                const rawHref = anchor.getAttribute("href");
                if (!rawHref || rawHref.startsWith("#")) return;

                let targetUrl;
                try {
                    targetUrl = new URL(rawHref, location.href);
                } catch {
                    return;
                }
                if (!isInternalUrl(targetUrl)) return;

                // same-page hash jump => no loader
                const isSameDocumentHashJump =
                    targetUrl.pathname === location.pathname &&
                    targetUrl.search === location.search &&
                    targetUrl.hash &&
                    targetUrl.hash !== location.hash;
                if (isSameDocumentHashJump) return;

                showLoaderOverlay("Chargement…");
            },
            true
        );

        document.addEventListener(
            "submit",
            (event) => {
                showLoaderOverlay("Envoi…");
                setTimeout(() => {
                    if (event.defaultPrevented) hideLoaderOverlay();
                }, 0);
            },
            true
        );

        // Keep beforeunload silent (no "switch page" text)
        window.addEventListener("beforeunload", () => showLoaderOverlay());
    })();
</script>
<script src="{{ asset('assets/passion-common.js') }}"></script>
@stack('end_scripts')
</body>
</html>
