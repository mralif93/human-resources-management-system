<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\User;
use Database\Seeders\LeaveSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\DepartmentAndDesignationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private Employee $employee;
    private LeaveType $annualLeave;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->adminUser = User::where('email', 'admin@hrms.test')->first();

        $dept = Department::create(['name' => 'Tech', 'code' => 'TC']);
        $desig = Designation::create(['department_id' => $dept->id, 'title' => 'Dev', 'code' => 'TC-DEV', 'description' => 'L1']);

        $this->employee = Employee::create([
            'user_id' => $this->adminUser->id,
            'first_name' => 'Julian',
            'last_name' => 'Casablancas',
            'email' => 'julian@hrms.test',
            'joining_date' => '2025-01-01',
            'department_id' => $dept->id,
            'designation_id' => $desig->id,
        ]);

        $this->annualLeave = LeaveType::create([
            'name' => 'Annual Leave',
            'code' => 'AL',
            'days_allowed' => 14.0,
            'is_paid' => true,
        ]);

        EmployeeLeaveBalance::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->annualLeave->id,
            'year' => (int) date('Y'),
            'entitled_days' => 14.0,
            'used_days' => 0.0,
            'pending_days' => 0.0,
            'remaining_days' => 14.0,
        ]);
    }

    public function test_guest_cannot_access_leave_roster(): void
    {
        $response = $this->get(route('leaves.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_leave_roster(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('leaves.index'));
        $response->assertStatus(200);
        $response->assertSee('Leave &amp; Time-Off Operations', false);
    }

    public function test_employee_can_submit_leave_application(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('leaves.store'), [
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->annualLeave->id,
            'start_date' => date('Y-m-d', strtotime('+3 days')),
            'end_date' => date('Y-m-d', strtotime('+4 days')),
            'total_days' => 2.0,
            'reason' => 'Mid-year personal family break.',
        ]);

        $response->assertRedirect(route('leaves.index'));
        $this->assertDatabaseHas('leave_applications', [
            'employee_id' => $this->employee->id,
            'total_days' => 2.0,
            'status' => 'pending',
        ]);

        // Balance should have pending days reserved
        $balance = EmployeeLeaveBalance::where('employee_id', $this->employee->id)->first();
        $this->assertEquals(2.0, $balance->pending_days);
    }

    public function test_cannot_apply_beyond_entitlement_balance(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('leaves.store'), [
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->annualLeave->id,
            'start_date' => date('Y-m-d', strtotime('+3 days')),
            'end_date' => date('Y-m-d', strtotime('+20 days')),
            'total_days' => 18.0, // Entitled is only 14
            'reason' => 'Way too long vacation.',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('leave_applications', [
            'employee_id' => $this->employee->id,
            'total_days' => 18.0,
        ]);
    }

    public function test_admin_can_approve_leave_application(): void
    {
        $application = LeaveApplication::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->annualLeave->id,
            'start_date' => date('Y-m-d', strtotime('+5 days')),
            'end_date' => date('Y-m-d', strtotime('+6 days')),
            'total_days' => 2.0,
            'reason' => 'Concert trip.',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('leaves.approve', $application));
        $response->assertRedirect(route('leaves.index'));

        $this->assertEquals('approved', $application->fresh()->status);
        $this->assertNotNull($application->fresh()->approved_at);

        $balance = EmployeeLeaveBalance::where('employee_id', $this->employee->id)->first();
        $this->assertEquals(2.0, $balance->used_days);
        $this->assertEquals(12.0, $balance->remaining_days);
    }

    public function test_admin_can_reject_leave_application(): void
    {
        $application = LeaveApplication::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->annualLeave->id,
            'start_date' => date('Y-m-d', strtotime('+5 days')),
            'end_date' => date('Y-m-d', strtotime('+6 days')),
            'total_days' => 2.0,
            'reason' => 'Emergency rest.',
            'status' => 'pending',
        ]);

        $balance = EmployeeLeaveBalance::where('employee_id', $this->employee->id)->first();
        $balance->reserveDays(2.0);

        $response = $this->actingAs($this->adminUser)->post(route('leaves.reject', $application), [
            'rejection_reason' => 'Critical product release deadline.',
        ]);

        $response->assertRedirect(route('leaves.index'));
        $this->assertEquals('rejected', $application->fresh()->status);

        // Reserved pending days should be released back
        $this->assertEquals(0.0, $balance->fresh()->pending_days);
        $this->assertEquals(14.0, $balance->fresh()->remaining_days);
    }

    public function test_leave_seeder_populates_verified_records(): void
    {
        $this->seed(LeaveSeeder::class);

        $this->assertDatabaseHas('leave_types', ['code' => 'AL']);
        $this->assertDatabaseHas('leave_types', ['code' => 'SL']);
        $this->assertDatabaseHas('leave_types', ['code' => 'HL']);
        $this->assertDatabaseHas('leave_types', ['code' => 'UL']);
        $this->assertDatabaseHas('employee_leave_balances', [
            'employee_id' => $this->employee->id,
        ]);
    }
}
