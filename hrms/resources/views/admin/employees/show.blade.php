@extends('layouts.admin')

@section('title', $employee->full_name . ' - Employee Profile')
@section('page-title', 'Employee Dossier & Personnel Record')

@section('content')
<div class="space-y-6 animate__animated animate__fadeIn">

    <!-- Top Navigation Breadcrumbs -->
    <div class="flex items-center justify-between">
        <a href="{{ route('employees.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
            <i class="bx bx-arrow-back text-sm"></i>
            <span>Back to Staff Directory</span>
        </a>
        <span class="text-xs font-mono font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/60 px-3 py-1 rounded-xl border border-indigo-200 dark:border-indigo-800">
            {{ $employee->employee_code }}
        </span>
    </div>

    <!-- Header Profile Hero Banner -->
    <div class="bg-gradient-to-r from-indigo-950 via-slate-900 to-indigo-900 text-white rounded-3xl border border-indigo-800/40 shadow-xl shadow-indigo-950/40 p-6 sm:p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-md text-white font-black text-2xl flex items-center justify-center border border-white/20 shadow-lg shadow-indigo-900/50 shrink-0">
                {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
            </div>
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h2 class="text-xl font-black text-white tracking-tight">{{ $employee->full_name }}</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                        {{ $employee->employment_status }}
                    </span>
                </div>
                <p class="text-xs text-slate-300 mt-1 font-medium">
                    {{ $employee->designation?->title ?? 'Designation Unassigned' }} &bull; {{ $employee->department?->name ?? 'Department Unassigned' }}
                </p>
                <p class="text-xs text-slate-400 mt-0.5 flex items-center gap-1.5">
                    <i class="bx bx-map-pin text-indigo-400"></i>
                    <span>{{ $employee->branch_location }}</span>
                    <span>&bull;</span>
                    <span>Joined {{ $employee->joining_date?->format('d M Y') }}</span>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <form method="POST" action="{{ route('employees.destroy', $employee) }}" onsubmit="return confirm('Soft-delete this employee?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2.5 rounded-xl text-xs font-bold text-rose-300 bg-rose-500/20 hover:bg-rose-500/30 border border-rose-400/30 transition-colors cursor-pointer flex items-center gap-1.5">
                    <i class="bx bx-trash text-sm"></i>
                    <span>Archive Employee</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 1. Personal & Contact Details -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-100 dark:border-slate-800">
                <i class="bx bx-user text-indigo-600 text-lg"></i>
                <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Personal Identity (PII)</h3>
            </div>
            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-slate-400 block font-medium">National ID / Passport (Encrypted)</span>
                    <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $employee->national_id ? 'Decrypted: ' . $employee->national_id : 'Not on file' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Work Email</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $employee->email }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Contact Phone</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $employee->phone ?? 'Not specified' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Gender &amp; Date of Birth</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $employee->gender }} &bull; {{ $employee->date_of_birth?->format('d M Y') ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <!-- 2. Employment & Org Reporting Structure -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-100 dark:border-slate-800">
                <i class="bx bx-git-branch text-emerald-600 text-lg"></i>
                <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Organizational Line</h3>
            </div>
            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-slate-400 block font-medium">Direct Line Manager</span>
                    <span class="font-bold text-slate-900 dark:text-white">
                        {{ $employee->manager?->full_name ?? 'Top-Level Executive / None' }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Department</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $employee->department?->name ?? 'None' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Job Designation</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $employee->designation?->title ?? 'None' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Direct Subordinates</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $employee->subordinates->count() }} reporting team members</span>
                </div>
            </div>
        </div>

        <!-- 3. Compensation & External Payroll Feeder Data -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-100 dark:border-slate-800">
                <i class="bx bx-wallet text-purple-600 text-lg"></i>
                <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Payroll Feeder Attributes</h3>
            </div>
            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-slate-400 block font-medium">Basic Monthly Salary</span>
                    <span class="font-mono text-base font-black text-emerald-600 dark:text-emerald-400">
                        MYR {{ number_format($employee->basic_salary, 2) }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Designated Bank</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $employee->bank_name ?? 'Pending configuration' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Account Number (Encrypted Vault)</span>
                    <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $employee->bank_account_number ? 'Decrypted: ' . $employee->bank_account_number : 'N/A' }}</span>
                </div>
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                    <span class="inline-flex items-center gap-1 text-[11px] text-indigo-600 dark:text-indigo-400 font-semibold">
                        <i class="bx bx-check-shield"></i> Synced to PayFlow MY Engine
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
