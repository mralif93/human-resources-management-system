@extends('layouts.admin')

@section('page-title', 'Activity Audit Trail')

@section('content')
<div class="space-y-6">

    <!-- Standard Page Header Banner -->
    <x-page-header
        title="Enterprise Activity Audit Trail"
        subtitle="Chronological ledger of security, payroll sync, employee PIM modifications, and administrative decisions"
        icon="bx-history"
        badge="Immutable Log"
        badgeVariant="emerald"
    >
        <x-button
            type="button"
            variant="secondary"
            size="md"
            icon="bx bx-download"
            onclick="document.getElementById('modal-confirm-export-audit-logs').classList.remove('hidden')"
            class="bg-white/10 hover:bg-white/20 text-white border-white/20"
        >
            Export CSV
        </x-button>

        <x-button
            type="button"
            variant="secondary"
            size="md"
            icon="bx bx-refresh"
            onclick="window.location.reload()"
            class="bg-white/10 hover:bg-white/20 text-white border-white/20"
        >
            Refresh Log
        </x-button>
    </x-page-header>

    <!-- High-Impact KPI Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card
            title="Total Audit Events"
            :value="$totalLogs"
            icon="bx bx-history"
            color="indigo"
            subtitle="Recorded ledger entries"
        />

        <x-stat-card
            title="Security &amp; Auth"
            :value="$authEvents"
            icon="bx bx-shield-quarter"
            color="blue"
            subtitle="Login and session challenges"
        />

        <x-stat-card
            title="Payroll Sync Feeds"
            :value="$payrollEvents"
            icon="bx bx-wallet"
            color="emerald"
            subtitle="PayFlow MY data transfers"
        />

        <x-stat-card
            title="Distinct Admin Actors"
            :value="$activeActors"
            icon="bx bx-user-check"
            color="purple"
            subtitle="Operators with recorded events"
        />
    </div>

    <!-- Search & Category Filters -->
    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 text-xs font-bold gap-4 flex-wrap pb-3">
        <div class="flex items-center gap-2 flex-wrap">
            @php
                $categories = [
                    '' => 'All Categories',
                    'auth' => 'Security & Auth',
                    'pim' => 'Personnel (PIM)',
                    'attendance' => 'Attendance',
                    'leaves' => 'Leave Requests',
                    'performance' => 'Performance OKRs',
                    'recruitment' => 'Recruitment ATS',
                    'payroll' => 'Payroll Feeder',
                ];
            @endphp

            @foreach($categories as $catKey => $catLabel)
                <a
                    href="{{ route('audit-logs.index', ['category' => $catKey, 'search' => $search]) }}"
                    class="px-3 py-1.5 rounded-xl border text-xs font-bold transition {{ (string)$category === (string)$catKey ? 'bg-indigo-600 text-white border-indigo-600 shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:border-slate-400' }}"
                >
                    {{ $catLabel }}
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('audit-logs.index') }}" class="flex items-center gap-2">
            @if($category)
                <input type="hidden" name="category" value="{{ $category }}">
            @endif
            <div class="relative">
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search logs, IP, action..."
                    class="w-64 h-9 pl-8 pr-3 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500"
                />
                <i class="bx bx-search absolute left-2.5 top-2.5 text-slate-400 text-sm pointer-events-none"></i>
            </div>
            <x-button type="submit" variant="secondary" size="xs">Search</x-button>
        </form>
    </div>

    <!-- Audit Log Records Table with Mobile Progressive Disclosure -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-950/50 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-4 sm:px-6">Timestamp &amp; Actor</th>
                        <th class="py-3.5 px-4">Event Category</th>
                        <th class="py-3.5 px-4">Action Summary</th>
                        <th class="py-3.5 px-4 hidden md:table-cell">Client IP / Device</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/70 text-slate-600 dark:text-slate-300 font-medium">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="py-3.5 px-4 sm:px-6">
                                <div class="space-y-0.5">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-slate-900 dark:text-white">
                                            {{ $log->user?->name ?? 'System Service' }}
                                        </span>
                                    </div>
                                    <p class="text-[10px] text-slate-400 font-mono">
                                        {{ $log->created_at->format('d M Y, h:i:s A') }}
                                    </p>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase tracking-wider
                                    @if($log->category === 'auth') bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800
                                    @elseif($log->category === 'payroll') bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800
                                    @elseif($log->category === 'leaves') bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800
                                    @elseif($log->category === 'pim') bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800
                                    @else bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800 @endif
                                ">
                                    {{ $log->category }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-slate-900 dark:text-white block max-w-sm sm:max-w-md truncate">
                                    {{ $log->description }}
                                </span>
                                <span class="text-[10px] text-slate-400 font-mono block truncate max-w-xs sm:max-w-sm">
                                    Event: {{ $log->event }}
                                </span>

                                <div class="mt-1 block md:hidden text-[10px] text-slate-400 font-mono">
                                    IP: {{ $log->ip_address ?? '127.0.0.1' }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 hidden md:table-cell font-mono text-[11px]">
                                <span class="font-semibold text-slate-800 dark:text-slate-200 block">{{ $log->ip_address ?? '127.0.0.1' }}</span>
                                <span class="text-[10px] text-slate-400 truncate max-w-[180px] block" title="{{ $log->user_agent }}">
                                    {{ $log->user_agent ?? 'Internal Agent' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 sm:px-6 text-right">
                                <x-button
                                    type="button"
                                    variant="secondary"
                                    size="xs"
                                    icon="bx bx-code-alt"
                                    onclick="inspectAuditPayload({{ json_encode([
                                        'event' => $log->event,
                                        'category' => $log->category,
                                        'description' => $log->description,
                                        'actor' => $log->user?->name ?? 'System Console',
                                        'ip' => $log->ip_address ?? '127.0.0.1',
                                        'timestamp' => $log->created_at->format('Y-m-d H:i:s T'),
                                        'payload' => $log->payload,
                                    ]) }})"
                                >
                                    Inspect
                                </x-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                <i class="bx bx-history text-3xl block mb-2 text-slate-300 dark:text-slate-600"></i>
                                No activity audit log entries matching criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination :paginator="$logs" />
    </div>

</div>

<!-- Modal: Inspect Audit Log Payload (JSON Inspector) -->
<x-modal name="inspect-payload" title="Audit Entry Event Inspection" size="xl">
    <div class="space-y-4 text-xs">
        <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl space-y-1">
            <p class="font-bold text-slate-900 dark:text-white" id="modal-audit-event"></p>
            <p class="text-slate-600 dark:text-slate-300" id="modal-audit-desc"></p>
            <div class="flex items-center gap-3 pt-1 text-[11px] text-slate-400 font-mono">
                <span>Actor: <strong id="modal-audit-actor" class="text-indigo-600"></strong></span>
                <span>&bull; IP: <strong id="modal-audit-ip"></strong></span>
                <span>&bull; Time: <strong id="modal-audit-time"></strong></span>
            </div>
        </div>

        <div>
            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                Contextual Event Payload (JSON Diff)
            </label>
            <pre id="modal-audit-json" class="p-4 rounded-xl bg-slate-900 text-emerald-400 font-mono text-[11px] overflow-x-auto max-h-60 custom-scrollbar"></pre>
        </div>

        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end">
            <x-button type="button" variant="ghost" size="sm" onclick="document.getElementById('modal-inspect-payload').classList.add('hidden')">
                Close
            </x-button>
        </div>
    </div>
</x-modal>

@push('scripts')
<script>
    function inspectAuditPayload(data) {
        document.getElementById('modal-audit-event').innerText = data.event + ' (' + data.category + ')';
        document.getElementById('modal-audit-desc').innerText = data.description;
        document.getElementById('modal-audit-actor').innerText = data.actor;
        document.getElementById('modal-audit-ip').innerText = data.ip;
        document.getElementById('modal-audit-time').innerText = data.timestamp;
        document.getElementById('modal-audit-json').innerText = JSON.stringify(data.payload, null, 2) || '{}';

        document.getElementById('modal-inspect-payload').classList.remove('hidden');
    }
</script>
@endpush

<!-- Confirmation Dialog: Export Activity Audit Trail -->
<x-confirm-dialog
    name="export-audit-logs"
    title="Confirm Audit Trail Export"
    message="Are you sure you want to export activity audit trail records to CSV? This compliance dataset contains historical user security authentications, payroll synchronization triggers, and employee record updates."
    confirmText="Download Audit CSV"
    cancelText="Cancel"
    variant="success"
    icon="bx bx-download text-emerald-600 dark:text-emerald-400"
/>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const exportForm = document.getElementById('modal-confirm-export-audit-logs-form');
        if (exportForm) {
            exportForm.method = 'GET';
            exportForm.action = "{{ route('audit-logs.export', ['category' => request('category'), 'search' => request('search')]) }}";
        }
    });
</script>

@endsection
