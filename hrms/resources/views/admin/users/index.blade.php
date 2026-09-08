@extends('layouts.admin')
@section('title', 'System User Management - PulseHR')
@section('page-title', 'Staff User & Role Access Control')
@section('content')

    <div class="space-y-8">

        <!-- Flash Messages -->
        @if(session('success'))
            <x-alert type="success" dismissible="true">
                {{ session('success') }}
            </x-alert>
        @endif

        @if(session('error'))
            <x-alert type="danger" dismissible="true">
                {{ session('error') }}
            </x-alert>
        @endif

        <!-- Executive Page Hero Banner & Action Suite -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-950 via-slate-900 to-indigo-900 text-white p-6 sm:p-8 shadow-xl shadow-indigo-950/40 border border-indigo-800/40">
            <!-- Background Decorative Glow -->
            <div class="absolute -right-16 -top-16 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute right-1/3 -bottom-20 w-48 h-48 bg-purple-500/15 rounded-full blur-2xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                <div class="space-y-2 max-w-2xl">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <div class="w-9 h-9 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center text-indigo-300 font-bold text-base shadow-xs">
                            <i class="bx bx-user-pin"></i>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">Staff &amp; Identity Management</h1>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 inline-flex items-center gap-1.5 backdrop-blur-xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            {{ $stats['active_users'] }} Active Staff
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-indigo-100/80 leading-relaxed">
                        Manage clinical credentials, doctor and cashier assignments, multi-role RBAC permissions, and account access status.
                    </p>
                </div>

                <div class="flex items-center gap-2.5 sm:gap-3 flex-wrap shrink-0">
                    <a 
                        href="{{ route('roles.index') }}" 
                        class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 text-white text-xs font-bold border border-white/20 transition flex items-center gap-2 cursor-pointer"
                    >
                        <i class="bx bx-shield-quarter text-base text-indigo-300"></i>
                        <span>Role Permissions Matrix</span>
                    </a>
                    <button 
                        type="button" 
                        onclick="document.getElementById('modal-create-user').classList.remove('hidden')"
                        class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 border border-indigo-500/30 transition flex items-center gap-2 cursor-pointer hover:scale-[1.02] active:scale-[0.98]"
                    >
                        <i class="bx bx-user-plus text-base"></i>
                        <span>Register Staff Account</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Metric KPI Highlights -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-card 
                title="Total Personnel"
                value="{{ $stats['total_users'] }}"
                icon="bx bx-group"
                color="indigo"
                subtitle="Registered clinic accounts"
            />
            <x-stat-card 
                title="Active Personnel"
                value="{{ $stats['active_users'] }}"
                icon="bx bx-check-shield"
                color="emerald"
                subtitle="Operational station access"
            />
            <x-stat-card 
                title="Clinical Administrators"
                value="{{ $stats['admins'] }}"
                icon="bx bx-crown"
                color="purple"
                subtitle="Doctors & Practice Directors"
            />
            <x-stat-card 
                title="Suspended Accounts"
                value="{{ $stats['suspended'] }}"
                icon="bx bx-block"
                color="rose"
                subtitle="Locked or deactivated accounts"
            />
        </div>

        <!-- Filter & Search Toolbar -->
        <x-card class="p-4">
            <form method="GET" action="{{ route('users.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
                <!-- Search Input -->
                <div class="sm:col-span-6 relative">
                    <i class="bx bx-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base"></i>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Search by name, email, staff ID, or designation..." 
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/90 text-slate-900 dark:text-white placeholder:text-slate-400 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition"
                    >
                </div>

                <!-- Role Filter -->
                <div class="sm:col-span-3">
                    <select 
                        name="role_id" 
                        onchange="this.form.submit()"
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/90 text-slate-900 dark:text-white outline-none focus:border-indigo-500 transition cursor-pointer"
                    >
                        <option value="">All Access Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->display_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="sm:col-span-3 flex items-center gap-2">
                    <select 
                        name="status" 
                        onchange="this.form.submit()"
                        class="w-full py-2.5 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/90 text-slate-900 dark:text-white outline-none focus:border-indigo-500 transition cursor-pointer"
                    >
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended Only</option>
                    </select>

                    @if(request()->anyFilled(['search', 'role_id', 'status']))
                        <a 
                            href="{{ route('users.index') }}" 
                            class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-800 dark:hover:text-white transition shrink-0"
                            title="Clear Filters"
                        >
                            <i class="bx bx-reset text-base"></i>
                        </a>
                    @endif
                </div>
            </form>
        </x-card>

        <!-- Personnel Data Table -->
        <x-card class="overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-900/80 border-b border-slate-200/80 dark:border-slate-800 text-slate-500 font-bold uppercase tracking-wider">
                        <tr>
                            <th class="py-3.5 px-4 sm:px-6">Staff Personnel</th>
                            <th class="py-3.5 px-4">Staff ID / Code</th>
                            <th class="py-3.5 px-4">Assigned Roles</th>
                            <th class="py-3.5 px-4">Department &amp; Title</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 sm:px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @forelse($users as $user)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition">
                                <!-- User Identity -->
                                <td class="py-4 px-4 sm:px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-black text-xs shadow-xs shrink-0">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-slate-900 dark:text-white truncate flex items-center gap-1.5">
                                                <span>{{ $user->name }}</span>
                                                @if($user->id === auth()->id())
                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-indigo-100 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300 border border-indigo-300 dark:border-indigo-800">You</span>
                                                @endif
                                            </div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400 font-mono truncate">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Staff ID / Code -->
                                <td class="py-4 px-4">
                                    <div class="font-mono font-bold text-slate-800 dark:text-slate-200">
                                        {{ $user->staff_id ?? '—' }}
                                    </div>
                                    @if($user->employee_code)
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $user->employee_code }}</div>
                                    @endif
                                </td>

                                <!-- Assigned Roles -->
                                <td class="py-4 px-4">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($user->roles as $assignedRole)
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $assignedRole->is_system ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700' }}">
                                                {{ $assignedRole->display_name }}
                                            </span>
                                        @empty
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 capitalize">
                                                {{ $user->role }}
                                            </span>
                                        @endforelse
                                    </div>
                                </td>

                                <!-- Department & Designation -->
                                <td class="py-4 px-4">
                                    <div class="font-medium text-slate-800 dark:text-slate-200 truncate max-w-[180px]">
                                        {{ $user->designation ?? 'Clinical Staff' }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 truncate max-w-[180px]">
                                        {{ $user->department ?? 'General Operations' }}
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="py-4 px-4 text-center">
                                    @if($user->status === 'active')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Active
                                        </span>
                                    @elseif($user->status === 'suspended')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Suspended
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                <!-- Actions Suite -->
                                <td class="py-4 px-4 sm:px-6 text-right">
                                    <div class="inline-flex items-center gap-1.5 justify-end">
                                        <!-- Edit Modal Trigger -->
                                        <button 
                                            type="button" 
                                            onclick='openEditUserModal(@json($user), @json($user->roles->pluck("id")))'
                                            class="p-2 rounded-xl text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950/60 dark:hover:text-indigo-400 transition cursor-pointer"
                                            title="Edit Staff User"
                                        >
                                            <i class="bx bx-edit text-base"></i>
                                        </button>

                                        <!-- Password Reset Trigger -->
                                        <button 
                                            type="button" 
                                            onclick="openResetPasswordModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                            class="p-2 rounded-xl text-slate-500 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/60 dark:hover:text-amber-400 transition cursor-pointer"
                                            title="Reset Password"
                                        >
                                            <i class="bx bx-key text-base"></i>
                                        </button>

                                        <!-- Status Toggle (Block/Activate) -->
                                        @if($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('users.toggle-status', $user) }}" class="inline">
                                                @csrf
                                                <button 
                                                    type="submit" 
                                                    onclick="return confirm('Change access state for {{ addslashes($user->name) }}?')"
                                                    class="p-2 rounded-xl text-slate-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/60 dark:hover:text-rose-400 transition cursor-pointer"
                                                    title="{{ $user->status === 'active' ? 'Suspend Account Access' : 'Activate Account Access' }}"
                                                >
                                                    <i class="bx {{ $user->status === 'active' ? 'bx-lock' : 'bx-lock-open' }} text-base"></i>
                                                </button>
                                            </form>

                                            <!-- Delete User -->
                                            <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline" onsubmit="return confirm('Permanently delete staff account for {{ addslashes($user->name) }}? This action cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button 
                                                    type="submit" 
                                                    class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/60 dark:hover:text-rose-400 transition cursor-pointer"
                                                    title="Delete Account"
                                                >
                                                    <i class="bx bx-trash text-base"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i class="bx bx-user-x text-3xl"></i>
                                        <p class="font-medium">No personnel records found matching your filter criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $users->links() }}
                </div>
            @endif
        </x-card>

    </div>

    <!-- CREATE USER MODAL -->
    <x-modal name="create-user" title="Register New Clinic Staff Account" size="lg">
        <form method="POST" action="{{ route('users.store') }}" class="p-6 space-y-4 text-left">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-input label="Full Name" name="name" required placeholder="e.g. Dr. Emily Wong" />
                <x-input label="Official Work Email" name="email" type="email" required placeholder="doctor@clinic.my" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <x-input label="Clinic Staff ID" name="staff_id" placeholder="e.g. DOC-003" />
                <x-input label="Employee Code" name="employee_code" placeholder="e.g. EMP-105" />
                <x-input label="Phone Number" name="phone" placeholder="+60123456789" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-input label="Department" name="department" placeholder="e.g. Clinical Consultations" />
                <x-input label="Job Title / Designation" name="designation" placeholder="e.g. General Practitioner" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-input label="Initial Password" name="password" type="password" required placeholder="Minimum 6 characters" />
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Account Access Status
                    </label>
                    <select name="status" required class="w-full py-2.5 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white text-xs outline-none focus:border-indigo-500">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
            </div>

            <!-- Role Selection -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                    Assign Access Roles
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/50 max-h-48 overflow-y-auto">
                    @foreach($roles as $role)
                        <label class="flex items-start gap-2.5 p-2 rounded-xl hover:bg-white dark:hover:bg-slate-800 transition cursor-pointer text-xs">
                            <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" class="mt-0.5 rounded text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <span class="font-bold text-slate-900 dark:text-white block">{{ $role->display_name }}</span>
                                <span class="text-[10px] text-slate-400 block">{{ $role->description }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                <x-button variant="secondary" size="sm" type="button" onclick="document.getElementById('modal-create-user').classList.add('hidden')">
                    Cancel
                </x-button>
                <x-button variant="primary" size="sm" type="submit">
                    Create Staff Account
                </x-button>
            </div>
        </form>
    </x-modal>

    <!-- EDIT USER MODAL -->
    <x-modal name="edit-user" title="Edit Staff User Profile &amp; Permissions" size="lg">
        <form id="edit-user-form" method="POST" action="" class="p-6 space-y-4 text-left">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-input label="Full Name" name="name" id="edit-user-name" required />
                <x-input label="Official Work Email" name="email" id="edit-user-email" type="email" required />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <x-input label="Clinic Staff ID" name="staff_id" id="edit-user-staff-id" />
                <x-input label="Employee Code" name="employee_code" id="edit-user-employee-code" />
                <x-input label="Phone Number" name="phone" id="edit-user-phone" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-input label="Department" name="department" id="edit-user-department" />
                <x-input label="Job Title / Designation" name="designation" id="edit-user-designation" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-input label="Update Password (Leave blank to keep)" name="password" type="password" placeholder="••••••••" />
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Account Access Status
                    </label>
                    <select name="status" id="edit-user-status" required class="w-full py-2.5 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white text-xs outline-none focus:border-indigo-500">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
            </div>

            <!-- Role Selection -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                    Assign Access Roles
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/50 max-h-48 overflow-y-auto">
                    @foreach($roles as $role)
                        <label class="flex items-start gap-2.5 p-2 rounded-xl hover:bg-white dark:hover:bg-slate-800 transition cursor-pointer text-xs">
                            <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" class="edit-role-checkbox mt-0.5 rounded text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <span class="font-bold text-slate-900 dark:text-white block">{{ $role->display_name }}</span>
                                <span class="text-[10px] text-slate-400 block">{{ $role->description }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                <x-button variant="secondary" size="sm" type="button" onclick="document.getElementById('modal-edit-user').classList.add('hidden')">
                    Cancel
                </x-button>
                <x-button variant="primary" size="sm" type="submit">
                    Save Changes
                </x-button>
            </div>
        </form>
    </x-modal>

    <!-- PASSWORD RESET MODAL -->
    <x-modal name="reset-password" title="Direct Password Reset" size="md">
        <form id="reset-password-form" method="POST" action="" class="p-6 space-y-4 text-left">
            @csrf

            <p class="text-xs text-slate-500 dark:text-slate-400">
                Resetting security credentials for: <span id="reset-password-user-name" class="font-bold text-slate-900 dark:text-white"></span>
            </p>

            <x-input label="New Password" name="password" type="password" required placeholder="Minimum 6 characters" />
            <x-input label="Confirm New Password" name="password_confirmation" type="password" required placeholder="Repeat new password" />

            <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                <x-button variant="secondary" size="sm" type="button" onclick="document.getElementById('modal-reset-password').classList.add('hidden')">
                    Cancel
                </x-button>
                <x-button variant="warning" size="sm" type="submit">
                    Update Password
                </x-button>
            </div>
        </form>
    </x-modal>

    @push('scripts')
    <script>
        function openEditUserModal(user, roleIds) {
            const form = document.getElementById('edit-user-form');
            form.action = `/users/${user.id}`;

            document.getElementById('edit-user-name').value = user.name || '';
            document.getElementById('edit-user-email').value = user.email || '';
            document.getElementById('edit-user-staff-id').value = user.staff_id || '';
            document.getElementById('edit-user-employee-code').value = user.employee_code || '';
            document.getElementById('edit-user-phone').value = user.phone || '';
            document.getElementById('edit-user-department').value = user.department || '';
            document.getElementById('edit-user-designation').value = user.designation || '';
            document.getElementById('edit-user-status').value = user.status || 'active';

            const checkboxes = document.querySelectorAll('.edit-role-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = roleIds.includes(parseInt(cb.value));
            });

            document.getElementById('modal-edit-user').classList.remove('hidden');
        }

        function openResetPasswordModal(userId, userName) {
            const form = document.getElementById('reset-password-form');
            form.action = `/users/${userId}/reset-password`;
            document.getElementById('reset-password-user-name').textContent = userName;
            document.getElementById('modal-reset-password').classList.remove('hidden');
        }
    </script>
    @endpush

@endsection