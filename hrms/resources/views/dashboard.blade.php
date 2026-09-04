@extends('layouts.admin')

@section('title', 'Workforce Overview - PulseHR Management System')
@section('page-title', 'Workforce Operations & Governance')

@section('content')
<div class="space-y-8 animate__animated animate__fadeIn">

    <!-- Standard Page Header Banner -->
    <x-page-header
        title="Workforce Operations & Governance"
        subtitle="Real-time personnel monitoring, attendance verification, and operational KPI telemetry"
        icon="bx-grid-alt"
        badge="Live Telemetry"
        badgeVariant="emerald"
    >
        <x-button
            type="button"
            variant="secondary"
            size="md"
            icon="bx bx-refresh"
            onclick="window.location.reload()"
            class="bg-white/10 hover:bg-white/20 text-white border-white/20"
        >
            Refresh Data
        </x-button>
    </x-page-header>

    <!-- 4 High-Level Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <x-stat-card 
            title="Active Workforce"
            value="1,248"
            icon="bx bx-group"
            color="indigo"
            change="+2.4%"
            changeType="increase"
            subtitle="8 departments &bull; 3 regional branches"
        />

        <x-stat-card 
            title="Clocked In Today"
            value="1,183"
            icon="bx bx-time-five"
            color="emerald"
            change="94.8% on-duty"
            changeType="increase"
            subtitle="42 approved leave &bull; 23 unlogged"
        />

        <x-stat-card 
            title="Leave Applications"
            value="18"
            icon="bx bx-calendar-event"
            color="amber"
            subtitle="6 awaiting HR &bull; 12 manager-approved"
        />

        <x-stat-card 
            title="Payroll Feed Status"
            value="Ready"
            icon="bx bx-transfer-alt"
            color="purple"
            subtitle="Auto-sync verified to PayFlow MY engine"
        />
    </div>

    <!-- External Payroll Engine Integration Banner -->
    <div class="p-6 rounded-2xl bg-gradient-to-r from-indigo-950 via-slate-900 to-indigo-900 text-white shadow-xl border border-indigo-800/40 flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
        <div class="flex items-start gap-4">
            <div class="w-11 h-11 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center text-white text-xl font-bold shrink-0 border border-white/15">
                <i class="bx bxs-wallet"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="text-base font-black tracking-tight text-white">External Payroll Engine Connected: PayFlow MY</h3>
                    <span class="px-2.5 py-0.5 text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 rounded-full inline-flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Decoupled Architecture
                    </span>
                </div>
                <p class="text-xs text-slate-300 mt-1 max-w-2xl font-normal leading-relaxed">
                    Malaysian statutory calculations (EPF, SOCSO/SKBBK 2026, EIS, PCB/MTD) are processed via your standalone <code class="bg-white/10 px-1.5 py-0.5 rounded text-indigo-200 font-mono text-[11px]">payroll-management-system</code>. PulseHR streams verified employee hours, overtime totals, and approved unpaid leave days.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <a href="https://github.com/mralif93/payroll-management-system" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-500 text-white shadow-md transition-all border border-indigo-400/30">
                <i class="bx bx-git-repo-forked text-sm"></i>
                <span>Payroll Repository</span>
                <i class="bx bx-link-external text-xs"></i>
            </a>
        </div>
    </div>

    <!-- Main Operations Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left 2 Cols: Employee Master Directory -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 transition-colors">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Active Personnel Directory</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Recently updated master records across departments</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/60 px-3 py-1 rounded-xl border border-indigo-200 dark:border-indigo-800">
                        1,248 Records
                    </span>
                </div>
            </div>

            <!-- Table -->
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800">
                            <th class="pb-3">Employee</th>
                            <th class="pb-3 hidden md:table-cell">Department</th>
                            <th class="pb-3 hidden lg:table-cell">Designation</th>
                            <th class="pb-3 hidden sm:table-cell">Status</th>
                            <th class="pb-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 text-slate-600 dark:text-slate-300 font-medium">
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3.5 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-indigo-100 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300 font-black flex items-center justify-center text-xs shrink-0">
                                    AV
                                </div>
                                <div class="min-w-0">
                                    <span class="font-bold text-slate-900 dark:text-white block truncate max-w-[150px] sm:max-w-none">Alexander Vance</span>
                                    <span class="text-[10px] text-slate-400 font-mono">EMP-2026-0001</span>
                                    <span class="text-[10px] text-slate-500 block md:hidden">Executive Office &bull; CTO</span>
                                </div>
                            </td>
                            <td class="py-3.5 text-slate-500 dark:text-slate-400 hidden md:table-cell">Executive Office</td>
                            <td class="py-3.5 text-slate-500 dark:text-slate-400 hidden lg:table-cell">CTO &amp; Admin</td>
                            <td class="py-3.5 hidden sm:table-cell">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                    Permanent
                                </span>
                            </td>
                            <td class="py-3.5 text-right">
                                <a href="{{ route('employees.index') }}" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">View</a>
                            </td>
                        </tr>

                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3.5 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-purple-100 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 font-black flex items-center justify-center text-xs shrink-0">
                                    SJ
                                </div>
                                <div class="min-w-0">
                                    <span class="font-bold text-slate-900 dark:text-white block truncate max-w-[150px] sm:max-w-none">Sarah Jenkins</span>
                                    <span class="text-[10px] text-slate-400 font-mono">EMP-2026-0002</span>
                                    <span class="text-[10px] text-slate-500 block md:hidden">HR &bull; People Ops Lead</span>
                                </div>
                            </td>
                            <td class="py-3.5 text-slate-500 dark:text-slate-400 hidden md:table-cell">Human Resources</td>
                            <td class="py-3.5 text-slate-500 dark:text-slate-400 hidden lg:table-cell">People Ops Lead</td>
                            <td class="py-3.5 hidden sm:table-cell">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                    Permanent
                                </span>
                            </td>
                            <td class="py-3.5 text-right">
                                <a href="{{ route('employees.index') }}" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">View</a>
                            </td>
                        </tr>

                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3.5 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-blue-100 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 font-black flex items-center justify-center text-xs shrink-0">
                                    MC
                                </div>
                                <div class="min-w-0">
                                    <span class="font-bold text-slate-900 dark:text-white block truncate max-w-[150px] sm:max-w-none">Marcus Chen</span>
                                    <span class="text-[10px] text-slate-400 font-mono">EMP-2026-0003</span>
                                    <span class="text-[10px] text-slate-500 block md:hidden">Engineering &bull; VP Eng</span>
                                </div>
                            </td>
                            <td class="py-3.5 text-slate-500 dark:text-slate-400 hidden md:table-cell">Engineering</td>
                            <td class="py-3.5 text-slate-500 dark:text-slate-400 hidden lg:table-cell">VP of Engineering</td>
                            <td class="py-3.5 hidden sm:table-cell">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                    Permanent
                                </span>
                            </td>
                            <td class="py-3.5 text-right">
                                <a href="{{ route('employees.index') }}" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">View</a>
                            </td>
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3.5 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 font-black flex items-center justify-center text-xs shrink-0">
                                    EW
                                </div>
                                <div class="min-w-0">
                                    <span class="font-bold text-slate-900 dark:text-white block truncate max-w-[150px] sm:max-w-none">Emily Watson</span>
                                    <span class="text-[10px] text-slate-400 font-mono">EMP-2026-0004</span>
                                    <span class="text-[10px] text-slate-500 block md:hidden">Product &bull; UI/UX</span>
                                </div>
                            </td>
                            <td class="py-3.5 text-slate-500 dark:text-slate-400 hidden md:table-cell">Product &amp; Design</td>
                            <td class="py-3.5 text-slate-500 dark:text-slate-400 hidden lg:table-cell">Senior UI/UX Designer</td>
                            <td class="py-3.5 hidden sm:table-cell">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                    Probation
                                </span>
                            </td>
                            <td class="py-3.5 text-right">
                                <a href="{{ route('employees.index') }}" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">View</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right 1 Col: Geofence Punch & Pending Queue -->
        <div class="space-y-6">
            <!-- Digital Geofenced Punch Widget -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 transition-colors">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Digital Punch Terminal</h3>
                    <span class="flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                </div>
                <div class="mt-4 text-center">
                    <p class="text-3xl font-mono font-black text-slate-900 dark:text-white">09:14:32 AM</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center justify-center gap-1 font-medium">
                        <i class="bx bx-map-pin text-indigo-600 dark:text-indigo-400"></i>
                        <span>Office HQ Campus (Within 100m)</span>
                    </p>
                </div>
                <div class="mt-5 grid grid-cols-2 gap-3">
                    <button class="py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold flex items-center justify-center gap-1.5 shadow-sm transition-all cursor-pointer">
                        <i class="bx bx-log-in"></i>
                        <span>Clock In</span>
                    </button>
                    <button class="py-2.5 px-3 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold flex items-center justify-center gap-1.5 shadow-sm transition-all cursor-pointer">
                        <i class="bx bx-log-out"></i>
                        <span>Clock Out</span>
                    </button>
                </div>
            </div>

            <!-- Pending Leave Requests Card -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 transition-colors">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Awaiting Approvals</h3>
                    <span class="text-[10px] font-bold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/60 px-2 py-0.5 rounded-full">4 Urgent</span>
                </div>
                <div class="mt-4 space-y-3">
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/60 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-900 dark:text-white">Emily Watson</span>
                            <span class="px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 text-[10px] font-bold">Annual (3d)</span>
                        </div>
                        <p class="text-slate-500 dark:text-slate-400 mt-1 text-[11px]">Sept 10 - Sept 12 &bull; Family vacation</p>
                        <div class="mt-2.5 flex items-center gap-2">
                            <button class="px-3 py-1 rounded-lg bg-indigo-600 text-white text-[11px] font-bold hover:bg-indigo-500 transition-colors cursor-pointer">Approve</button>
                            <button class="px-3 py-1 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-[11px] font-bold hover:bg-slate-300 transition-colors cursor-pointer">Reject</button>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/60 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-900 dark:text-white">David Miller</span>
                            <span class="px-1.5 py-0.5 rounded bg-blue-100 dark:bg-blue-950/80 text-blue-800 dark:text-blue-300 text-[10px] font-bold">Sick Leave (1d)</span>
                        </div>
                        <p class="text-slate-500 dark:text-slate-400 mt-1 text-[11px]">MC certificate uploaded</p>
                        <div class="mt-2.5 flex items-center gap-2">
                            <button class="px-3 py-1 rounded-lg bg-indigo-600 text-white text-[11px] font-bold hover:bg-indigo-500 transition-colors cursor-pointer">Approve</button>
                            <button class="px-3 py-1 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-[11px] font-bold hover:bg-slate-300 transition-colors cursor-pointer">Reject</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
