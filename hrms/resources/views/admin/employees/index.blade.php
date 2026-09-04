@extends('layouts.admin')

@section('title', 'Staff Directory - PulseHR Management System')
@section('page-title', 'Personnel Information Management (PIM)')

@section('content')
<div class="space-y-6 animate__animated animate__fadeIn">

    <!-- Page Header (matching standard) -->
    <x-page-header
        title="Active Employee Roster"
        subtitle="Manage workforce lifecycle, employment contracts, and department assignments"
        icon="bx-user-pin"
    >
        <x-button
            type="button"
            variant="secondary"
            size="md"
            icon="bx bx-upload"
            onclick="document.getElementById('modal-import-employees').classList.remove('hidden')"
            class="bg-white/10 hover:bg-white/20 text-white border-white/20"
        >
            Import CSV
        </x-button>

        <x-button
            type="button"
            variant="secondary"
            size="md"
            icon="bx bx-download"
            onclick="document.getElementById('modal-confirm-export-employees').classList.remove('hidden')"
            class="bg-white/10 hover:bg-white/20 text-white border-white/20"
        >
            Export CSV
        </x-button>

        <x-button
            variant="primary"
            size="md"
            icon="bx bx-user-plus"
            onclick="document.getElementById('modal-new-employee').classList.remove('hidden')"
            class="shadow-lg shadow-indigo-600/30"
        >
            Add Employee
        </x-button>
    </x-page-header>

    <!-- Search & Filter Section (Reusable pure Tailwind component) -->
    <x-filter-toolbar
        title="Search &amp; Filter Employees"
        subtitle="Filter directory by employee code, work email, department, or contract status"
        action="{{ route('employees.index') }}"
        searchValue="{{ request('search') }}"
        searchPlaceholder="Search by name, work email, or EMP-YYYY-XXXX code..."
        resetUrl="{{ route('employees.index') }}"
    >
        <x-slot:filters>
            <x-select label="Department" name="department_id">
                <option value="">All Departments</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
            </x-select>

            <x-select label="Employment Status" name="employment_status">
                <option value="">All Statuses</option>
                <option value="Permanent" {{ request('employment_status') == 'Permanent' ? 'selected' : '' }}>Permanent</option>
                <option value="Probation" {{ request('employment_status') == 'Probation' ? 'selected' : '' }}>Probation</option>
                <option value="Contract" {{ request('employment_status') == 'Contract' ? 'selected' : '' }}>Contract</option>
                <option value="Intern" {{ request('employment_status') == 'Intern' ? 'selected' : '' }}>Intern</option>
            </x-select>
        </x-slot:filters>
    </x-filter-toolbar>

    <!-- Employees Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-950/50 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-4 sm:px-6">Employee Profile</th>
                        <th class="py-3.5 px-4 hidden md:table-cell">Department &amp; Title</th>
                        <th class="py-3.5 px-4 hidden lg:table-cell">Reporting Manager</th>
                        <th class="py-3.5 px-4 hidden sm:table-cell">Status</th>
                        <th class="py-3.5 px-4 hidden xl:table-cell">Location</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/70 text-slate-600 dark:text-slate-300 font-medium">
                    @forelse ($employees as $emp)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="py-3.5 px-4 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300 font-black flex items-center justify-center text-xs shrink-0">
                                        {{ strtoupper(substr($emp->first_name, 0, 1) . substr($emp->last_name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('employees.show', $emp) }}" class="font-bold text-slate-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors block truncate max-w-[180px] sm:max-w-none">
                                            {{ $emp->full_name }}
                                        </a>
                                        <div class="flex items-center gap-1.5 text-[10px] text-slate-400 font-mono flex-wrap">
                                            <span>{{ $emp->employee_code }}</span>
                                            <span class="hidden sm:inline">&bull; {{ $emp->email }}</span>
                                        </div>

                                        <!-- Mobile Sub-Details: Visible only on smaller screens -->
                                        <div class="mt-1 flex items-center gap-2 flex-wrap sm:hidden">
                                            <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400">
                                                {{ $emp->designation?->title ?? 'Unassigned' }}
                                            </span>
                                            @if($emp->employment_status === 'Permanent')
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">
                                                    Permanent
                                                </span>
                                            @else
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60">
                                                    {{ $emp->employment_status }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 hidden md:table-cell">
                                <span class="font-semibold text-slate-900 dark:text-white block">{{ $emp->designation?->title ?? 'Unassigned' }}</span>
                                <span class="text-[10px] text-slate-400">{{ $emp->department?->name ?? 'None' }}</span>
                            </td>
                            <td class="py-3.5 px-4 hidden lg:table-cell text-slate-600 dark:text-slate-400">
                                {{ $emp->manager?->full_name ?? '—' }}
                            </td>
                            <td class="py-3.5 px-4 hidden sm:table-cell">
                                @if($emp->employment_status === 'Permanent')
                                    <x-badge variant="emerald" size="sm" :dot="true">
                                        Permanent
                                    </x-badge>
                                @elseif($emp->employment_status === 'Probation')
                                    <x-badge variant="amber" size="sm" :dot="true">
                                        Probation
                                    </x-badge>
                                @else
                                    <x-badge variant="slate" size="sm">
                                        {{ $emp->employment_status }}
                                    </x-badge>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 hidden xl:table-cell text-slate-500 dark:text-slate-400 text-[11px]">
                                {{ $emp->branch_location }}
                            </td>
                            <td class="py-3.5 px-4 sm:px-6 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <x-circle-action-button
                                        icon="bx bx-show"
                                        variant="indigo"
                                        size="sm"
                                        href="{{ route('employees.show', $emp) }}"
                                        title="View Profile Dossier"
                                    />
                                    <x-circle-action-button
                                        icon="bx bx-trash"
                                        variant="rose"
                                        size="sm"
                                        type="button"
                                        title="Archive / Soft Delete"
                                        onclick="openConfirmDialog({
                                            name: 'delete-employee',
                                            action: '{{ route('employees.destroy', $emp) }}',
                                            method: 'DELETE',
                                            title: 'Archive {{ addslashes($emp->full_name) }}?',
                                            message: 'Are you sure you want to soft-delete {{ addslashes($emp->full_name) }} ({{ $emp->employee_code }})? All records will be safely archived.',
                                            confirmText: 'Yes, Archive Record'
                                        })"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                <i class="bx bx-user-x text-3xl block mb-2"></i>
                                No employee records matching the search criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination :paginator="$employees" />
    </div>
</div>

<!-- Modal: New Employee Record Form -->
<x-modal name="new-employee" title="Create Employee Master Record" subtitle="Employee ID is automatically generated in EMP-YYYY-XXXX format" size="4xl">
    <form method="POST" action="{{ route('employees.store') }}" class="space-y-6 text-xs">
        @csrf

        <!-- Section 1: Personal Identification & Contact -->
        <div class="space-y-3">
            <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-800">
                <i class="bx bx-user-pin text-indigo-600 dark:text-indigo-400 text-base"></i>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">1. Personal &amp; Contact Details</h4>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input label="First Name" name="first_name" placeholder="e.g. John" required />
                <x-input label="Last Name" name="last_name" placeholder="e.g. Doe" required />

                <x-input label="Work Email Address" name="email" type="email" placeholder="john.doe@company.test" icon="bx bx-envelope" required />
                <x-input label="Contact Phone" name="phone" placeholder="+60 12-345 6789" icon="bx bx-phone" />

                <x-input label="National ID / Passport (Encrypted)" name="national_id" placeholder="e.g. 920101-14-5566" icon="bx bx-id-card" required hint="Encrypted at rest with AES-256" />
                <div class="grid grid-cols-2 gap-3">
                    <x-select label="Gender" name="gender" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </x-select>
                    <x-date-picker label="Date of Birth" name="date_of_birth" />
                </div>
            </div>
        </div>

        <!-- Section 2: Organizational Assignment & Employment Lifecycle -->
        <div class="space-y-3">
            <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-800">
                <i class="bx bx-briefcase text-emerald-600 dark:text-emerald-400 text-base"></i>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">2. Employment &amp; Department Allocation</h4>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-select label="Assigned Department" name="department_id" required>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->code }})</option>
                    @endforeach
                </x-select>

                <x-select label="Designation / Role Title" name="designation_id" required>
                    @foreach ($designations as $desig)
                        <option value="{{ $desig->id }}">{{ $desig->title }}</option>
                    @endforeach
                </x-select>

                <x-select label="Employment Contract Status" name="employment_status" required>
                    <option value="Probation">Probationary Period</option>
                    <option value="Permanent">Permanent Staff</option>
                    <option value="Contract">Fixed Term Contract</option>
                    <option value="Intern">Internship</option>
                </x-select>

                <x-date-picker label="Official Joining Date" name="joining_date" value="{{ date('Y-m-d') }}" required />

                <div class="md:col-span-2">
                    <x-input label="Workplace / Branch Location" name="branch_location" value="Headquarters (Kuala Lumpur)" icon="bx bx-buildings" required />
                </div>
            </div>
        </div>

        <!-- Section 3: Payroll Feeder & Banking Details -->
        <div class="space-y-3">
            <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-800">
                <i class="bx bx-wallet text-purple-600 dark:text-purple-400 text-base"></i>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">3. External Payroll Feeder &amp; Bank Attributes</h4>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input label="Basic Monthly Salary (MYR)" name="basic_salary" type="number" step="0.01" placeholder="0.00" icon="bx bx-dollar-circle" required hint="Exported to PayFlow MY engine for statutory computation" />
                <x-input label="Bank Name" name="bank_name" placeholder="Maybank / CIMB / Public Bank" icon="bx bx-credit-card" />
                
                <div class="md:col-span-2">
                    <x-input label="Bank Account Number (Encrypted Vault)" name="bank_account_number" placeholder="Enter bank account number" icon="bx bx-lock-alt" hint="Encrypted at rest. Transparently decrypted on verified payroll export." />
                </div>
            </div>
        </div>

        <!-- Footer Action Buttons -->
        <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800">
            <x-button type="button" variant="secondary" size="md" onclick="document.getElementById('modal-new-employee').classList.add('hidden')">
                Cancel
            </x-button>
            <x-button type="submit" variant="primary" size="md" icon="bx bx-check-circle">
                Save &amp; Generate EMP Code
            </x-button>
        </div>
    </form>
</x-modal>

<!-- Reusable Pure Tailwind Confirmation Dialog -->
<x-confirm-dialog
    name="delete-employee"
    title="Archive Employee Record?"
    message="This will soft-delete the employee. Historical logs and attendance summaries remain preserved for payroll sync."
    confirmText="Yes, Archive Record"
    variant="danger"
/>

<!-- Confirmation Dialog: Export Employee Roster -->
<x-confirm-dialog
    name="export-employees"
    title="Confirm Export Employee Roster"
    message="Are you sure you want to export the entire active employee directory to CSV? The generated file includes contact details, department allocations, and basic salary data."
    confirmText="Download Directory CSV"
    cancelText="Cancel"
    variant="success"
    icon="bx bx-download text-emerald-600 dark:text-emerald-400"
/>

<!-- Modal: Import Employees CSV with Confirmation -->
<x-modal name="import-employees" title="Import Employee Roster via CSV" size="lg">
    <form method="POST" action="{{ route('employees.import') }}" enctype="multipart/form-data" class="space-y-4 text-xs">
        @csrf

        <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/50 space-y-1">
            <p class="font-bold text-amber-800 dark:text-amber-300 flex items-center gap-1.5">
                <i class="bx bx-info-circle text-base"></i>
                <span>CSV Upload Confirmation &amp; Validation</span>
            </p>
            <p class="text-amber-700 dark:text-amber-400 text-[11px] leading-relaxed">
                Please confirm that your CSV file includes column headers: <code>Code, First Name, Last Name, Email, Phone</code>. Duplicate email addresses will be skipped automatically.
            </p>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Select CSV Data File <span class="text-rose-500">*</span>
            </label>
            <input
                type="file"
                name="csv_file"
                accept=".csv,text/csv,text/plain"
                required
                class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-950/60 dark:file:text-indigo-300 cursor-pointer border border-slate-200 dark:border-slate-700 rounded-xl p-2 bg-white dark:bg-slate-800"
            />
        </div>

        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
            <a
                href="{{ route('employees.template') }}"
                class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors"
            >
                <i class="bx bx-download text-sm"></i>
                <span>Download Sample CSV Template</span>
            </a>
            <div class="flex items-center gap-2">
                <x-button type="button" variant="ghost" size="sm" onclick="document.getElementById('modal-import-employees').classList.add('hidden')">
                    Cancel
                </x-button>
                <x-button type="submit" variant="primary" size="sm" icon="bx bx-upload">
                    Confirm &amp; Upload CSV
                </x-button>
            </div>
        </div>
    </form>
</x-modal>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const exportForm = document.getElementById('modal-confirm-export-employees-form');
        if (exportForm) {
            exportForm.method = 'GET';
            exportForm.action = "{{ route('employees.export') }}";
        }
    });
</script>
@endsection
