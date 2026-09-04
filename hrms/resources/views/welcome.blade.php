@extends('layouts.public')

@section('title', 'PulseHR - Enterprise Human Resource Management Suite')

@section('content')
<!-- Hero Section -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 pb-16 lg:pt-16 lg:pb-24" id="overview">
    
    <!-- Executive Hero Banner with Signature Deep Indigo Gradient (Standard from CIS & PayFlow MY) -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-950 via-slate-900 to-indigo-900 p-8 sm:p-12 lg:p-14 shadow-2xl shadow-indigo-950/40 border border-indigo-800/40 mb-12 text-white">
        <!-- Background Decorative Glow Elements -->
        <div class="absolute -right-20 -top-20 w-80 h-80 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute right-1/3 -bottom-24 w-64 h-64 bg-purple-500/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 bottom-0 w-48 h-48 bg-indigo-600/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
            
            <!-- Left: Hero Headline & Value Proposition -->
            <div class="lg:col-span-7 space-y-6">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <div class="w-9 h-9 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center text-indigo-300 font-bold text-lg shadow-xs border border-white/10">
                        <i class="bx bx-pulse"></i>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 inline-flex items-center gap-1.5 backdrop-blur-xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        HR Operations Ready
                    </span>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-400/30">
                        SRS Compliant
                    </span>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-500/20 text-purple-300 border border-purple-400/30">
                        Payroll Decoupled
                    </span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight leading-[1.18]">
                    Precision Workforce Governance &amp; HR Operations
                </h1>

                <p class="text-slate-300 text-sm sm:text-base leading-relaxed max-w-xl font-normal">
                    Centralize employee records, geofenced GPS clock-ins, multi-tiered leave approvals, quarterly OKRs, recruitment Kanban, and seamless data synchronization with <a href="https://github.com/mralif93/payroll-management-system" target="_blank" class="text-indigo-400 font-semibold underline underline-offset-2">PayFlow MY</a>.
                </p>

                <div class="flex flex-wrap items-center gap-4 pt-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2.5 px-6 py-3.5 rounded-xl font-bold text-sm bg-indigo-600 hover:bg-indigo-500 text-white shadow-xl shadow-indigo-600/40 border border-indigo-400/30 transition-all transform hover:-translate-y-0.5">
                            <i class="bx bxs-dashboard text-lg"></i>
                            <span>Enter Dashboard</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2.5 px-7 py-3.5 rounded-xl font-bold text-sm bg-indigo-600 hover:bg-indigo-500 text-white shadow-xl shadow-indigo-600/40 border border-indigo-400/30 transition-all transform hover:-translate-y-0.5">
                            <i class="bx bx-shield-quarter text-lg"></i>
                            <span>Staff Login</span>
                        </a>
                        <a href="#modules" class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl font-bold text-sm bg-slate-800/90 text-slate-200 hover:text-white hover:bg-slate-700/90 border border-slate-700 shadow-md transition-all">
                            <i class="bx bx-layer text-lg text-indigo-400"></i>
                            <span>Explore Modules</span>
                        </a>
                    @endauth
                </div>

                <!-- Quick Demo Credentials Hint -->
                <div class="inline-flex items-center gap-2.5 p-2.5 px-4 rounded-xl bg-white/5 border border-white/10 text-xs text-slate-300 font-mono">
                    <i class="bx bx-key text-indigo-400 text-sm"></i>
                    <span>Quick demo: <strong>admin@hrms.test</strong> &bull; pass: <strong>password</strong></span>
                </div>
            </div>

            <!-- Right: Executive KPI Quick Widget -->
            <div class="lg:col-span-5">
                <div class="rounded-2xl bg-slate-900/90 border border-indigo-700/40 p-6 shadow-2xl backdrop-blur-md">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-4 mb-4">
                        <div class="flex items-center gap-2 text-xs font-bold text-slate-300">
                            <i class="bx bx-broadcast text-emerald-400 text-base"></i>
                            <span>Live System Velocity</span>
                        </div>
                        <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-bold border border-emerald-500/30">
                            ONLINE
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3.5 rounded-xl bg-slate-800/80 border border-slate-700/60">
                            <span class="text-[11px] font-semibold text-slate-400 block">Total Staff</span>
                            <span class="text-2xl font-black text-white block mt-1">1,248</span>
                            <span class="text-[10px] text-emerald-400 font-bold flex items-center gap-0.5 mt-0.5">
                                <i class="bx bx-up-arrow-alt"></i> +12 this mo
                            </span>
                        </div>
                        <div class="p-3.5 rounded-xl bg-slate-800/80 border border-slate-700/60">
                            <span class="text-[11px] font-semibold text-slate-400 block">Clocked In</span>
                            <span class="text-2xl font-black text-white block mt-1">94.8%</span>
                            <span class="text-[10px] text-slate-400 font-medium block mt-0.5">
                                1,183 verified
                            </span>
                        </div>
                        <div class="p-3.5 rounded-xl bg-slate-800/80 border border-slate-700/60">
                            <span class="text-[11px] font-semibold text-slate-400 block">Leave In-Flight</span>
                            <span class="text-2xl font-black text-amber-400 block mt-1">18</span>
                            <span class="text-[10px] text-amber-300 font-medium block mt-0.5">
                                6 awaiting HR
                            </span>
                        </div>
                        <div class="p-3.5 rounded-xl bg-slate-800/80 border border-slate-700/60">
                            <span class="text-[11px] font-semibold text-slate-400 block">Payroll Feeder</span>
                            <span class="text-2xl font-black text-indigo-400 block mt-1">Ready</span>
                            <span class="text-[10px] text-indigo-300 font-medium block mt-0.5">
                                PayFlow MY Sync
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modules Catalog Grid Section -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20" id="modules">
    <div class="text-center max-w-2xl mx-auto mb-12">
        <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 uppercase tracking-wider">
            Architecture Blueprint
        </span>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mt-3 tracking-tight">
            Core HR Operations Matrix
        </h2>
        <p class="text-slate-600 dark:text-slate-400 text-sm mt-2">
            Structured modules built strictly adhering to your Software Requirements Specification.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Module 1: PIM -->
        <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-all">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-2xl font-bold mb-4 border border-indigo-100 dark:border-indigo-800/60">
                <i class="bx bx-user-check"></i>
            </div>
            <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Employee Lifecycle (PIM)</h3>
            <p class="mt-2 text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                Centralized profile vault, auto-generated codes (<code class="font-mono text-indigo-600 dark:text-indigo-400 text-[11px]">EMP-YYYY-XXXX</code>), encrypted NRIC/Tax IDs, and visual reporting org hierarchy.
            </p>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs font-semibold text-indigo-600 dark:text-indigo-400">
                <span>Module 02 &bull; Master Records</span>
                <i class="bx bx-chevron-right text-lg"></i>
            </div>
        </div>

        <!-- Module 2: Attendance & Geofencing -->
        <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-all">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl font-bold mb-4 border border-emerald-100 dark:border-emerald-800/60">
                <i class="bx bx-map-pin"></i>
            </div>
            <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Geofenced Attendance</h3>
            <p class="mt-2 text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                Single-click punch-in validated with HTML5 GPS (100m perimeter tolerance), flexible shift scheduling, grace periods, and overtime multiplier calculation.
            </p>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                <span>Module 03 &bull; Time Tracking</span>
                <i class="bx bx-chevron-right text-lg"></i>
            </div>
        </div>

        <!-- Module 3: Leave Engine -->
        <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-all">
            <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-2xl font-bold mb-4 border border-amber-100 dark:border-amber-800/60">
                <i class="bx bx-calendar-event"></i>
            </div>
            <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Leave &amp; Absence Governance</h3>
            <p class="mt-2 text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                Automated entitlement accruals, dual approval chain (Manager &rarr; HR), overlapping request validation, and department leave calendar integration.
            </p>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs font-semibold text-amber-600 dark:text-amber-400">
                <span>Module 04 &bull; Multi-tier Approvals</span>
                <i class="bx bx-chevron-right text-lg"></i>
            </div>
        </div>

        <!-- Module 4: Performance OKRs -->
        <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-all">
            <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-2xl font-bold mb-4 border border-purple-100 dark:border-purple-800/60">
                <i class="bx bx-target-lock"></i>
            </div>
            <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Performance Appraisals &amp; OKRs</h3>
            <p class="mt-2 text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                Quarterly quantifiable OKRs, 360-degree appraisal cycles (Self, Peer, and Manager reviews), with weighted scoring distribution.
            </p>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs font-semibold text-purple-600 dark:text-purple-400">
                <span>Module 05 &bull; Appraisal Matrix</span>
                <i class="bx bx-chevron-right text-lg"></i>
            </div>
        </div>

        <!-- Module 5: Recruitment ATS -->
        <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-all">
            <div class="w-12 h-12 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-2xl font-bold mb-4 border border-sky-100 dark:border-sky-800/60">
                <i class="bx bx-briefcase"></i>
            </div>
            <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Recruitment &amp; ATS Pipeline</h3>
            <p class="mt-2 text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                SEO-friendly careers portal, interactive drag-and-drop Kanban hiring stages, interview scheduling, and 1-click conversion to employee onboarding.
            </p>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs font-semibold text-sky-600 dark:text-sky-400">
                <span>Module 06 &bull; Talent Acquisition</span>
                <i class="bx bx-chevron-right text-lg"></i>
            </div>
        </div>

        <!-- Module 6: Payroll Feeder -->
        <div id="payroll-integration" class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-all">
            <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 flex items-center justify-center text-2xl font-bold mb-4 border border-slate-200 dark:border-slate-700">
                <i class="bx bx-transfer-alt"></i>
            </div>
            <h3 class="text-base font-extrabold text-slate-900 dark:text-white">External Payroll Feeder</h3>
            <p class="mt-2 text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                Dedicated sync endpoints and CSV feeders connected directly to your standalone <a href="https://github.com/mralif93/payroll-management-system" target="_blank" class="text-indigo-600 dark:text-indigo-400 font-semibold underline">PayFlow MY</a> for statutory tax computations.
            </p>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs font-semibold text-slate-700 dark:text-slate-300">
                <span>Module 07 &bull; Decoupled Feeder</span>
                <i class="bx bx-link-external text-lg"></i>
            </div>
        </div>
    </div>
</section>

<!-- RBAC Matrix Section -->
<section class="border-t border-slate-200 dark:border-slate-800/80 bg-white/70 dark:bg-slate-900/60 py-16" id="rbac">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 uppercase tracking-wider">
                Security &amp; Compliance
            </span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mt-3 tracking-tight">
                Role-Based Access Matrix (RBAC)
            </h2>
            <p class="text-slate-600 dark:text-slate-400 text-sm mt-2">
                Enforcing strict segregation between Super Admin, HR, Managers, and Employees.
            </p>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm bg-white dark:bg-slate-900">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/60 text-slate-700 dark:text-slate-300 font-bold uppercase tracking-wider">
                        <th class="py-3.5 px-6">Module / Area</th>
                        <th class="py-3.5 px-6 text-center">Super Admin</th>
                        <th class="py-3.5 px-6 text-center">HR Administrator</th>
                        <th class="py-3.5 px-6 text-center">Department Lead</th>
                        <th class="py-3.5 px-6 text-center">Employee</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 text-slate-600 dark:text-slate-400 font-medium">
                    <tr>
                        <td class="py-3.5 px-6 font-bold text-slate-900 dark:text-white">System Settings &amp; Audit Trail</td>
                        <td class="py-3.5 px-6 text-center text-emerald-600 font-bold">Full Access</td>
                        <td class="py-3.5 px-6 text-center text-slate-400">No Access</td>
                        <td class="py-3.5 px-6 text-center text-slate-400">No Access</td>
                        <td class="py-3.5 px-6 text-center text-slate-400">No Access</td>
                    </tr>
                    <tr>
                        <td class="py-3.5 px-6 font-bold text-slate-900 dark:text-white">Personnel Information (PIM)</td>
                        <td class="py-3.5 px-6 text-center text-emerald-600 font-bold">Full Access</td>
                        <td class="py-3.5 px-6 text-center text-emerald-600 font-bold">Full Access</td>
                        <td class="py-3.5 px-6 text-center text-indigo-600 font-semibold">Team Only</td>
                        <td class="py-3.5 px-6 text-center text-slate-600">Self Only</td>
                    </tr>
                    <tr>
                        <td class="py-3.5 px-6 font-bold text-slate-900 dark:text-white">Attendance &amp; Geofencing</td>
                        <td class="py-3.5 px-6 text-center text-emerald-600 font-bold">Full Access</td>
                        <td class="py-3.5 px-6 text-center text-emerald-600 font-bold">Full Access</td>
                        <td class="py-3.5 px-6 text-center text-indigo-600 font-semibold">Approve Team</td>
                        <td class="py-3.5 px-6 text-center text-slate-600">Punch &amp; View Self</td>
                    </tr>
                    <tr>
                        <td class="py-3.5 px-6 font-bold text-slate-900 dark:text-white">Leave Administration</td>
                        <td class="py-3.5 px-6 text-center text-emerald-600 font-bold">Full Access</td>
                        <td class="py-3.5 px-6 text-center text-emerald-600 font-bold">Manage Policies</td>
                        <td class="py-3.5 px-6 text-center text-indigo-600 font-semibold">Approve Team</td>
                        <td class="py-3.5 px-6 text-center text-slate-600">Apply &amp; Balance</td>
                    </tr>
                    <tr>
                        <td class="py-3.5 px-6 font-bold text-slate-900 dark:text-white">Performance Appraisals</td>
                        <td class="py-3.5 px-6 text-center text-emerald-600 font-bold">Full Access</td>
                        <td class="py-3.5 px-6 text-center text-emerald-600 font-bold">Manage Cycles</td>
                        <td class="py-3.5 px-6 text-center text-indigo-600 font-semibold">Review Team</td>
                        <td class="py-3.5 px-6 text-center text-slate-600">Self Assessment</td>
                    </tr>
                    <tr>
                        <td class="py-3.5 px-6 font-bold text-slate-900 dark:text-white">Recruitment &amp; ATS</td>
                        <td class="py-3.5 px-6 text-center text-emerald-600 font-bold">Full Access</td>
                        <td class="py-3.5 px-6 text-center text-emerald-600 font-bold">Full Access</td>
                        <td class="py-3.5 px-6 text-center text-indigo-600 font-semibold">Interviewer</td>
                        <td class="py-3.5 px-6 text-center text-slate-600">Referrals</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
