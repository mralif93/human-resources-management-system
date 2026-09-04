@extends('layouts.admin')

@section('title', 'Work Shifts & Rosters - PulseHR Management System')
@section('page-title', 'Work Shifts & Rosters')

@section('content')
<div class="space-y-6 animate__animated animate__fadeIn">

    <!-- Page Header (matching standard) -->
    <x-page-header
        title="Work Shift Rosters"
        subtitle="Manage daily operating schedules, working hours, late grace tolerances, and half-day thresholds"
        icon="bx-time-five"
    >
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
    </x-page-header>

    <!-- Feedback Alerts -->
    @if(session('status'))
        <x-alert variant="success" icon="bx bx-check-circle" title="Success">
            {{ session('status') }}
        </x-alert>
    @endif

    @if($errors->any())
        <x-alert variant="danger" icon="bx bx-error-circle" title="Notice">
            {{ $errors->first() }}
        </x-alert>
    @endif

    <!-- KPI Metric Cards Grid (Standard 4 Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card
            title="Total Configured Shifts"
            value="{{ $totalShifts }}"
            icon="bx bx-time"
            color="indigo"
            subtitle="Active roster configurations"
        />

        <x-stat-card
            title="Primary Default Shift"
            value="{{ $defaultShift ? $defaultShift->code : 'None' }}"
            icon="bx bx-star"
            color="amber"
            subtitle="{{ $defaultShift ? $defaultShift->name : 'Unassigned' }}"
        />

        <x-stat-card
            title="Standard Core Hours"
            value="{{ $defaultShift ? substr($defaultShift->start_time, 0, 5) . ' - ' . substr($defaultShift->end_time, 0, 5) : '--:--' }}"
            icon="bx bx-calendar-event"
            color="emerald"
            subtitle="Default daily window"
        />

        <x-stat-card
            title="Total Punch Records"
            value="{{ number_format($totalPunches) }}"
            icon="bx bx-fingerprint"
            color="sky"
            subtitle="Verified attendance logs"
        />
    </div>

    <!-- Full Width Configured Work Shifts Table -->
    <x-card title="Configured Work Shifts" subtitle="Daily attendance schedules, grace limits, and half-day thresholds">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider bg-slate-50/50 dark:bg-slate-800/30">
                        <th class="py-3.5 px-4 sm:px-6">Shift Details</th>
                        <th class="py-3.5 px-4">Working Hours</th>
                        <th class="py-3.5 px-4 hidden sm:table-cell">Late Grace Window</th>
                        <th class="py-3.5 px-4 hidden md:table-cell">Half-Day Threshold</th>
                        <th class="py-3.5 px-4">Designation</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-600 dark:text-slate-300 font-medium">
                    @forelse($shifts as $s)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="py-3.5 px-4 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800 flex items-center justify-center font-bold text-indigo-700 dark:text-indigo-300 text-sm shrink-0">
                                        <i class="bx bx-time-five"></i>
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-900 dark:text-white block text-sm leading-snug">{{ $s->name }}</span>
                                        <span class="font-mono text-[11px] text-slate-400 font-semibold">{{ $s->code }}</span>
                                    </div>
                                </div>
                            </td>

                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="font-bold text-slate-900 dark:text-slate-200 text-xs font-mono px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                    {{ substr($s->start_time, 0, 5) }} &mdash; {{ substr($s->end_time, 0, 5) }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4 hidden sm:table-cell whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <i class="bx bx-stopwatch text-slate-400 text-sm"></i>
                                    <span class="font-bold text-slate-800 dark:text-slate-200 font-mono text-xs">{{ $s->late_grace_minutes }} mins</span>
                                </div>
                                <span class="text-[10px] text-slate-400">Allowed before penalty</span>
                            </td>

                            <td class="py-3.5 px-4 hidden md:table-cell whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <i class="bx bx-hourglass text-slate-400 text-sm"></i>
                                    <span class="font-bold text-slate-800 dark:text-slate-200 font-mono text-xs">{{ $s->half_day_threshold_minutes / 60 }} hours</span>
                                </div>
                                <span class="text-[10px] text-slate-400">Minimum threshold</span>
                            </td>

                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($s->is_default)
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 flex items-center gap-1.5 w-fit">
                                        <i class="bx bxs-star text-amber-500"></i> Primary Default
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 w-fit block">
                                        Secondary Roster
                                    </span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Set Default Button if not already -->
                                    @if(!$s->is_default)
                                        <form action="{{ route('settings.shifts.set-default', $s) }}" method="POST" class="inline">
                                            @csrf
                                            <x-circle-action-button
                                                icon="bx bx-star"
                                                variant="amber"
                                                size="md"
                                                type="submit"
                                                title="Set as Primary Default Shift"
                                            />
                                        </form>
                                    @else
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-500 border border-amber-200/80 dark:border-amber-800/80 text-base" title="Active Primary Default Shift">
                                            <i class="bx bxs-star"></i>
                                        </span>
                                    @endif

                                    <!-- Edit Shift Button -->
                                    <x-circle-action-button
                                        icon="bx bx-edit-alt"
                                        variant="indigo"
                                        size="md"
                                        type="button"
                                        title="Edit Shift Configuration"
                                        onclick="openEditShiftModal({{ json_encode($s) }})"
                                    />

                                    <!-- Delete Shift Button -->
                                    @if(!$s->is_default)
                                        <x-circle-action-button
                                            icon="bx bx-trash"
                                            variant="rose"
                                            size="md"
                                            type="button"
                                            title="Remove Shift Roster"
                                            onclick="openConfirmDialog({
                                                name: 'delete-shift',
                                                action: '{{ route('settings.shifts.destroy', $s) }}',
                                                method: 'DELETE',
                                                title: 'Remove {{ addslashes($s->name) }}?',
                                                message: 'Are you sure you want to remove this shift roster ({{ $s->code }})? Any past historical attendances tied to this shift will safely have their shift reference detached.',
                                                confirmText: 'Yes, Remove Shift'
                                            })"
                                        />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400 text-xs">
                                <div class="max-w-xs mx-auto space-y-2">
                                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-500 flex items-center justify-center text-2xl mx-auto">
                                        <i class="bx bx-time"></i>
                                    </div>
                                    <p class="font-bold text-slate-800 dark:text-slate-200">No work shifts configured</p>
                                    <p class="text-[11px]">Click <strong>Add Work Shift</strong> above to establish your primary attendance schedule.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

</div>

<!-- Modal: Add Work Shift (Standard 2-Column Responsive Layout) -->
<x-modal name="new-shift" title="Create Operational Work Shift" subtitle="Establish daily working hours, grace window, and company defaults" icon="bx-plus-circle" size="lg">
    <form action="{{ route('settings.shifts.store') }}" method="POST" class="space-y-4 text-xs">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input label="Shift Name" name="name" placeholder="e.g. Standard Office Shift" required />
            <x-input label="Shift Code" name="code" placeholder="STD-01" required />

            <x-input label="Start Time (HH:MM)" name="start_time" type="time" value="09:00" required />
            <x-input label="End Time (HH:MM)" name="end_time" type="time" value="18:00" required />

            <x-input label="Late Grace Limit (Minutes)" name="late_grace_minutes" type="number" min="0" max="120" value="15" hint="Clock-ins within grace are marked on-time" required />
            <x-input label="Half-Day Threshold (Minutes)" name="half_day_threshold_minutes" type="number" min="60" max="480" value="240" hint="e.g. 240 mins = 4 hours work minimum" required />

            <div class="sm:col-span-2 flex items-center gap-2 pt-1">
                <input type="checkbox" id="is_default" name="is_default" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                <label for="is_default" class="text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">
                    Set as default company shift for new employees
                </label>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5">
            <x-button type="button" variant="ghost" size="md" class="w-full sm:w-auto font-bold justify-center" onclick="document.getElementById('modal-new-shift').classList.add('hidden')">
                Cancel
            </x-button>
            <x-button type="submit" variant="primary" size="md" icon="bx bx-check" class="w-full sm:w-auto font-bold justify-center shadow-md shadow-indigo-600/20">
                Create Shift
            </x-button>
        </div>
    </form>
</x-modal>

<!-- Modal: Edit Work Shift (Standard 2-Column Responsive Layout) -->
<x-modal name="edit-shift" title="Edit Work Shift Schedule" subtitle="Update timing, grace tolerance, or set as default" icon="bx-edit" size="lg">
    <form id="edit-shift-form" action="" method="POST" class="space-y-4 text-xs">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input label="Shift Name" name="name" id="edit-shift-name" required />
            <x-input label="Shift Code" name="code" id="edit-shift-code" required />

            <x-input label="Start Time (HH:MM)" name="start_time" id="edit-shift-start" type="time" required />
            <x-input label="End Time (HH:MM)" name="end_time" id="edit-shift-end" type="time" required />

            <x-input label="Late Grace Limit (Minutes)" name="late_grace_minutes" id="edit-shift-grace" type="number" min="0" max="120" required />
            <x-input label="Half-Day Threshold (Minutes)" name="half_day_threshold_minutes" id="edit-shift-halfday" type="number" min="60" max="480" required />

            <div class="sm:col-span-2 flex items-center gap-2 pt-1">
                <input type="checkbox" id="edit-shift-default" name="is_default" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                <label for="edit-shift-default" class="text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">
                    Set as default company shift for new employees
                </label>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5">
            <x-button type="button" variant="ghost" size="md" class="w-full sm:w-auto font-bold justify-center" onclick="document.getElementById('modal-edit-shift').classList.add('hidden')">
                Cancel
            </x-button>
            <x-button type="submit" variant="primary" size="md" icon="bx bx-save" class="w-full sm:w-auto font-bold justify-center shadow-md shadow-indigo-600/20">
                Save Changes
            </x-button>
        </div>
    </form>
</x-modal>

<!-- Reusable Delete Confirmation Dialog -->
<x-confirm-dialog
    name="delete-shift"
    title="Remove Shift Roster?"
    message="Are you sure you want to remove this work shift configuration? This action cannot be undone."
    confirmText="Yes, Remove Shift"
    variant="danger"
/>

@push('scripts')
<script>
    function openEditShiftModal(shift) {
        const form = document.getElementById('edit-shift-form');
        form.action = `/settings/shifts/${shift.id}`;

        document.getElementById('edit-shift-name').value = shift.name;
        document.getElementById('edit-shift-code').value = shift.code;
        document.getElementById('edit-shift-start').value = shift.start_time.substring(0, 5);
        document.getElementById('edit-shift-end').value = shift.end_time.substring(0, 5);
        document.getElementById('edit-shift-grace').value = shift.late_grace_minutes;
        document.getElementById('edit-shift-halfday').value = shift.half_day_threshold_minutes;
        document.getElementById('edit-shift-default').checked = Boolean(shift.is_default);

        document.getElementById('modal-edit-shift').classList.remove('hidden');
    }
</script>
@endpush

@endsection
