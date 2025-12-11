{{-- resources/views/admin/users/create.blade.php --}}
@extends('admin.layout')

@section('title', "Admin – Nouveau compte")

@section('header-tag', 'Comptes & rôles')

@section('content')
    @php
        /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role> $roles */
    @endphp

    <div class="mb-4">
        <a href="{{ route('admin.users.index') }}"
           class="text-xs text-slate-500 hover:text-slate-700">
            ← Retour à la liste des comptes
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 max-w-xl">
        <h1 class="text-lg font-semibold mb-1">
            Créer un nouveau compte admin
        </h1>
        <p class="text-sm text-slate-600 mb-4">
            Le rôle
            <span class="font-mono bg-slate-100 px-1 rounded text-[11px]">ROLE_SUPER_ADMIN</span>
            ne peut pas être attribué ici. Pour des raisons de sécurité, il doit rester géré directement en base de données.
        </p>

        @if($errors->any())
            <div class="mb-4 bg-red-500/10 border border-red-500/60 text-red-100 px-4 py-3 rounded-lg text-sm">
                <p class="font-semibold mb-1">Erreur dans le formulaire :</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-xs font-semibold text-slate-500 uppercase mb-1">
                    Email universitaire
                </label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                    placeholder="prenom.nom@imt-atlantique.net"
                >
                <p class="mt-1 text-[11px] text-slate-500">
                    Format imposé : <code>prenom.nom@imt-atlantique.net</code>.
                </p>
            </div>

            <div>
                <label for="display_name" class="block text-xs font-semibold text-slate-500 uppercase mb-1">
                    Nom affiché (optionnel)
                </label>
                <input
                    id="display_name"
                    name="display_name"
                    type="text"
                    value="{{ old('display_name') }}"
                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                >
            </div>

            <div class="flex items-center gap-2">
                <input
                    id="is_active"
                    name="is_active"
                    type="hidden"
                    value="1"
                >
                <input
                    id="is_active_checkbox"
                    type="checkbox"
                    value="1"
                    checked
                    onclick="document.getElementById('is_active').value = this.checked ? '1' : '0';"
                    class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                >
                <label for="is_active_checkbox" class="text-sm text-slate-700">
                    Compte actif
                </label>
            </div>

            <div>
                <div class="text-xs font-semibold text-slate-500 uppercase mb-1">
                    Rôles (hors super admin)
                </div>
                <p class="text-[11px] text-slate-500 mb-2">
                    Sélectionne les rôles de ce compte. Tu peux modifier ça plus tard.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($roles as $role)
                        <label class="flex items-start gap-2 text-sm border border-slate-200 rounded-lg px-3 py-2 hover:bg-slate-50">
                            <input
                                type="checkbox"
                                name="roles[]"
                                value="{{ $role->name }}"
                                @checked(in_array($role->name, old('roles', [])))
                                class="mt-0.5 h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                            >
                            <span>
                                <span class="font-mono text-[11px] bg-slate-100 px-1.5 py-0.5 rounded">
                                    {{ $role->name }}
                                </span>
                                @if($role->description)
                                    <span class="block text-[11px] text-slate-500 mt-0.5">
                                        {{ $role->description }}
                                    </span>
                                @endif
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="pt-2 flex items-center justify-end gap-2">
                <a href="{{ route('admin.users.index') }}"
                   class="px-3 py-1.5 rounded-lg border border-slate-300 text-sm text-slate-700 hover:bg-slate-100">
                    Annuler
                </a>
                <button
                    type="submit"
                    class="px-3 py-1.5 rounded-lg bg-slate-900 text-sm text-white hover:bg-slate-800"
                >
                    Créer le compte
                </button>
            </div>
        </form>
    </div>
@endsection
