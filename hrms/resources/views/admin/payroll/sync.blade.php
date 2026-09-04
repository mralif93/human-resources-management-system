@extends('layouts.admin')

@section('page-title', 'Payroll Integration & Feeder')

@section('content')
<div class="space-y-6">

    <!-- Standard Page Header Banner -->
    <x-page-header
        title="Payroll Feeder & External Sync Hub"
        subtitle="Automated monthly feeder syncing verified work hours, overtime, and unpaid leave deductions"
        icon="bx-sync"
        badge="PayFlow MY Connected"
        badgeVariant="indigo"
    >
        <x-button
            type="button"
            variant="secondary"
            size="md"
            icon="bx bx-key"
            onclick="document.getElementById('modal-api-credentials').classList.remove('hidden')"
            class="bg-white/10 hover:bg-white/20 text-white border-white/20"
        >
            API Tokens
        </x-button>

        <!-- Month Selector Form -->
        <form method="GET" action="{{ route('payroll-sync.index') }}" class="inline-block">
            <input
                type="month"
                name="month"
                value="{{ $month }}"
                onchange="this.form.submit()"
                class="h-10 px-3.5 py-2 rounded-xl text-xs font-bold border border-white/20 bg-white/10 text-white placeholder-white/60 outline-none cursor-pointer focus:ring-2 focus:ring-indigo-400 backdrop-blur-sm"
            />
        </form>

        <x-button
            type="button"
            variant="success"
            size="md"
            icon="bx bx-download"
            onclick="document.getElementById('modal-confirm-export-payroll').classList.remove('hidden')"
            class="shadow-md shadow-emerald-600/20"
        >
            Export CSV
        </x-button>
    </x-page-header>

    <!-- Feedback Alerts -->
    @if (session('status'))
        <x-alert variant="success" icon="bx bx-check-circle" title="Success">
            {{ session('status') }}
        </x-alert>
    @endif

    <!-- High-Impact KPI Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card
            title="Base Gross Remuneration"
            :value="'MYR ' . number_format($totalGross, 2)"
            icon="bx bx-wallet"
            color="indigo"
            subtitle="Base monthly staff payroll"
        />

        <x-stat-card
            title="Regular Hours Tracked"
            :value="number_format($totalHours, 1) . ' hrs'"
            icon="bx bx-time"
            color="blue"
            subtitle="Verified attendance punches"
        />

        <x-stat-card
            title="Overtime Total"
            :value="number_format($totalOt, 1) . ' hrs'"
            icon="bx bx-bolt-circle"
            color="amber"
            change="Subject to 1.5x / 2.0x"
            changeType="increase"
        />

        <x-stat-card
            title="Unpaid Leaves Deducted"
            :value="number_format($totalUnpaidDays, 1) . ' days'"
            icon="bx bx-calendar-x"
            color="rose"
            subtitle="Formula: Daily Rate x Days"
        />
    </div>

    <!-- Feeder Calculation Matrix Table with Mobile Progressive Disclosure -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between gap-4 flex-wrap">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">
                    Monthly Payroll Data Feeder Calculation — Period {{ $month }}
                </h3>
                <p class="text-xs text-slate-500">
                    Live calculated values sent automatically to <strong class="text-indigo-600">PayFlow MY</strong> via REST endpoint <code class="bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded text-[11px]">GET /api/v1/payroll/feeder</code>
                </p>
            </div>

            <div class="flex items-center gap-2 text-xs">
                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-500">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    API Endpoint Live
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-950/50 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-4 sm:px-6">Employee</th>
                        <th class="py-3.5 px-4 hidden md:table-cell">Bank Details</th>
                        <th class="py-3.5 px-4">Basic Salary</th>
                        <th class="py-3.5 px-4 hidden sm:table-cell">Hours Worked</th>
                        <th class="py-3.5 px-4 hidden sm:table-cell">Overtime</th>
                        <th class="py-3.5 px-4">Unpaid Leave</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Feeder Net Base</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/70 text-slate-600 dark:text-slate-300 font-medium">
                    @forelse ($rows as $row)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="py-3.5 px-4 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300 font-black flex items-center justify-center text-xs shrink-0">
                                        {{ strtoupper(substr($row->employee->first_name, 0, 1) . substr($row->employee->last_name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold text-slate-900 dark:text-white block truncate max-w-[170px] sm:max-w-none">
                                            {{ $row->employee->full_name }}
                                        </span>
                                        <div class="flex items-center gap-1.5 text-[10px] text-slate-400 font-mono">
                                            <span>{{ $row->employee->employee_code }}</span>
                                            <span>&bull; {{ $row->employee->department?->name ?? 'General' }}</span>
                                        </div>

                                        <div class="mt-1 block sm:hidden text-[10px] text-slate-500">
                                            {{ $row->hours }} hrs &bull; OT: {{ $row->ot }} hrs
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 hidden md:table-cell font-mono text-[11px]">
                                <span class="font-semibold text-slate-900 dark:text-white block">{{ $row->employee->bank_name ?? 'Maybank' }}</span>
                                <span class="text-slate-400">{{ $row->employee->bank_account_number ?? '512345678901' }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900 dark:text-white">
                                MYR {{ number_format($row->salary, 2) }}
                            </td>
                            <td class="py-3.5 px-4 hidden sm:table-cell font-mono">
                                <span class="font-bold text-slate-700 dark:text-slate-300">{{ number_format($row->hours, 1) }}</span> hrs
                            </td>
                            <td class="py-3.5 px-4 hidden sm:table-cell font-mono">
                                @if($row->ot > 0)
                                    <span class="font-bold text-amber-600 dark:text-amber-400">+{{ number_format($row->ot, 1) }}</span> hrs
                                @else
                                    <span class="text-slate-400">0.0</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono">
                                @if($row->unpaidDays > 0)
                                    <span class="text-rose-600 font-bold block">-{{ $row->unpaidDays }} days</span>
                                    <span class="text-[10px] text-rose-500">(-MYR {{ number_format($row->deduction, 2) }})</span>
                                @else
                                    <span class="text-slate-400 text-xs">0 days</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 sm:px-6 text-right font-mono font-black text-indigo-600 dark:text-indigo-400 text-sm">
                                MYR {{ number_format(max(0, $row->salary - $row->deduction), 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                No active employees found for this period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal: API Credentials & PayFlow Integration Spec -->
<x-modal name="api-credentials" title="PayFlow MY REST API Credentials" size="2xl">
    <div class="space-y-4 text-xs">
        <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
            External payroll systems ingest real-time employee attendance hours, overtime totals, and unpaid leave days using standard Bearer token authorization.
        </p>

        <!-- Quick Curl Snippet -->
        <div class="p-3.5 rounded-xl bg-slate-900 text-slate-200 font-mono text-[11px] space-y-1.5 overflow-x-auto">
            <p class="text-slate-400 text-[10px] uppercase font-bold">Sample Integration Request (cURL):</p>
            <p class="text-emerald-400 select-all">
                curl -H "Authorization: Bearer {{ $tokens->first()->token ?? 'payflow_sec_live_...' }}" \<br>
                &nbsp;&nbsp;&nbsp;&nbsp; "{{ url('/api/v1/payroll/feeder?month=' . $month) }}"
            </p>
        </div>

        <!-- Token Management Table -->
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <h4 class="font-extrabold text-slate-900 dark:text-white">Active Feeder API Tokens</h4>
            </div>

            <div class="rounded-xl border border-slate-200 dark:border-slate-800 divide-y divide-slate-200 dark:divide-slate-800 overflow-hidden">
                @foreach($tokens as $tok)
                    <div class="p-3 flex items-center justify-between gap-3 bg-white dark:bg-slate-900">
                        <div class="min-w-0">
                            <p class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                <span>{{ $tok->name }}</span>
                                @if($tok->is_active)
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300">Active</span>
                                @else
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-100 text-slate-600">Inactive</span>
                                @endif
                            </p>
                            <p class="font-mono text-[10px] text-slate-400 truncate max-w-sm">{{ $tok->token }}</p>
                        </div>

                        <form method="POST" action="{{ route('payroll-sync.tokens.toggle', $tok) }}">
                            @csrf
                            <x-button type="submit" variant="{{ $tok->is_active ? 'secondary' : 'primary' }}" size="xs">
                                {{ $tok->is_active ? 'Deactivate' : 'Activate' }}
                            </x-button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Create New Token Form -->
        <form method="POST" action="{{ route('payroll-sync.tokens.store') }}" class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center gap-2">
            @csrf
            <input
                type="text"
                name="name"
                required
                placeholder="Token Name (e.g. Staging Server)"
                class="flex-1 h-9 px-3 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500"
            />
            <x-button type="submit" variant="primary" size="sm" icon="bx bx-plus">
                Generate Token
            </x-button>
        </form>

        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end">
            <x-button type="button" variant="ghost" size="sm" onclick="document.getElementById('modal-api-credentials').classList.add('hidden')">
                Close
            </x-button>
        </div>
    </div>
</x-modal>

<!-- Confirmation Dialog: Export Payroll Dataset -->
<x-confirm-dialog
    name="export-payroll"
    title="Confirm Payroll Feeder Export"
    message="Are you sure you want to download the payroll feeder CSV dataset for period {{ $month }}? This will log an export audit record and download current verified work hours and deductions."
    confirmText="Download Payroll CSV"
    cancelText="Cancel"
    variant="success"
    icon="bx bx-download text-emerald-600 dark:text-emerald-400"
/>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('modal-confirm-export-payroll-form');
        if (form) {
            form.method = 'GET';
            form.action = "{{ route('payroll-sync.export', ['month' => $month]) }}";
        }
    });
</script>

@endsection
