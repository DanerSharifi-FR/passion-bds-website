@extends('admin.layout')

@section('title', "Gestion des Allos - P'AS'SION BDS")

@section('top_bar_buttons')
    @include('admin.allos.partials.top-bar')
@endsection

@section('content')
    <div id="viewRequests">
        <div class="flex flex-col md:flex-row items-start md:items-center gap-4 mb-6">
            <div>
                <h2 class="text-xl font-bold text-white min-w-fit">Suivi des Allos</h2>
                <p id="resultsCount" class="text-xs text-slate-400 mt-1">0 allos</p>
            </div>
            <div class="flex flex-wrap gap-2 w-full">
                <select id="filterStatus" class="bg-slate-800 border border-slate-600 text-white text-xs rounded-lg p-2 focus:ring-indigo-500" onchange="renderRequests()">
                    <option value="ACTIVE" selected>En cours</option>
                    <option value="ALL">Tout l'historique</option>
                    <option value="PENDING">En attente</option>
                    <option value="ACCEPTED">Acceptés</option>
                    <option value="DONE">Terminés</option>
                    <option value="CANCELLED">Annulés</option>
                </select>
                <select id="filterAllo" class="bg-slate-800 border border-slate-600 text-white text-xs rounded-lg p-2 focus:ring-indigo-500 max-w-[200px]" onchange="renderRequests()">
                    <option value="">Tous les allos</option>
                </select>
                <div class="relative w-full md:w-64">
                    <input type="text" id="filterUser" class="w-full bg-slate-800 border border-slate-600 text-white text-xs rounded-lg p-2 pl-8 focus:ring-indigo-500" placeholder="Chercher un étudiant..." autocomplete="off">
                    <i class="fa-solid fa-search absolute left-2.5 top-2.5 text-slate-500 text-xs"></i>
                    <button id="clearUserFilter" onclick="clearUserFilter()" class="absolute right-2 top-2 text-slate-500 hover:text-white hidden"><i class="fa-solid fa-xmark"></i></button>
                    <div id="userSuggestions" class="absolute z-10 w-full bg-slate-800 border border-slate-600 rounded-lg mt-1 hidden max-h-48 overflow-y-auto shadow-xl"></div>
                </div>
            </div>
            <div class="bg-slate-900/70 border border-slate-700 rounded-2xl p-4 flex flex-col gap-4 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center gap-3">
                    <div class="relative flex-1">
                        <input type="text" id="filterSearch" class="w-full bg-slate-800 border border-slate-600 text-white text-sm rounded-lg p-2.5 pl-9 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Chercher un étudiant, un allo, une note..." autocomplete="off">
                        <i class="fa-solid fa-search absolute left-3 top-3.5 text-slate-500 text-sm"></i>
                        <button id="clearSearch" type="button" class="absolute right-3 top-3 text-slate-500 hover:text-white hidden focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="md:w-64">
                        <label class="sr-only" for="filterSort">Trier par</label>
                        <select id="filterSort" class="w-full bg-slate-800 border border-slate-600 text-white text-sm rounded-lg p-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="slot_soon">Créneau le plus proche</option>
                            <option value="recent">Plus récent</option>
                            <option value="status">Statut</option>
                            <option value="student">Étudiant</option>
                        </select>
                    </div>
                </div>
                <div id="filtersPanel" class="hidden md:flex flex-col gap-4">
                    <div class="flex flex-wrap gap-3">
                        <div class="relative">
                            <button id="statusFilterButton" type="button" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-slate-800 border border-slate-600 text-sm text-white hover:border-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                                Statuts
                                <span id="statusFilterCount" class="text-[10px] uppercase tracking-wide bg-indigo-500/20 text-indigo-200 px-2 py-0.5 rounded-full">2</span>
                                <i class="fa-solid fa-chevron-down text-xs text-slate-400"></i>
                            </button>
                            <div id="statusFilterMenu" class="absolute z-20 mt-2 w-56 rounded-xl bg-slate-900 border border-slate-700 shadow-xl p-2 hidden">
                                <div class="flex items-center justify-between px-2 py-1 text-xs text-slate-400">
                                    <span>Sélection rapide</span>
                                    <button id="statusSelectAll" type="button" class="text-indigo-300 hover:text-indigo-200">Tout</button>
                                </div>
                                <div class="flex flex-col gap-1 p-2">
                                    <label class="flex items-center gap-2 text-sm text-slate-200">
                                        <input type="checkbox" value="PENDING" class="status-checkbox text-indigo-500 focus:ring-indigo-500 rounded">
                                        En attente
                                    </label>
                                    <label class="flex items-center gap-2 text-sm text-slate-200">
                                        <input type="checkbox" value="ACCEPTED" class="status-checkbox text-indigo-500 focus:ring-indigo-500 rounded">
                                        En cours
                                    </label>
                                    <label class="flex items-center gap-2 text-sm text-slate-200">
                                        <input type="checkbox" value="DONE" class="status-checkbox text-indigo-500 focus:ring-indigo-500 rounded">
                                        Terminés
                                    </label>
                                    <label class="flex items-center gap-2 text-sm text-slate-200">
                                        <input type="checkbox" value="CANCELLED" class="status-checkbox text-indigo-500 focus:ring-indigo-500 rounded">
                                        Annulés
                                    </label>
                                </div>
                                <div class="px-2 py-1 text-xs text-slate-500 border-t border-slate-700">
                                    Sélection multiple autorisée
                                </div>
                            </div>
                        </div>
                        <select id="filterAllo" class="bg-slate-800 border border-slate-600 text-white text-sm rounded-lg p-2.5 focus:ring-indigo-500 focus:border-indigo-500 max-w-[220px]">
                            <option value="">Tous les allos</option>
                        </select>
                        <select id="filterAssignee" class="bg-slate-800 border border-slate-600 text-white text-sm rounded-lg p-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="all">Tous les agents</option>
                            <option value="unassigned">Non attribués</option>
                            <option value="me">Moi</option>
                        </select>
                        <select id="filterDateRange" class="bg-slate-800 border border-slate-600 text-white text-sm rounded-lg p-2.5 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="all">Tous les créneaux</option>
                            <option value="today">Aujourd’hui</option>
                            <option value="tomorrow">Demain</option>
                            <option value="week">Semaine</option>
                            <option value="custom">Plage personnalisée</option>
                        </select>
                        <div id="customDateRange" class="hidden flex items-center gap-2">
                            <input type="date" id="filterDateFrom" class="bg-slate-800 border border-slate-600 text-white text-xs rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <span class="text-slate-500 text-xs">→</span>
                            <input type="date" id="filterDateTo" class="bg-slate-800 border border-slate-600 text-white text-xs rounded-lg p-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="quick-filter inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-slate-700 text-xs text-slate-300 hover:text-white hover:border-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500" data-quick="mine">
                            <i class="fa-solid fa-user-check text-indigo-300"></i> Mes allos
                        </button>
                        <button type="button" class="quick-filter inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-slate-700 text-xs text-slate-300 hover:text-white hover:border-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500" data-quick="unassigned">
                            <i class="fa-solid fa-user-slash text-amber-300"></i> Non attribués
                        </button>
                        <button type="button" class="quick-filter inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-slate-700 text-xs text-slate-300 hover:text-white hover:border-slate-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500" data-quick="upcoming">
                            <i class="fa-regular fa-calendar text-emerald-300"></i> À venir (aujourd’hui/demain)
                        </button>
                    </div>
                </div>
                <div id="activeFilters" class="flex flex-wrap gap-2"></div>
            </div>
        </div>
        <div class="space-y-4" id="requestsContainer"></div>
    </div>
@endsection

@push('end_scripts')
    @include('admin.allos.partials.scripts', ['activeView' => 'requests'])
@endpush
