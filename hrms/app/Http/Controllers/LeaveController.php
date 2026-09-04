<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveController extends Controller
{
    /**
     * Display leave dashboard, stats, filters, and paginated roster.
     */
    public function index(Request $request): View
    {
        $selectedYear = (int) $request->input('year', date('Y'));
        $search = $request->input('search');
        $leaveTypeId = $request->input('leave_type_id');
        $status = $request->input('status');
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100]) ? (int) $request->input('per_page') : 10;

        $query = LeaveApplication::with(['employee.department', 'leaveType', 'approver'])
            ->whereYear('start_date', $selectedYear)
            ->latest('created_at');

        if ($search) {
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        if ($leaveTypeId) {
            $query->where('leave_type_id', $leaveTypeId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $leaves = $query->paginate($perPage)->withQueryString();

        // High Level Metrics
        $totalApplications = LeaveApplication::whereYear('start_date', $selectedYear)->count();
        $pendingApprovals = LeaveApplication::where('status', 'pending')->count();
        $onLeaveToday = LeaveApplication::where('status', 'approved')
            ->whereDate('start_date', '<=', Carbon::today())
            ->whereDate('end_date', '>=', Carbon::today())
            ->count();
        
        // Unpaid Leave Days (feeds into PayFlow MY Payroll Engine)
        $unpaidLeaveDays = LeaveApplication::whereHas('leaveType', function ($q) {
                $q->where('is_paid', false);
            })
            ->where('status', 'approved')
            ->whereYear('start_date', $selectedYear)
            ->sum('total_days');

        $leaveTypes = LeaveType::active()->orderBy('name')->get();
        $employees = Employee::orderBy('first_name')->get();

        return view('admin.leaves.index', compact(
            'leaves',
            'leaveTypes',
            'employees',
            'totalApplications',
            'pendingApprovals',
            'onLeaveToday',
            'unpaidLeaveDays',
            'selectedYear'
        ));
    }

    /**
     * Submit a new leave application with entitlement balance validation.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_days' => 'required|numeric|min:0.5|max:365',
            'reason' => 'required|string|max:1000',
        ]);

        $leaveType = LeaveType::findOrFail($validated['leave_type_id']);
        $year = Carbon::parse($validated['start_date'])->year;

        // Verify balance if it's a paid capped leave type
        if ($leaveType->is_paid) {
            $balance = EmployeeLeaveBalance::firstOrCreate(
                [
                    'employee_id' => $validated['employee_id'],
                    'leave_type_id' => $leaveType->id,
                    'year' => $year,
                ],
                [
                    'entitled_days' => $leaveType->days_allowed,
                    'used_days' => 0.0,
                    'pending_days' => 0.0,
                    'remaining_days' => $leaveType->days_allowed,
                ]
            );

            $availableDays = $balance->remaining_days - $balance->pending_days;
            if ($validated['total_days'] > $availableDays) {
                return back()->withInput()->with('error', "Insufficient leave balance. Remaining available: {$availableDays} days.");
            }

            // Reserve pending days
            $balance->reserveDays((float) $validated['total_days']);
        }

        LeaveApplication::create([
            'employee_id' => $validated['employee_id'],
            'leave_type_id' => $leaveType->id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_days' => $validated['total_days'],
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return redirect()->route('leaves.index')->with('status', 'Leave application submitted successfully for manager approval.');
    }

    /**
     * Approve a pending leave application.
     */
    public function approve(Request $request, LeaveApplication $leave): RedirectResponse
    {
        if ($leave->status !== 'pending') {
            return back()->with('error', 'Only pending leave requests can be approved.');
        }

        $approver = Employee::where('user_id', auth()->id())->first() ?? Employee::first();
        $year = Carbon::parse($leave->start_date)->year;

        $leave->update([
            'status' => 'approved',
            'approver_id' => $approver?->id,
            'approved_at' => now(),
        ]);

        // Deduct from balance
        $balance = EmployeeLeaveBalance::where('employee_id', $leave->employee_id)
            ->where('leave_type_id', $leave->leave_type_id)
            ->where('year', $year)
            ->first();

        if ($balance) {
            $balance->deductDays((float) $leave->total_days);
        }

        return redirect()->route('leaves.index')->with('status', "Leave application #{$leave->id} has been approved.");
    }

    /**
     * Reject a pending leave application with optional reason.
     */
    public function reject(Request $request, LeaveApplication $leave): RedirectResponse
    {
        if ($leave->status !== 'pending') {
            return back()->with('error', 'Only pending leave requests can be rejected.');
        }

        $approver = Employee::where('user_id', auth()->id())->first() ?? Employee::first();
        $year = Carbon::parse($leave->start_date)->year;

        $leave->update([
            'status' => 'rejected',
            'approver_id' => $approver?->id,
            'rejection_reason' => $request->input('rejection_reason', 'Operational workforce constraints.'),
        ]);

        // Release pending days back to available balance
        $balance = EmployeeLeaveBalance::where('employee_id', $leave->employee_id)
            ->where('leave_type_id', $leave->leave_type_id)
            ->where('year', $year)
            ->first();

        if ($balance) {
            $balance->releasePendingDays((float) $leave->total_days);
        }

        return redirect()->route('leaves.index')->with('status', "Leave application #{$leave->id} has been rejected.");
    }

    /**
     * Export leave applications to CSV.
     */
    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $selectedYear = (int) $request->input('year', date('Y'));
        $search = $request->input('search');
        $leaveTypeId = $request->input('leave_type_id');
        $status = $request->input('status');

        $query = LeaveApplication::with(['employee.department', 'leaveType', 'approver'])
            ->whereYear('start_date', $selectedYear)
            ->latest('start_date');

        if ($search) {
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        if ($leaveTypeId) {
            $query->where('leave_type_id', $leaveTypeId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $records = $query->get();
        $fileName = 'leave_applications_' . date('Y_m_d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        return response()->stream(function () use ($records) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Employee Code',
                'Employee Name',
                'Department',
                'Leave Type',
                'Leave Type Code',
                'Start Date',
                'End Date',
                'Total Days',
                'Status',
                'Reason',
                'Approver',
                'Approved At',
            ]);

            foreach ($records as $item) {
                fputcsv($file, [
                    $item->employee?->employee_code,
                    $item->employee?->full_name,
                    $item->employee?->department?->name ?? 'Unassigned',
                    $item->leaveType?->name,
                    $item->leaveType?->code,
                    $item->start_date?->toDateString() ?? $item->start_date,
                    $item->end_date?->toDateString() ?? $item->end_date,
                    $item->total_days,
                    $item->status,
                    $item->reason,
                    $item->approver?->full_name ?? 'Pending',
                    $item->approved_at ? Carbon::parse($item->approved_at)->format('Y-m-d H:i:s') : '',
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }

    /**
     * Import historical or migrated leave records from CSV.
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
        $defaultLeaveType = LeaveType::first();

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 4 || empty($row[0]) || empty($row[3])) {
                continue; // Need at least employee_code and start_date
            }

            $empCode = trim($row[0]);
            $employee = Employee::where('employee_code', $empCode)->orWhere('email', $empCode)->first();
            if (!$employee) {
                continue;
            }

            // Match leave type by code or name
            $leaveTypeCode = trim($row[2] ?? '');
            $leaveType = LeaveType::where('code', $leaveTypeCode)->orWhere('name', $leaveTypeCode)->first() ?? $defaultLeaveType;
            if (!$leaveType) {
                continue;
            }

            $startDate = Carbon::parse(trim($row[3]))->toDateString();
            $endDate = !empty($row[4]) ? Carbon::parse(trim($row[4]))->toDateString() : $startDate;
            $totalDays = !empty($row[5]) ? (float) $row[5] : 1.0;
            $status = !empty($row[6]) ? strtolower(trim($row[6])) : 'approved';
            if (!in_array($status, ['pending', 'approved', 'rejected', 'cancelled'])) {
                $status = 'approved';
            }

            $reason = !empty($row[7]) ? trim($row[7]) : 'Batch Migrated Record';

            LeaveApplication::create([
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveType->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_days' => $totalDays,
                'status' => $status,
                'reason' => $reason,
                'approved_at' => $status === 'approved' ? now() : null,
            ]);

            $imported++;
        }

        fclose($handle);

        return redirect()->route('leaves.index')->with('status', "CSV Import complete! {$imported} leave records successfully imported.");
    }

    /**
     * Download sample CSV template for leave import.
     */
    public function template(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $fileName = 'sample_leave_template.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        return response()->stream(function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Employee Code',
                'Employee Name',
                'Leave Type Code',
                'Start Date',
                'End Date',
                'Total Days',
                'Status',
                'Reason',
            ]);

            $firstEmp = Employee::first();
            $code = $firstEmp?->employee_code ?? 'EMP-2026-0001';
            $name = $firstEmp?->full_name ?? 'Alexander Vance';

            fputcsv($file, [
                $code,
                $name,
                'AL',
                date('Y-06-10'),
                date('Y-06-12'),
                '3.0',
                'approved',
                'Family vacation and rest',
            ]);

            fputcsv($file, [
                $code,
                $name,
                'SL',
                date('Y-04-05'),
                date('Y-04-05'),
                '1.0',
                'approved',
                'Medical checkup and flu',
            ]);

            fclose($file);
        }, 200, $headers);
    }
}

