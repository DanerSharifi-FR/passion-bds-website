<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"
    >

    <title>@yield('title', "P'AS'SION BDS - Accueil")</title>
    <meta name="description" content="@yield('meta_description', "Site de la campagne BDS P'AS'SION IMT Atlantique Nantes.")">
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
    <meta property="og:title" content="@yield('og_title', "P'AS'SION : Bureau Des Sports")">
    <meta property="og:description" content="@yield('og_description', "Site de la campagne BDS P'AS'SION IMT Atlantique Nantes.")">
    <meta property="og:image" content="@yield('og_image', 'https://placehold.co/1200x630/9B1237/FFC94A?text=PASSION+BDS')">

    {{-- Favicons (change later if you want real files) --}}
    <link rel="icon" type="image/png" href="https://placehold.co/32x32/FF914D/9B1237?text=P">
    <link rel="apple-touch-icon" href="https://placehold.co/180x180/FF914D/9B1237?text=P">

    {{-- Fonts: Inter & Poppins --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Poppins:wght@500;700;900&display=swap"
        rel="stylesheet"
    >

    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>

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
    @yield('content')
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

<script src="{{ asset('assets/passion-common.js') }}"></script>
</body>
</html>
