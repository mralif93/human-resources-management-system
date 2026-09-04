<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run seeds for Module 3: Shifts, Schedules & Verified Geofence Logs.
     */
    public function run(): void
    {
        // 1. Create Standard Shifts
        $shiftStandard = Shift::updateOrCreate(
            ['code' => 'STD-01'],
            [
                'name' => 'Standard Office Shift (09:00 - 18:00)',
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'late_grace_minutes' => 15,
                'half_day_threshold_minutes' => 240,
                'is_default' => true,
            ]
        );

        Shift::updateOrCreate(
            ['code' => 'ROT-NIGHT'],
            [
                'name' => 'Rotational NOC Night Shift (22:00 - 07:00)',
                'start_time' => '22:00:00',
                'end_time' => '07:00:00',
                'late_grace_minutes' => 15,
                'half_day_threshold_minutes' => 240,
                'is_default' => false,
            ]
        );

        // 2. Seed verified geofenced attendances for today
        $employees = Employee::all();
        $today = Carbon::today()->toDateString();

        foreach ($employees as $index => $employee) {
            // Employee 1 & 2: Clocked in on time within office HQ
            if ($index < 2) {
                Attendance::updateOrCreate(
                    ['employee_id' => $employee->id, 'date' => $today],
                    [
                        'shift_id' => $shiftStandard->id,
                        'clock_in' => Carbon::parse($today . ' 08:52:00'),
                        'clock_out' => null,
                        'status' => 'on_time',
                        'is_late' => false,
                        'late_minutes' => 0,
                        'clock_in_latitude' => 3.1391, // 15 meters from HQ
                        'clock_in_longitude' => 101.6870,
                        'clock_in_ip' => '192.168.1.45',
                        'is_within_geofence' => true,
                        'remarks' => 'Verified biometric geofenced punch-in',
                    ]
                );
            } elseif ($index === 2) {
                // Employee 3: Late check-in (09:25 AM, grace period exceeded) (TC-ATT-03)
                Attendance::updateOrCreate(
                    ['employee_id' => $employee->id, 'date' => $today],
                    [
                        'shift_id' => $shiftStandard->id,
                        'clock_in' => Carbon::parse($today . ' 09:25:00'),
                        'clock_out' => null,
                        'status' => 'late',
                        'is_late' => true,
                        'late_minutes' => 25,
                        'clock_in_latitude' => 3.1390,
                        'clock_in_longitude' => 101.6869,
                        'clock_in_ip' => '192.168.1.102',
                        'is_within_geofence' => true,
                        'remarks' => 'Late clock-in recorded beyond 15m grace period',
                    ]
                );
            }
        }
    }
}
