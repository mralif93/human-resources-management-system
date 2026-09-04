<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LeaveSeeder extends Seeder
{
    /**
     * Run the database seeds for Module 4: Leave & Absence Management.
     * Strictly 1 seeder for this module.
     */
    public function run(): void
    {
        // 1. Malaysian Statutory & Standard Corporate Leave Types
        $leaveTypes = [
            [
                'name' => 'Annual Leave',
                'code' => 'AL',
                'days_allowed' => 14.0,
                'is_paid' => true,
                'requires_attachment' => false,
                'color' => 'indigo',
                'description' => 'Statutory paid annual vacation leave under Malaysian Employment Act.',
            ],
            [
                'name' => 'Medical / Sick Leave',
                'code' => 'SL',
                'days_allowed' => 14.0,
                'is_paid' => true,
                'requires_attachment' => true,
                'color' => 'emerald',
                'description' => 'Certified outpatient illness or clinic visit with registered medical practitioner certificate.',
            ],
            [
                'name' => 'Hospitalisation Leave',
                'code' => 'HL',
                'days_allowed' => 60.0,
                'is_paid' => true,
                'requires_attachment' => true,
                'color' => 'amber',
                'description' => 'Inpatient hospital stay or post-discharge medical recovery.',
            ],
            [
                'name' => 'Maternity Leave',
                'code' => 'ML',
                'days_allowed' => 98.0,
                'is_paid' => true,
                'requires_attachment' => true,
                'color' => 'purple',
                'description' => 'Statutory 98 days maternity protection for female employees.',
            ],
            [
                'name' => 'Emergency / Compassionate',
                'code' => 'EL',
                'days_allowed' => 5.0,
                'is_paid' => true,
                'requires_attachment' => false,
                'color' => 'rose',
                'description' => 'Immediate family bereavement, natural disaster or critical emergencies.',
            ],
            [
                'name' => 'Unpaid Leave',
                'code' => 'UL',
                'days_allowed' => 0.0,
                'is_paid' => false,
                'requires_attachment' => false,
                'color' => 'slate',
                'description' => 'Unpaid absence deductible via external PayFlow MY payroll feeder.',
            ],
        ];

        $createdTypes = [];
        foreach ($leaveTypes as $data) {
            $createdTypes[$data['code']] = LeaveType::updateOrCreate(
                ['code' => $data['code']],
                $data
            );
        }

        // 2. Allocate Current Year Leave Balances for all Seeded Employees
        $currentYear = (int) date('Y');
        $employees = Employee::all();

        foreach ($employees as $emp) {
            foreach ($createdTypes as $code => $type) {
                // Entitlement is 0 for Unpaid Leave, full days for statutory types
                $entitled = $type->days_allowed;

                // Create initial balance
                EmployeeLeaveBalance::updateOrCreate(
                    [
                        'employee_id' => $emp->id,
                        'leave_type_id' => $type->id,
                        'year' => $currentYear,
                    ],
                    [
                        'entitled_days' => $entitled,
                        'used_days' => 0.0,
                        'pending_days' => 0.0,
                        'remaining_days' => $entitled,
                    ]
                );
            }
        }

        // 3. Create Sample Leave Applications (Pending, Approved, Rejected)
        $superAdmin = Employee::where('first_name', 'Alexander')->first() ?? $employees->first();
        $sarah = Employee::where('first_name', 'Sarah')->first() ?? $employees->skip(1)->first();
        $marcus = Employee::where('first_name', 'Marcus')->first() ?? $employees->skip(2)->first();
        $emily = Employee::where('first_name', 'Emily')->first() ?? $employees->skip(3)->first();

        $alType = $createdTypes['AL'];
        $slType = $createdTypes['SL'];
        $ulType = $createdTypes['UL'];

        if ($sarah && $superAdmin) {
            // Sarah took 2 days Annual Leave last week (Approved)
            $sarahLeave = LeaveApplication::create([
                'employee_id' => $sarah->id,
                'leave_type_id' => $alType->id,
                'approver_id' => $superAdmin->id,
                'start_date' => Carbon::today()->subDays(7),
                'end_date' => Carbon::today()->subDays(6),
                'total_days' => 2.0,
                'reason' => 'Family vacation to Penang.',
                'status' => 'approved',
                'approved_at' => Carbon::today()->subDays(8),
            ]);

            // Deduct balance for Sarah
            $bal = EmployeeLeaveBalance::where('employee_id', $sarah->id)
                ->where('leave_type_id', $alType->id)
                ->where('year', $currentYear)
                ->first();
            if ($bal) {
                $bal->deductDays(2.0);
            }
        }

        if ($marcus && $superAdmin) {
            // Marcus has a pending Sick Leave request for tomorrow
            LeaveApplication::create([
                'employee_id' => $marcus->id,
                'leave_type_id' => $slType->id,
                'approver_id' => $superAdmin->id,
                'start_date' => Carbon::tomorrow(),
                'end_date' => Carbon::tomorrow(),
                'total_days' => 1.0,
                'reason' => 'Dental surgery appointment & recovery.',
                'status' => 'pending',
            ]);

            // Reserve pending day
            $bal = EmployeeLeaveBalance::where('employee_id', $marcus->id)
                ->where('leave_type_id', $slType->id)
                ->where('year', $currentYear)
                ->first();
            if ($bal) {
                $bal->reserveDays(1.0);
            }
        }

        if ($emily && $superAdmin) {
            // Emily has a pending Annual Leave for next week
            LeaveApplication::create([
                'employee_id' => $emily->id,
                'leave_type_id' => $alType->id,
                'approver_id' => $superAdmin->id,
                'start_date' => Carbon::today()->addDays(5),
                'end_date' => Carbon::today()->addDays(7),
                'total_days' => 3.0,
                'reason' => 'Attending Design Leadership Conference.',
                'status' => 'pending',
            ]);

            $bal = EmployeeLeaveBalance::where('employee_id', $emily->id)
                ->where('leave_type_id', $alType->id)
                ->where('year', $currentYear)
                ->first();
            if ($bal) {
                $bal->reserveDays(3.0);
            }

            // Emily had a 1-day Unpaid Leave recorded (Feeder test case)
            LeaveApplication::create([
                'employee_id' => $emily->id,
                'leave_type_id' => $ulType->id,
                'approver_id' => $superAdmin->id,
                'start_date' => Carbon::today()->subDays(14),
                'end_date' => Carbon::today()->subDays(14),
                'total_days' => 1.0,
                'reason' => 'Personal matters - exhausted annual leave.',
                'status' => 'approved',
                'approved_at' => Carbon::today()->subDays(15),
            ]);
        }
    }
}
