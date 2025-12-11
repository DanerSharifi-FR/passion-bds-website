{{-- resources/views/admin/dashboard/index.blade.php --}}
@extends('admin.layout')

@section('title', "Admin – Dashboard")

@section('header-tag', 'Dashboard')

@section('content')
    @php
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
    @endphp

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <h1 class="text-xl font-semibold mb-1">
            Bienvenue sur l’admin P'AS'SION BDS
        </h1>
        <p class="text-sm text-slate-600">
            Cet espace est réservé aux rôles administrateurs
            (<code class="bg-slate-100 px-1 rounded">ROLE_GAMEMASTER</code>,
            <code class="bg-slate-100 px-1 rounded">ROLE_BLOGGER</code>,
            <code class="bg-slate-100 px-1 rounded">ROLE_TEAM</code>,
            <code class="bg-slate-100 px-1 rounded">ROLE_SHOP</code>,
            <code class="bg-slate-100 px-1 rounded">ROLE_SUPER_ADMIN</code>).
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <div class="text-xs font-semibold text-slate-400 uppercase mb-1">
                Compte
            </div>
            @if($user)
                <div class="text-sm">
                    <div><span class="font-semibold">Email :</span> {{ $user->university_email }}</div>
                    @if($user->display_name)
                        <div><span class="font-semibold">Nom affiché :</span> {{ $user->display_name }}</div>
                    @endif
                    <div class="mt-1 text-xs text-slate-500">
                        Dernière connexion :
                        {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'inconnue' }}
                    </div>
                </div>
            @else
                <div class="text-sm text-slate-500">
                    Aucun utilisateur connecté.
                </div>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <div class="text-xs font-semibold text-slate-400 uppercase mb-1">
                Rôles
            </div>
            @if($user && $user->roles->isNotEmpty())
                <ul class="text-sm list-disc list-inside space-y-0.5">
                    @foreach($user->roles as $role)
                        <li>
                            <span class="font-mono text-xs bg-slate-100 px-1.5 py-0.5 rounded">
                                {{ $role->name }}
                            </span>
                            @if($role->description)
                                <span class="text-slate-500 text-xs">– {{ $role->description }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-slate-500">
                    Aucun rôle administrateur assigné.
                </p>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <div class="text-xs font-semibold text-slate-400 uppercase mb-1">
                Prochaines étapes
            </div>
            <ul class="text-sm list-disc list-inside text-slate-600 space-y-0.5">
                <li>Module Allos (CRUD + slots + usages).</li>
                <li>Challenges (questions / actions / points).</li>
                <li>Gestion évènements + galerie.</li>
                <li>Équipe, pôles, shop, audit logs.</li>
            </ul>
        </div>
    </div>
@endsection
