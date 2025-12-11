@extends('app')

@section('title', "P'AS'SION BDS - Galerie")
@section('meta_description', "Galerie photo des événements du P'AS'SION BDS IMT Atlantique Nantes pour l'année 2025-2026.")

@section('content')
        <!-- Page Title -->
        <div class="relative text-center mb-8 z-10 w-full max-w-4xl px-4">
            <h1 class="font-display font-black text-4xl md:text-7xl text-passion-red uppercase tracking-tighter leading-none mb-2 drop-shadow-sm skew-box transform-gpu">
                Souvenirs<br><span class="text-white text-stroke-red-thick">du Crash</span>
            </h1>
            <p class="font-display font-bold text-passion-pink-500 text-sm md:text-xl bg-white/80 px-4 py-1 inline-block skew-box border-2 border-passion-pink-300">
                <span class="unskew-text">📸 Si tu n'es pas dessus, c'est que tu dormais.</span>
            </p>
        </div>

        <!-- Call to Action: BIG DRIVE BUTTON -->
        <div class="w-full max-w-4xl mb-12 flex justify-center z-20">
            <a href="https://drive.google.com" target="_blank" class="group relative inline-block">
                <div class="absolute inset-0 bg-passion-red translate-x-2 translate-y-2 rounded-sm"></div>
                <div class="relative bg-passion-fire-yellow border-4 border-passion-red px-6 py-4 md:px-10 md:py-6 flex items-center gap-4 transition-transform group-hover:-translate-y-1 group-hover:-translate-x-1">
                    <span class="text-3xl md:text-5xl animate-bounce-slow">📂</span>
                    <div class="flex flex-col text-left">
                        <span class="font-display font-black text-xl md:text-3xl text-passion-red uppercase leading-none">Accès Drive Complet</span>
                        <span class="font-mono font-bold text-xs md:text-sm text-passion-red opacity-80 uppercase">Toutes les photos (HD) sont ici</span>
                    </div>
                    <div class="hidden md:flex bg-passion-red text-white p-2 rounded-full">
                        <svg class="w-6 h-6 transform -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </div>
                </div>
            </a>
        </div>

        <!-- GALLERY CONTAINER -->
        <div class="w-full max-w-5xl z-10 min-h-[600px] pb-12">

            <!-- Events List (Injected via JS) -->
            <div id="events-container" class="flex flex-col gap-10 md:gap-16 w-full">
                <div class="gallery-card w-[80%] md:w-[90%] mx-auto p-4 md:p-6 relative skew-box group cursor-pointer" onclick="window.open('https://drive.google.com/drive/u/0/my-drive', '_blank')">

                    <!-- Inner Container to Un-Skew Content at once -->
                    <div class="unskew-text h-full w-full">

                        <!-- Header -->
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-4 border-b-2 border-passion-pink-200 pb-2">
                            <div class="w-full">
                                <span class="block text-xs font-bold text-passion-fire-orange bg-passion-red px-2 py-0.5 rounded w-fit mb-1">Octobre 2024</span>
                                <h2 class="font-display font-black text-2xl md:text-4xl text-passion-red uppercase leading-none break-words">WEI 2024</h2>
                            </div>
                            <div class="hidden md:block whitespace-nowrap ml-4">
                                <span class="text-xs font-mono text-passion-pink-500 font-bold group-hover:text-passion-fire-orange transition-colors">OPEN DRIVE ↗</span>
                            </div>
                        </div>

                        <!-- Photos Grid (Polaroid Style) -->
                        <div class="flex justify-center gap-2 md:gap-8 py-4 relative h-40 md:h-56 items-center">

                            <div class="polaroid absolute w-24 md:w-40 bg-white border border-gray-200" style="left: 30%; transform: translateX(-50%) rotate(-5deg);">
                                <div class="w-full aspect-square bg-gray-100 mb-2 overflow-hidden">
                                    <img src="https://placehold.co/400x400/FF914D/9B1237?text=WEI+1" class="w-full h-full object-cover mix-blend-multiply opacity-90 hover:opacity-100 transition-opacity" alt="Photo WEI 2024">
                                </div>
                                <div class="h-1 md:h-2 w-full bg-gray-100/50"></div>
                            </div>

                            <div class="polaroid absolute w-24 md:w-40 bg-white border border-gray-200" style="left: 50%; transform: translateX(-50%) rotate(0deg);">
                                <div class="w-full aspect-square bg-gray-100 mb-2 overflow-hidden">
                                    <img src="https://placehold.co/400x400/9B1237/FFC94A?text=WEI+2" class="w-full h-full object-cover mix-blend-multiply opacity-90 hover:opacity-100 transition-opacity" alt="Photo WEI 2024">
                                </div>
                                <div class="h-1 md:h-2 w-full bg-gray-100/50"></div>
                            </div>

                            <div class="polaroid absolute w-24 md:w-40 bg-white border border-gray-200" style="left: 70%; transform: translateX(-50%) rotate(5deg);">
                                <div class="w-full aspect-square bg-gray-100 mb-2 overflow-hidden">
                                    <img src="https://placehold.co/400x400/FCE2EC/E4476A?text=WEI+3" class="w-full h-full object-cover mix-blend-multiply opacity-90 hover:opacity-100 transition-opacity" alt="Photo WEI 2024">
                                </div>
                                <div class="h-1 md:h-2 w-full bg-gray-100/50"></div>
                            </div>

                        </div>

                        <!-- Mobile Action Text -->
                        <div class="md:hidden mt-4 text-center">
                            <span class="inline-block bg-passion-pink-100 text-passion-red text-[10px] font-bold px-3 py-1 rounded-full border border-passion-red">
                                Voir l'album ➜
                            </span>
                        </div>

                    </div>
                    <!-- End Inner Un-Skew -->

                    <!-- Decorative Corner (Outside unskew, so it follows the skew) -->
                    <div class="absolute top-2 right-2 w-2 h-2 md:w-3 md:h-3 bg-passion-fire-yellow rounded-full border border-passion-red"></div>
                </div>

                <div class="gallery-card w-[80%] md:w-[90%] mx-auto p-4 md:p-6 relative skew-box-r group cursor-pointer" onclick="window.open('https://drive.google.com', '_blank')">

                    <!-- Inner Container to Un-Skew Content at once -->
                    <div class="unskew-text-r h-full w-full">

                        <!-- Header -->
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-4 border-b-2 border-passion-pink-200 pb-2">
                            <div class="w-full">
                                <span class="block text-xs font-bold text-passion-fire-orange bg-passion-red px-2 py-0.5 rounded w-fit mb-1">31 Octobre 2024</span>
                                <h2 class="font-display font-black text-2xl md:text-4xl text-passion-red uppercase leading-none break-words">Afterwork Halloween</h2>
                            </div>
                            <div class="hidden md:block whitespace-nowrap ml-4">
                                <span class="text-xs font-mono text-passion-pink-500 font-bold group-hover:text-passion-fire-orange transition-colors">OPEN DRIVE ↗</span>
                            </div>
                        </div>

                        <!-- Photos Grid (Polaroid Style) -->
                        <div class="flex justify-center gap-2 md:gap-8 py-4 relative h-40 md:h-56 items-center">

                            <div class="polaroid absolute w-24 md:w-40 bg-white border border-gray-200" style="left: 30%; transform: translateX(-50%) rotate(-5deg);">
                                <div class="w-full aspect-square bg-gray-100 mb-2 overflow-hidden">
                                    <img src="https://placehold.co/400x400/000000/FFFFFF?text=Spooky" class="w-full h-full object-cover mix-blend-multiply opacity-90 hover:opacity-100 transition-opacity" alt="Photo Afterwork Halloween">
                                </div>
                                <div class="h-1 md:h-2 w-full bg-gray-100/50"></div>
                            </div>

                            <div class="polaroid absolute w-24 md:w-40 bg-white border border-gray-200" style="left: 50%; transform: translateX(-50%) rotate(0deg);">
                                <div class="w-full aspect-square bg-gray-100 mb-2 overflow-hidden">
                                    <img src="https://placehold.co/400x400/FF914D/000000?text=Pumpkin" class="w-full h-full object-cover mix-blend-multiply opacity-90 hover:opacity-100 transition-opacity" alt="Photo Afterwork Halloween">
                                </div>
                                <div class="h-1 md:h-2 w-full bg-gray-100/50"></div>
                            </div>

                            <div class="polaroid absolute w-24 md:w-40 bg-white border border-gray-200" style="left: 70%; transform: translateX(-50%) rotate(5deg);">
                                <div class="w-full aspect-square bg-gray-100 mb-2 overflow-hidden">
                                    <img src="https://placehold.co/400x400/9B1237/FFFFFF?text=Party" class="w-full h-full object-cover mix-blend-multiply opacity-90 hover:opacity-100 transition-opacity" alt="Photo Afterwork Halloween">
                                </div>
                                <div class="h-1 md:h-2 w-full bg-gray-100/50"></div>
                            </div>

                        </div>

                        <!-- Mobile Action Text -->
                        <div class="md:hidden mt-4 text-center">
                            <span class="inline-block bg-passion-pink-100 text-passion-red text-[10px] font-bold px-3 py-1 rounded-full border border-passion-red">
                                Voir l'album ➜
                            </span>
                        </div>

                    </div>
                    <!-- End Inner Un-Skew -->

                    <!-- Decorative Corner (Outside unskew, so it follows the skew) -->
                    <div class="absolute top-2 right-2 w-2 h-2 md:w-3 md:h-3 bg-passion-fire-yellow rounded-full border border-passion-red"></div>
                </div>

                <div class="gallery-card w-[80%] md:w-[90%] mx-auto p-4 md:p-6 relative skew-box group cursor-pointer" onclick="window.open('https://drive.google.com', '_blank')">

                    <!-- Inner Container to Un-Skew Content at once -->
                    <div class="unskew-text h-full w-full">

                        <!-- Header -->
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-4 border-b-2 border-passion-pink-200 pb-2">
                            <div class="w-full">
                                <span class="block text-xs font-bold text-passion-fire-orange bg-passion-red px-2 py-0.5 rounded w-fit mb-1">15 Novembre 2024</span>
                                <h2 class="font-display font-black text-2xl md:text-4xl text-passion-red uppercase leading-none break-words">Match vs Centrale</h2>
                            </div>
                            <div class="hidden md:block whitespace-nowrap ml-4">
                                <span class="text-xs font-mono text-passion-pink-500 font-bold group-hover:text-passion-fire-orange transition-colors">OPEN DRIVE ↗</span>
                            </div>
                        </div>

                        <!-- Photos Grid (Polaroid Style) -->
                        <div class="flex justify-center gap-2 md:gap-8 py-4 relative h-40 md:h-56 items-center">

                            <div class="polaroid absolute w-24 md:w-40 bg-white border border-gray-200" style="left: 30%; transform: translateX(-50%) rotate(-5deg);">
                                <div class="w-full aspect-square bg-gray-100 mb-2 overflow-hidden">
                                    <img src="https://placehold.co/400x400/E4476A/FFFFFF?text=Rugby" class="w-full h-full object-cover mix-blend-multiply opacity-90 hover:opacity-100 transition-opacity" alt="Photo Match vs Centrale">
                                </div>
                                <div class="h-1 md:h-2 w-full bg-gray-100/50"></div>
                            </div>

                            <div class="polaroid absolute w-24 md:w-40 bg-white border border-gray-200" style="left: 50%; transform: translateX(-50%) rotate(0deg);">
                                <div class="w-full aspect-square bg-gray-100 mb-2 overflow-hidden">
                                    <img src="https://placehold.co/400x400/9B1237/FFFFFF?text=Score" class="w-full h-full object-cover mix-blend-multiply opacity-90 hover:opacity-100 transition-opacity" alt="Photo Match vs Centrale">
                                </div>
                                <div class="h-1 md:h-2 w-full bg-gray-100/50"></div>
                            </div>

                            <div class="polaroid absolute w-24 md:w-40 bg-white border border-gray-200" style="left: 70%; transform: translateX(-50%) rotate(5deg);">
                                <div class="w-full aspect-square bg-gray-100 mb-2 overflow-hidden">
                                    <img src="https://placehold.co/400x400/FFC94A/9B1237?text=Fans" class="w-full h-full object-cover mix-blend-multiply opacity-90 hover:opacity-100 transition-opacity" alt="Photo Match vs Centrale">
                                </div>
                                <div class="h-1 md:h-2 w-full bg-gray-100/50"></div>
                            </div>

                        </div>

                        <!-- Mobile Action Text -->
                        <div class="md:hidden mt-4 text-center">
                            <span class="inline-block bg-passion-pink-100 text-passion-red text-[10px] font-bold px-3 py-1 rounded-full border border-passion-red">
                                Voir l'album ➜
                            </span>
                        </div>

                    </div>
                    <!-- End Inner Un-Skew -->

                    <!-- Decorative Corner (Outside unskew, so it follows the skew) -->
                    <div class="absolute top-2 right-2 w-2 h-2 md:w-3 md:h-3 bg-passion-fire-yellow rounded-full border border-passion-red"></div>
                </div>
            </div>

            <!-- Pagination Controls -->
            <div class="flex justify-center items-center gap-4 mt-12 mb-8">
                <button onclick="changePage(-1)" id="btn-prev" class="bg-white border-2 border-passion-red text-passion-red px-4 py-2 font-display font-black uppercase hover:bg-passion-pink-100 disabled:opacity-50 disabled:cursor-not-allowed skew-box" disabled="">
                    <span class="unskew-text">◄ Précédent</span>
                </button>

                <div class="font-display font-black text-xl text-passion-red bg-white px-4 py-2 border-2 border-passion-red skew-box">
                    <span class="unskew-text" id="page-indicator">1 / 3</span>
                </div>

                <button onclick="changePage(1)" id="btn-next" class="bg-passion-fire-orange border-2 border-passion-red text-passion-red px-4 py-2 font-display font-black uppercase hover:bg-passion-fire-yellow disabled:opacity-50 disabled:cursor-not-allowed skew-box">
                    <span class="unskew-text">Suivant ►</span>
                </button>
            </div>

        </div>
@endsection
