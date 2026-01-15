<div class="flex bg-slate-900 p-1 rounded-lg border border-slate-700 ml-4">
    <a href="{{ route('admin.allos.requests') }}" id="tabRequests" class="px-4 py-1.5 rounded-md text-sm font-medium transition-all {{ request()->routeIs('admin.allos.requests') ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-white' }}">
        <i class="fa-solid fa-bell mr-2"></i> Demandes <span class="ml-1 bg-red-500 text-white text-[10px] px-1.5 rounded-full" id="pendingCount">3</span>
    </a>
    <a href="{{ route('admin.allos.catalog') }}" id="tabCatalog" class="px-4 py-1.5 rounded-md text-sm font-medium transition-all {{ request()->routeIs('admin.allos.catalog') ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-white' }}">
        <i class="fa-solid fa-store mr-2"></i> Catalogue &amp; Créneaux
    </a>
</div>
