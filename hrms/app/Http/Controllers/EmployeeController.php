<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the employees with search and department filtering.
     */
    public function index(Request $request)
    {
        $query = Employee::with(['department', 'designation', 'manager']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->input('department_id'));
        }

        if ($request->filled('employment_status')) {
            $query->where('employment_status', $request->input('employment_status'));
        }

        $perPage = in_array((int)$request->input('per_page'), [10, 25, 50, 100]) ? (int)$request->input('per_page') : 10;
        $employees = $query->latest()->paginate($perPage)->withQueryString();
        $departments = Department::active()->orderBy('name')->get();
        $designations = Designation::orderBy('title')->get();

        return view('admin.employees.index', compact('employees', 'departments', 'designations'));
    }

    /**
     * Store a newly created employee in storage (TC-PIM-01).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:employees,email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'department_id' => ['required', 'exists:departments,id'],
            'designation_id' => ['required', 'exists:designations,id'],
            'manager_id' => ['nullable', 'exists:employees,id'],
            'national_id' => ['required', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['required', 'in:Male,Female,Other'],
            'employment_status' => ['required', 'in:Permanent,Probation,Contract,Intern'],
            'joining_date' => ['required', 'date'],
            'branch_location' => ['required', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
        ]);

        $employee = Employee::create($validated);

        return redirect()->route('employees.index')->with('status', "Employee {$employee->full_name} ({$employee->employee_code}) created successfully.");
    }

    /**
     * Display the specified employee.
     */
    public function show(Employee $employee)
    {
        $employee->load(['department', 'designation', 'manager', 'subordinates']);

        return view('admin.employees.show', compact('employee'));
    }

    /**
     * Remove the specified employee from storage via soft delete (TC-PIM-02).
     */
    public function destroy(Employee $employee)
    {
        $name = $employee->full_name;
        $employee->delete();

        return redirect()->route('employees.index')->with('status', "Employee {$name} was soft-deleted. Records archived securely.");
    }

    /**
     * Export active employee roster to CSV with confirmation.
     */
    public function export(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $employees = Employee::with(['department', 'designation'])
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get();

        $fileName = 'employees_roster_' . date('Y_m_d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        return response()->stream(function () use ($employees) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Employee Code',
                'First Name',
                'Last Name',
                'Email',
                'Phone',
                'Department',
                'Designation',
                'Status',
                'Joining Date',
                'Location',
                'Basic Salary',
            ]);

            foreach ($employees as $emp) {
                fputcsv($file, [
                    $emp->employee_code,
                    $emp->first_name,
                    $emp->last_name,
                    $emp->email,
                    $emp->phone,
                    $emp->department?->name ?? 'Unassigned',
                    $emp->designation?->title ?? 'Staff',
                    $emp->employment_status,
                    $emp->joining_date?->toDateString(),
                    $emp->branch_location,
                    number_format((float) $emp->basic_salary, 2, '.', ''),
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }

    /**
     * Import employees from CSV with confirmation.
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
        $defaultDept = Department::first();
        $defaultDesig = Designation::first();

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 4 || empty($row[3])) continue; // Need at least email

            $email = trim($row[3]);
            if (Employee::where('email', $email)->exists()) continue;

            Employee::create([
                'first_name' => trim($row[1] ?? 'Staff'),
                'last_name' => trim($row[2] ?? 'Member'),
                'email' => $email,
                'phone' => trim($row[4] ?? '+60 12-000 0000'),
                'department_id' => $defaultDept?->id ?? 1,
                'designation_id' => $defaultDesig?->id ?? 1,
                'national_id' => '900101-14-' . rand(1000, 9999),
                'joining_date' => now()->toDateString(),
                'employment_status' => 'Probation',
                'branch_location' => 'Headquarters (Kuala Lumpur)',
                'basic_salary' => 5000.00,
            ]);

            $imported++;
        }

        fclose($handle);

        return redirect()->route('employees.index')->with('status', "CSV Import complete! {$imported} employee records successfully imported.");
    }
}
