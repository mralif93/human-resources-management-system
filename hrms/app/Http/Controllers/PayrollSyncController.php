<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\PayrollExportLog;
use App\Models\PayrollSyncToken;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayrollSyncController extends Controller
{
    /**
     * Display Payroll Sync Hub & Feeder Monitor.
     */
    public function index(Request $request): View
    {
        $month = $request->input('month', date('Y-m'));

        try {
            $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $endDate = Carbon::createFromFormat('Y-m', $month)->endOfMonth();
        } catch (\Exception $e) {
            $month = date('Y-m');
            $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $endDate = Carbon::createFromFormat('Y-m', $month)->endOfMonth();
        }

        $employees = Employee::with(['department', 'designation'])
            ->whereNull('deleted_at')
            ->where('employment_status', '!=', 'Terminated')
            ->orderBy('id')
            ->get();

        $unpaidLeaveType = LeaveType::where('is_paid', false)->first();

        $rows = [];
        $totalGross = 0;
        $totalHours = 0;
        $totalOt = 0;
        $totalUnpaidDays = 0;

        foreach ($employees as $emp) {
            $attendances = Attendance::where('employee_id', $emp->id)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->get();

            $hours = (float) $attendances->sum('total_work_hours');
            $ot = (float) $attendances->sum('overtime_hours');

            $unpaidDays = 0.0;
            if ($unpaidLeaveType) {
                $unpaidDays = (float) LeaveApplication::where('employee_id', $emp->id)
                    ->where('leave_type_id', $unpaidLeaveType->id)
                    ->where('status', 'approved')
                    ->where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
                          ->orWhereBetween('end_date', [$startDate->toDateString(), $endDate->toDateString()]);
                    })
                    ->sum('total_days');
            }

            $salary = (float) $emp->basic_salary;
            $dailyRate = $salary > 0 ? round($salary / 26, 2) : 0.0;
            $deduction = round($dailyRate * $unpaidDays, 2);

            $rows[] = (object) [
                'employee' => $emp,
                'hours' => $hours,
                'ot' => $ot,
                'unpaidDays' => $unpaidDays,
                'deduction' => $deduction,
                'salary' => $salary,
            ];

            $totalGross += $salary;
            $totalHours += $hours;
            $totalOt += $ot;
            $totalUnpaidDays += $unpaidDays;
        }

        $tokens = PayrollSyncToken::latest()->get();
        $recentExports = PayrollExportLog::with('user')->latest('id')->take(5)->get();

        return view('admin.payroll.sync', compact(
            'month',
            'rows',
            'totalGross',
            'totalHours',
            'totalOt',
            'totalUnpaidDays',
            'tokens',
            'recentExports'
        ));
    }

    /**
     * REQ-INT-02: Export monthly payroll feeder dataset to CSV file.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $month = $request->input('month', date('Y-m'));
        $fileName = "pulsehr_payroll_feeder_{$month}.csv";

        try {
            $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $endDate = Carbon::createFromFormat('Y-m', $month)->endOfMonth();
        } catch (\Exception $e) {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        }

        $employees = Employee::with(['department', 'designation'])
            ->whereNull('deleted_at')
            ->where('employment_status', '!=', 'Terminated')
            ->orderBy('id')
            ->get();

        $unpaidLeaveType = LeaveType::where('is_paid', false)->first();

        // Log export event
        PayrollExportLog::create([
            'user_id' => auth()->id(),
            'period' => $month,
            'export_format' => 'csv',
            'records_count' => $employees->count(),
            'file_name' => $fileName,
            'created_at' => now(),
        ]);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($employees, $startDate, $endDate, $unpaidLeaveType) {
            $file = fopen('php://output', 'w');

            // CSV Header Row
            fputcsv($file, [
                'Employee Code',
                'Full Name',
                'Department',
                'Designation',
                'Bank Name',
                'Account Number',
                'Basic Salary (MYR)',
                'Regular Work Hours',
                'Overtime Hours',
                'Unpaid Leave Days',
                'Leave Deduction (MYR)',
            ]);

            foreach ($employees as $emp) {
                $attendances = Attendance::where('employee_id', $emp->id)
                    ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->get();

                $hours = (float) $attendances->sum('total_work_hours');
                $ot = (float) $attendances->sum('overtime_hours');

                $unpaidDays = 0.0;
                if ($unpaidLeaveType) {
                    $unpaidDays = (float) LeaveApplication::where('employee_id', $emp->id)
                        ->where('leave_type_id', $unpaidLeaveType->id)
                        ->where('status', 'approved')
                        ->where(function ($q) use ($startDate, $endDate) {
                            $q->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
                              ->orWhereBetween('end_date', [$startDate->toDateString(), $endDate->toDateString()]);
                        })
                        ->sum('total_days');
                }

                $salary = (float) $emp->basic_salary;
                $dailyRate = $salary > 0 ? round($salary / 26, 2) : 0.0;
                $deduction = round($dailyRate * $unpaidDays, 2);

                fputcsv($file, [
                    $emp->employee_code,
                    $emp->full_name,
                    $emp->department?->name ?? 'Unassigned',
                    $emp->designation?->title ?? 'Staff',
                    $emp->bank_name ?? 'Maybank',
                    $emp->bank_account_number ?? '512345678901',
                    number_format($salary, 2, '.', ''),
                    number_format($hours, 2, '.', ''),
                    number_format($ot, 2, '.', ''),
                    number_format($unpaidDays, 1, '.', ''),
                    number_format($deduction, 2, '.', ''),
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }

    /**
     * Generate new Bearer API Token for PayFlow MY integration.
     */
    public function generateToken(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $token = PayrollSyncToken::createToken($validated['name']);

        return back()->with('status', "API Token '{$token->name}' generated successfully. Bearer Key: {$token->token}");
    }

    /**
     * Revoke or toggle an existing API Token.
     */
    public function toggleToken(Request $request, PayrollSyncToken $token): RedirectResponse
    {
        $token->update(['is_active' => !$token->is_active]);

        $status = $token->is_active ? 'activated' : 'deactivated';
        return back()->with('status', "API Token '{$token->name}' has been {$status}.");
    }
}
