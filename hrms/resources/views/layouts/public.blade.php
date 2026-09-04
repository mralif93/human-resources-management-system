<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'PulseHR - Enterprise Human Resource Management Suite')</title>

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

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 antialiased selection:bg-indigo-600 selection:text-white min-h-screen flex flex-col justify-between relative overflow-x-hidden transition-colors duration-300">

    <!-- Background Decorative Glow -->
    <div class="absolute inset-0 glow-radial pointer-events-none -z-10"></div>
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[400px] bg-gradient-to-tr from-indigo-500/15 via-purple-500/10 to-indigo-700/15 blur-[130px] rounded-full pointer-events-none -z-10"></div>

    <!-- Navigation Header -->
    <header class="w-full border-b border-slate-200 dark:border-slate-800/80 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md sticky top-0 z-50 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between gap-2">
            <!-- Brand Logo -->
            <a href="{{ route('welcome') }}" class="flex items-center gap-2.5 sm:gap-3.5 group min-w-0">
                <div class="h-9 w-9 sm:h-11 sm:w-11 rounded-xl bg-gradient-to-br from-indigo-600 via-indigo-700 to-blue-800 flex items-center justify-center text-white shadow-lg shadow-indigo-600/30 border border-indigo-400/30 group-hover:scale-105 transition-transform shrink-0">
                    <i class="bx bxs-user-badge text-xl sm:text-2xl font-bold"></i>
                </div>
                <div class="min-w-0">
                    <span class="text-lg sm:text-xl font-extrabold tracking-tight bg-gradient-to-r from-slate-900 dark:from-white via-indigo-950 dark:via-indigo-100 to-indigo-600 dark:to-indigo-300 bg-clip-text text-transparent truncate block">
                        Pulse<span class="text-indigo-600 dark:text-indigo-400">HR</span>
                    </span>
                    <span class="hidden sm:inline-block px-2 py-0.5 text-[9px] font-bold tracking-wider uppercase bg-indigo-50 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 rounded-full border border-indigo-200 dark:border-indigo-400/30">
                        Human Resource Suite
                    </span>
                </div>
            </a>

            <!-- Navigation Links -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600 dark:text-slate-300">
                <a href="{{ route('welcome') }}#overview" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Overview</a>
                <a href="{{ route('welcome') }}#modules" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Modules</a>
                <a href="{{ route('welcome') }}#payroll-integration" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors flex items-center gap-1.5">
                    <span>Payroll Feeder</span>
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-400/30">Sync</span>
                </a>
                <a href="{{ route('welcome') }}#rbac" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Security &amp; RBAC</a>
            </nav>

            <!-- Header Quick Actions -->
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <!-- Theme Toggle Button -->
                <x-theme-toggle />

                @auth
                    <a href="{{ route('dashboard') }}" class="hidden sm:inline-flex items-center gap-2 px-4 sm:px-5 py-2 sm:py-2.5 rounded-xl font-bold text-xs sm:text-sm bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/30 border border-indigo-400/30 transition-all">
                        <i class="bx bxs-dashboard text-base sm:text-lg"></i>
                        <span>Admin Portal</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center gap-2 px-4 sm:px-5 py-2 sm:py-2.5 rounded-xl font-bold text-xs sm:text-sm bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/30 border border-indigo-400/30 transition-all">
                        <i class="bx bx-shield-quarter text-base sm:text-lg"></i>
                        <span>Staff Login</span>
                    </a>
                @endauth

                <!-- Mobile Menu Button -->
                <button
                    type="button"
                    onclick="document.getElementById('public-mobile-menu').classList.toggle('hidden')"
                    class="md:hidden p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                    aria-label="Toggle navigation menu"
                >
                    <i class="bx bx-menu text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Navigation Drawer Dropdown -->
        <div id="public-mobile-menu" class="hidden md:hidden border-t border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 py-4 space-y-2 text-xs font-bold shadow-lg animate__animated animate__fadeIn">
            <a href="{{ route('welcome') }}#overview" onclick="document.getElementById('public-mobile-menu').classList.add('hidden')" class="block px-3 py-2.5 rounded-xl text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">Overview</a>
            <a href="{{ route('welcome') }}#modules" onclick="document.getElementById('public-mobile-menu').classList.add('hidden')" class="block px-3 py-2.5 rounded-xl text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">HR Modules</a>
            <a href="{{ route('welcome') }}#payroll-integration" onclick="document.getElementById('public-mobile-menu').classList.add('hidden')" class="block px-3 py-2.5 rounded-xl text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">Payroll Sync</a>
            <a href="{{ route('welcome') }}#rbac" onclick="document.getElementById('public-mobile-menu').classList.add('hidden')" class="block px-3 py-2.5 rounded-xl text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">Security &amp; RBAC</a>
            
            <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                @auth
                    <a href="{{ route('dashboard') }}" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-bold bg-indigo-600 text-white shadow-md">
                        <i class="bx bxs-dashboard text-base"></i>
                        <span>Enter Admin Portal</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-bold bg-indigo-600 text-white shadow-md">
                        <i class="bx bx-shield-quarter text-base"></i>
                        <span>Staff Login</span>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content Slot -->
    <main class="flex-grow">
        @if (session('status'))
            <div class="max-w-md mx-auto mt-6 px-4">
                <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm animate__animated animate__fadeInDown">
                    <i class="bx bx-check-circle text-xl text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                    <span>{{ session('status') }}</span>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="max-w-md mx-auto mt-6 px-4">
                <div class="flex items-center gap-3 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-sm animate__animated animate__fadeInDown">
                    <i class="bx bx-error-circle text-xl text-rose-600 dark:text-rose-400 shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer (Mobile Optimized) -->
    <footer class="border-t border-slate-200 dark:border-slate-800/80 bg-white/80 dark:bg-slate-900/90 py-6 sm:py-8 text-xs text-slate-500 dark:text-slate-400 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-4 text-center md:text-left">
            <div class="flex flex-wrap items-center justify-center md:justify-start gap-2">
                <div class="w-6 h-6 rounded-md bg-indigo-600 flex items-center justify-center text-white text-sm shrink-0">
                    <i class="bx bxs-user-badge"></i>
                </div>
                <span class="font-bold text-slate-900 dark:text-white">PulseHR Core System</span>
                <span class="hidden sm:inline text-slate-300 dark:text-slate-700">&bull;</span>
                <span class="text-[11px] sm:text-xs">Workforce Operations &amp; Governance</span>
            </div>
            <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-4 text-xs">
                <a href="https://github.com/mralif93/payroll-management-system" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1 font-semibold">
                    <span>PayFlow MY (Payroll Repo)</span>
                    <i class="bx bx-link-external text-xs"></i>
                </a>
                <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                <span class="text-slate-400 dark:text-slate-500 font-mono text-[11px] sm:text-xs">&copy; {{ date('Y') }} PulseHR. All rights reserved.</span>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
