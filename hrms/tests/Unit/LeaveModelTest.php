<?php

namespace Tests\Unit;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use App\Models\LeaveType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveModelTest extends TestCase
{
    use RefreshDatabase;

    private Employee $employee;
    private LeaveType $annualLeave;

    protected function setUp(): void
    {
        parent::setUp();

        $dept = Department::create(['name' => 'HR', 'code' => 'HR']);
        $desig = Designation::create(['department_id' => $dept->id, 'title' => 'Specialist', 'code' => 'HR-SPEC', 'description' => 'L2']);

        $this->employee = Employee::create([
            'first_name' => 'Adeline',
            'last_name' => 'Wong',
            'email' => 'adeline.wong@hrms.test',
            'joining_date' => '2024-01-01',
            'department_id' => $dept->id,
            'designation_id' => $desig->id,
        ]);

        $this->annualLeave = LeaveType::create([
            'name' => 'Annual Leave',
            'code' => 'AL',
            'days_allowed' => 14.0,
            'is_paid' => true,
        ]);
    }

    public function test_leave_balance_initialization_and_deduction(): void
    {
        $balance = EmployeeLeaveBalance::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->annualLeave->id,
            'year' => 2026,
            'entitled_days' => 14.0,
            'used_days' => 0.0,
            'pending_days' => 0.0,
            'remaining_days' => 14.0,
        ]);

        // Reserve 2 pending days
        $balance->reserveDays(2.0);
        $this->assertEquals(2.0, $balance->fresh()->pending_days);

        // Deduct 2 approved days
        $balance->fresh()->deductDays(2.0);
        $refreshed = $balance->fresh();

        $this->assertEquals(2.0, $refreshed->used_days);
        $this->assertEquals(0.0, $refreshed->pending_days);
        $this->assertEquals(12.0, $refreshed->remaining_days);
    }

    public function test_release_pending_days_on_rejection(): void
    {
        $balance = EmployeeLeaveBalance::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->annualLeave->id,
            'year' => 2026,
            'entitled_days' => 14.0,
            'used_days' => 0.0,
            'pending_days' => 3.0,
            'remaining_days' => 14.0,
        ]);

        $balance->releasePendingDays(3.0);
        $this->assertEquals(0.0, $balance->fresh()->pending_days);
    }
}
