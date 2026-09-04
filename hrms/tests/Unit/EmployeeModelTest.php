<?php

namespace Tests\Unit;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test auto-generation of Employee ID (TC-PIM-01).
     */
    public function test_auto_generates_employee_code_on_creation(): void
    {
        $department = Department::create(['name' => 'IT', 'code' => 'IT']);
        $designation = Designation::create(['department_id' => $department->id, 'title' => 'Engineer', 'code' => 'ENG']);

        $employee = Employee::create([
            'first_name' => 'Michael',
            'last_name' => 'Scott',
            'email' => 'michael@hrms.test',
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'national_id' => '123456-78-9012',
            'gender' => 'Male',
            'employment_status' => 'Permanent',
            'joining_date' => '2026-01-01',
            'branch_location' => 'HQ',
            'basic_salary' => 8000.00,
        ]);

        $year = date('Y');
        $this->assertNotEmpty($employee->employee_code);
        $this->assertStringStartsWith("EMP-{$year}-", $employee->employee_code);
    }

    /**
     * Test encrypted field persistence & decryption (TC-PIM-03, REQ-PIM-01).
     */
    public function test_national_id_and_bank_account_are_encrypted_at_rest(): void
    {
        $department = Department::create(['name' => 'Finance', 'code' => 'FIN']);
        $designation = Designation::create(['department_id' => $department->id, 'title' => 'Analyst', 'code' => 'FA']);

        $plainNric = '920304-10-5421';
        $plainBank = '514012348888';

        $employee = Employee::create([
            'first_name' => 'Pam',
            'last_name' => 'Beesly',
            'email' => 'pam@hrms.test',
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'national_id' => $plainNric,
            'bank_name' => 'Maybank',
            'bank_account_number' => $plainBank,
            'gender' => 'Female',
            'employment_status' => 'Permanent',
            'joining_date' => '2026-02-01',
            'branch_location' => 'HQ',
            'basic_salary' => 4500.00,
        ]);

        // Decrypted transparently via Eloquent accessor
        $this->assertEquals($plainNric, $employee->national_id);
        $this->assertEquals($plainBank, $employee->bank_account_number);

        // Fetch raw database row directly using PDO to assert encryption
        $rawRow = \Illuminate\Support\Facades\DB::table('employees')->where('id', $employee->id)->first();
        $this->assertNotEquals($plainNric, $rawRow->national_id);
        $this->assertNotEquals($plainBank, $rawRow->bank_account_number);
    }

    /**
     * Test soft delete behavior (TC-PIM-02).
     */
    public function test_soft_delete_preserves_records_with_deleted_at(): void
    {
        $department = Department::create(['name' => 'Sales', 'code' => 'SLS']);
        $designation = Designation::create(['department_id' => $department->id, 'title' => 'Executive', 'code' => 'SE']);

        $employee = Employee::create([
            'first_name' => 'Dwight',
            'last_name' => 'Schrute',
            'email' => 'dwight@hrms.test',
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'national_id' => '800505-14-1122',
            'gender' => 'Male',
            'employment_status' => 'Permanent',
            'joining_date' => '2026-03-01',
            'branch_location' => 'HQ',
            'basic_salary' => 6000.00,
        ]);

        $employeeId = $employee->id;
        $employee->delete();

        // Normal query excludes soft deleted records
        $this->assertNull(Employee::find($employeeId));

        // WithTrashed finds it with populated deleted_at
        $trashed = Employee::withTrashed()->find($employeeId);
        $this->assertNotNull($trashed);
        $this->assertNotNull($trashed->deleted_at);
    }
}
