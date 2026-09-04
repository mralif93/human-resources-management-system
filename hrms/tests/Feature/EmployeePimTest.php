<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeePimTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Department $dept;
    private Designation $desig;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin@hrms.test',
            'role' => 'Super Admin',
        ]);

        $this->dept = Department::create([
            'name' => 'Human Resources',
            'code' => 'HR',
            'description' => 'HR operations',
        ]);

        $this->desig = Designation::create([
            'department_id' => $this->dept->id,
            'title' => 'HR Specialist',
            'code' => 'HR-SPEC',
        ]);
    }

    public function test_guest_cannot_access_employee_directory(): void
    {
        $response = $this->get('/employees');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_employee_roster(): void
    {
        $employee = Employee::create([
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'email' => 'alice@hrms.test',
            'department_id' => $this->dept->id,
            'designation_id' => $this->desig->id,
            'national_id' => '940101-14-1122',
            'gender' => 'Female',
            'employment_status' => 'Permanent',
            'joining_date' => '2026-01-01',
            'branch_location' => 'HQ',
            'basic_salary' => 5000.00,
        ]);

        $response = $this->actingAs($this->admin)->get('/employees');

        $response->assertStatus(200);
        $response->assertSee('Active Employee Roster');
        $response->assertSee('Alice Smith');
        $response->assertSee($employee->employee_code);
    }

    public function test_admin_can_create_new_employee_record(): void
    {
        $payload = [
            'first_name' => 'Robert',
            'last_name' => 'Langdon',
            'email' => 'robert.langdon@hrms.test',
            'phone' => '+60 12-888 9999',
            'department_id' => $this->dept->id,
            'designation_id' => $this->desig->id,
            'national_id' => '820404-10-9988',
            'gender' => 'Male',
            'employment_status' => 'Probation',
            'joining_date' => '2026-04-01',
            'branch_location' => 'Headquarters (Kuala Lumpur)',
            'bank_name' => 'Maybank',
            'bank_account_number' => '123456789012',
            'basic_salary' => 7500.00,
        ];

        $response = $this->actingAs($this->admin)->post('/employees', $payload);

        $response->assertStatus(302);
        $response->assertRedirect(route('employees.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('employees', [
            'first_name' => 'Robert',
            'last_name' => 'Langdon',
            'email' => 'robert.langdon@hrms.test',
            'employment_status' => 'Probation',
        ]);
    }

    public function test_admin_can_view_employee_profile_dossier(): void
    {
        $employee = Employee::create([
            'first_name' => 'Jessica',
            'last_name' => 'Alba',
            'email' => 'jessica@hrms.test',
            'department_id' => $this->dept->id,
            'designation_id' => $this->desig->id,
            'national_id' => '900101-14-3344',
            'gender' => 'Female',
            'employment_status' => 'Permanent',
            'joining_date' => '2026-02-15',
            'branch_location' => 'Headquarters (Kuala Lumpur)',
            'basic_salary' => 9200.00,
        ]);

        $response = $this->actingAs($this->admin)->get("/employees/{$employee->id}");

        $response->assertStatus(200);
        $response->assertSee('Jessica Alba');
        $response->assertSee('Employee Dossier &amp; Personnel Record', false);
        $response->assertSee('MYR 9,200.00');
    }

    public function test_admin_can_soft_delete_employee(): void
    {
        $employee = Employee::create([
            'first_name' => 'Thomas',
            'last_name' => 'Anderson',
            'email' => 'thomas@hrms.test',
            'department_id' => $this->dept->id,
            'designation_id' => $this->desig->id,
            'national_id' => '780101-14-0011',
            'gender' => 'Male',
            'employment_status' => 'Permanent',
            'joining_date' => '2025-01-01',
            'branch_location' => 'HQ',
            'basic_salary' => 6500.00,
        ]);

        $response = $this->actingAs($this->admin)->delete("/employees/{$employee->id}");

        $response->assertStatus(302);
        $response->assertRedirect(route('employees.index'));

        $this->assertSoftDeleted('employees', [
            'id' => $employee->id,
        ]);
    }
}
