{{-- resources/views/admin/layout.blade.php --}}
    <!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', "Admin – P'AS'SION BDS")</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
@php
    /** @var \App\Models\User|null $authUser */
    $authUser = auth()->user();

    $roleNames = $authUser
        ? $authUser->roles->pluck('name')->all()
        : [];

    $isSuperAdmin = in_array('ROLE_SUPER_ADMIN', $roleNames, true);

    $canGamemaster = $authUser && (
        $isSuperAdmin
        || in_array('ROLE_GAMEMASTER', $roleNames, true)
    );

    $canBlogger = $authUser && (
        $isSuperAdmin
        || in_array('ROLE_BLOGGER', $roleNames, true)
    );

    $canTeam = $authUser && (
        $isSuperAdmin
        || in_array('ROLE_TEAM', $roleNames, true)
    );

    $canShop = $authUser && (
        $isSuperAdmin
        || in_array('ROLE_SHOP', $roleNames, true)
    );
@endphp

<header class="bg-slate-900 text-white border-b border-slate-800">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.dashboard') }}" class="text-xs text-slate-300 hover:text-white">
                Admin
            </a>
            <div class="flex items-center gap-2">
                <span class="font-black tracking-tight text-lg">
                    P'AS'SION BDS
                </span>
                <span class="ml-1 text-[11px] px-2 py-0.5 rounded-full bg-slate-800 text-slate-200 border border-slate-700 uppercase tracking-wide">
                    Panel Admin
                </span>

                @hasSection('header-tag')
                    <span class="ml-2 text-[11px] px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-200 border border-emerald-400/40">
                        @yield('header-tag')
                    </span>
                @endif
            </div>
        </div>

        @if($authUser)
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <div class="text-sm font-semibold">
                        {{ $authUser->display_name ?? $authUser->university_email }}
                    </div>
                    <div class="text-xs text-slate-300">
                        @if(empty($roleNames))
                            Aucun rôle admin
                        @else
                            {{ implode(' · ', $roleNames) }}
                        @endif
                    </div>
                </div>

                @if($authUser->avatar_url)
                    <img
                        src="{{ $authUser->avatar_url }}"
                        alt="Avatar"
                        class="w-8 h-8 rounded-full object-cover border border-slate-700"
                    >
                @else
                    <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-xs font-bold">
                        {{ strtoupper(mb_substr($authUser->display_name ?? $authUser->university_email, 0, 2)) }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="text-xs px-3 py-1.5 rounded-lg border border-slate-600 bg-slate-800 hover:bg-slate-700 text-slate-100"
                    >
                        Déconnexion
                    </button>
                </form>
            </div>
        @endif
    </div>
</header>

<main class="max-w-6xl mx-auto px-4 py-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        {{-- SIDEBAR --}}
        <aside class="md:col-span-1">
            <nav class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 space-y-3 text-sm">
                <div class="text-xs font-semibold text-slate-400 uppercase mb-1">
                    Navigation
                </div>

                {{-- Dashboard --}}
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center justify-between px-3 py-2 rounded-lg
                        {{ request()->routeIs('admin.dashboard') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                    <span>Dashboard</span>
                    @if(request()->routeIs('admin.dashboard'))
                        <span class="text-[10px] uppercase tracking-wide opacity-80">Now</span>
                    @endif
                </a>

                {{-- GAMEMASTER / POINTS / ALLOS --}}
                @if($canGamemaster)
                    <div class="mt-3 text-xs font-semibold text-slate-400 uppercase">
                        Gamemaster
                    </div>

                    <a href="{{ route('admin.allos.index') }}"
                       class="block px-3 py-2 rounded-lg
                            {{ request()->routeIs('admin.allos.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                        Allos
                    </a>

                    <a href="{{ route('admin.points.index') }}"
                       class="block px-3 py-2 rounded-lg
                            {{ request()->routeIs('admin.points.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                        Points / Transactions
                    </a>

                    <div class="px-3 py-2 rounded-lg bg-slate-50 border border-dashed border-slate-200 text-slate-500 text-xs">
                        Challenges (bientôt)
                    </div>
                @endif

                {{-- BLOGGER --}}
                @if($canBlogger)
                    <div class="mt-3 text-xs font-semibold text-slate-400 uppercase">
                        Contenu
                    </div>

                    <div class="px-3 py-2 rounded-lg bg-slate-50 border border-dashed border-slate-200 text-slate-500 text-xs">
                        Évènements / Galerie (bientôt)
                    </div>
                @endif

                {{-- TEAM --}}
                @if($canTeam)
                    <div class="mt-3 text-xs font-semibold text-slate-400 uppercase">
                        Équipe
                    </div>

                    <div class="px-3 py-2 rounded-lg bg-slate-50 border border-dashed border-slate-200 text-slate-500 text-xs">
                        Équipe / Pôles (bientôt)
                    </div>
                @endif

                {{-- SHOP --}}
                @if($canShop)
                    <div class="mt-3 text-xs font-semibold text-slate-400 uppercase">
                        Shop
                    </div>

                    <div class="px-3 py-2 rounded-lg bg-slate-50 border border-dashed border-slate-200 text-slate-500 text-xs">
                        Catalogue shop (bientôt)
                    </div>
                @endif

                {{-- SUPER ADMIN --}}
                @if($isSuperAdmin)
                    <div class="mt-3 text-xs font-semibold text-slate-400 uppercase">
                        Super Admin
                    </div>

                    <a href="{{ route('admin.users.index') }}"
                       class="block px-3 py-2 rounded-lg
                            {{ request()->routeIs('admin.users.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                        Comptes & rôles
                    </a>

                    <div class="px-3 py-2 rounded-lg bg-slate-50 border border-dashed border-slate-200 text-slate-500 text-xs">
                        Audit logs (bientôt)
                    </div>
                @endif
            </nav>
        </aside>

        {{-- CONTENT --}}
        <section class="md:col-span-3 space-y-6">
            @yield('content')
        </section>
    </div>
</main>
</body>
</html>
