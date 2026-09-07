<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Dashboard - PulseHR')</title>

    <!-- Theme Initialization to prevent FOUC -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles / Scripts via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="h-full bg-slate-100/80 dark:bg-slate-950 text-slate-900 dark:text-slate-100 antialiased selection:bg-indigo-600 selection:text-white transition-colors duration-300 flex overflow-hidden">

    <!-- Mobile Sidebar Backdrop Overlay -->
    <div id="sidebar-backdrop" onclick="toggleMobileSidebar()" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-40 lg:hidden hidden transition-opacity duration-300"></div>

    <!-- SIDEBAR NAVIGATION (Standard PayFlow MY / CIS Style) -->
    <aside id="admin-sidebar" class="fixed lg:static inset-y-0 left-0 -translate-x-full lg:translate-x-0 w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col shrink-0 z-40 lg:z-auto transition-transform duration-300 ease-in-out">
        
        <!-- Sidebar Brand Logo & Mobile Close Button -->
        <div class="h-16 sm:h-20 flex items-center justify-between px-5 border-b border-slate-100 dark:border-slate-800/80">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                <div class="h-9 w-9 rounded-xl bg-gradient-to-br from-indigo-500 via-indigo-600 to-indigo-700 flex items-center justify-center text-white shadow-md shadow-indigo-600/30 border border-indigo-400/30 group-hover:scale-105 transition-transform">
                    <i class="bx bxs-user-badge text-xl font-bold"></i>
                </div>
                <div>
                    <span class="font-extrabold text-slate-900 dark:text-white text-base tracking-tight">
                        Pulse<span class="text-indigo-600 dark:text-indigo-400">HR</span>
                    </span>
                    <span class="block text-[9px] uppercase font-bold tracking-wider text-slate-400">
                        Admin Console
                    </span>
                </div>
            </a>

            <!-- Mobile Close Sidebar Button -->
            <button
                type="button"
                onclick="toggleMobileSidebar()"
                class="lg:hidden p-1.5 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition"
            >
                <i class="bx bx-x text-2xl"></i>
            </button>
        </div>

        <!-- Navigation Menu List (Categorized in structured modules matching CIS standard) -->
        <div class="flex-1 overflow-y-auto px-3 py-4 space-y-6 custom-scrollbar text-xs">
            
            <!-- Group 1: Core Operations -->
            <div class="space-y-1">
                <div class="px-3 pb-1 text-[10px] uppercase tracking-wider text-slate-400 dark:text-slate-500 font-bold">
                    Core Operations
                </div>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                    <i class="bx bxs-dashboard text-lg"></i>
                    <span>Workforce Dashboard</span>
                </a>
            </div>

            <!-- Group 2: Workforce Lifecycle & PIM (Super Admin, HR Admin, Department Manager) -->
            @if(auth()->user()->isSuperAdmin() || auth()->user()->isHrAdmin() || auth()->user()->isManager())
            <div class="space-y-1">
                <div class="px-3 pb-1 text-[10px] uppercase tracking-wider text-slate-400 dark:text-slate-500 font-bold">
                    Personnel Management
                </div>
                <a href="{{ route('employees.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl font-semibold transition {{ request()->routeIs('employees.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <i class="bx bx-user-pin text-lg {{ request()->routeIs('employees.*') ? 'text-white' : 'text-indigo-500' }}"></i>
                        <span>Employee Master PIM</span>
                    </div>
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-bold {{ request()->routeIs('employees.*') ? 'bg-indigo-700 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                        Roster
                    </span>
                </a>
                <a href="{{ route('attendance.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl font-semibold transition {{ request()->routeIs('attendance.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <i class="bx bx-time-five text-lg {{ request()->routeIs('attendance.*') ? 'text-white' : 'text-emerald-500' }}"></i>
                        <span>Attendance &amp; Shifts</span>
                    </div>
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                </a>
            </div>
            @else
            <div class="space-y-1">
                <div class="px-3 pb-1 text-[10px] uppercase tracking-wider text-slate-400 dark:text-slate-500 font-bold">
                    My Operations
                </div>
                <a href="{{ route('attendance.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl font-semibold transition {{ request()->routeIs('attendance.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <i class="bx bx-time-five text-lg {{ request()->routeIs('attendance.*') ? 'text-white' : 'text-emerald-500' }}"></i>
                        <span>My Attendance</span>
                    </div>
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                </a>
            </div>
            @endif

            <!-- Group 3: Leave, Appraisals & ATS -->
            <div class="space-y-1">
                <div class="px-3 pb-1 text-[10px] uppercase tracking-wider text-slate-400 dark:text-slate-500 font-bold">
                    Talent &amp; Operations
                </div>
                <a href="{{ route('leaves.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl font-semibold transition {{ request()->routeIs('leaves.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <i class="bx bx-calendar-check text-lg {{ request()->routeIs('leaves.*') ? 'text-white' : 'text-amber-500' }}"></i>
                        <span>Leave Requests</span>
                    </div>
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold {{ request()->routeIs('leaves.*') ? 'bg-indigo-700 text-white' : 'bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300' }}">
                        Active
                    </span>
                </a>
                <a href="{{ route('performance.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold transition {{ request()->routeIs('performance.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                    <i class="bx bx-target-lock text-lg {{ request()->routeIs('performance.*') ? 'text-white' : 'text-purple-500' }}"></i>
                    <span>OKRs &amp; Appraisals</span>
                </a>
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isHrAdmin() || auth()->user()->isManager())
                <a href="{{ route('recruitment.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl font-semibold transition {{ request()->routeIs('recruitment.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <i class="bx bx-briefcase text-lg {{ request()->routeIs('recruitment.*') ? 'text-white' : 'text-sky-500' }}"></i>
                        <span>Recruitment ATS</span>
                    </div>
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold {{ request()->routeIs('recruitment.*') ? 'bg-indigo-700 text-white' : 'bg-sky-100 dark:bg-sky-950/60 text-sky-800 dark:text-sky-300' }}">
                        Active
                    </span>
                </a>
                @endif
            </div>

            <!-- Group 4: Integrations & External Feeder (Super Admin & HR Admin) -->
            @if(auth()->user()->isSuperAdmin() || auth()->user()->isHrAdmin())
            <div class="space-y-1">
                <div class="px-3 pb-1 text-[10px] uppercase tracking-wider text-slate-400 dark:text-slate-500 font-bold">
                    Connected Engines
                </div>
                <a href="{{ route('payroll-sync.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl font-semibold transition {{ request()->routeIs('payroll-sync.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <i class="bx bx-wallet text-lg {{ request()->routeIs('payroll-sync.*') ? 'text-white' : 'text-indigo-500' }}"></i>
                        <span>PayFlow MY (Payroll)</span>
                    </div>
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold {{ request()->routeIs('payroll-sync.*') ? 'bg-indigo-700 text-white' : 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300' }}">
                        Sync
                    </span>
                </a>
                <a href="{{ route('audit-logs.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl font-semibold transition {{ request()->routeIs('audit-logs.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <i class="bx bx-history text-lg {{ request()->routeIs('audit-logs.*') ? 'text-white' : 'text-slate-400' }}"></i>
                        <span>Activity Audit Logs</span>
                    </div>
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold {{ request()->routeIs('audit-logs.*') ? 'bg-indigo-700 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                        Audit
                    </span>
                </a>
            </div>
            @endif

            <!-- Group 5: Organization & Settings (Super Admin & HR Admin) -->
            @if(auth()->user()->isSuperAdmin() || auth()->user()->isHrAdmin())
            <div class="space-y-1">
                <div class="px-3 pb-1 text-[10px] uppercase tracking-wider text-slate-400 dark:text-slate-500 font-bold">
                    Settings &amp; Branding
                </div>
                <a href="{{ route('settings.profile') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold transition {{ request()->routeIs('settings.profile*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                    <i class="bx bx-buildings text-lg {{ request()->routeIs('settings.profile*') ? 'text-white' : 'text-slate-400' }}"></i>
                    <span>Company Profile</span>
                </a>
                <a href="{{ route('settings.shifts') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold transition {{ request()->routeIs('settings.shifts*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                    <i class="bx bx-time-five text-lg {{ request()->routeIs('settings.shifts*') ? 'text-white' : 'text-slate-400' }}"></i>
                    <span>Work Shifts</span>
                </a>
                <a href="{{ route('settings.leave-types') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold transition {{ request()->routeIs('settings.leave-types*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                    <i class="bx bx-calendar-edit text-lg {{ request()->routeIs('settings.leave-types*') ? 'text-white' : 'text-slate-400' }}"></i>
                    <span>Leave Policies</span>
                </a>
                <a href="{{ route('settings.departments') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold transition {{ request()->routeIs('settings.departments*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                    <i class="bx bx-network-chart text-lg {{ request()->routeIs('settings.departments*') ? 'text-white' : 'text-slate-400' }}"></i>
                    <span>Departments</span>
                </a>
                <a href="{{ route('settings.templates') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold transition {{ request()->routeIs('settings.templates*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                    <i class="bx bx-file-blank text-lg {{ request()->routeIs('settings.templates*') ? 'text-white' : 'text-slate-400' }}"></i>
                    <span>Offer Letter Template</span>
                </a>
            </div>
            @endif

        </div>

        <!-- Sidebar Station Footer -->
        <div class="p-3 border-t border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-950/30">
            <div class="flex items-center justify-between px-2 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-[10px] font-bold text-slate-700 dark:text-slate-300">HQ Station Active</span>
                </div>
                <span class="text-[9px] font-mono text-slate-400">SRS v1.0</span>
            </div>
        </div>
    </aside>

    <!-- MAIN BODY VIEWPORT -->
    <div class="flex-1 flex flex-col lg:pl-0 min-w-0 overflow-hidden">
        
        <!-- Top Navbar (Mobile Optimized) -->
        <header class="h-16 sm:h-20 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 px-3 sm:px-8 flex items-center justify-between z-30 shrink-0 transition-colors gap-2">
            <div class="flex items-center gap-2 sm:gap-4 min-w-0">
                <button type="button" onclick="toggleMobileSidebar()" class="lg:hidden p-2 rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer shrink-0" aria-label="Toggle navigation drawer">
                    <i class="bx bx-menu text-2xl"></i>
                </button>
                <div class="min-w-0">
                    <h1 class="text-sm sm:text-lg font-black text-slate-900 dark:text-white tracking-tight truncate">@yield('page-title', 'Dashboard Overview')</h1>
                    <p class="text-[11px] text-slate-400 hidden sm:block">Enterprise HRMS &bull; Role: {{ auth()->user()->role ?? 'Super Admin' }}</p>
                </div>
            </div>

            <!-- Top Right Bar -->
            <div class="flex items-center gap-2 sm:gap-4 shrink-0">
                <!-- Theme Toggle Button Component -->
                <x-theme-toggle />

                <!-- Digital Clock In Pill (Desktop & Tablet) -->
                <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 text-xs font-bold border border-emerald-200 dark:border-emerald-800">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Clocked In (08:58 AM)</span>
                </div>

                <div class="h-5 w-px bg-slate-200 dark:border-slate-800 hidden sm:block"></div>

                <!-- User Dropdown Menu (Standard Clinic Invoice System & PayFlow MY Style) -->
                <div class="relative" id="user-menu-container">
                    <button
                        type="button"
                        id="user-menu-button"
                        onclick="document.getElementById('user-dropdown').classList.toggle('hidden')"
                        class="flex items-center gap-2.5 p-1.5 sm:px-2.5 sm:py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800/80 hover:bg-slate-200 dark:hover:bg-slate-700/80 border border-slate-200 dark:border-slate-700/80 transition focus:outline-none cursor-pointer"
                    >
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-xs shadow-xs shrink-0">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                        </div>
                        <div class="text-left hidden md:block">
                            <div class="text-xs font-bold text-slate-800 dark:text-white leading-tight truncate max-w-[130px]">
                                {{ auth()->user()->name ?? 'Alexander Vance' }}
                            </div>
                            <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono flex items-center gap-1">
                                <span>{{ auth()->user()->employee_code ?? 'EMP-ADMIN' }}</span>
                                <span>&bull;</span>
                                <span class="capitalize text-indigo-600 dark:text-indigo-400 font-semibold">{{ auth()->user()->role ?? 'Super Admin' }}</span>
                            </div>
                        </div>
                        <i class="bx bx-chevron-down text-slate-400 text-base"></i>
                    </button>

                    <!-- Dropdown Modal Popup -->
                    <div
                        id="user-dropdown"
                        class="hidden absolute right-0 mt-2 w-64 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl shadow-slate-400/20 dark:shadow-black/60 py-2 z-50 animate__animated animate__fadeIn animate__faster text-xs"
                    >
                        <!-- User Card Info Header -->
                        <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800">
                            <p class="font-bold text-slate-900 dark:text-white text-xs truncate">{{ auth()->user()->name ?? 'Alexander Vance' }}</p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 font-mono truncate">{{ auth()->user()->email ?? 'admin@hrms.test' }}</p>
                            <div class="mt-2 inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase bg-indigo-50 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-400/30">
                                <i class="bx bx-shield-quarter"></i>
                                <span>{{ auth()->user()->role ?? 'Super Admin' }} Station</span>
                            </div>
                        </div>

                        <!-- Dropdown Menu Links -->
                        <div class="py-1.5 px-2 space-y-0.5">
                            <a href="{{ route('employees.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-indigo-600 dark:hover:text-white transition">
                                <i class="bx bx-user-pin text-base text-slate-400"></i>
                                <span>Personnel Directory</span>
                            </a>
                            <a href="{{ route('attendance.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-indigo-600 dark:hover:text-white transition">
                                <i class="bx bx-time text-base text-slate-400"></i>
                                <span>My Attendance Logs</span>
                            </a>
                            <a href="https://github.com/mralif93/payroll-management-system" target="_blank" class="flex items-center justify-between px-3 py-2 rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-indigo-600 dark:hover:text-white transition">
                                <div class="flex items-center gap-2.5">
                                    <i class="bx bx-wallet text-base text-slate-400"></i>
                                    <span>PayFlow MY (Payroll)</span>
                                </div>
                                <i class="bx bx-link-external text-xs text-slate-400"></i>
                            </a>
                        </div>

                        <!-- Logout Form Action -->
                        <div class="pt-1.5 px-2 border-t border-slate-100 dark:border-slate-800">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 font-semibold transition cursor-pointer">
                                    <i class="bx bx-log-out text-base"></i>
                                    <span>Sign Out Console</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Body Scroll Area with Responsive Footer -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 flex flex-col justify-between">
            <div class="flex-1">
                @yield('content')
            </div>

            <!-- Admin Internal Footer -->
            <footer class="mt-8 pt-4 border-t border-slate-200 dark:border-slate-800/80 flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px] text-slate-400 text-center sm:text-left">
                <div class="flex items-center gap-1.5 justify-center sm:justify-start">
                    <span class="font-bold text-slate-600 dark:text-slate-300">PulseHR Console</span>
                    <span>&bull;</span>
                    <span class="text-emerald-600 dark:text-emerald-400 font-medium">v1.0-alpha SRS Ready</span>
                </div>
                <div>
                    <span>External Payroll sync target: </span>
                    <a href="https://github.com/mralif93/payroll-management-system" target="_blank" class="text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">PayFlow MY</a>
                </div>
            </footer>
        </main>
    </div>

    <!-- Global Modal Portal Insertion Slot -->
    @stack('modals')

    @stack('scripts')

    <script>
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
            }
        }

        // Close User Dropdown when clicking outside
        document.addEventListener('click', function (e) {
            const container = document.getElementById('user-menu-container');
            const dropdown = document.getElementById('user-dropdown');
            if (container && dropdown && !container.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
