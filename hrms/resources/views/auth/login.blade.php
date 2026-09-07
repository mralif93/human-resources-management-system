@extends('layouts.public')

@section('title', 'Staff Portal Sign In - PulseHR')

@section('content')
<div class="min-h-[calc(100vh-14rem)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative">
    <div class="w-full max-w-[440px] animate__animated animate__fadeInUp animate__faster">
        
        <!-- Card Container -->
        <div class="bg-white dark:bg-slate-900 rounded-[2.25rem] border border-slate-200/80 dark:border-slate-800 shadow-[0_25px_60px_-15px_rgba(99,102,241,0.18)] p-8 sm:p-10 transition-all text-center">
            
            <!-- Header Icon & Brand -->
            <div class="flex flex-col items-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-b from-indigo-500 via-indigo-600 to-blue-600 text-white shadow-[0_12px_24px_-4px_rgba(99,102,241,0.4)] mb-4">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M21 7.28V5c0-1.1-.9-2-2-2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-2.28c.59-.35 1-.98 1-1.72V9c0-.74-.41-1.37-1-1.72zM20 9v6h-7V9h7zM5 19V5h14v2h-6c-1.1 0-2 .9-2 2v6c0 1.1.9 2 2 2h6v2H5z"/>
                        <circle cx="16" cy="12" r="1.5"/>
                    </svg>
                </div>
                <h1 class="text-2xl sm:text-[1.65rem] font-extrabold text-slate-900 dark:text-white tracking-tight">Staff Portal Sign In</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 font-medium max-w-[280px] leading-relaxed">
                    Access your workforce records, leave requests, and administrative console
                </p>
            </div>

            <!-- Dismissible Logout Alert Banner (Pixel-matched to design) -->
            @if(session('status') || request()->has('logged_out'))
                <div id="logout-alert" class="mb-6 px-4 py-3.5 rounded-2xl bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-300 dark:border-emerald-800/80 text-emerald-800 dark:text-emerald-300 text-xs flex items-center justify-between gap-3 animate__animated animate__fadeIn">
                    <div class="flex items-center gap-2.5 text-left">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-medium">You have been logged out securely.</span>
                    </div>
                    <button type="button" onclick="document.getElementById('logout-alert').remove()" class="text-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-200 transition p-0.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endif

            @if($errors->has('oauth'))
                <div class="mb-6 p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900/50 text-rose-700 dark:text-rose-300 text-xs flex items-center gap-2 text-left">
                    <i class="bx bx-error-circle text-lg shrink-0"></i>
                    <span>{{ $errors->first('oauth') }}</span>
                </div>
            @endif

            <!-- Info Container Card (Enterprise Identity Protection) -->
            <div class="p-6 rounded-3xl bg-slate-50/80 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800/80 mb-6 text-center space-y-2">
                <div class="inline-flex items-center justify-center w-10 h-10 rounded-2xl bg-indigo-100/70 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-400 mb-1">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2L4 5v6.09c0 5.05 3.41 9.76 8 10.91 4.59-1.15 8-5.86 8-10.91V5l-8-3zm0 2.18l6 2.25v4.66c0 4.1-2.6 7.9-6 8.91-3.4-1.01-6-4.81-6-8.91V6.43l6-2.25z"/>
                        <path d="M12 7c-1.1 0-2 .9-2 2v2H9v5h6v-5h-1V9c0-1.1-.9-2-2-2zm-1 4V9c0-.55.45-1 1-1s1 .45 1 1v2h-2z"/>
                    </svg>
                </div>
                <h2 class="text-xs font-bold text-slate-900 dark:text-white tracking-tight">
                    Enterprise Identity Protection Enforced
                </h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed max-w-[280px] mx-auto font-normal">
                    Authentication for PulseHR is centrally managed by CentraFlow Identity Hub. Click below to sign in with your corporate credentials.
                </p>
            </div>

            <!-- Sign in with CentraFlow SSO Button -->
            <a href="{{ route('sso.login') }}" 
               class="w-full inline-flex items-center justify-center gap-3 py-3.5 px-5 rounded-2xl text-sm font-bold text-white bg-gradient-to-r from-blue-600 via-indigo-600 to-indigo-700 hover:from-blue-500 hover:to-indigo-600 shadow-[0_12px_28px_-6px_rgba(79,70,229,0.5)] active:scale-[0.99] transition-all cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
                <span>Sign in with CentraFlow SSO</span>
            </a>

            <!-- Status Footer -->
            <div class="pt-6 text-center">
                <span class="text-[11px] text-slate-400 dark:text-slate-500 flex items-center justify-center gap-2 font-mono">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    CentraFlow OAuth 2.0 Server Active (:8004)
                </span>
            </div>

        </div>
    </div>
</div>
@endsection
