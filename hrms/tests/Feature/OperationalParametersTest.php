<?php

namespace Tests\Feature;

use App\Models\CompanyProfile;
use App\Models\Department;
use App\Models\Designation;
use App\Models\LeaveType;
use App\Models\Shift;
use App\Models\User;
use Database\Seeders\DepartmentAndDesignationSeeder;
use Database\Seeders\LeaveSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalParametersTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(DepartmentAndDesignationSeeder::class);
        $this->seed(LeaveSeeder::class);
        $this->seed(\Database\Seeders\AttendanceSeeder::class);

        $this->adminUser = User::where('email', 'admin@hrms.test')->first();
    }

    public function test_admin_can_update_office_geofence_coordinates(): void
    {
        $response = $this->actingAs($this->adminUser)->put(route('settings.shifts.geofence'), [
            'office_latitude' => 3.1500,
            'office_longitude' => 101.7000,
            'geofence_radius_meters' => 150,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('company_profiles', [
            'office_latitude' => 3.15,
            'office_longitude' => 101.7,
            'geofence_radius_meters' => 150,
        ]);
    }

    public function test_admin_can_create_new_work_shift(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('settings.shifts.store'), [
            'name' => 'Overnight Operations Roster',
            'code' => 'SFT-NIGHT-02',
            'start_time' => '22:00',
            'end_time' => '07:00',
            'late_grace_minutes' => 20,
            'half_day_threshold_minutes' => 240,
            'is_default' => 0,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('shifts', [
            'code' => 'SFT-NIGHT-02',
            'late_grace_minutes' => 20,
        ]);
    }

    public function test_admin_can_update_existing_work_shift(): void
    {
        $shift = Shift::first();

        $response = $this->actingAs($this->adminUser)->put(route('settings.shifts.update', $shift), [
            'name' => 'Updated Core Hours',
            'code' => 'SFT-CORE-UPDATED',
            'start_time' => '08:30',
            'end_time' => '17:30',
            'late_grace_minutes' => 25,
            'half_day_threshold_minutes' => 210,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('shifts', [
            'id' => $shift->id,
            'name' => 'Updated Core Hours',
            'code' => 'SFT-CORE-UPDATED',
            'start_time' => '08:30',
            'end_time' => '17:30',
            'late_grace_minutes' => 25,
        ]);
    }

    public function test_admin_can_set_default_work_shift(): void
    {
        $shift1 = Shift::first();
        $shift2 = Shift::create([
            'name' => 'Second Shift',
            'code' => 'SFT-SECOND',
            'start_time' => '14:00',
            'end_time' => '23:00',
            'late_grace_minutes' => 15,
            'half_day_threshold_minutes' => 240,
            'is_default' => false,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('settings.shifts.set-default', $shift2));

        $response->assertRedirect();
        $this->assertDatabaseHas('shifts', [
            'id' => $shift2->id,
            'is_default' => true,
        ]);
        $this->assertDatabaseHas('shifts', [
            'id' => $shift1->id,
            'is_default' => false,
        ]);
    }

    public function test_admin_can_delete_non_default_work_shift(): void
    {
        $shift = Shift::create([
            'name' => 'Temporary Weekend Shift',
            'code' => 'SFT-WEEKEND',
            'start_time' => '10:00',
            'end_time' => '18:00',
            'late_grace_minutes' => 15,
            'half_day_threshold_minutes' => 240,
            'is_default' => false,
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('settings.shifts.destroy', $shift));

        $response->assertRedirect();
        $this->assertDatabaseMissing('shifts', [
            'id' => $shift->id,
        ]);
    }

    public function test_admin_cannot_delete_primary_default_work_shift(): void
    {
        $defaultShift = Shift::where('is_default', true)->first();

        // Create a secondary shift so count > 1
        Shift::create([
            'name' => 'Secondary Shift',
            'code' => 'SFT-SEC-2',
            'start_time' => '12:00',
            'end_time' => '20:00',
            'late_grace_minutes' => 10,
            'half_day_threshold_minutes' => 240,
            'is_default' => false,
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('settings.shifts.destroy', $defaultShift));

        $response->assertRedirect();
        $response->assertSessionHasErrors('shift_delete');
        $this->assertDatabaseHas('shifts', [
            'id' => $defaultShift->id,
        ]);
    }

    public function test_admin_can_update_leave_type_policy(): void
    {
        $leaveType = LeaveType::where('code', 'AL')->first();

        $response = $this->actingAs($this->adminUser)->put(route('settings.leave-types.update', $leaveType), [
            'days_allowed' => 18.0,
            'is_paid' => 1,
            'requires_attachment' => 0,
            'is_active' => 1,
            'color' => 'indigo',
            'description' => 'Updated standard annual entitlement.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('leave_types', [
            'id' => $leaveType->id,
            'days_allowed' => 18.0,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_toggle_leave_type_active_status(): void
    {
        $leaveType = LeaveType::where('code', 'AL')->first();
        $this->assertTrue($leaveType->is_active);

        // Deactivate
        $response = $this->actingAs($this->adminUser)->post(route('settings.leave-types.toggle', $leaveType));
        $response->assertRedirect();
        $this->assertDatabaseHas('leave_types', [
            'id' => $leaveType->id,
            'is_active' => false,
        ]);

        // Reactivate
        $response2 = $this->actingAs($this->adminUser)->post(route('settings.leave-types.toggle', $leaveType));
        $response2->assertRedirect();
        $this->assertDatabaseHas('leave_types', [
            'id' => $leaveType->id,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_create_custom_company_leave_type(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('settings.leave-types.store'), [
            'name' => 'Academic Exam Leave',
            'code' => 'EXAM',
            'days_allowed' => 3.0,
            'is_paid' => 1,
            'requires_attachment' => 1,
            'color' => 'purple',
            'description' => 'For professional examinations.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('leave_types', [
            'code' => 'EXAM',
            'days_allowed' => 3.0,
        ]);
    }

    public function test_admin_can_create_department_and_designation(): void
    {
        $responseDept = $this->actingAs($this->adminUser)->post(route('settings.departments.store'), [
            'name' => 'Quality Assurance & Security',
            'code' => 'DEP-QA',
            'description' => 'Enterprise QA Testing Group',
        ]);
        $responseDept->assertRedirect();
        $this->assertDatabaseHas('departments', ['code' => 'DEP-QA']);

        $dept = Department::where('code', 'DEP-QA')->first();

        $responseDesig = $this->actingAs($this->adminUser)->post(route('settings.designations.store'), [
            'department_id' => $dept->id,
            'title' => 'Principal Security Auditor',
            'code' => 'DSG-SEC-AUD',
            'description' => 'Security compliance lead',
        ]);
        $responseDesig->assertRedirect();
        $this->assertDatabaseHas('designations', ['code' => 'DSG-SEC-AUD']);
    }

    public function test_admin_can_search_and_filter_leave_policies(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('settings.leave-types', [
            'search' => 'Annual',
            'is_paid' => 1,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Annual Leave', false);
    }

    public function test_admin_can_search_and_filter_departments(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('settings.departments', [
            'search' => 'Engineering',
            'has_manager' => 'yes',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Engineering', false);
    }

    public function test_admin_can_toggle_department_status(): void
    {
        $dept = Department::first();
        $this->assertTrue($dept->is_active);

        // Deactivate
        $response = $this->actingAs($this->adminUser)->post(route('settings.departments.toggle', $dept));
        $response->assertRedirect();
        $this->assertDatabaseHas('departments', [
            'id' => $dept->id,
            'is_active' => false,
        ]);

        // Reactivate
        $response2 = $this->actingAs($this->adminUser)->post(route('settings.departments.toggle', $dept));
        $response2->assertRedirect();
        $this->assertDatabaseHas('departments', [
            'id' => $dept->id,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_update_department(): void
    {
        $dept = Department::first();

        $response = $this->actingAs($this->adminUser)->put(route('settings.departments.update', $dept), [
            'name' => 'Technology & Platform Services',
            'code' => $dept->code,
            'is_active' => 1,
            'description' => 'Updated core engineering mandate.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('departments', [
            'id' => $dept->id,
            'name' => 'Technology & Platform Services',
            'is_active' => true,
        ]);
    }
}
