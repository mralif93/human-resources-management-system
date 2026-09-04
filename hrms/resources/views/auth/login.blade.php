@extends('layouts.public')

@section('title', 'Sign In - PulseHR Management System')

@section('content')
<div class="min-h-[calc(100vh-14rem)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative">
    <div class="w-full max-w-md animate__animated animate__fadeInUp animate__faster">
        
        <!-- Card Container -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl shadow-indigo-500/10 p-8 sm:p-10 transition-colors">
            
            <!-- Header & Brand -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-600 via-indigo-700 to-blue-800 text-white shadow-lg shadow-indigo-600/30 border border-indigo-400/30 mb-4">
                    <i class="bx bxs-user-badge text-3xl"></i>
                </div>
                <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Staff Portal Sign In</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 font-medium">Access your workforce records and management console</p>
            </div>

            <!-- Login Form -->
            <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
                @csrf

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                        Work Email Address
                    </label>
                    <div class="relative rounded-xl shadow-xs">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-lg">
                            <i class="bx bx-envelope"></i>
                        </div>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            required
                            value="{{ old('email', 'admin@hrms.test') }}"
                            autocomplete="email"
                            placeholder="user@hrms.test"
                            class="block w-full pl-10 pr-4 py-3 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/80 focus:bg-white dark:focus:bg-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-900 dark:text-white placeholder:text-slate-400 transition-all outline-none"
                        >
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
                            <i class="bx bx-error-circle"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Password Input -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                            Password
                        </label>
                        <a href="{{ route('password.request') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline transition-colors">
                            Forgot password?
                        </a>
                    </div>
                    <div class="relative rounded-xl shadow-xs">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-lg">
                            <i class="bx bx-lock-alt"></i>
                        </div>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            required
                            value="password"
                            autocomplete="current-password"
                            placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"
                            class="block w-full pl-10 pr-4 py-3 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/80 focus:bg-white dark:focus:bg-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-slate-900 dark:text-white placeholder:text-slate-400 transition-all outline-none"
                        >
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
                            <i class="bx bx-error-circle"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" id="remember" class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-xs font-medium text-slate-600 dark:text-slate-400">Remember credentials</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 py-3.5 px-4 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-600/30 border border-indigo-500/30 active:scale-[0.99] transition-all cursor-pointer"
                >
                    <i class="bx bx-log-in text-lg"></i>
                    <span>Authenticate &amp; Enter</span>
                </button>
            </form>

            <!-- Quick Demo Credentials Switcher (Standard practice across repos) -->
            <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-3 text-center">
                    Quick Role Profiles (Click to prefill)
                </p>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <button type="button" onclick="fillCreds('admin@hrms.test')" class="p-2.5 text-left rounded-xl bg-slate-50 dark:bg-slate-800/60 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 transition-all cursor-pointer">
                        <span class="font-bold block text-slate-900 dark:text-white flex items-center gap-1">
                            <i class="bx bxs-badge-check text-indigo-600"></i> Admin
                        </span>
                        <span class="text-[10px] text-slate-400 block font-mono">admin@hrms.test</span>
                    </button>
                    <button type="button" onclick="fillCreds('hr@hrms.test')" class="p-2.5 text-left rounded-xl bg-slate-50 dark:bg-slate-800/60 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 transition-all cursor-pointer">
                        <span class="font-bold block text-slate-900 dark:text-white flex items-center gap-1">
                            <i class="bx bx-user-pin text-purple-600"></i> HR Admin
                        </span>
                        <span class="text-[10px] text-slate-400 block font-mono">hr@hrms.test</span>
                    </button>
                    <button type="button" onclick="fillCreds('manager@hrms.test')" class="p-2.5 text-left rounded-xl bg-slate-50 dark:bg-slate-800/60 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 transition-all cursor-pointer">
                        <span class="font-bold block text-slate-900 dark:text-white flex items-center gap-1">
                            <i class="bx bx-briefcase text-blue-600"></i> Manager
                        </span>
                        <span class="text-[10px] text-slate-400 block font-mono">manager@hrms.test</span>
                    </button>
                    <button type="button" onclick="fillCreds('employee@hrms.test')" class="p-2.5 text-left rounded-xl bg-slate-50 dark:bg-slate-800/60 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 transition-all cursor-pointer">
                        <span class="font-bold block text-slate-900 dark:text-white flex items-center gap-1">
                            <i class="bx bx-user text-amber-600"></i> Employee
                        </span>
                        <span class="text-[10px] text-slate-400 block font-mono">employee@hrms.test</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function fillCreds(email) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = 'password';
}
</script>
@endpush
