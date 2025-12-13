@extends('admin.layout')

@section('title', "Tableau de bord admin - P'AS'SION BDS")

@section('content')
    <!-- Page Title -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white">Tableau de Bord</h2>
            <p class="text-slate-400 mt-1">
                Bienvenue,
                <span class="text-slate-200 font-semibold">
                    {{ auth()->user()?->display_name ?: auth()->user()?->university_email ?: 'Admin' }}
                </span>
            </p>
        </div>
        {{-- Buttons placeholder (disabled for now) --}}
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Card 1 -->
        <div class="bg-slate-800 rounded-xl p-6 border border-slate-700 shadow-lg">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Points Distribués</p>
                    <h3 class="text-2xl font-bold text-white mt-1">
                        {{ number_format((int) ($stats['points_distributed'] ?? 0), 0, ',', ' ') }}
                    </h3>
                </div>
                <div class="p-2 bg-yellow-500/10 rounded-lg text-yellow-400">
                    <i class="fa-solid fa-coins text-lg"></i>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-4">
                {{ $stats['points_distributed_hint'] ?? '—' }}
            </p>
        </div>

        <!-- Card 2 -->
        <div class="bg-slate-800 rounded-xl p-6 border border-slate-700 shadow-lg">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Comptes Actifs</p>
                    <h3 class="text-2xl font-bold text-white mt-1">
                        {{ number_format((int) ($stats['active_users'] ?? 0), 0, ',', ' ') }}
                    </h3>
                </div>
                <div class="p-2 bg-indigo-500/10 rounded-lg text-indigo-400">
                    <i class="fa-solid fa-users text-lg"></i>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-4">
                {{ $stats['active_users_hint'] ?? '—' }}
            </p>
        </div>

        <!-- Card 3 -->
        <div class="bg-slate-800 rounded-xl p-6 border border-slate-700 shadow-lg">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Allos en attente</p>
                    <h3 class="text-2xl font-bold text-white mt-1">
                        {{ number_format((int) ($stats['pending_allos'] ?? 0), 0, ',', ' ') }}
                    </h3>
                </div>
                <div class="p-2 bg-red-500/10 rounded-lg text-red-400">
                    <i class="fa-solid fa-phone text-lg"></i>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-4">
                {{ $stats['pending_allos_hint'] ?? '—' }}
            </p>
        </div>

        <!-- Card 4 -->
        <div class="bg-slate-800 rounded-xl p-6 border border-slate-700 shadow-lg">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Preuves Défis</p>
                    <h3 class="text-2xl font-bold text-white mt-1">
                        {{ number_format((int) ($stats['pending_challenge_proofs'] ?? 0), 0, ',', ' ') }}
                    </h3>
                </div>
                <div class="p-2 bg-green-500/10 rounded-lg text-green-400">
                    <i class="fa-solid fa-image text-lg"></i>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-4">
                {{ $stats['pending_challenge_proofs_hint'] ?? '—' }}
            </p>
        </div>
    </div>

    <!-- Recent Activity Table (Audit Logs) -->
    <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white">Activité</h3>

            {{-- adapte ce lien quand tu auras la page index --}}
            <a href="{{ url('/admin/audit-logs') }}" class="text-xs text-indigo-400 hover:text-indigo-300">
                Tout voir
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-400">
                <thead class="bg-slate-900/50 text-slate-200 uppercase text-xs font-semibold">
                <tr>
                    <th class="px-6 py-3">Admin</th>
                    <th class="px-6 py-3">Action</th>
                    <th class="px-6 py-3">Cible</th>
                    <th class="px-6 py-3">IP</th>
                    <th class="px-6 py-3 text-right">Date</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-slate-700" id="activityTableBody">
                <tr>
                    <td class="px-6 py-4" colspan="4">Chargement…</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
@push('end_scripts')
    <script>
        (function () {
            'use strict';

            const DISPLAY_TIMEZONE = 'Europe/Paris';
            const MANUAL_HOUR_OFFSET = -1;

            const activityTableBodyElement = document.getElementById('activityTableBody');
            if (!activityTableBodyElement) return;

            const auditLogsEndpointUrl = "{{ url('/admin/api/audit-logs') }}";

            const seenLogIds = new Set();
            let lastSeenId = 0;

            function escapeHtml(value) {
                return String(value ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            function formatDateFrench(isoString) {
                if (!isoString) return '—';

                const dateObject = new Date(isoString);
                if (Number.isNaN(dateObject.getTime())) return '—';

                const shiftedDate = new Date(dateObject.getTime() + (MANUAL_HOUR_OFFSET * 60 * 60 * 1000));

                return new Intl.DateTimeFormat('fr-FR', {
                    dateStyle: 'short',
                    timeStyle: 'short',
                    timeZone: DISPLAY_TIMEZONE,
                }).format(shiftedDate);
            }

            function buildTargetLabel(entityType, entityId) {
                const safeType = escapeHtml(entityType || '—');
                const safeId = entityId ? `#${escapeHtml(entityId)}` : '';
                return `${safeType}${safeId ? ' ' + safeId : ''}`;
            }

            function buildActionBadge(action) {
                const safeAction = escapeHtml(action || '—');
                const isAuth = String(action || '').startsWith('AUTH.');

                const badgeClass = isAuth
                    ? 'bg-indigo-500/10 text-indigo-300 border-indigo-500/30'
                    : 'bg-slate-700/60 text-slate-200 border-slate-600';

                return `
                <span class="inline-flex items-center px-2 py-1 rounded-md border text-xs font-semibold ${badgeClass}">
                    ${safeAction}
                </span>
            `;
            }

            function renderEmptyRow(message) {
                activityTableBodyElement.innerHTML = `
                <tr>
                    <td class="px-6 py-4 text-slate-500" colspan="4">${escapeHtml(message)}</td>
                </tr>
            `;
            }

            function renderErrorRow(message) {
                activityTableBodyElement.innerHTML = `
                <tr>
                    <td class="px-6 py-4 text-red-300" colspan="4">
                        <i class="fa-solid fa-circle-exclamation mr-2"></i>${escapeHtml(message)}
                    </td>
                </tr>
            `;
            }

            function buildRowHtml(auditLog) {
                const actorName = auditLog.actor_name || 'Anonyme';
                const action = auditLog.action || '—';
                const entityType = auditLog.entity_type || '—';
                const entityId = auditLog.entity_id ?? null;
                const createdAt = auditLog.created_at || null;

                const ipAddress = auditLog.ip_address || '—';
                const userAgent = auditLog.user_agent || '';

                return `
        <tr class="hover:bg-slate-900/30 transition-colors">
            <td class="px-6 py-4 text-slate-200 font-semibold">${escapeHtml(actorName)}</td>
            <td class="px-6 py-4">${buildActionBadge(action)}</td>
            <td class="px-6 py-4">${buildTargetLabel(entityType, entityId)}</td>
            <td class="px-6 py-4 text-slate-300" title="${escapeHtml(userAgent)}">${escapeHtml(ipAddress)}</td>
            <td class="px-6 py-4 text-right text-slate-300">${escapeHtml(formatDateFrench(createdAt))}</td>
        </tr>
    `;
            }


            function trackLastSeenId(log) {
                const id = Number(log?.id || 0);
                if (id > lastSeenId) lastSeenId = id;
            }

            function prependIfNew(log) {
                const id = Number(log?.id || 0);
                if (!id || seenLogIds.has(id)) return;

                seenLogIds.add(id);
                trackLastSeenId(log);

                // Newest first
                activityTableBodyElement.insertAdjacentHTML('afterbegin', buildRowHtml(log));
            }

            async function fetchLogs(afterId) {
                const url = afterId > 0
                    ? `${auditLogsEndpointUrl}?after_id=${encodeURIComponent(afterId)}`
                    : auditLogsEndpointUrl;

                const response = await fetch(url, {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' },
                });

                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            }

            async function loadInitialSnapshot() {
                renderEmptyRow('Chargement…');

                try {
                    const payload = await fetchLogs(0);
                    const logs = Array.isArray(payload?.data) ? payload.data : [];

                    if (logs.length === 0) {
                        renderEmptyRow('Aucune activité pour le moment.');
                        return;
                    }

                    // backend returns newest first for snapshot
                    activityTableBodyElement.innerHTML = '';
                    seenLogIds.clear();
                    lastSeenId = 0;

                    for (const log of logs) {
                        const id = Number(log?.id || 0);
                        if (id) seenLogIds.add(id);
                        trackLastSeenId(log);
                        activityTableBodyElement.insertAdjacentHTML('beforeend', buildRowHtml(log));
                    }
                } catch (e) {
                    renderErrorRow('Impossible de charger les logs.');
                }
            }

            async function pollNewLogs() {
                try {
                    const payload = await fetchLogs(lastSeenId);
                    const logs = Array.isArray(payload?.data) ? payload.data : [];
                    // backend returns ASC when after_id is used
                    for (const log of logs) prependIfNew(log);
                } catch (e) {
                    // no UI spam; keep quiet after initial
                }
            }

            loadInitialSnapshot().then(() => {
                window.setInterval(pollNewLogs, 5000);
            });
        })();
    </script>
@endpush

