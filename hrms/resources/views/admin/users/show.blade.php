@extends('layouts.admin')

@section('title', $user->name . ' - User Profile & Security')
@section('page-title', 'User Dossier & Identity Governance')

@section('content')
<div class="space-y-6 animate__animated animate__fadeIn">

    <!-- Top Navigation Breadcrumbs -->
    <div class="flex items-center justify-between">
        <a href="{{ route('users.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
            <i class="bx bx-arrow-back text-sm"></i>
            <span>Back to User Accounts</span>
        </a>
        <div class="flex items-center gap-2">
            @if($user->employee_code)
                <span class="text-xs font-mono font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/60 px-3 py-1 rounded-xl border border-indigo-200 dark:border-indigo-800">
                    {{ $user->employee_code }}
                </span>
            @endif
            @if($user->status === 'active')
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Active
                </span>
            @elseif($user->status === 'suspended')
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    Suspended
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                    Inactive
                </span>
            @endif
        </div>
    </div>

    <!-- Header Profile Hero Banner -->
    <div class="bg-gradient-to-r from-indigo-950 via-slate-900 to-indigo-900 text-white rounded-3xl border border-indigo-800/40 shadow-xl shadow-indigo-950/40 p-6 sm:p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-md text-white font-black text-2xl flex items-center justify-center border border-white/20 shadow-lg shadow-indigo-900/50 shrink-0">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h2 class="text-xl font-black text-white tracking-tight">{{ $user->name }}</h2>
                    @if($user->id === auth()->id())
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-500/30 text-indigo-200 border border-indigo-400/30">You</span>
                    @endif
                </div>
                <p class="text-xs text-slate-300 mt-1 font-medium">
                    {{ $user->job_title ?? ($user->designation ?? 'Role: ' . $user->role) }} &bull; {{ $user->department ?? 'General Workforce' }}
                </p>
                <p class="text-xs text-slate-400 mt-0.5 flex items-center gap-2">
                    <i class="bx bx-envelope text-indigo-400"></i>
                    <span>{{ $user->email }}</span>
                    @if($user->phone)
                        <span>&bull;</span>
                        <i class="bx bx-phone text-indigo-400"></i>
                        <span>{{ $user->phone }}</span>
                    @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            <!-- Edit User Button -->
            <button 
                type="button" 
                onclick="document.getElementById('modal-edit-user-page').classList.remove('hidden')"
                class="px-4 py-2.5 rounded-xl text-xs font-bold text-indigo-100 bg-indigo-600 hover:bg-indigo-500 border border-indigo-400/30 transition-all cursor-pointer flex items-center gap-1.5 shadow-sm hover:scale-[1.02] active:scale-[0.98]"
            >
                <i class="bx bx-edit text-sm"></i>
                <span>Edit User Profile</span>
            </button>

            <!-- Reset Password Button -->
            <button 
                type="button" 
                onclick="document.getElementById('modal-reset-password-page').classList.remove('hidden')"
                class="px-4 py-2.5 rounded-xl text-xs font-bold text-amber-200 bg-amber-500/20 hover:bg-amber-500/30 border border-amber-400/30 transition-all cursor-pointer flex items-center gap-1.5"
            >
                <i class="bx bx-key text-sm"></i>
                <span>Reset Password</span>
            </button>

            <!-- Status Toggle Button -->
            @if($user->id !== auth()->id())
                <form method="POST" action="{{ route('users.toggle-status', $user) }}" class="inline">
                    @csrf
                    <button 
                        type="submit" 
                        onclick="return confirm('Change access state for {{ addslashes($user->name) }}?')"
                        class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-300 bg-white/10 hover:bg-white/15 border border-white/20 transition-all cursor-pointer flex items-center gap-1.5"
                    >
                        <i class="bx {{ $user->status === 'active' ? 'bx-lock' : 'bx-lock-open' }} text-sm"></i>
                        <span>{{ $user->status === 'active' ? 'Suspend Account' : 'Activate Account' }}</span>
                    </button>
                </form>

                <!-- Delete User Button -->
                <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline" onsubmit="return confirm('Permanently delete this user account? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button 
                        type="submit" 
                        class="px-4 py-2.5 rounded-xl text-xs font-bold text-rose-300 bg-rose-500/20 hover:bg-rose-500/30 border border-rose-400/30 transition-all cursor-pointer flex items-center gap-1.5"
                    >
                        <i class="bx bx-trash text-sm"></i>
                        <span>Delete User</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- 1. Account Details & Credentials -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-slate-100 dark:border-slate-800">
                <i class="bx bx-user text-indigo-600 text-lg"></i>
                <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Account Credentials</h3>
            </div>
            <div class="space-y-3.5 text-xs">
                <div>
                    <span class="text-slate-400 block font-medium">Full Legal Name</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $user->name }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Official Email (Username)</span>
                    <span class="font-bold text-slate-900 dark:text-white font-mono">{{ $user->email }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Employee Code / Staff ID</span>
                    <span class="font-bold text-slate-900 dark:text-white font-mono">{{ $user->employee_code ?? 'Not assigned' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Phone Number</span>
                    <span class="font-bold text-slate-900 dark:text-white">{{ $user->phone ?? 'Not provided' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Email Verification</span>
                    <span class="font-semibold {{ $user->email_verified_at ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-500' }}">
                        {{ $user->email_verified_at ? 'Verified on ' . $user->email_verified_at->format('d M Y, h:i A') : 'Pending verification' }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Account Created</span>
                    <span class="font-semibold text-slate-700 dark:text-slate-300">
                        {{ $user->created_at ? $user->created_at->format('d M Y, h:i A') : 'N/A' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- 2. Assigned RBAC Roles & Permissions -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <i class="bx bx-shield-quarter text-purple-600 text-lg"></i>
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Assigned Security Roles</h3>
                </div>
                <a href="{{ route('roles.index') }}" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                    Roles Matrix &rarr;
                </a>
            </div>

            <!-- Role Tags -->
            <div class="space-y-3">
                <div class="flex flex-wrap gap-1.5">
                    @forelse($user->roles as $role)
                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold {{ $role->is_system ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700' }}">
                            <i class="bx bx-badge-check mr-1"></i>{{ $role->display_name }}
                        </span>
                    @empty
                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                            {{ $user->role }} (Direct)
                        </span>
                    @endforelse
                </div>

                <!-- Granular Permissions Granted via Roles -->
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-2">
                        Effective Granted Permissions ({{ $user->roles->flatMap->permissions->unique('id')->count() }})
                    </span>
                    <div class="flex flex-wrap gap-1 max-h-36 overflow-y-auto custom-scrollbar p-1">
                        @forelse($user->roles->flatMap->permissions->unique('id') as $permission)
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-slate-50 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700" title="{{ $permission->display_name }}">
                                {{ $permission->name }}
                            </span>
                        @empty
                            <p class="text-[11px] text-slate-400 italic">No specific granular permissions attached.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Linked Employee Profile & Org Hierarchy -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <i class="bx bx-id-card text-emerald-600 text-lg"></i>
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Linked Employee Record</h3>
                </div>
                @if($user->employee)
                    <a href="{{ route('employees.show', $user->employee) }}" class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                        View Dossier &rarr;
                    </a>
                @endif
            </div>

            @if($user->employee)
                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-slate-400 block font-medium">Employee Master File</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ $user->employee->full_name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Department &amp; Designation</span>
                        <span class="font-bold text-slate-900 dark:text-white">
                            {{ $user->employee->department?->name ?? 'None' }} &bull; {{ $user->employee->designation?->title ?? 'None' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Employment Status</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                            {{ $user->employee->employment_status }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Branch Location</span>
                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ $user->employee->branch_location }}</span>
                    </div>
                </div>
            @else
                <div class="py-4 text-center text-slate-400 text-xs">
                    <i class="bx bx-info-circle text-2xl text-slate-300 dark:text-slate-600 mb-1"></i>
                    <p class="font-medium">No master employee profile linked to this system user account.</p>
                    <p class="text-[11px] text-slate-400 mt-1">This user functions as a system operator or administrator.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Security & Audit Activity Logs -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2">
                <i class="bx bx-history text-indigo-600 text-lg"></i>
                <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Recent User Security &amp; Activity Log</h3>
            </div>
            <a href="{{ route('audit-logs.index') }}" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                Full Audit Trail &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-900/80 border-b border-slate-100 dark:border-slate-800 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Event</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Description</th>
                        <th class="py-3 px-4">IP Address</th>
                        <th class="py-3 px-4 text-right">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($auditLogs as $log)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                {{ $log->event }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                    {{ $log->category }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-700 dark:text-slate-300 font-medium">
                                {{ $log->description }}
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-500 dark:text-slate-400 text-[11px]">
                                {{ $log->ip_address }}
                            </td>
                            <td class="py-3 px-4 text-right text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                {{ $log->created_at->format('d M Y, h:i A') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">
                                <i class="bx bx-check-shield text-2xl mb-1 text-slate-300 dark:text-slate-600"></i>
                                <p class="font-medium">No recorded security incidents or activity logs for this user account.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- EDIT USER MODAL FOR SHOW PAGE -->
<x-modal name="edit-user-page" title="Edit User Profile: {{ $user->name }}" size="lg">
    <form method="POST" action="{{ route('users.update', $user) }}" class="p-6 space-y-4 text-left">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input label="Full Name" name="name" value="{{ $user->name }}" required />
            <x-input label="Official Work Email" name="email" value="{{ $user->email }}" type="email" required />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input label="Employee Code / Staff ID" name="employee_code" value="{{ $user->employee_code }}" />
            <x-input label="Phone Number" name="phone" value="{{ $user->phone }}" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input label="Department" name="department" value="{{ $user->department }}" />
            <x-input label="Job Title / Designation" name="job_title" value="{{ $user->job_title ?? $user->designation }}" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input label="Update Password (Leave blank to keep current)" name="password" type="password" placeholder="••••••••" />
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    Account Access Status
                </label>
                <select name="status" required class="w-full py-2.5 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white text-xs outline-none focus:border-indigo-500">
                    <option value="active" {{ $user->status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $user->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="suspended" {{ $user->status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>
        </div>

        <!-- Role Selection -->
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                Assign Access Roles
            </label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/50 max-h-48 overflow-y-auto">
                @php $assignedRoleIds = $user->roles->pluck('id')->toArray(); @endphp
                @foreach(\App\Models\Role::orderBy('name')->get() as $role)
                    <label class="flex items-start gap-2.5 p-2 rounded-xl hover:bg-white dark:hover:bg-slate-800 transition cursor-pointer text-xs">
                        <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" {{ in_array($role->id, $assignedRoleIds) ? 'checked' : '' }} class="mt-0.5 rounded text-indigo-600 focus:ring-indigo-500">
                        <div>
                            <span class="font-bold text-slate-900 dark:text-white block">{{ $role->display_name }}</span>
                            <span class="text-[10px] text-slate-400 block">{{ $role->description }}</span>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
            <x-button variant="secondary" size="sm" type="button" onclick="document.getElementById('modal-edit-user-page').classList.add('hidden')">
                Cancel
            </x-button>
            <x-button variant="primary" size="sm" type="submit">
                Save Changes
            </x-button>
        </div>
    </form>
</x-modal>

<!-- RESET PASSWORD MODAL FOR SHOW PAGE -->
<x-modal name="reset-password-page" title="Direct Password Reset" size="md">
    <form method="POST" action="{{ route('users.reset-password', $user) }}" class="p-6 space-y-4 text-left">
        @csrf
        <p class="text-xs text-slate-500 dark:text-slate-400">
            Resetting security credentials for: <span class="font-bold text-slate-900 dark:text-white">{{ $user->name }}</span>
        </p>

        <x-input label="New Password" name="password" type="password" required placeholder="Minimum 8 characters" />
        <x-input label="Confirm New Password" name="password_confirmation" type="password" required placeholder="Repeat new password" />

        <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
            <x-button variant="secondary" size="sm" type="button" onclick="document.getElementById('modal-reset-password-page').classList.add('hidden')">
                Cancel
            </x-button>
            <x-button variant="warning" size="sm" type="submit">
                Update Password
            </x-button>
        </div>
    </form>
</x-modal>

@endsection
