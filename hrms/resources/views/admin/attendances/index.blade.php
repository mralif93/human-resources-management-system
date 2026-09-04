@extends('layouts.admin')

@section('title', 'Attendance & Shifts - PulseHR')
@section('page-title', 'Attendance Tracking & Shifts Management')

@section('content')
<div class="space-y-6 animate__animated animate__fadeIn">

    <!-- Top Status / Geofence Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 border border-slate-800 rounded-3xl p-6 text-white shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-1.5">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    Geofence Perimeter Active
                </span>
                <span class="text-xs font-mono text-slate-400">HQ Radius: 100 meters</span>
            </div>
            <h2 class="text-lg font-black tracking-tight">Real-Time Attendance &amp; Shift Operations</h2>
            <p class="text-xs text-slate-300 max-w-xl">
                Precision GPS tracking with biometric integration, automatic late penalty detection, and daily work hour calculation for export to PayFlow MY.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <div class="bg-white/10 backdrop-blur-xs px-4 py-2.5 rounded-2xl border border-white/10 text-center">
                <span class="text-[10px] uppercase font-bold text-indigo-200 block">Default Shift</span>
                <span class="text-xs font-black font-mono">09:00 - 18:00 (15m Grace)</span>
            </div>
        </div>
    </div>

    <!-- Punch Action Widget & Filters -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Interactive Punch Terminal Card -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <i class="bx bx-fingerprint text-indigo-600 text-xl"></i>
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Simulated Punch Terminal</h3>
                </div>
                <span class="text-[10px] font-mono text-slate-400">GPS: 3.1390, 101.6869</span>
            </div>

            @if ($errors->any())
                <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/50 text-rose-700 dark:text-rose-300 text-xs">
                    {{ $errors->first() }}
                </div>
            @endif

            <p class="text-xs text-slate-500 dark:text-slate-400">
                Clock in or out on behalf of active staff using verified headquarters coordinates.
            </p>

            <form method="POST" action="{{ route('attendance.punch-in') }}" class="space-y-3 text-xs">
                @csrf
                <input type="hidden" name="latitude" value="3.1390">
                <input type="hidden" name="longitude" value="101.6869">

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Select Employee</label>
                    <select name="employee_id" required class="w-full p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white outline-none">
                        @foreach (\App\Models\Employee::all() as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_code }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-2">
                    <button type="submit" class="w-full py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-md shadow-indigo-600/30 transition-all cursor-pointer flex items-center justify-center gap-1.5">
                        <i class="bx bx-log-in-circle text-base"></i>
                        <span>Clock In</span>
                    </button>
                    <button type="submit" formaction="{{ route('attendance.punch-out') }}" class="w-full py-2.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-all cursor-pointer flex items-center justify-center gap-1.5">
                        <i class="bx bx-log-out-circle text-base"></i>
                        <span>Clock Out</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Attendance Logs Table Filter -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
            <form method="GET" action="{{ route('attendance.index') }}" class="flex flex-col sm:flex-row items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">Daily Punch Activity</h3>
                    <p class="text-xs text-slate-400">Showing logs for: <span class="font-mono text-indigo-600 dark:text-indigo-400 font-bold">{{ $selectedDate }}</span></p>
                </div>
                <div class="flex items-center gap-2">
                    <input type="date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()" class="px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white outline-none">
                    <select name="status" onchange="this.form.submit()" class="px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white outline-none">
                        <option value="">All Statuses</option>
                        <option value="on_time" {{ request('status') == 'on_time' ? 'selected' : '' }}>On Time</option>
                        <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                    </select>
                </div>
            </form>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-[10px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 dark:border-slate-800">
                            <th class="pb-2">Employee</th>
                            <th class="pb-2 hidden sm:table-cell">Clock In</th>
                            <th class="pb-2 hidden md:table-cell">Clock Out</th>
                            <th class="pb-2">Work Hours</th>
                            <th class="pb-2 text-right sm:text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 text-slate-600 dark:text-slate-300">
                        @forelse ($attendances as $att)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30">
                                <td class="py-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300 font-bold flex items-center justify-center text-[10px] shrink-0">
                                            {{ substr($att->employee->first_name, 0, 1) }}{{ substr($att->employee->last_name, 0, 1) }}
                                        </div>
                                        <div class="min-w-0">
                                            <span class="font-bold text-slate-900 dark:text-white block truncate max-w-[140px] sm:max-w-none">{{ $att->employee->full_name }}</span>
                                            <span class="text-[10px] text-slate-400 font-mono">{{ $att->employee->employee_code }}</span>
                                            <span class="text-[10px] text-slate-500 font-mono block sm:hidden">
                                                In: {{ $att->clock_in ? $att->clock_in->format('H:i') : '—' }} | Out: {{ $att->clock_out ? $att->clock_out->format('H:i') : 'Active' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 font-mono hidden sm:table-cell">
                                    {{ $att->clock_in ? $att->clock_in->format('H:i:s') : '—' }}
                                </td>
                                <td class="py-3 font-mono hidden md:table-cell">
                                    {{ $att->clock_out ? $att->clock_out->format('H:i:s') : 'Active' }}
                                </td>
                                <td class="py-3 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                    {{ $att->total_work_hours }} hrs
                                </td>
                                <td class="py-3 text-right sm:text-left">
                                    @if($att->status === 'on_time')
                                        <x-badge variant="emerald" size="sm" :dot="true">
                                            On Time
                                        </x-badge>
                                    @elseif($att->status === 'late')
                                        <x-badge variant="rose" size="sm" :dot="true">
                                            Late ({{ $att->late_minutes }}m)
                                        </x-badge>
                                    @else
                                        <x-badge variant="slate" size="sm">
                                            {{ $att->status }}
                                        </x-badge>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-400">
                                    No attendance records found for this date.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$attendances" class="mt-4" />
        </div>
    </div>
</div>
@endsection
