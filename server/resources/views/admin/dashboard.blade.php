@extends('admin.layout')

@section('title', "Tableau de bord admin - P'AS'SION BDS")

@section('content')
    <!-- Page Title -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white">Tableau de Bord</h2>
            <p class="text-slate-400 mt-1">Bienvenue, XXX</p>
        </div>
        {{--<div class="flex gap-3">
            <button class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg text-sm font-medium transition-colors">
                <i class="fa-solid fa-plus mr-2"></i> Défi Rapide
            </button>
            <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors shadow-lg shadow-indigo-500/20">
                <i class="fa-solid fa-gift mr-2"></i> Donner Points
            </button>
        </div>--}}
    </div>

    <!-- Stats Grid -->
    <!-- Grid responsive behavior kept standard (md:2 cols, lg:4 cols) as it fits content -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

        <!-- Card 1 -->
        <div class="bg-slate-800 rounded-xl p-6 border border-slate-700 shadow-lg">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Points Distribués</p>
                    <h3 class="text-2xl font-bold text-white mt-1">124,500</h3>
                </div>
                <div class="p-2 bg-yellow-500/10 rounded-lg text-yellow-400">
                    <i class="fa-solid fa-coins text-lg"></i>
                </div>
            </div>
            <p class="text-xs text-green-400 mt-4 flex items-center">
                <i class="fa-solid fa-arrow-trend-up mr-1"></i> +12% cette semaine
            </p>
        </div>

        <!-- Card 2 -->
        <div class="bg-slate-800 rounded-xl p-6 border border-slate-700 shadow-lg">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Comptes Actifs</p>
                    <h3 class="text-2xl font-bold text-white mt-1">843</h3>
                </div>
                <div class="p-2 bg-indigo-500/10 rounded-lg text-indigo-400">
                    <i class="fa-solid fa-users text-lg"></i>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-4">Sur 1200 étudiants</p>
        </div>

        <!-- Card 3 -->
        <div class="bg-slate-800 rounded-xl p-6 border border-slate-700 shadow-lg">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Allos en attente</p>
                    <h3 class="text-2xl font-bold text-white mt-1">3</h3>
                </div>
                <div class="p-2 bg-red-500/10 rounded-lg text-red-400">
                    <i class="fa-solid fa-phone text-lg"></i>
                </div>
            </div>
            <p class="text-xs text-red-400 mt-4 flex items-center">
                <i class="fa-solid fa-circle-exclamation mr-1"></i> Action requise
            </p>
        </div>

        <!-- Card 4 -->
        <div class="bg-slate-800 rounded-xl p-6 border border-slate-700 shadow-lg">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Preuves Défis</p>
                    <h3 class="text-2xl font-bold text-white mt-1">12</h3>
                </div>
                <div class="p-2 bg-green-500/10 rounded-lg text-green-400">
                    <i class="fa-solid fa-image text-lg"></i>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-4">À valider par les admins</p>
        </div>
    </div>

    <!-- Recent Activity Table (Audit Logs) -->
    <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white">Activité</h3>
            <a href="#" class="text-xs text-indigo-400 hover:text-indigo-300">Tout voir</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-400">
                <thead class="bg-slate-900/50 text-slate-200 uppercase text-xs font-semibold">
                <tr>
                    <th class="px-6 py-3">Admin</th>
                    <th class="px-6 py-3">Action</th>
                    <th class="px-6 py-3">Cible</th>
                    <th class="px-6 py-3 text-right">Date</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-700" id="activityTableBody">
                <!-- Data injected via JS -->
                </tbody>
            </table>
        </div>
    </div>
@endsection
