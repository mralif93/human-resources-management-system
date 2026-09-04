<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentTaxonomyController extends Controller
{
    /**
     * Display departments directory with designations, managers, search and filters.
     */
     public function index(Request $request): View
     {
         $query = Department::with(['manager', 'designations', 'employees'])->withCount('employees');

         if ($search = $request->input('search')) {
             $query->where(function ($q) use ($search) {
                 $q->where('name', 'like', "%{$search}%")
                   ->orWhere('code', 'like', "%{$search}%")
                   ->orWhere('description', 'like', "%{$search}%")
                   ->orWhereHas('designations', function ($d) use ($search) {
                       $d->where('title', 'like', "%{$search}%");
                   });
             });
         }

         if ($request->input('has_manager') === 'yes') {
             $query->whereNotNull('manager_id');
         } elseif ($request->input('has_manager') === 'no') {
             $query->whereNull('manager_id');
         }

         if ($request->filled('is_active')) {
             $query->where('is_active', $request->boolean('is_active'));
         }

         $departments = $query->orderBy('name')->paginate(8)->withQueryString();
         $employees = Employee::orderBy('first_name')->get();

         // KPI Counters
         $totalDepartments = Department::count();
         $activeDepartments = Department::where('is_active', true)->count();
         $inactiveDepartments = Department::where('is_active', false)->count();
         $totalDesignations = Designation::count();
         $assignedManagers = Department::whereNotNull('manager_id')->count();
         $vacantHeads = Department::whereNull('manager_id')->count();

         return view('admin.settings.departments', compact(
             'departments',
             'employees',
             'totalDepartments',
             'activeDepartments',
             'inactiveDepartments',
             'totalDesignations',
             'assignedManagers',
             'vacantHeads'
         ));
     }

    /**
     * Toggle active/inactive lifecycle status of a department.
     */
    public function toggleDepartment(Department $department): RedirectResponse
    {
        $newStatus = !$department->is_active;
        $department->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'activated' : 'deactivated';

        AuditLog::record(
            'department.status_toggled',
            'pim',
            "Department '{$department->name}' ({$department->code}) has been {$statusText}.",
            ['is_active' => $newStatus]
        );

        return back()->with('status', "Department '{$department->name}' has been {$statusText} successfully.");
    }

    /**
     * Update an existing department's details and active status.
     */
    public function updateDepartment(Request $request, Department $department): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:departments,name,' . $department->id,
            'code' => 'required|string|max:20|unique:departments,code,' . $department->id,
            'manager_id' => 'nullable|exists:employees,id',
            'is_active' => 'required|boolean',
            'description' => 'nullable|string|max:500',
        ]);

        $department->update($validated);

        AuditLog::record(
            'department.updated',
            'pim',
            "Department '{$department->name}' updated. Status: " . ($validated['is_active'] ? 'Active' : 'Inactive') . ".",
            $validated
        );

        return back()->with('status', "Department '{$department->name}' updated successfully.");
    }

    /**
     * Create a new organization department.
     */
    public function storeDepartment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:departments,name',
            'code' => 'required|string|max:20|unique:departments,code',
            'manager_id' => 'nullable|exists:employees,id',
            'description' => 'nullable|string|max:500',
        ]);

        $dept = Department::create($validated);

        AuditLog::record(
            'department.created',
            'pim',
            "New department established: {$dept->name} ({$dept->code}).",
            $validated
        );

        return back()->with('status', "Department '{$dept->name}' created successfully.");
    }

    /**
     * Create a new job designation within a department.
     */
    public function storeDesignation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'title' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:designations,code',
            'description' => 'nullable|string|max:500',
        ]);

        $desig = Designation::create($validated);

        AuditLog::record(
            'designation.created',
            'pim',
            "New job designation created: {$desig->title} ({$desig->code}).",
            $validated
        );

        return back()->with('status', "Job designation '{$desig->title}' added successfully.");
    }
}
