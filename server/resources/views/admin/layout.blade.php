<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', "BDS Admin")</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Highlight.js for JSON formatting -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/atom-one-dark.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #0f172a;
        }

        ::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }

        .modal {
            transition: opacity 0.25s ease;
        }

        .modal-active {
            overflow-y: hidden;
        }

        pre code.hljs {
            background: #1e293b;
            padding: 1rem;
            border-radius: 0.5rem;
            font-size: 0.85rem;
        }

        /* Modal Transition */
        .modal {
            transition: opacity 0.25s ease;
        }

        .modal-active {
            overflow-y: hidden;
        }

        /* Toast Animations */
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }

        .toast-enter {
            animation: slideIn 0.3s ease-out forwards;
        }

        .toast-exit {
            animation: fadeOut 0.3s ease-in forwards;
        }
    </style>

    {{-- Page-specific extra CSS if needed --}}
    @stack('styles')

    {{-- Page-specific extra JS in <head> if really needed --}}
    @stack('head_scripts')
</head>
<body class="h-full text-slate-200">
<!-- TOAST CONTAINER -->
<div id="toastContainer" class="fixed top-4 right-4 z-[100] flex flex-col gap-3 w-80 pointer-events-none"></div>

<div class="flex h-screen overflow-hidden">

    <!-- SIDEBAR -->
    <aside id="sidebar"
           class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-800 border-r border-slate-700 transform -translate-x-full lg:relative lg:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col">
        <div class="h-16 flex items-center px-6 border-b border-slate-700 bg-indigo-600">
            <i class="fa-solid fa-bolt text-xl text-white mr-3"></i>
            <span class="text-lg font-bold text-white tracking-wide">BDS Admin</span>
        </div>
        <nav class="flex-1 overflow-y-auto py-4">
            <div class="px-4 space-y-1">
                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center px-4 @if(request()->routeIs('admin.dashboard')) py-3 bg-slate-700/50 text-white rounded-lg group transition-colors border-l-4 border-indigo-500 @else py-2.5 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg group transition-colors @endif">
                    <i class="fa-solid fa-gauge-high w-6 @if(request()->routeIs('admin.dashboard')) text-indigo-400 @else group-hover:text-indigo-400 transition-colors @endif"></i>
                    <span>Vue d'ensemble</span>
                </a>

                @if(auth()->user()->hasRole('ROLE_GAMEMASTER') || auth()->user()->hasRole('ROLE_SUPER_ADMIN'))
                    <!-- Jeu & Points -->
                    <div class="pt-4 pb-2 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Jeu &
                        Points
                    </div>
                    <a href="{{ route('admin.transactions') }}"
                       class="flex items-center px-4 @if(request()->routeIs('admin.transactions')) py-3 bg-slate-700/50 text-white rounded-lg group transition-colors border-l-4 border-yellow-500 @else py-2.5 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg group transition-colors @endif">
                        <i class="fa-solid fa-coins w-6 @if(request()->routeIs('admin.transactions')) text-yellow-400 @else group-hover:text-yellow-400 transition-colors @endif"></i>
                        <span>Transactions</span>
                    </a>
                    <a href="{{ route('admin.activities') }}"
                       class="flex items-center px-4 @if(request()->routeIs('admin.activities*')) py-3 bg-slate-700/50 text-white rounded-lg group transition-colors border-l-4 border-yellow-500 @else py-2.5 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg group transition-colors @endif">
                        <i class="fa-solid fa-trophy w-6 @if(request()->routeIs('admin.activities*')) text-yellow-400 @else group-hover:text-yellow-400 transition-colors @endif"></i>
                        <span>Activités</span>
                    </a>
                    <a href="{{ route('admin.challenges') }}"
                       class="flex items-center px-4 @if(request()->routeIs('admin.challenges')) py-3 bg-slate-700/50 text-white rounded-lg group transition-colors border-l-4 border-yellow-500 @else py-2.5 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg group transition-colors @endif">
                        <i class="fa-solid fa-trophy w-6 @if(request()->routeIs('admin.challenges')) text-yellow-400 @else group-hover:text-yellow-400 transition-colors @endif"></i>
                        <span>Défis & Quêtes</span>
                    </a>
                    <a href="{{ route('admin.allos') }}"
                       class="flex items-center px-4 @if(request()->routeIs('admin.allos')) py-3 bg-slate-700/50 text-white rounded-lg group transition-colors border-l-4 border-yellow-500 @else py-2.5 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg group transition-colors @endif">
                        <i class="fa-solid fa-phone-volume w-6 @if(request()->routeIs('admin.allos')) text-yellow-400 @else group-hover:text-yellow-400 transition-colors @endif"></i>
                        <span>Allos</span>
                    </a>
                @endif

                @if(auth()->user()->hasRole('ROLE_BLOGGER') || auth()->user()->hasRole('ROLE_SUPER_ADMIN'))
                    <!-- Contenu -->
                    <div class="pt-4 pb-2 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Contenu
                    </div>
                    <a href="admin_events.html"
                       class="flex items-center px-4 py-2.5 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg group transition-colors">
                        <i class="fa-solid fa-calendar-day w-6 group-hover:text-purple-400 transition-colors"></i>
                        <span>Événements</span>
                    </a>
                    <a href="admin_team.html"
                       class="flex items-center px-4 py-2.5 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg group transition-colors">
                        <i class="fa-solid fa-users w-6 group-hover:text-purple-400 transition-colors"></i>
                        <span>L'Équipe (Pôles)</span>
                    </a>
                    <a href="admin_shop.html"
                       class="flex items-center px-4 py-2.5 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg group transition-colors">
                        <i class="fa-solid fa-store w-6 group-hover:text-purple-400 transition-colors"></i>
                        <span>Boutique</span>
                    </a>
                @endif

                @if(auth()->user()->hasRole('ROLE_SUPER_ADMIN') || auth()->user()->hasRole('ROLE_GAMEMASTER'))
                    <!-- Système -->
                    <div class="pt-4 pb-2 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Système
                    </div>
                    @if(auth()->user()->hasRole('ROLE_SUPER_ADMIN') || auth()->user()->hasRole('ROLE_GAMEMASTER'))
                        <a href="{{ route('admin.users') }}"
                           class="flex items-center px-4 @if(request()->routeIs('admin.users')) py-3 bg-slate-700/50 text-white rounded-lg group transition-colors border-l-4 border-red-500 @else py-2.5 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg group transition-colors @endif">
                            <i class="fa-solid fa-users-gear w-6 @if(request()->routeIs('admin.users')) text-red-400 @else group-hover:text-yellow-400 transition-colors @endif"></i>
                            <span>Utilisateurs & Rôles</span>
                        </a>
                    @endif
                    @if(auth()->user()->hasRole('ROLE_SUPER_ADMIN'))
                        <!-- Active State -->
                        <a href="{{ route('admin.logs') }}"
                           class="flex items-center px-4 @if(request()->routeIs('admin.logs')) py-3 bg-slate-700/50 text-white rounded-lg group transition-colors border-l-4 border-red-500 @else py-2.5 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg group transition-colors @endif">
                            <i class="fa-solid fa-clipboard-list w-6 @if(request()->routeIs('admin.logs')) text-red-400 @else group-hover:text-yellow-400 transition-colors @endif"></i>
                            <span class="font-medium">Audit Logs</span>
                        </a>
                    @endif
                @endif
            </div>
        </nav>
        <div class="p-4 border-t border-slate-700 bg-slate-900/50">
            <div class="flex items-center">
                <div
                    class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center font-bold text-white text-xs">
                    {{ strtoupper(substr(explode(' ', auth()->user()->display_name)[0], 0, 1) . substr(explode(' ', auth()->user()->display_name)[1] ?? '', 0, 1)) }}
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-white">
                        {{ auth()->user()->display_name }}
                    </p>
                    <p class="text-xs text-slate-500">
                        {{ ucwords(strtolower(str_replace(['ROLE_', '_'], ['', ' '], auth()->user()->roles->last()->name ?? 'Utilisateur'))) }}
                    </p>
                </div>
                <div class="ml-auto">
                    <form method="POST" action="{{ route('auth.logout') }}">
                        @csrf
                        <button type="submit" class="text-slate-400 hover:text-white">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/80 z-40 hidden lg:hidden glass-effect"
         onclick="toggleSidebar()"></div>

    <!-- MAIN CONTENT -->
    <div class="flex-1 flex flex-col min-w-0 bg-slate-900">

        <!-- Topbar -->
        <header class="h-16 flex items-center justify-between px-4 lg:px-8 bg-slate-800 border-b border-slate-700">
            <button onclick="toggleSidebar()" class="lg:hidden text-slate-400 hover:text-white">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>
            @yield('top_bar_buttons')
            <div class="flex items-center space-x-4 ml-auto">
                <a href="/" target="_blank" class="text-sm text-indigo-400 hover:text-indigo-300 font-medium">Voir le
                    site <i class="fa-solid fa-external-link-alt ml-1"></i></a>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 lg:p-8">
            @yield('content')
        </main>
    </div>
</div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        if (sidebar.classList.contains('-translate-x-full')) {
// Open
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        } else {
// Close
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        }
    }
</script>
@stack('end_scripts')
</body>
</html>
