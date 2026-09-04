@extends('layouts.admin')

@section('page-title', 'Work Shifts & Geofence GPS Parameters')

@section('content')
<div class="space-y-6">

    <!-- Page Header (matching clinic-invoice-system standard) -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-950 via-slate-900 to-indigo-900 p-6 sm:p-7 border border-indigo-800/40 shadow-xl shadow-indigo-950/40 text-white">
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center text-indigo-300 font-bold border border-white/10">
                        <i class="bx bx-map-pin text-lg"></i>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black tracking-tight">Work Shifts &amp; Geofence GPS Settings</h1>
                </div>
                <p class="text-xs text-slate-300">Configure headquarters coordinates, allowed punch radius, and operational shift rosters</p>
            </div>

            <div class="flex items-center gap-2">
                <x-button
                    type="button"
                    variant="primary"
                    size="md"
                    icon="bx bx-plus"
                    onclick="document.getElementById('modal-new-shift').classList.remove('hidden')"
                    class="shadow-lg shadow-indigo-600/30"
                >
                    Add Work Shift
                </x-button>
            </div>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('status'))
        <x-alert variant="success" icon="bx bx-check-circle" title="Success">
            {{ session('status') }}
        </x-alert>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left: Geofence GPS Configuration Card (5 Cols) -->
        <div class="lg:col-span-5 space-y-4">
            <x-card title="Headquarters GPS Geofence" subtitle="Physical perimeter perimeter for biometric clock-in verification">
                <form action="{{ route('settings.shifts.geofence') }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    @method('PUT')

                    <div class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/50 space-y-1">
                        <p class="font-bold text-emerald-800 dark:text-emerald-300 flex items-center gap-1.5">
                            <i class="bx bx-radar text-base"></i>
                            <span>Active Boundary Enforcement</span>
                        </p>
                        <p class="text-emerald-700 dark:text-emerald-400 text-[11px] leading-relaxed">
                            Punches outside this perimeter are rejected automatically with coordinate distance logs.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <x-input
                            label="Latitude"
                            name="office_latitude"
                            value="{{ old('office_latitude', $profile->office_latitude ?? 3.1390) }}"
                            icon="bx bx-navigation"
                            required
                        />

                        <x-input
                            label="Longitude"
                            name="office_longitude"
                            value="{{ old('office_longitude', $profile->office_longitude ?? 101.6869) }}"
                            icon="bx bx-compass"
                            required
                        />
                    </div>

                    <div>
                        <x-input
                            label="Allowed Radius (Meters)"
                            name="geofence_radius_meters"
                            type="number"
                            min="10"
                            max="5000"
                            value="{{ old('geofence_radius_meters', $profile->geofence_radius_meters ?? 100) }}"
                            icon="bx bx-circle"
                            hint="Standard default: 100 meters"
                            required
                        />
                    </div>

                    <div class="pt-2 flex justify-end">
                        <x-button type="submit" variant="primary" size="sm" icon="bx bx-save">
                            Save Geofence Coordinates
                        </x-button>
                    </div>
                </form>
            </x-card>
        </div>

        <!-- Right: Active Work Shifts Roster (7 Cols) -->
        <div class="lg:col-span-7 space-y-4">
            <x-card title="Configured Work Shifts" subtitle="Daily attendance schedules, grace limits, and half-day thresholds">
                <div class="space-y-3">
                    @foreach($shifts as $s)
                        <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 flex items-center justify-between gap-4 flex-wrap">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900 dark:text-white text-sm">{{ $s->name }}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                        {{ $s->code }}
                                    </span>
                                    @if($s->is_default)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                            Default Shift
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3 text-xs text-slate-500 font-mono">
                                    <span><i class="bx bx-time"></i> {{ substr($s->start_time, 0, 5) }} - {{ substr($s->end_time, 0, 5) }}</span>
                                    <span>&bull; Grace: {{ $s->late_grace_minutes }} mins</span>
                                    <span>&bull; Half Day: {{ $s->half_day_threshold_minutes / 60 }} hrs</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>
        </div>

    </div>

</div>

<!-- Modal: Add Work Shift -->
<x-modal name="new-shift" title="Create Operational Work Shift" size="lg">
    <form action="{{ route('settings.shifts.store') }}" method="POST" class="space-y-4 text-xs">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input label="Shift Name" name="name" placeholder="e.g. Night Shift" required />
            <x-input label="Shift Code" name="code" placeholder="SFT-NIGHT" required />

            <x-input label="Start Time (HH:MM)" name="start_time" type="time" value="09:00" required />
            <x-input label="End Time (HH:MM)" name="end_time" type="time" value="18:00" required />

            <x-input label="Late Grace (Mins)" name="late_grace_minutes" type="number" min="0" max="120" value="15" required />
            <x-input label="Half-Day Limit (Mins)" name="half_day_threshold_minutes" type="number" min="60" max="480" value="240" required />

            <div class="sm:col-span-2 flex items-center gap-2 pt-1">
                <input type="checkbox" id="is_default" name="is_default" value="1" class="rounded text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                <label for="is_default" class="text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">
                    Set as default company shift
                </label>
            </div>
        </div>

        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2">
            <x-button type="button" variant="ghost" size="sm" onclick="document.getElementById('modal-new-shift').classList.add('hidden')">
                Cancel
            </x-button>
            <x-button type="submit" variant="primary" size="sm" icon="bx bx-check">
                Create Shift
            </x-button>
        </div>
    </form>
</x-modal>

@endsection
