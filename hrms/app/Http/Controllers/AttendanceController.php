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

    /**
     * Export attendance records to CSV.
     */
    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $query = Attendance::with(['employee', 'shift'])->latest('date');

        if ($request->filled('date')) {
            $query->whereDate('date', $request->input('date'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $records = $query->get();
        $fileName = 'attendance_logs_' . date('Y_m_d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        return response()->stream(function () use ($records) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Employee Code',
                'Employee Name',
                'Date',
                'Shift',
                'Clock In',
                'Clock Out',
                'Total Hours',
                'Overtime Hours',
                'Status',
                'Late (Minutes)',
                'Geofence Verified',
                'Remarks',
            ]);

            foreach ($records as $att) {
                fputcsv($file, [
                    $att->employee?->employee_code,
                    $att->employee?->full_name,
                    $att->date?->toDateString() ?? $att->date,
                    $att->shift?->name ?? 'Standard',
                    $att->clock_in ? Carbon::parse($att->clock_in)->format('Y-m-d H:i:s') : '',
                    $att->clock_out ? Carbon::parse($att->clock_out)->format('Y-m-d H:i:s') : '',
                    $att->total_work_hours,
                    $att->overtime_hours,
                    $att->status,
                    $att->late_minutes,
                    $att->is_within_geofence ? 'Yes' : 'No',
                    $att->remarks,
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }

    /**
     * Import attendance punch records from CSV.
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle); // skip header row

        $imported = 0;
        $defaultShift = Shift::first();

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 3 || empty($row[0]) || empty($row[2])) {
                continue; // Need at least employee_code and date
            }

            $empCode = trim($row[0]);
            $employee = Employee::where('employee_code', $empCode)->orWhere('email', $empCode)->first();
            if (!$employee) {
                continue;
            }

            $date = trim($row[2]);
            $clockIn = !empty($row[4]) ? Carbon::parse(trim($row[4])) : null;
            $clockOut = !empty($row[5]) ? Carbon::parse(trim($row[5])) : null;
            $status = !empty($row[8]) ? strtolower(trim($row[8])) : 'on_time';
            if (!in_array($status, ['on_time', 'late', 'half_day', 'absent', 'on_leave'])) {
                $status = 'on_time';
            }

            $totalHours = !empty($row[6]) ? (float) $row[6] : 0.0;
            if ($totalHours <= 0 && $clockIn && $clockOut) {
                $totalHours = round($clockIn->diffInMinutes($clockOut) / 60, 2);
            }

            $lateMinutes = !empty($row[9]) ? (int) $row[9] : 0;
            $remarks = !empty($row[11]) ? trim($row[11]) : 'CSV Batch Import';

            Attendance::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'date' => $date,
                ],
                [
                    'shift_id' => $defaultShift?->id,
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                    'total_work_hours' => $totalHours,
                    'overtime_hours' => max(0, $totalHours - 8.0),
                    'status' => $status,
                    'is_late' => $lateMinutes > 0,
                    'late_minutes' => $lateMinutes,
                    'is_within_geofence' => true,
                    'remarks' => $remarks,
                ]
            );

            $imported++;
        }

        fclose($handle);

        return redirect()->route('attendance.index')->with('status', "CSV Import complete! {$imported} attendance records successfully processed.");
    }

    /**
     * Download sample CSV template for attendance import.
     */
    public function template(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $fileName = 'sample_attendance_template.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        return response()->stream(function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Employee Code',
                'Employee Name',
                'Date',
                'Shift',
                'Clock In',
                'Clock Out',
                'Total Hours',
                'Overtime Hours',
                'Status',
                'Late Minutes',
                'Geofence Verified',
                'Remarks',
            ]);

            $firstEmp = Employee::first();
            $code = $firstEmp?->employee_code ?? 'EMP-2026-0001';
            $name = $firstEmp?->full_name ?? 'Alexander Vance';

            fputcsv($file, [
                $code,
                $name,
                date('Y-m-d'),
                'Standard Shift',
                date('Y-m-d') . ' 08:55:00',
                date('Y-m-d') . ' 18:05:00',
                '8.50',
                '0.50',
                'on_time',
                '0',
                'Yes',
                'Biometric Punch In',
            ]);

            fputcsv($file, [
                $code,
                $name,
                date('Y-m-d', strtotime('-1 day')),
                'Standard Shift',
                date('Y-m-d', strtotime('-1 day')) . ' 09:25:00',
                date('Y-m-d', strtotime('-1 day')) . ' 18:00:00',
                '8.00',
                '0.00',
                'late',
                '25',
                'Yes',
                'Traffic congestion note',
            ]);

            fclose($file);
        }, 200, $headers);
    }
}

