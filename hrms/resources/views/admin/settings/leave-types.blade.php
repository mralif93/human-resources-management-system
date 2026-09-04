@extends('layouts.admin')

@section('title', 'Leave Policies & Statutory Rules - PulseHR Management System')
@section('page-title', 'Leave Policies & Statutory Entitlements')

@section('content')
<div class="space-y-6 animate__animated animate__fadeIn">

    <!-- Top Action Header Banner (matching PIM & ATS standard) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div>
            <h2 class="text-base font-extrabold text-slate-900 dark:text-white">Leave Entitlements &amp; Policy Registry</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Configure statutory allowances under Employment Act 1955, paid remuneration flags, and policy lifecycle status</p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <x-button
                type="button"
                variant="primary"
                size="md"
                icon="bx bx-plus"
                onclick="document.getElementById('modal-new-leavetype').classList.remove('hidden')"
                class="shadow-md shadow-indigo-600/20"
            >
                Add Leave Policy
            </x-button>
        </div>
    </div>

    <!-- KPI Metric Cards Grid (Standard 4 Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card
            title="Total Policies"
            value="{{ $totalCategories }}"
            icon="bx bx-calendar-event"
            color="indigo"
            subtitle="{{ $activeCategories }} active, {{ $inactiveCategories }} archived"
        />

        <x-stat-card
            title="Active Policies"
            value="{{ $activeCategories }}"
            icon="bx bx-check-circle"
            color="emerald"
            subtitle="Available for application"
        />

        <x-stat-card
            title="Deactivated / Archived"
            value="{{ $inactiveCategories }}"
            icon="bx bx-archive"
            color="rose"
            subtitle="Hidden from employees"
        />

        <x-stat-card
            title="Proof / MC Required"
            value="{{ $attachmentMandatory }}"
            icon="bx bx-paperclip"
            color="sky"
            subtitle="Mandatory documentation"
        />
    </div>

    <!-- Search & Filter Toolbar Component -->
    <x-filter-toolbar
        title="Search &amp; Filter Policies"
        subtitle="Search leave categories by name, code (e.g. AL, ML), or lifecycle status"
        action="{{ route('settings.leave-types') }}"
        searchValue="{{ request('search') }}"
        searchPlaceholder="Search by name, code (AL, ML, HL), or statutory description..."
        resetUrl="{{ route('settings.leave-types') }}"
    >
        <x-slot:filters>
            <x-select label="Lifecycle Status" name="is_active">
                <option value="">All Statuses</option>
                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active Only</option>
                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Deactivated / Archived</option>
            </x-select>

            <x-select label="Remuneration Status" name="is_paid">
                <option value="">All Remuneration</option>
                <option value="1" {{ request('is_paid') === '1' ? 'selected' : '' }}>Paid Leave</option>
                <option value="0" {{ request('is_paid') === '0' ? 'selected' : '' }}>Unpaid Leave</option>
            </x-select>

            <x-select label="Attachment Requirement" name="requires_attachment">
                <option value="">All Requirements</option>
                <option value="1" {{ request('requires_attachment') === '1' ? 'selected' : '' }}>Mandatory (MC / Proof)</option>
                <option value="0" {{ request('requires_attachment') === '0' ? 'selected' : '' }}>Optional</option>
            </x-select>
        </x-slot:filters>
    </x-filter-toolbar>

    <!-- Feedback Alerts -->
    @if(session('status'))
        <x-alert variant="success" icon="bx bx-check-circle" title="Success">
            {{ session('status') }}
        </x-alert>
    @endif

    <!-- Leave Types Table Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-950/50 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-4 sm:px-6">Leave Category &amp; Details</th>
                        <th class="py-3.5 px-4">Code</th>
                        <th class="py-3.5 px-4">Statutory Allowance</th>
                        <th class="py-3.5 px-4">Remuneration</th>
                        <th class="py-3.5 px-4 hidden sm:table-cell">MC / Proof</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/70 text-slate-600 dark:text-slate-300 font-medium">
                    @forelse($leaveTypes as $lt)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition-colors {{ !$lt->is_active ? 'opacity-70 bg-slate-50/30 dark:bg-slate-950/20' : '' }}">
                            <td class="py-3.5 px-4 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-{{ $lt->color ?? 'indigo' }}-50 dark:bg-{{ $lt->color ?? 'indigo' }}-950/60 border border-{{ $lt->color ?? 'indigo' }}-200 dark:border-{{ $lt->color ?? 'indigo' }}-800 flex items-center justify-center font-bold text-{{ $lt->color ?? 'indigo' }}-700 dark:text-{{ $lt->color ?? 'indigo' }}-300 text-xs shrink-0">
                                        {{ substr($lt->code, 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-900 dark:text-white block text-sm">{{ $lt->name }}</span>
                                            @if(!$lt->is_active)
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400 uppercase">Archived</span>
                                            @endif
                                        </div>
                                        <span class="text-[11px] text-slate-400 line-clamp-1">{{ $lt->description ?? 'Standard company entitlement policy' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                    {{ $lt->code }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-black text-indigo-600 dark:text-indigo-400 text-sm">
                                {{ (float) $lt->days_allowed }} days <span class="text-[10px] font-normal text-slate-400">/ year</span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($lt->is_paid)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">Paid</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">Unpaid</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 hidden sm:table-cell">
                                @if($lt->requires_attachment)
                                    <span class="inline-flex items-center gap-1 text-slate-700 dark:text-slate-300 font-semibold text-[11px]"><i class="bx bx-paperclip text-indigo-500"></i> Required</span>
                                @else
                                    <span class="text-slate-400 text-[11px]">Optional</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @if($lt->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Deactivated
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 sm:px-6 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Toggle Status Button using reusable Confirm Dialog -->
                                    @if($lt->is_active)
                                        <x-circle-action-button
                                            icon="bx bx-pause"
                                            variant="amber"
                                            size="md"
                                            type="button"
                                            title="Deactivate Policy (Hide from employees)"
                                            onclick="openConfirmDialog({
                                                name: 'toggle-leavetype',
                                                action: '{{ route('settings.leave-types.toggle', $lt) }}',
                                                method: 'POST',
                                                title: 'Deactivate {{ addslashes($lt->name) }}?',
                                                message: 'Employees will no longer be able to select {{ addslashes($lt->name) }} for new leave requests. All past applications and attendance histories remain safely preserved.',
                                                confirmText: 'Yes, Deactivate'
                                            })"
                                        />
                                    @else
                                        <x-circle-action-button
                                            icon="bx bx-play"
                                            variant="emerald"
                                            size="md"
                                            type="button"
                                            title="Reactivate Policy"
                                            onclick="openConfirmDialog({
                                                name: 'toggle-leavetype',
                                                action: '{{ route('settings.leave-types.toggle', $lt) }}',
                                                method: 'POST',
                                                title: 'Reactivate {{ addslashes($lt->name) }}?',
                                                message: 'This leave policy will immediately become active and available again for employee leave applications.',
                                                confirmText: 'Yes, Reactivate'
                                            })"
                                        />
                                    @endif

                                    <!-- Edit Policy Modal Button -->
                                    <x-circle-action-button
                                        icon="bx bx-edit-alt"
                                        variant="indigo"
                                        size="md"
                                        type="button"
                                        title="Edit Policy"
                                        onclick="openEditLeaveModal({{ json_encode($lt) }})"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i class="bx bx-search text-4xl block mb-2 text-slate-300"></i>
                                <p class="text-sm font-semibold">No leave policies match your search filters.</p>
                                <a href="{{ route('settings.leave-types') }}" class="text-xs text-indigo-600 font-bold hover:underline mt-1 inline-block">Reset search filters</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leaveTypes->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $leaveTypes->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal: Edit Leave Type -->
<x-modal name="edit-leavetype" title="Edit Leave Entitlement Policy" subtitle="Configure annual allowances, remuneration status, and statutory flags" icon="bx-slider" size="lg">
    <form id="edit-leavetype-form" method="POST" action="" class="space-y-4 text-xs">
        @csrf
        @method('PUT')

        <div class="p-3.5 bg-indigo-50/70 dark:bg-indigo-950/40 rounded-2xl border border-indigo-100 dark:border-indigo-900/60 flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-500">Selected Policy</span>
                <h4 id="edit-leavetype-name" class="text-sm font-extrabold text-indigo-950 dark:text-white"></h4>
            </div>
            <span id="edit-leavetype-code" class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 shadow-2xs"></span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input label="Annual Entitlement (Days)" name="days_allowed" id="edit-days-allowed" type="number" step="0.5" min="0" max="365" required />
            
            <x-select label="Badge Accent Color" name="color" id="edit-color">
                <option value="indigo">Indigo</option>
                <option value="emerald">Emerald</option>
                <option value="rose">Rose</option>
                <option value="amber">Amber</option>
                <option value="sky">Sky Blue</option>
                <option value="purple">Purple</option>
            </x-select>

            <x-select label="Remuneration Status" name="is_paid" id="edit-is-paid">
                <option value="1">Fully Paid</option>
                <option value="0">Unpaid Leave</option>
            </x-select>

            <x-select label="MC / Attachment" name="requires_attachment" id="edit-requires-attachment">
                <option value="1">Mandatory</option>
                <option value="0">Optional</option>
            </x-select>

            <div class="sm:col-span-2">
                <x-select label="Lifecycle Status" name="is_active" id="edit-is-active">
                    <option value="1">Active</option>
                    <option value="0">Deactivated</option>
                </x-select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Description / Statutory Reference</label>
            <textarea name="description" id="edit-description" rows="2" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-3 outline-none focus:ring-2 focus:ring-indigo-500" placeholder="State policy parameters or legal employment act references..."></textarea>
        </div>

        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5">
            <x-button type="button" variant="ghost" size="md" class="w-full sm:w-auto font-bold justify-center" onclick="document.getElementById('modal-edit-leavetype').classList.add('hidden')">
                Cancel
            </x-button>
            <x-button type="submit" variant="primary" size="md" icon="bx bx-save" class="w-full sm:w-auto font-bold justify-center shadow-md shadow-indigo-600/20">
                Save Policy Changes
            </x-button>
        </div>
    </form>
</x-modal>

<!-- Modal: Add Leave Type -->
<x-modal name="new-leavetype" title="Add Custom Leave Category" subtitle="Create custom statutory or discretionary organization leave entitlements" icon="bx-plus-circle" size="lg">
    <form action="{{ route('settings.leave-types.store') }}" method="POST" class="space-y-4 text-xs">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input label="Category Name" name="name" placeholder="e.g. Study Leave" required />
            <x-input label="Category Code" name="code" placeholder="STL" required />

            <x-input label="Annual Days Allowed" name="days_allowed" type="number" step="0.5" min="0" max="365" value="5" required />
            
            <x-select label="Accent Color" name="color">
                <option value="indigo">Indigo</option>
                <option value="emerald">Emerald</option>
                <option value="rose">Rose</option>
                <option value="amber">Amber</option>
                <option value="sky">Sky Blue</option>
                <option value="purple">Purple</option>
            </x-select>

            <x-select label="Remuneration" name="is_paid">
                <option value="1">Fully Paid</option>
                <option value="0">Unpaid Leave</option>
            </x-select>

            <x-select label="MC / Attachment" name="requires_attachment">
                <option value="0">Optional</option>
                <option value="1">Mandatory</option>
            </x-select>

            <div class="sm:col-span-2">
                <x-select label="Initial Status" name="is_active">
                    <option value="1">Active</option>
                    <option value="0">Deactivated</option>
                </x-select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Policy Description</label>
            <textarea name="description" rows="2" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-3 outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Optional statutory reference or policy details..."></textarea>
        </div>

        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5">
            <x-button type="button" variant="ghost" size="md" class="w-full sm:w-auto font-bold justify-center" onclick="document.getElementById('modal-new-leavetype').classList.add('hidden')">
                Cancel
            </x-button>
            <x-button type="submit" variant="primary" size="md" icon="bx bx-check" class="w-full sm:w-auto font-bold justify-center shadow-md shadow-indigo-600/20">
                Create Leave Type
            </x-button>
        </div>
    </form>
</x-modal>

<!-- Reusable Confirmation Dialog for Leave Policy Lifecycle -->
<x-confirm-dialog
    name="toggle-leavetype"
    title="Confirm Policy Lifecycle Change"
    message="Please confirm whether to update the availability status for this policy."
    confirmText="Confirm Change"
    variant="warning"
/>

@push('scripts')
<script>
    function openEditLeaveModal(data) {
        const form = document.getElementById('edit-leavetype-form');
        form.action = `/settings/leave-types/${data.id}`;

        document.getElementById('edit-leavetype-name').innerText = data.name;
        document.getElementById('edit-leavetype-code').innerText = 'Code: ' + data.code;
        document.getElementById('edit-days-allowed').value = data.days_allowed;
        document.getElementById('edit-color').value = data.color || 'indigo';
        document.getElementById('edit-is-paid').value = data.is_paid ? '1' : '0';
        document.getElementById('edit-requires-attachment').value = data.requires_attachment ? '1' : '0';
        document.getElementById('edit-is-active').value = (data.is_active !== undefined && !data.is_active) ? '0' : '1';
        document.getElementById('edit-description').value = data.description || '';

        document.getElementById('modal-edit-leavetype').classList.remove('hidden');
    }
</script>
@endpush

@endsection
