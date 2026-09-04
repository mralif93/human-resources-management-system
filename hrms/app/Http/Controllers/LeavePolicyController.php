<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\LeaveType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeavePolicyController extends Controller
{
    /**
     * Display leave policies and statutory entitlement allowances with search and filters.
     */
    public function index(Request $request): View
    {
        $query = LeaveType::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_paid')) {
            $query->where('is_paid', $request->boolean('is_paid'));
        }

        if ($request->filled('requires_attachment')) {
            $query->where('requires_attachment', $request->boolean('requires_attachment'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $leaveTypes = $query->orderBy('id')->paginate(10)->withQueryString();

        // Metrics KPI Counters
        $totalCategories = LeaveType::count();
        $activeCategories = LeaveType::where('is_active', true)->count();
        $inactiveCategories = LeaveType::where('is_active', false)->count();
        $paidCategories = LeaveType::where('is_paid', true)->count();
        $unpaidCategories = LeaveType::where('is_paid', false)->count();
        $attachmentMandatory = LeaveType::where('requires_attachment', true)->count();

        return view('admin.settings.leave-types', compact(
            'leaveTypes',
            'totalCategories',
            'activeCategories',
            'inactiveCategories',
            'paidCategories',
            'unpaidCategories',
            'attachmentMandatory'
        ));
    }

    /**
     * Update an existing leave type's days allowed, paid flag, document requirement, and active status.
     */
    public function update(Request $request, LeaveType $leaveType): RedirectResponse
    {
        $validated = $request->validate([
            'days_allowed' => 'required|numeric|min:0|max:365',
            'is_paid' => 'required|boolean',
            'requires_attachment' => 'required|boolean',
            'is_active' => 'required|boolean',
            'color' => 'required|string|in:indigo,emerald,rose,amber,sky,purple',
            'description' => 'nullable|string|max:500',
        ]);

        $leaveType->update($validated);

        AuditLog::record(
            'leave_type.updated',
            'leaves',
            "Leave policy updated for {$leaveType->name}: {$validated['days_allowed']} days allowed. Status: " . ($validated['is_active'] ? 'Active' : 'Inactive') . ".",
            $validated
        );

        return back()->with('status', "Leave policy for '{$leaveType->name}' updated successfully.");
    }

    /**
     * Toggle active/inactive status of a leave policy.
     */
    public function toggleStatus(LeaveType $leaveType): RedirectResponse
    {
        $newStatus = !$leaveType->is_active;
        $leaveType->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'activated' : 'deactivated';

        AuditLog::record(
            'leave_type.status_toggled',
            'leaves',
            "Leave policy '{$leaveType->name}' has been {$statusText}.",
            ['is_active' => $newStatus]
        );

        return back()->with('status', "Leave policy '{$leaveType->name}' has been {$statusText} successfully.");
    }

    /**
     * Create a new custom company leave type.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:10|unique:leave_types,code',
            'days_allowed' => 'required|numeric|min:0|max:365',
            'is_paid' => 'required|boolean',
            'requires_attachment' => 'required|boolean',
            'is_active' => 'nullable|boolean',
            'color' => 'required|string|in:indigo,emerald,rose,amber,sky,purple',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $leaveType = LeaveType::create($validated);

        AuditLog::record(
            'leave_type.created',
            'leaves',
            "New leave type created: {$leaveType->name} ({$leaveType->code}).",
            $validated
        );

        return back()->with('status', "Custom leave type '{$leaveType->name}' created successfully.");
    }
}
