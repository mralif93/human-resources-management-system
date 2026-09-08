@extends('layouts.admin')

@section('title', 'Register New User - PulseHR')
@section('page-title', 'Create User Account')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 animate__animated animate__fadeIn">

    <div class="flex items-center justify-between">
        <a href="{{ route('users.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
            <i class="bx bx-arrow-back text-sm"></i>
            <span>Back to User Accounts</span>
        </a>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-6 sm:p-8 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/30 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                    <i class="bx bx-user-plus"></i>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white tracking-tight">Register New User Account</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Provision a new staff profile, assign role privileges, and generate credentials.</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('users.store') }}" class="p-6 sm:p-8 space-y-6 text-left">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <x-input label="Full Legal Name" name="name" value="{{ old('name') }}" required placeholder="e.g. Rachel Adams" />
                <x-input label="Official Work Email" name="email" value="{{ old('email') }}" type="email" required placeholder="rachel.a@hrms.test" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <x-input label="Employee Code (Optional)" name="employee_code" value="{{ old('employee_code') }}" placeholder="e.g. EMP-2026-0005" />
                <x-input label="Contact Phone Number" name="phone" value="{{ old('phone') }}" placeholder="+60 12-345 6789" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <x-input label="Department" name="department" value="{{ old('department') }}" placeholder="e.g. Human Resources" />
                <x-input label="Job Title / Designation" name="job_title" value="{{ old('job_title') }}" placeholder="e.g. Talent Acquisition Lead" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <x-input label="Initial Password" name="password" type="password" required placeholder="Minimum 8 characters" />
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Account Access Status
                    </label>
                    <select name="status" required class="w-full py-2.5 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white text-xs outline-none focus:border-indigo-500">
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>
            </div>

            <!-- Role Selection -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                    Assign Access Roles (RBAC)
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/50 max-h-56 overflow-y-auto custom-scrollbar">
                    @foreach($roles as $role)
                        <label class="flex items-start gap-2.5 p-2.5 rounded-xl hover:bg-white dark:hover:bg-slate-800 transition cursor-pointer text-xs">
                            <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" {{ is_array(old('role_ids')) && in_array($role->id, old('role_ids')) ? 'checked' : '' }} class="mt-0.5 rounded text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <span class="font-bold text-slate-900 dark:text-white block">{{ $role->display_name }}</span>
                                <span class="text-[11px] text-slate-400 block">{{ $role->description }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('users.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/30 transition hover:scale-[1.02] active:scale-[0.98] cursor-pointer flex items-center gap-2">
                    <i class="bx bx-check text-base"></i>
                    <span>Create User Account</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
