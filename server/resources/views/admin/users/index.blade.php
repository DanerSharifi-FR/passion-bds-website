{{-- resources/views/admin/users/index.blade.php --}}
@extends('admin.layout')

@section('title', "Admin – Comptes & rôles")

@section('header-tag', 'Comptes & rôles')

@section('content')
    @php
        /** @var \Illuminate\Pagination\LengthAwarePaginator<\App\Models\User> $users */
        /** @var \App\Models\User $currentUser */
    @endphp

    @if ($errors->has('user'))
        <div class="mb-4 bg-red-500/10 border border-red-500/60 text-red-100 px-4 py-3 rounded-lg text-sm">
            {{ $errors->first('user') }}
        </div>
    @endif

    @if (session('status') === 'admin_user_created')
        <div class="mb-4 bg-sky-500/10 border border-sky-500/60 text-sky-100 px-4 py-3 rounded-lg text-sm">
            Nouveau compte créé.
        </div>
    @endif

    @if (session('status') === 'admin_user_updated')
        <div class="mb-4 bg-emerald-500/10 border border-emerald-500/60 text-emerald-100 px-4 py-3 rounded-lg text-sm">
            Compte mis à jour.
        </div>
    @endif

    @if (session('status') === 'admin_user_deleted')
        <div class="mb-4 bg-amber-500/10 border border-amber-500/60 text-amber-100 px-4 py-3 rounded-lg text-sm">
            Compte supprimé.
        </div>
    @endif

    <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold mb-1">
                Comptes & rôles
            </h1>
            <p class="text-sm text-slate-600">
                Gestion des comptes ayant un rôle admin (Gamemaster, Blogger, Team, Shop, etc.).
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.users.create') }}"
               class="text-xs px-3 py-1.5 rounded-lg bg-slate-900 text-white hover:bg-slate-800">
                + Ajouter un compte
            </a>

            <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-2">
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Rechercher par email / nom"
                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                >
                <button
                    type="submit"
                    class="text-xs px-3 py-1.5 rounded-lg bg-slate-100 border border-slate-300 text-slate-800 hover:bg-slate-200"
                >
                    Rechercher
                </button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase text-slate-500">
            <tr>
                <th class="px-4 py-2 text-left">Email</th>
                <th class="px-4 py-2 text-left">Nom affiché</th>
                <th class="px-4 py-2 text-left">Rôles</th>
                <th class="px-4 py-2 text-left">Statut</th>
                <th class="px-4 py-2 text-right">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($users as $user)
                @php
                    $isSuperAdmin = $user->isSuperAdmin();
                    $isSelf = $currentUser->id === $user->id;
                @endphp
                <tr class="border-b border-slate-100 hover:bg-slate-50/60">
                    <td class="px-4 py-2 align-top">
                        <div class="font-mono text-xs">{{ $user->university_email }}</div>
                    </td>
                    <td class="px-4 py-2 align-top">
                        @if($user->display_name)
                            <div>{{ $user->display_name }}</div>
                        @else
                            <div class="text-slate-400 text-xs italic">Non renseigné</div>
                        @endif
                    </td>
                    <td class="px-4 py-2 align-top">
                        @if($user->roles->isEmpty())
                            <span class="text-xs text-slate-400">Aucun rôle</span>
                        @else
                            <div class="flex flex-wrap gap-1">
                                @foreach($user->roles as $role)
                                    <span class="inline-flex items-center text-[11px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $role->name }}
                                        </span>
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td class="px-4 py-2 align-top">
                        @if($user->is_active)
                            <span class="inline-flex items-center text-[11px] px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Actif
                                </span>
                        @else
                            <span class="inline-flex items-center text-[11px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">
                                    Inactif
                                </span>
                        @endif
                    </td>
                    <td class="px-4 py-2 align-top">
                        <div class="flex items-center justify-end gap-2 text-xs">
                            @if($isSuperAdmin)
                                <span class="text-[11px] text-slate-400 italic">
                                        Super admin (verrouillé)
                                    </span>
                            @else
                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="px-2 py-1 rounded border border-slate-300 text-slate-800 hover:bg-slate-100">
                                    Éditer
                                </a>

                                @if(! $isSelf)
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                          onsubmit="return confirm('Supprimer ce compte ? Cette action est définitive.');">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="px-2 py-1 rounded border border-rose-300 text-rose-700 hover:bg-rose-50"
                                        >
                                            Supprimer
                                        </button>
                                    </form>
                                @else
                                    <span class="text-[11px] text-slate-400 italic">
                                            Impossible de supprimer ton propre compte
                                        </span>
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-4 text-center text-sm text-slate-500">
                        Aucun utilisateur trouvé.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>

        @if($users->hasPages())
            <div class="px-4 py-3 border-t border-slate-200">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@endsection
