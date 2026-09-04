<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CompanyProfile;
use App\Models\Shift;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftGeofenceController extends Controller
{
    /**
     * Display work shifts and geofence GPS parameters.
     */
    public function index(): View
    {
        $profile = CompanyProfile::current();
        $shifts = Shift::withCount('attendances')->orderByDesc('is_default')->orderBy('start_time')->get();

        // Standard KPI counters
        $totalShifts = $shifts->count();
        $defaultShift = $shifts->firstWhere('is_default', true);
        $geofenceRadius = $profile->geofence_radius_meters ?? 100;
        $totalPunches = \App\Models\Attendance::count();

        return view('admin.settings.shifts', compact(
            'profile',
            'shifts',
            'totalShifts',
            'defaultShift',
            'geofenceRadius',
            'totalPunches'
        ));
    }

    /**
     * Update headquarters office geofence GPS coordinates and radius.
     */
    public function updateGeofence(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'office_latitude' => 'required|numeric|between:-90,90',
            'office_longitude' => 'required|numeric|between:-180,180',
            'geofence_radius_meters' => 'required|integer|min:10|max:5000',
        ]);

        $profile = CompanyProfile::current();
        $profile->update($validated);

        AuditLog::record(
            'geofence.updated',
            'attendance',
            "Office geofence parameters updated to {$validated['office_latitude']}, {$validated['office_longitude']} (Radius: {$validated['geofence_radius_meters']}m).",
            $validated
        );

        return back()->with('status', 'Office geofence GPS coordinates and perimeter radius updated successfully.');
    }

    /**
     * Create a new work shift.
     */
    public function storeShift(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:shifts,code',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'late_grace_minutes' => 'required|integer|min:0|max:120',
            'half_day_threshold_minutes' => 'required|integer|min:60|max:480',
            'is_default' => 'nullable|boolean',
        ]);

        $isDefault = $request->boolean('is_default');
        if ($isDefault) {
            Shift::where('is_default', true)->update(['is_default' => false]);
        }

        $shift = Shift::create(array_merge($validated, ['is_default' => $isDefault]));

        AuditLog::record(
            'shift.created',
            'attendance',
            "New operational shift created: {$shift->name} ({$shift->code}).",
            $validated
        );

        return back()->with('status', "Work shift '{$shift->name}' created successfully.");
    }

    /**
     * Update an existing work shift.
     */
    public function updateShift(Request $request, Shift $shift): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:shifts,code,' . $shift->id,
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'late_grace_minutes' => 'required|integer|min:0|max:120',
            'half_day_threshold_minutes' => 'required|integer|min:60|max:480',
            'is_default' => 'nullable|boolean',
        ]);

        $isDefault = $request->boolean('is_default');
        if ($isDefault && !$shift->is_default) {
            Shift::where('is_default', true)->update(['is_default' => false]);
        }

        $shift->update(array_merge($validated, ['is_default' => $isDefault]));

        AuditLog::record(
            'shift.updated',
            'attendance',
            "Operational shift '{$shift->name}' ({$shift->code}) updated.",
            $validated
        );

        return back()->with('status', "Work shift '{$shift->name}' updated successfully.");
    }

    /**
     * Set a shift as company default.
     */
    public function setDefault(Shift $shift): RedirectResponse
    {
        Shift::where('is_default', true)->update(['is_default' => false]);
        $shift->update(['is_default' => true]);

        AuditLog::record(
            'shift.set_default',
            'attendance',
            "Work shift '{$shift->name}' ({$shift->code}) designated as default company roster.",
            ['shift_id' => $shift->id]
        );

        return back()->with('status', "Work shift '{$shift->name}' is now the primary default company roster.");
    }

    /**
     * Delete/Remove a work shift.
     */
    public function destroyShift(Shift $shift): RedirectResponse
    {
        // Prevent deleting the primary default shift if there are others
        if ($shift->is_default && Shift::count() > 1) {
            return back()->withErrors(['shift_delete' => "Cannot remove the default shift '{$shift->name}'. Please designate another shift as default first."]);
        }

        // Check if there are active punches tied to this shift
        $attendanceCount = $shift->attendances()->count();

        $shiftName = $shift->name;
        $shiftCode = $shift->code;
        $shift->delete();

        AuditLog::record(
            'shift.deleted',
            'attendance',
            "Work shift '{$shiftName}' ({$shiftCode}) was removed from the roster registry.",
            ['attendance_records_affected' => $attendanceCount]
        );

        return back()->with('status', "Work shift '{$shiftName}' has been removed successfully.");
    }
}
