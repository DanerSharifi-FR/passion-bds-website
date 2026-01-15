@extends('admin.layout')

@section('title', "Gestion des Allos - P'AS'SION BDS")

@section('top_bar_buttons')
    @include('admin.allos.partials.top-bar')
@endsection

@section('content')
    <div id="viewCatalog">
        <div class="flex flex-col gap-4 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold text-white">Catalogue des Allos</h2>
                    <p id="resultsCount" class="text-xs text-slate-400 mt-1">0 allos</p>
                </div>
                <button onclick="openAlloModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors shadow">
                    <i class="fa-solid fa-plus mr-2"></i> Nouvel Allo
                </button>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-slate-400 mb-1" for="catalogTitleFilter">Filtrer par titre</label>
                    <input id="catalogTitleFilter" type="text" class="w-full bg-slate-800 border border-slate-600 text-white text-sm rounded-lg p-2 focus:ring-indigo-500" placeholder="Ex: petit dej, massage...">
                </div>
                <div class="sm:w-56">
                    <label class="block text-xs font-medium text-slate-400 mb-1" for="catalogStatusFilter">Statut</label>
                    <select id="catalogStatusFilter" class="w-full bg-slate-800 border border-slate-600 text-white text-sm rounded-lg p-2 focus:ring-indigo-500">
                        <option value="ALL">Tous les statuts</option>
                        <option value="DRAFT">Brouillon</option>
                        <option value="OPEN">Ouvert</option>
                        <option value="CLOSED">Fermé</option>
                        <option value="DISABLED">Désactivé</option>
                    </select>
                </div>
                <div class="sm:w-40 sm:flex sm:items-end">
                    <button id="catalogResetFilters" class="w-full bg-slate-700 hover:bg-slate-600 text-white text-sm rounded-lg px-3 py-2 transition-colors">
                        Réinitialiser
                    </button>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6" id="catalogGrid"></div>
    </div>
@endsection

@push('end_scripts')
    @include('admin.allos.partials.scripts', ['activeView' => 'catalog'])
@endpush
