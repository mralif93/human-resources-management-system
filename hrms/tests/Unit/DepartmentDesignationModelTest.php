<?php

namespace Tests\Unit;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentDesignationModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_department_can_be_created_with_manager_relationship(): void
    {
        $managerUser = User::factory()->create([
            'name' => 'Department Head',
            'email' => 'head@hrms.test',
            'role' => 'Department Manager',
        ]);

        $department = Department::create([
            'name' => 'Engineering',
            'code' => 'ENG',
            'description' => 'Software engineering and IT',
            'manager_id' => $managerUser->id,
        ]);

        $this->assertEquals('Engineering', $department->name);
        $this->assertEquals('ENG', $department->code);
        $this->assertNotNull($department->manager);
        $this->assertEquals('Department Head', $department->manager->name);
    }

    public function test_designation_belongs_to_department(): void
    {
        $department = Department::create([
            'name' => 'Human Resources',
            'code' => 'HR',
        ]);

        $designation = Designation::create([
            'department_id' => $department->id,
            'title' => 'Talent Acquisition Specialist',
            'code' => 'HR-TAS',
            'description' => 'Recruitment officer',
        ]);

        $this->assertEquals('Talent Acquisition Specialist', $designation->title);
        $this->assertEquals($department->id, $designation->department->id);
        $this->assertTrue($department->designations->contains($designation));
    }

    public function test_department_has_many_employees(): void
    {
        $department = Department::create(['name' => 'Marketing', 'code' => 'MKT']);
        $designation = Designation::create(['department_id' => $department->id, 'title' => 'Copywriter', 'code' => 'CW']);

        $employee = Employee::create([
            'first_name' => 'Sarah',
            'last_name' => 'Connor',
            'email' => 'sarah.c@hrms.test',
            'department_id' => $department->id,
            'designation_id' => $designation->id,
            'national_id' => '900101-14-1122',
            'gender' => 'Female',
            'employment_status' => 'Permanent',
            'joining_date' => '2026-01-15',
            'branch_location' => 'HQ',
            'basic_salary' => 5000.00,
        ]);

        $this->assertTrue($department->employees->contains($employee));
        $this->assertTrue($designation->employees->contains($employee));
    }
}
