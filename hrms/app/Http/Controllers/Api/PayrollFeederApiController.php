<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\PayrollSyncToken;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollFeederApiController extends Controller
{
    /**
     * REQ-INT-01: Authenticated REST API endpoint for external Payroll Management System (PayFlow MY).
     * Ingests master employee compensation, verified work hours, overtime totals, and unpaid leave days.
     */
    public function feed(Request $request): JsonResponse
    {
        // 1. Bearer Token Verification
        $bearerToken = $request->bearerToken();
        if (!$bearerToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Missing Authorization Bearer token header.',
            ], 401);
        }

        $syncToken = PayrollSyncToken::where('token', $bearerToken)
            ->where('is_active', true)
            ->first();

        if (!$syncToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Invalid or revoked Payroll Feeder API token.',
            ], 403);
        }

        // Update token last used timestamp
        $syncToken->update(['last_used_at' => now()]);

        // 2. Validate Target Payroll Period Month (YYYY-MM)
        $month = $request->input('month', date('Y-m'));
        try {
            $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $endDate = Carbon::createFromFormat('Y-m', $month)->endOfMonth();
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid month format. Expected format: YYYY-MM (e.g. 2026-09).',
            ], 422);
        }

        // 3. Fetch Active Workforce Records
        $employees = Employee::with(['department', 'designation'])
            ->whereNull('deleted_at')
            ->where('employment_status', '!=', 'Terminated')
            ->orderBy('id')
            ->get();

        $unpaidLeaveType = LeaveType::where('is_paid', false)->first();

        $feederData = [];

        foreach ($employees as $employee) {
            // Aggregate Monthly Attendance Hours
            $attendances = Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->get();

            $verifiedWorkHours = (float) $attendances->sum('total_work_hours');
            $overtimeHours = (float) $attendances->sum('overtime_hours');

            // Aggregate Monthly Unpaid Leave Days
            $unpaidDays = 0.0;
            if ($unpaidLeaveType) {
                $unpaidLeaves = LeaveApplication::where('employee_id', $employee->id)
                    ->where('leave_type_id', $unpaidLeaveType->id)
                    ->where('status', 'approved')
                    ->where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
                          ->orWhereBetween('end_date', [$startDate->toDateString(), $endDate->toDateString()]);
                    })
                    ->get();

                $unpaidDays = (float) $unpaidLeaves->sum('total_days');
            }

            // Calculation of daily rate (Standard 26 working days per Malaysian Employment Act)
            $basicSalary = (float) $employee->basic_salary;
            $dailyRate = $basicSalary > 0 ? round($basicSalary / 26, 2) : 0.0;
            $unpaidDeduction = round($dailyRate * $unpaidDays, 2);

            $feederData[] = [
                'employee_id' => $employee->id,
                'employee_code' => $employee->employee_code,
                'full_name' => $employee->full_name,
                'email' => $employee->email,
                'department' => $employee->department?->name ?? 'Unassigned',
                'designation' => $employee->designation?->title ?? 'Staff',
                'employment_status' => $employee->employment_status,
                'joining_date' => $employee->joining_date?->toDateString(),
                'bank_name' => $employee->bank_name ?? 'Maybank Berhad',
                'bank_account_number' => $employee->bank_account_number ?? '512345678901',
                'basic_salary' => $basicSalary,
                'verified_work_hours' => $verifiedWorkHours,
                'overtime_hours' => $overtimeHours,
                'unpaid_leave_days' => $unpaidDays,
                'unpaid_leave_deduction' => $unpaidDeduction,
            ];
        }

        return response()->json([
            'status' => 'success',
            'payroll_period' => $month,
            'feeder_engine' => 'PulseHR Core API v1.0',
            'client_system' => 'PayFlow MY (Payroll Management System)',
            'token_name' => $syncToken->name,
            'generated_at' => now()->toIso8601String(),
            'records_count' => count($feederData),
            'data' => $feederData,
        ], 200);
    }
}
