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
}
