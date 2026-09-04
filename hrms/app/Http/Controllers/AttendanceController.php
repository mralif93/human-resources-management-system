<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AttendanceController extends Controller
{
    /**
     * Display the Attendance log view with shift roster.
     */
    public function index(Request $request)
    {
        $query = Attendance::with(['employee.department', 'employee.designation', 'shift']);

        if ($request->filled('date')) {
            $query->whereDate('date', $request->input('date'));
        } else {
            $query->whereDate('date', Carbon::today());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = in_array((int)$request->input('per_page'), [10, 15, 25, 50, 100]) ? (int)$request->input('per_page') : 15;
        $attendances = $query->latest('clock_in')->paginate($perPage)->withQueryString();
        $shifts = Shift::all();
        $selectedDate = $request->input('date', Carbon::today()->toDateString());

        return view('admin.attendances.index', compact('attendances', 'shifts', 'selectedDate'));
    }

    /**
     * Digital Punch In (TC-ATT-01, TC-ATT-02, TC-ATT-04).
     */
    public function punchIn(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ]);

        // Atomic lock to prevent duplicate rapid punches (TC-ATT-04)
        $lockKey = "punch_lock_{$validated['employee_id']}_" . Carbon::today()->toDateString();
        $lock = Cache::lock($lockKey, 5);

        if (!$lock->get()) {
            return back()->withErrors(['punch' => 'Action throttled. A check-in request is already being processed.']);
        }

        try {
            // Geofence Validation: 100-meter radius around HQ (TC-ATT-01, TC-ATT-02)
            $isWithinGeofence = Attendance::isWithinGeofence($validated['latitude'], $validated['longitude']);

            if (!$isWithinGeofence) {
                return back()->withErrors(['geofence' => 'Outside designated check-in perimeter. Must be within 100m of office coordinates.']);
            }

            $today = Carbon::today()->toDateString();
            $now = Carbon::now();

            // Fetch or assign default shift
            $shift = Shift::where('is_default', true)->first() ?? Shift::first();

            // Check if already checked in today
            $existing = Attendance::where('employee_id', $validated['employee_id'])
                ->where('date', $today)
                ->first();

            if ($existing) {
                return back()->withErrors(['punch' => 'Employee has already clocked in for today.']);
            }

            // Evaluate shift late threshold (TC-ATT-03)
            $shiftStart = Carbon::parse($today . ' ' . ($shift ? $shift->start_time : '09:00:00'));
            $graceEnd = $shiftStart->copy()->addMinutes($shift ? $shift->late_grace_minutes : 15);
            $isLate = $now->greaterThan($graceEnd);
            $lateMinutes = $isLate ? max(0, (int) $now->diffInMinutes($shiftStart)) : 0;

            $attendance = Attendance::create([
                'employee_id' => $validated['employee_id'],
                'shift_id' => $shift?->id,
                'date' => $today,
                'clock_in' => $now,
                'status' => $isLate ? 'late' : 'on_time',
                'is_late' => $isLate,
                'late_minutes' => $lateMinutes,
                'clock_in_latitude' => $validated['latitude'],
                'clock_in_longitude' => $validated['longitude'],
                'clock_in_ip' => $request->ip(),
                'is_within_geofence' => true,
                'remarks' => $isLate ? "Clocked in {$lateMinutes} minutes late" : "On-time arrival",
            ]);

            return back()->with('status', "Clock-in successful at {$now->format('H:i:s')}. Status: " . ($isLate ? 'Late' : 'On Time'));
        } finally {
            $lock->release();
        }
    }

    /**
     * Digital Punch Out.
     */
    public function punchOut(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ]);

        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        $attendance = Attendance::where('employee_id', $validated['employee_id'])
            ->whereDate('date', $today)
            ->first();

        if (!$attendance || !$attendance->clock_in) {
            return back()->withErrors(['punch' => 'No active clock-in found for today.']);
        }

        if ($attendance->clock_out) {
            return back()->withErrors(['punch' => 'Employee has already clocked out for today.']);
        }

        $clockIn = Carbon::parse($attendance->clock_in);
        $totalHours = round($clockIn->diffInMinutes($now) / 60, 2);

        $attendance->update([
            'clock_out' => $now,
            'clock_out_latitude' => $validated['latitude'],
            'clock_out_longitude' => $validated['longitude'],
            'clock_out_ip' => $request->ip(),
            'total_work_hours' => $totalHours,
            'overtime_hours' => max(0, $totalHours - 8.0),
        ]);

        return back()->with('status', "Clock-out successful at {$now->format('H:i:s')}. Total Hours: {$totalHours} hrs.");
    }
}
