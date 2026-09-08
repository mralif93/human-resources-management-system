@extends('layouts.admin')
@section('title', 'Roles & Permissions - PulseHR')
@section('page-title', 'Role-Based Access Control (RBAC)')
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

        <!-- Executive Page Hero Banner -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-purple-950 via-slate-900 to-indigo-950 text-white p-6 sm:p-8 shadow-xl shadow-purple-950/40 border border-purple-800/40">
            <div class="absolute -right-16 -top-16 w-64 h-64 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute right-1/3 -bottom-20 w-48 h-48 bg-indigo-500/15 rounded-full blur-2xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                <div class="space-y-2 max-w-2xl">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <div class="w-9 h-9 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center text-purple-300 font-bold text-base shadow-xs">
                            <i class="bx bx-shield-quarter"></i>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">Role-Based Access Control (RBAC)</h1>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-500/20 text-purple-300 border border-purple-400/30 inline-flex items-center gap-1.5 backdrop-blur-xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-purple-400 animate-pulse"></span>
                            {{ $roles->total() }} Roles Defined
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-purple-100/80 leading-relaxed">
                        Configure clinical security roles and granular permission matrices for Doctors, Cashiers, Receptionists, and Clinic Administrators.
                    </p>
                </div>

                <div class="flex items-center gap-2.5 sm:gap-3 flex-wrap shrink-0">
                    <a 
                        href="{{ route('users.index') }}" 
                        class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 text-white text-xs font-bold border border-white/20 transition flex items-center gap-2 cursor-pointer"
                    >
                        <i class="bx bx-user-pin text-base text-purple-300"></i>
                        <span>Staff Directory</span>
                    </a>
                    <button 
                        type="button" 
                        onclick="document.getElementById('modal-create-role').classList.remove('hidden')"
                        class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold shadow-lg shadow-purple-600/30 border border-purple-500/30 transition flex items-center gap-2 cursor-pointer hover:scale-[1.02] active:scale-[0.98]"
                    >
                        <i class="bx bx-plus-circle text-base"></i>
                        <span>Create Custom Role</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-card 
                title="Configured Roles"
                value="{{ $roles->total() }}"
                icon="bx bx-shield-quarter"
                color="purple"
                subtitle="Clinical privilege profiles"
            />
            <x-stat-card 
                title="System Roles"
                value="{{ $roles->where('is_system', true)->count() }}"
                icon="bx bx-lock-alt"
                color="indigo"
                subtitle="Protected core profiles"
            />
            <x-stat-card 
                title="Permission Nodes"
                value="{{ \App\Models\Permission::count() }}"
                icon="bx bx-key"
                color="emerald"
                subtitle="Granular security gates"
            />
            <x-stat-card 
                title="Assigned Personnel"
                value="{{ \App\Models\User::whereHas('roles')->count() }}"
                icon="bx bx-user-check"
                color="blue"
                subtitle="Users with explicit roles"
            />
        </div>

        <!-- Filter & Search Toolbar -->
        <x-card class="p-4">
            <form method="GET" action="{{ route('roles.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
                <div class="sm:col-span-10 relative">
                    <i class="bx bx-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base"></i>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="Search roles by title, identifier code, or description..."
                        class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-600 transition"
                    >
                </div>
                <div class="sm:col-span-2 flex items-center gap-2">
                    <button 
                        type="submit"
                        class="w-full py-2 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold transition flex items-center justify-center gap-1.5 shadow-sm cursor-pointer"
                    >
                        <i class="bx bx-filter-alt"></i>
                        <span>Filter</span>
                    </button>
                    @if(request('search'))
                        <a 
                            href="{{ route('roles.index') }}"
                            class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
                            title="Clear Filter"
                        >
                            <i class="bx bx-x text-base"></i>
                        </a>
                    @endif
                </div>
            </form>
        </x-card>

        <!-- Roles Listing Table -->
        <x-card class="overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50/80 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 font-bold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="px-6 py-4">Role Profile</th>
                            <th class="px-4 py-4">Slug Identifier</th>
                            <th class="px-4 py-4">Assigned Personnel</th>
                            <th class="px-4 py-4">Permissions Granted</th>
                            <th class="px-4 py-4">Protection Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300 font-normal">
                        @forelse($roles as $role)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition group">
                                <!-- Role Profile -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-sm border border-purple-200 dark:border-purple-800/50 shrink-0 shadow-2xs">
                                            <i class="bx {{ $role->name === 'super_admin' || $role->name === 'admin' ? 'bx-crown' : ($role->name === 'doctor' ? 'bx-plus-medical' : ($role->name === 'cashier' ? 'bx-credit-card' : 'bx-shield')) }}"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-slate-900 dark:text-white text-sm group-hover:text-purple-600 dark:group-hover:text-purple-400 transition">
                                                {{ $role->display_name }}
                                            </div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-1">
                                                {{ $role->description ?? 'Custom clinical role specification.' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Slug Identifier -->
                                <td class="px-4 py-4 font-mono text-[11px]">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                                        {{ $role->name }}
                                    </span>
                                </td>

                                <!-- Assigned Personnel -->
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-1.5 font-bold text-slate-800 dark:text-slate-200">
                                        <i class="bx bx-user text-slate-400"></i>
                                        <span>{{ $role->users->count() }} Users</span>
                                    </div>
                                </td>

                                <!-- Permissions Granted -->
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/80">
                                            {{ $role->permissions->count() }} Permissions
                                        </span>
                                    </div>
                                </td>

                                <!-- Protection Status -->
                                <td class="px-4 py-4">
                                    @if($role->is_system)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/20">
                                            <i class="bx bx-lock-alt text-xs"></i>
                                            <span>System Core</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20">
                                            <i class="bx bx-check-circle text-xs"></i>
                                            <span>Custom Role</span>
                                        </span>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Edit Matrix Button -->
                                        <button 
                                            type="button"
                                            onclick="openEditRoleModal({{ json_encode($role) }}, {{ json_encode($role->permissions->pluck('id')) }})"
                                            class="p-2 rounded-lg text-slate-500 hover:text-purple-600 hover:bg-purple-50 dark:hover:bg-purple-900/30 transition cursor-pointer"
                                            title="Edit Role Matrix"
                                        >
                                            <i class="bx bx-edit text-base"></i>
                                        </button>

                                        <!-- Delete Role -->
                                        @if(!$role->is_system && $role->users->count() === 0)
                                            <form action="{{ route('roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete the role {{ $role->display_name }}?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button 
                                                    type="submit"
                                                    class="p-2 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30 transition cursor-pointer"
                                                    title="Delete Custom Role"
                                                >
                                                    <i class="bx bx-trash text-base"></i>
                                                </button>
                                            </form>
                                        @else
                                            <button 
                                                type="button" 
                                                disabled 
                                                class="p-2 rounded-lg text-slate-300 dark:text-slate-700 cursor-not-allowed"
                                                title="{{ $role->is_system ? 'System core roles cannot be deleted' : 'Role cannot be deleted while assigned to active users' }}"
                                            >
                                                <i class="bx bx-trash text-base"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <i class="bx bx-shield-x text-4xl text-slate-300 dark:text-slate-700"></i>
                                        <p class="font-bold text-sm text-slate-600 dark:text-slate-400">No security roles matching your query</p>
                                        <p class="text-xs text-slate-400">Try adjusting your keyword filter or register a new custom role.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($roles->hasPages())
                <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                    {{ $roles->links() }}
                </div>
            @endif
        </x-card>

        <!-- Granular Permission Matrix Reference Accordion -->
        <x-card class="p-6">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4 mb-6">
                <div>
                    <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="bx bx-key text-purple-600"></i>
                        <span>Permission Node Matrix Reference</span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Granular security capabilities categorized by clinical and financial operational modules.
                    </p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-50 dark:bg-purple-950/50 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                    {{ count($permissions) }} Active Modules
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($permissions as $module => $modulePermissions)
                    <div class="rounded-2xl p-4 bg-slate-50/70 dark:bg-slate-900/50 border border-slate-200/80 dark:border-slate-800">
                        <div class="flex items-center gap-2.5 pb-3 border-b border-slate-200/60 dark:border-slate-800">
                            <div class="w-7 h-7 rounded-lg bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-xs">
                                <i class="bx bx-folder"></i>
                            </div>
                            <span class="font-bold text-xs uppercase tracking-wider text-slate-800 dark:text-slate-200">{{ $module }}</span>
                            <span class="ml-auto text-[10px] font-mono font-bold text-slate-400">({{ $modulePermissions->count() }})</span>
                        </div>
                        <div class="mt-3 space-y-2">
                            @foreach($modulePermissions as $perm)
                                <div class="flex items-start gap-2 text-xs">
                                    <i class="bx bx-check-shield text-purple-500 mt-0.5 shrink-0"></i>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-800 dark:text-slate-200 text-[11px] leading-tight">{{ $perm->display_name }}</div>
                                        <div class="font-mono text-[10px] text-slate-400">{{ $perm->name }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>

    </div>

    <!-- ========================================== -->
    <!-- MODAL: CREATE ROLE & PERMISSION MATRIX    -->
    <!-- ========================================== -->
    <x-modal name="create-role" title="Create Custom Access Role" maxWidth="2xl">
        <form action="{{ route('roles.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Basic Role Info -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                        Role Display Name <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="display_name" 
                        required 
                        placeholder="e.g. Senior Nurse / Pharmacist"
                        class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-600 transition"
                    >
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                        Identifier Slug <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        required 
                        placeholder="e.g. senior_nurse"
                        class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-600 transition"
                    >
                    <p class="text-[10px] text-slate-400">Lowercase letters and underscores only.</p>
                </div>

                <div class="sm:col-span-2 space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Role Description</label>
                    <textarea 
                        name="description" 
                        rows="2" 
                        placeholder="Brief summary of clinic duties and station privileges assigned to this profile..."
                        class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-600 transition"
                    ></textarea>
                </div>
            </div>

            <!-- Permission Matrix Selection -->
            <div class="space-y-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Granular Permission Grant Matrix
                    </label>
                    <button 
                        type="button" 
                        onclick="toggleAllCreatePermissions()"
                        class="text-[11px] font-bold text-purple-600 hover:text-purple-700 dark:text-purple-400 cursor-pointer"
                    >
                        Select / Unselect All
                    </button>
                </div>

                <div class="space-y-4 max-h-72 overflow-y-auto pr-1">
                    @foreach($permissions as $module => $modulePerms)
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800 space-y-2">
                            <div class="text-[11px] font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                                <i class="bx bx-shield text-purple-500"></i>
                                <span>{{ $module }}</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                                @foreach($modulePerms as $perm)
                                    <label class="flex items-start gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer hover:text-purple-600">
                                        <input 
                                            type="checkbox" 
                                            name="permission_ids[]" 
                                            value="{{ $perm->id }}"
                                            class="create-perm-checkbox mt-0.5 rounded border-slate-300 text-purple-600 focus:ring-purple-500"
                                        >
                                        <span class="text-[11px]">
                                            <span class="font-bold block">{{ $perm->display_name }}</span>
                                            <span class="text-[10px] text-slate-400 font-mono">{{ $perm->name }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button 
                    type="button"
                    onclick="document.getElementById('modal-create-role').classList.add('hidden')"
                    class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
                >
                    Cancel
                </button>
                <button 
                    type="submit"
                    class="px-5 py-2 rounded-xl text-xs font-bold bg-purple-600 hover:bg-purple-500 text-white shadow-md shadow-purple-600/30 transition cursor-pointer flex items-center gap-1.5"
                >
                    <i class="bx bx-check"></i>
                    <span>Save Custom Role</span>
                </button>
            </div>
        </form>
    </x-modal>

    <!-- ========================================== -->
    <!-- MODAL: EDIT ROLE & PERMISSION MATRIX      -->
    <!-- ========================================== -->
    <x-modal name="edit-role" title="Configure Role Permissions Matrix" maxWidth="2xl">
        <form id="edit-role-form" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Basic Role Info -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                        Role Display Name <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="display_name" 
                        id="edit-role-display-name"
                        required 
                        class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-600 transition"
                    >
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                        Identifier Slug (Read Only)
                    </label>
                    <input 
                        type="text" 
                        id="edit-role-name"
                        readonly 
                        class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 text-slate-500 text-xs font-mono cursor-not-allowed"
                    >
                </div>

                <div class="sm:col-span-2 space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Role Description</label>
                    <textarea 
                        name="description" 
                        id="edit-role-description"
                        rows="2" 
                        class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-600 transition"
                    ></textarea>
                </div>
            </div>

            <!-- Permission Matrix Selection -->
            <div class="space-y-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        Module Permission Matrix
                    </label>
                    <button 
                        type="button" 
                        onclick="toggleAllEditPermissions()"
                        class="text-[11px] font-bold text-purple-600 hover:text-purple-700 dark:text-purple-400 cursor-pointer"
                    >
                        Select / Unselect All
                    </button>
                </div>

                <div class="space-y-4 max-h-72 overflow-y-auto pr-1">
                    @foreach($permissions as $module => $modulePerms)
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800 space-y-2">
                            <div class="text-[11px] font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                                <i class="bx bx-shield text-purple-500"></i>
                                <span>{{ $module }}</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                                @foreach($modulePerms as $perm)
                                    <label class="flex items-start gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer hover:text-purple-600">
                                        <input 
                                            type="checkbox" 
                                            name="permission_ids[]" 
                                            value="{{ $perm->id }}"
                                            id="edit-perm-{{ $perm->id }}"
                                            class="edit-perm-checkbox mt-0.5 rounded border-slate-300 text-purple-600 focus:ring-purple-500"
                                        >
                                        <span class="text-[11px]">
                                            <span class="font-bold block">{{ $perm->display_name }}</span>
                                            <span class="text-[10px] text-slate-400 font-mono">{{ $perm->name }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button 
                    type="button"
                    onclick="document.getElementById('modal-edit-role').classList.add('hidden')"
                    class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer"
                >
                    Cancel
                </button>
                <button 
                    type="submit"
                    class="px-5 py-2 rounded-xl text-xs font-bold bg-purple-600 hover:bg-purple-500 text-white shadow-md shadow-purple-600/30 transition cursor-pointer flex items-center gap-1.5"
                >
                    <i class="bx bx-check"></i>
                    <span>Update Role Matrix</span>
                </button>
            </div>
        </form>
    </x-modal>

    <!-- Page Specific Script for Modal Binding -->
    <script>
        function toggleAllCreatePermissions() {
            const boxes = document.querySelectorAll('.create-perm-checkbox');
            const allChecked = Array.from(boxes).every(b => b.checked);
            boxes.forEach(b => b.checked = !allChecked);
        }

        function toggleAllEditPermissions() {
            const boxes = document.querySelectorAll('.edit-perm-checkbox');
            const allChecked = Array.from(boxes).every(b => b.checked);
            boxes.forEach(b => b.checked = !allChecked);
        }

        function openEditRoleModal(role, assignedPermIds) {
            const form = document.getElementById('edit-role-form');
            form.action = `/roles/${role.id}`;

            document.getElementById('edit-role-display-name').value = role.display_name || '';
            document.getElementById('edit-role-name').value = role.name || '';
            document.getElementById('edit-role-description').value = role.description || '';

            // Reset all checkboxes
            document.querySelectorAll('.edit-perm-checkbox').forEach(cb => cb.checked = false);

            // Check assigned permissions
            if (Array.isArray(assignedPermIds)) {
                assignedPermIds.forEach(id => {
                    const cb = document.getElementById(`edit-perm-${id}`);
                    if (cb) cb.checked = true;
                });
            }

            document.getElementById('modal-edit-role').classList.remove('hidden');
        }
    </script>

@endsection