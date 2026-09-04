@extends('layouts.admin')

@section('page-title', 'Leave & Absence Management')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar & Summary Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">Leave &amp; Time-Off Operations</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Manage statutory Malaysian leave entitlements, multi-tier approvals, and payroll unpaid deduction feeds.</p>
        </div>

        <div class="flex items-center gap-2.5">
            <x-button
                type="button"
                variant="primary"
                size="md"
                icon="bx bx-plus"
                onclick="document.getElementById('modal-apply-leave').classList.remove('hidden')"
                class="shadow-lg shadow-indigo-600/30"
            >
                Submit Leave Application
            </x-button>
        </div>
    </div>

    <!-- Alert Messages -->
    @if (session('status'))
        <x-alert variant="success" icon="bx bx-check-circle" title="Success">
            {{ session('status') }}
        </x-alert>
    @endif

    @if (session('error'))
        <x-alert variant="danger" icon="bx bx-error-circle" title="Action Prohibited">
            {{ session('error') }}
        </x-alert>
    @endif

    <!-- High-Impact KPI Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card
            title="Total Applications"
            :value="$totalApplications"
            icon="bx bx-calendar-event"
            color="indigo"
            subtitle="Calendar year {{ $selectedYear }}"
        />

        <x-stat-card
            title="Pending Approvals"
            :value="$pendingApprovals"
            icon="bx bx-time"
            color="amber"
            change="{{ $pendingApprovals > 0 ? 'Action Req' : 'Cleared' }}"
            changeType="{{ $pendingApprovals === 0 ? 'increase' : 'neutral' }}"
        />

        <x-stat-card
            title="On Leave Today"
            :value="$onLeaveToday"
            icon="bx bx-user-check"
            color="emerald"
            subtitle="Active absences today"
        />

        <x-stat-card
            title="Unpaid Leave Days"
            :value="number_format($unpaidLeaveDays, 1)"
            icon="bx bx-wallet"
            color="rose"
            subtitle="Payroll deduction feed"
        />
    </div>

    <!-- Filter & Search Toolbar Component -->
    <x-filter-toolbar
        :action="route('leaves.index')"
        :resetUrl="route('leaves.index')"
        :searchPlaceholder="'Search applicant name, employee code...'"
        :filterTitle="'Leave Directory Filters'"
    >
        <x-slot:filters>
            <!-- Filter: Leave Type -->
            <div>
                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                    Leave Type
                </label>
                <x-select name="leave_type_id" class="w-full">
                    <option value="">All Leave Categories</option>
                    @foreach($leaveTypes as $type)
                        <option value="{{ $type->id }}" {{ request('leave_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }} ({{ $type->code }})
                        </option>
                    @endforeach
                </x-select>
            </div>

            <!-- Filter: Approval Status -->
            <div>
                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                    Application Status
                </label>
                <x-select name="status" class="w-full">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Approval</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </x-select>
            </div>
        </x-slot:filters>
    </x-filter-toolbar>

    <!-- Leaves Roster Table Card with Progressive Mobile Disclosure -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-950/50 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-4 sm:px-6">Applicant Employee</th>
                        <th class="py-3.5 px-4">Leave Type</th>
                        <th class="py-3.5 px-4 hidden md:table-cell">Duration &amp; Dates</th>
                        <th class="py-3.5 px-4">Days</th>
                        <th class="py-3.5 px-4 hidden sm:table-cell">Status</th>
                        <th class="py-3.5 px-4 hidden lg:table-cell">Approver</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/70 text-slate-600 dark:text-slate-300 font-medium">
                    @forelse ($leaves as $leave)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="py-3.5 px-4 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300 font-black flex items-center justify-center text-xs shrink-0">
                                        {{ strtoupper(substr($leave->employee->first_name, 0, 1) . substr($leave->employee->last_name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold text-slate-900 dark:text-white block truncate max-w-[170px] sm:max-w-none">
                                            {{ $leave->employee->full_name }}
                                        </span>
                                        <div class="flex items-center gap-1.5 text-[10px] text-slate-400 font-mono">
                                            <span>{{ $leave->employee->employee_code }}</span>
                                            <span class="hidden sm:inline">&bull; {{ $leave->employee->department?->name ?? 'General' }}</span>
                                        </div>

                                        <!-- Mobile Duration details visible only on small screens -->
                                        <div class="mt-1 block md:hidden text-[10px] text-slate-500 dark:text-slate-400">
                                            {{ $leave->start_date->format('d M') }} - {{ $leave->end_date->format('d M Y') }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider
                                    @if($leave->leaveType->code === 'AL') bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/60
                                    @elseif($leave->leaveType->code === 'SL') bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60
                                    @elseif($leave->leaveType->code === 'HL') bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60
                                    @elseif($leave->leaveType->code === 'UL') bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60
                                    @else bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 @endif
                                ">
                                    {{ $leave->leaveType->name }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 hidden md:table-cell font-mono text-[11px]">
                                {{ $leave->start_date->format('d M Y') }} &rarr; {{ $leave->end_date->format('d M Y') }}
                                <p class="text-[10px] text-slate-400 font-sans italic truncate max-w-[200px]">{{ $leave->reason }}</p>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                {{ $leave->total_days }} {{ Str::plural('day', (float)$leave->total_days) }}
                            </td>
                            <td class="py-3.5 px-4 hidden sm:table-cell">
                                @if($leave->status === 'approved')
                                    <x-badge variant="emerald" size="sm" :dot="true">
                                        Approved
                                    </x-badge>
                                @elseif($leave->status === 'pending')
                                    <x-badge variant="amber" size="sm" :dot="true">
                                        Pending Review
                                    </x-badge>
                                @elseif($leave->status === 'rejected')
                                    <x-badge variant="rose" size="sm">
                                        Rejected
                                    </x-badge>
                                @else
                                    <x-badge variant="slate" size="sm">
                                        {{ ucfirst($leave->status) }}
                                    </x-badge>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 hidden lg:table-cell text-slate-500 dark:text-slate-400">
                                {{ $leave->approver?->full_name ?? '—' }}
                            </td>
                            <td class="py-3.5 px-4 sm:px-6 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($leave->status === 'pending')
                                        <x-circle-action-button
                                            icon="bx bx-check"
                                            variant="emerald"
                                            size="sm"
                                            type="button"
                                            title="Approve Leave"
                                            onclick="openConfirmDialog({
                                                name: 'approve-leave',
                                                action: '{{ route('leaves.approve', $leave) }}',
                                                method: 'POST',
                                                title: 'Approve Leave Application?',
                                                message: 'Approve {{ $leave->total_days }} day(s) of {{ $leave->leaveType->name }} for {{ addslashes($leave->employee->full_name) }}?',
                                                confirmText: 'Yes, Approve'
                                            })"
                                        />

                                        <x-circle-action-button
                                            icon="bx bx-x"
                                            variant="rose"
                                            size="sm"
                                            type="button"
                                            title="Reject Leave"
                                            onclick="openConfirmDialog({
                                                name: 'reject-leave',
                                                action: '{{ route('leaves.reject', $leave) }}',
                                                method: 'POST',
                                                title: 'Reject Leave Application?',
                                                message: 'Reject leave application for {{ addslashes($leave->employee->full_name) }}? Days will be released back to balance.',
                                                confirmText: 'Yes, Reject Application'
                                            })"
                                        />
                                    @else
                                        <span class="text-[10px] text-slate-400 italic">Concluded</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i class="bx bx-calendar-x text-3xl block mb-2 text-slate-300 dark:text-slate-600"></i>
                                No leave applications found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Section Component -->
        <x-pagination :paginator="$leaves" />
    </div>
</div>

<!-- Reusable Confirmation Dialogs -->
<x-confirm-dialog name="approve-leave" />
<x-confirm-dialog name="reject-leave" />

<!-- Reusable 2-Column Application Modal -->
<x-modal name="apply-leave" title="Submit Leave Application" size="2xl">
    <form method="POST" action="{{ route('leaves.store') }}" class="space-y-6">
        @csrf

        <!-- Section 1: Applicant & Leave Type -->
        <div>
            <h4 class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                <i class="bx bx-user-pin text-base"></i>
                <span>Employee &amp; Category</span>
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Select Employee <span class="text-rose-500">*</span>
                    </label>
                    <x-select name="employee_id" required class="w-full">
                        <option value="">Choose Employee...</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->full_name }} ({{ $emp->employee_code }})
                            </option>
                        @endforeach
                    </x-select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Leave Type <span class="text-rose-500">*</span>
                    </label>
                    <x-select name="leave_type_id" required class="w-full">
                        <option value="">Choose Leave Type...</option>
                        @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }} ({{ $type->code }}) — {{ $type->days_allowed }}d {{ $type->is_paid ? 'Paid' : 'Unpaid' }}
                            </option>
                        @endforeach
                    </x-select>
                </div>
            </div>
        </div>

        <!-- Section 2: Date Range & Duration -->
        <div>
            <h4 class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                <i class="bx bx-calendar text-base"></i>
                <span>Dates &amp; Total Duration</span>
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Start Date <span class="text-rose-500">*</span>
                    </label>
                    <x-date-picker name="start_date" :value="old('start_date', date('Y-m-d'))" required />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        End Date <span class="text-rose-500">*</span>
                    </label>
                    <x-date-picker name="end_date" :value="old('end_date', date('Y-m-d'))" required />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Total Days <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="number"
                        name="total_days"
                        step="0.5"
                        min="0.5"
                        max="365"
                        value="{{ old('total_days', '1.0') }}"
                        required
                        class="w-full h-10 px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                </div>
            </div>
        </div>

        <!-- Section 3: Reason & Justification -->
        <div>
            <h4 class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                <i class="bx bx-comment-detail text-base"></i>
                <span>Reason &amp; Remarks</span>
            </h4>
            <textarea
                name="reason"
                rows="3"
                required
                placeholder="State the reason or purpose for time-off..."
                class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-indigo-500"
            >{{ old('reason') }}</textarea>
        </div>

        <!-- Modal Action Buttons -->
        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
            <x-button
                type="button"
                variant="ghost"
                size="sm"
                onclick="document.getElementById('modal-apply-leave').classList.add('hidden')"
            >
                Cancel
            </x-button>
            <x-button type="submit" variant="primary" size="sm" icon="bx bx-paper-plane" class="shadow-md shadow-indigo-600/30">
                Submit For Review
            </x-button>
        </div>
    </form>
</x-modal>
@endsection
