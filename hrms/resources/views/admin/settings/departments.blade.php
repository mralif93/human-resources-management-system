@extends('layouts.admin')

@section('title', 'Departments & Designations - PulseHR Management System')
@section('page-title', 'Departments & Job Designations')

@section('content')
<div class="space-y-6 animate__animated animate__fadeIn">

    <!-- Top Action Header Banner (matching PIM & ATS standard) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div>
            <h2 class="text-base font-extrabold text-slate-900 dark:text-white">Organizational Departments &amp; Taxonomy</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Manage business unit structure, reporting managers, standardized job titles, and lifecycle status</p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <x-button
                type="button"
                variant="secondary"
                size="md"
                icon="bx bx-tag"
                onclick="document.getElementById('modal-new-designation').classList.remove('hidden')"
            >
                Add Designation
            </x-button>

            <x-button
                type="button"
                variant="primary"
                size="md"
                icon="bx bx-plus"
                onclick="document.getElementById('modal-new-department').classList.remove('hidden')"
                class="shadow-md shadow-indigo-600/20"
            >
                New Department
            </x-button>
        </div>
    </div>

    <!-- KPI Metric Cards Grid (Standard 4 Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card
            title="Total Units"
            value="{{ $totalDepartments }}"
            icon="bx bx-network-chart"
            color="indigo"
            subtitle="{{ $activeDepartments }} active, {{ $inactiveDepartments }} archived"
        />

        <x-stat-card
            title="Active Units"
            value="{{ $activeDepartments }}"
            icon="bx bx-check-circle"
            color="emerald"
            subtitle="Operational divisions"
        />

        <x-stat-card
            title="Deactivated / Archived"
            value="{{ $inactiveDepartments }}"
            icon="bx bx-archive"
            color="rose"
            subtitle="Hidden from new hiring"
        />

        <x-stat-card
            title="Job Designations"
            value="{{ $totalDesignations }}"
            icon="bx bx-id-card"
            color="sky"
            subtitle="Standardized titles"
        />
    </div>

    <!-- Search & Filter Toolbar Component -->
    <x-filter-toolbar
        title="Search &amp; Filter Departments"
        subtitle="Search by department name, unique code (e.g. ENG, HR), or designation titles"
        action="{{ route('settings.departments') }}"
        searchValue="{{ request('search') }}"
        searchPlaceholder="Search departments, codes, or job designation titles..."
        resetUrl="{{ route('settings.departments') }}"
    >
        <x-slot:filters>
            <x-select label="Lifecycle Status" name="is_active">
                <option value="">All Statuses</option>
                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active Only</option>
                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Deactivated / Archived</option>
            </x-select>

            <x-select label="Leadership Assignment" name="has_manager">
                <option value="">All Departments</option>
                <option value="yes" {{ request('has_manager') === 'yes' ? 'selected' : '' }}>Assigned Manager</option>
                <option value="no" {{ request('has_manager') === 'no' ? 'selected' : '' }}>Vacant / Unassigned</option>
            </x-select>
        </x-slot:filters>
    </x-filter-toolbar>

    <!-- Feedback Alerts -->
    @if(session('status'))
        <x-alert variant="success" icon="bx bx-check-circle" title="Success">
            {{ session('status') }}
        </x-alert>
    @endif

    <!-- Departments Directory Table Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-950/50 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-4 sm:px-6">Department Unit</th>
                        <th class="py-3.5 px-4">Code</th>
                        <th class="py-3.5 px-4">Department Head / Manager</th>
                        <th class="py-3.5 px-4 hidden lg:table-cell">Job Designations</th>
                        <th class="py-3.5 px-4">Headcount</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/70 text-slate-600 dark:text-slate-300 font-medium">
                    @forelse($departments as $dept)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition-colors {{ !$dept->is_active ? 'opacity-70 bg-slate-50/30 dark:bg-slate-950/20' : '' }}">
                            <td class="py-3.5 px-4 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800 flex items-center justify-center font-bold text-indigo-700 dark:text-indigo-300 text-xs shrink-0">
                                        {{ substr($dept->code, 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-900 dark:text-white block text-sm">{{ $dept->name }}</span>
                                            @if(!$dept->is_active)
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400 uppercase">Archived</span>
                                            @endif
                                        </div>
                                        <span class="text-[11px] text-slate-400 line-clamp-1">{{ $dept->description ?? 'Primary organizational unit' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                    {{ $dept->code }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($dept->manager)
                                    @php
                                        $mgrName = $dept->manager->name ?? $dept->manager->full_name ?? ($dept->manager->first_name . ' ' . $dept->manager->last_name);
                                        $mgrCode = $dept->manager->employee_code ?? ('EMP-' . str_pad($dept->manager->id, 4, '0', STR_PAD_LEFT));
                                        $mgrParts = array_filter(explode(' ', trim($mgrName ?? '')));
                                        $mgrInitials = '';
                                        foreach(array_slice($mgrParts, 0, 2) as $p) {
                                            $mgrInitials .= mb_substr($p, 0, 1);
                                        }
                                        $mgrInitials = strtoupper($mgrInitials);
                                    @endphp
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/80 flex items-center justify-center font-bold text-[11px] shrink-0 shadow-xs">
                                            @if(!empty($mgrInitials))
                                                {{ $mgrInitials }}
                                            @else
                                                <i class="bx bx-user text-xs"></i>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <span class="font-semibold text-slate-900 dark:text-white block text-xs truncate">{{ $mgrName }}</span>
                                            <span class="text-[10px] text-slate-400 font-mono block">{{ $mgrCode }}</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                        Vacant / Unassigned
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 hidden lg:table-cell">
                                <div class="flex flex-wrap gap-1 max-w-sm">
                                    @forelse($dept->designations->take(3) as $desig)
                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700">
                                            {{ $desig->title }}
                                        </span>
                                    @empty
                                        <span class="text-[11px] text-slate-400 italic">None registered</span>
                                    @endforelse
                                    @if($dept->designations->count() > 3)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold text-slate-400">
                                            +{{ $dept->designations->count() - 3 }} more
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                    {{ $dept->employees_count }} Staff
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($dept->is_active)
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
                                    @if($dept->is_active)
                                        <x-circle-action-button
                                            icon="bx bx-pause"
                                            variant="amber"
                                            size="md"
                                            type="button"
                                            title="Deactivate Department (Hide from recruitment & onboarding)"
                                            onclick="openConfirmDialog({
                                                name: 'toggle-department',
                                                action: '{{ route('settings.departments.toggle', $dept) }}',
                                                method: 'POST',
                                                title: 'Deactivate {{ addslashes($dept->name) }}?',
                                                message: 'This department will be hidden from new job vacancies and employee onboarding. Current employee assignments, organizational hierarchy, and historical attendance remain completely safe.',
                                                confirmText: 'Yes, Deactivate'
                                            })"
                                        />
                                    @else
                                        <x-circle-action-button
                                            icon="bx bx-play"
                                            variant="emerald"
                                            size="md"
                                            type="button"
                                            title="Reactivate Department"
                                            onclick="openConfirmDialog({
                                                name: 'toggle-department',
                                                action: '{{ route('settings.departments.toggle', $dept) }}',
                                                method: 'POST',
                                                title: 'Reactivate {{ addslashes($dept->name) }}?',
                                                message: 'This department will immediately become available again for new job recruitment postings and employee profile allocations.',
                                                confirmText: 'Yes, Reactivate'
                                            })"
                                        />
                                    @endif

                                    <!-- Edit Department Modal Button -->
                                    <x-circle-action-button
                                        icon="bx bx-edit-alt"
                                        variant="indigo"
                                        size="md"
                                        type="button"
                                        title="Edit Department Details"
                                        onclick="openEditDepartmentModal({{ json_encode($dept) }})"
                                    />

                                    <!-- Add Designation Title Button -->
                                    <x-circle-action-button
                                        icon="bx bx-plus"
                                        variant="sky"
                                        size="md"
                                        type="button"
                                        title="Add Job Title to {{ $dept->name }}"
                                        onclick="openAddDesignationModal({{ $dept->id }}, '{{ addslashes($dept->name) }}')"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i class="bx bx-search text-4xl block mb-2 text-slate-300"></i>
                                <p class="text-sm font-semibold">No departments match your search filters.</p>
                                <a href="{{ route('settings.departments') }}" class="text-xs text-indigo-600 font-bold hover:underline mt-1 inline-block">Reset search filters</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($departments->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $departments->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal: Edit Department -->
<x-modal name="edit-department" title="Edit Department Configuration" subtitle="Update operational unit code, management assignment, and status" icon="bx-edit" size="lg">
    <form id="edit-department-form" method="POST" action="" class="space-y-4 text-xs">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input label="Department Name" name="name" id="edit-dept-name" required />
            <x-input label="Department Code" name="code" id="edit-dept-code" required />

            <x-select label="Department Head / Manager" name="manager_id" id="edit-dept-manager">
                <option value="">-- No Manager Assigned --</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_code }})</option>
                @endforeach
            </x-select>

            <x-select label="Lifecycle Status" name="is_active" id="edit-dept-is-active">
                <option value="1">Active</option>
                <option value="0">Deactivated / Archived</option>
            </x-select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Operational Charter / Notes</label>
            <textarea name="description" id="edit-dept-description" rows="2" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-3 outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Charter, mandate, or division scope..."></textarea>
        </div>

        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5">
            <x-button type="button" variant="ghost" size="md" class="w-full sm:w-auto font-bold justify-center" onclick="document.getElementById('modal-edit-department').classList.add('hidden')">
                Cancel
            </x-button>
            <x-button type="submit" variant="primary" size="md" icon="bx bx-save" class="w-full sm:w-auto font-bold justify-center shadow-md shadow-indigo-600/20">
                Save Changes
            </x-button>
        </div>
    </form>
</x-modal>

<!-- Modal: New Department -->
<x-modal name="new-department" title="Establish New Department" subtitle="Register a new business division or organizational unit" icon="bx-plus-circle" size="lg">
    <form action="{{ route('settings.departments.store') }}" method="POST" class="space-y-4 text-xs">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input label="Department Name" name="name" placeholder="e.g. Legal & Compliance" required />
            <x-input label="Code (Unique)" name="code" placeholder="DEP-LGL" required />

            <x-select label="Department Head / Reporting Manager" name="manager_id">
                <option value="">-- Select Reporting Manager --</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_code }})</option>
                @endforeach
            </x-select>

            <x-select label="Initial Status" name="is_active">
                <option value="1">Active</option>
                <option value="0">Deactivated</option>
            </x-select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Operational Charter / Notes</label>
            <textarea name="description" rows="2" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-3 outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Scope and operational mandate..."></textarea>
        </div>

        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5">
            <x-button type="button" variant="ghost" size="md" class="w-full sm:w-auto font-bold justify-center" onclick="document.getElementById('modal-new-department').classList.add('hidden')">
                Cancel
            </x-button>
            <x-button type="submit" variant="primary" size="md" icon="bx bx-check" class="w-full sm:w-auto font-bold justify-center shadow-md shadow-indigo-600/20">
                Create Department
            </x-button>
        </div>
    </form>
</x-modal>

<!-- Modal: New Designation -->
<x-modal name="new-designation" title="Add Job Designation" subtitle="Define standardized job titles and functional hierarchy" icon="bx-tag" size="lg">
    <form action="{{ route('settings.designations.store') }}" method="POST" class="space-y-4 text-xs">
        @csrf

        <div class="space-y-4">
            <x-select label="Assign to Department" name="department_id" id="designation-dept-select" required>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->code }})</option>
                @endforeach
            </x-select>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-input label="Job Title" name="title" placeholder="e.g. Compliance Officer" required />
                <x-input label="Designation Code" name="code" placeholder="DSG-CMP" required />
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Job Responsibilities</label>
                <textarea name="description" rows="2" class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-3 outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Key responsibilities and qualifications..."></textarea>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5">
            <x-button type="button" variant="ghost" size="md" class="w-full sm:w-auto font-bold justify-center" onclick="document.getElementById('modal-new-designation').classList.add('hidden')">
                Cancel
            </x-button>
            <x-button type="submit" variant="primary" size="md" icon="bx bx-check" class="w-full sm:w-auto font-bold justify-center shadow-md shadow-indigo-600/20">
                Save Designation
            </x-button>
        </div>
    </form>
</x-modal>

<!-- Reusable Confirmation Dialog for Department Lifecycle -->
<x-confirm-dialog
    name="toggle-department"
    title="Confirm Department Status Change"
    message="Please confirm whether to update the operational status for this department."
    confirmText="Confirm Change"
    variant="warning"
/>

@push('scripts')
<script>
    function openAddDesignationModal(deptId, deptName) {
        const select = document.getElementById('designation-dept-select');
        if (select) {
            select.value = deptId;
        }
        document.getElementById('modal-new-designation').classList.remove('hidden');
    }

    function openEditDepartmentModal(data) {
        const form = document.getElementById('edit-department-form');
        form.action = `/settings/departments/${data.id}`;

        document.getElementById('edit-dept-name').value = data.name;
        document.getElementById('edit-dept-code').value = data.code;
        document.getElementById('edit-dept-manager').value = data.manager_id || '';
        document.getElementById('edit-dept-is-active').value = (data.is_active !== undefined && !data.is_active) ? '0' : '1';
        document.getElementById('edit-dept-description').value = data.description || '';

        document.getElementById('modal-edit-department').classList.remove('hidden');
    }
</script>
@endpush

@endsection
