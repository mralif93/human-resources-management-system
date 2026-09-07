@extends('layouts.admin')

@section('title', '403 Forbidden - Access Restricted')
@section('page-title', 'Security & Access Governance')

@section('content')
<div class="min-h-[60vh] flex flex-col items-center justify-center text-center p-6 space-y-6">
    <div class="w-20 h-20 rounded-3xl bg-rose-500/10 text-rose-500 flex items-center justify-center text-4xl border border-rose-500/20 shadow-xl shadow-rose-500/10">
        <i class="bx bx-shield-x"></i>
    </div>

    <div class="space-y-2 max-w-md">
        <span class="px-3 py-1 rounded-full text-xs font-mono font-extrabold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-900/50">
            HTTP 403 &bull; Access Forbidden
        </span>
        <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Privilege Restriction Enforced</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
            {{ $exception->getMessage() ?: 'Your authenticated user role does not possess authorization to access or modify this administrative resource.' }}
        </p>
    </div>

    <div class="flex items-center gap-3">
        <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2">
            <i class="bx bx-home-alt"></i>
            <span>Return to Dashboard</span>
        </a>
    </div>
</div>
@endsection
