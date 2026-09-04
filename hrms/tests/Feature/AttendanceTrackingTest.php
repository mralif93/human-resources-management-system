<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\AttendanceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTrackingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Employee $employee;
    private Shift $shift;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin@hrms.test',
            'role' => 'Super Admin',
        ]);

        $dept = Department::create(['name' => 'Operations', 'code' => 'OPS']);
        $desig = Designation::create(['department_id' => $dept->id, 'title' => 'Officer', 'code' => 'OFF']);

        $this->employee = Employee::create([
            'first_name' => 'Leonard',
            'last_name' => 'Hofstadter',
            'email' => 'leonard@hrms.test',
            'department_id' => $dept->id,
            'designation_id' => $desig->id,
            'national_id' => '800101-14-9988',
            'gender' => 'Male',
            'employment_status' => 'Permanent',
            'joining_date' => '2026-01-01',
            'branch_location' => 'HQ',
            'basic_salary' => 6000.00,
        ]);

        $this->shift = Shift::create([
            'name' => 'Standard Shift',
            'code' => 'STD-01',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'late_grace_minutes' => 15,
            'is_default' => true,
        ]);
    }

    public function test_guest_cannot_access_attendance_dashboard(): void
    {
        $response = $this->get('/attendance');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_attendance_roster(): void
    {
        $response = $this->actingAs($this->admin)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('Real-Time Attendance &amp; Shift Operations', false);
        $response->assertSee('Simulated Punch Terminal');
    }

    public function test_successful_geofenced_clock_in( ): void
    {
        // Coordinates within 100m of office HQ (3.1390, 101.6869)
        $payload = [
            'employee_id' => $this->employee->id,
            'latitude' => 3.1390,
            'longitude' => 101.6869,
        ];

        $response = $this->actingAs($this->admin)->post('/attendance/punch-in', $payload);

        $response->assertStatus(302);
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $this->employee->id,
            'is_within_geofence' => true,
        ]);
    }

    public function test_geofence_restriction_failure_rejects_clock_in(): void
    {
        // Coordinates 5km away in Putrajaya / Cyberjaya
        $payload = [
            'employee_id' => $this->employee->id,
            'latitude' => 2.9213,
            'longitude' => 101.6559,
        ];

        $response = $this->actingAs($this->admin)->post('/attendance/punch-in', $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('geofence');

        $this->assertDatabaseMissing('attendances', [
            'employee_id' => $this->employee->id,
        ]);
    }

    public function test_employee_can_clock_out_calculating_total_work_hours(): void
    {
        $today = Carbon::today()->toDateString();
        $clockInTime = Carbon::now()->subHours(8);

        $attendance = Attendance::create([
            'employee_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'date' => $today,
            'clock_in' => $clockInTime,
            'status' => 'on_time',
            'is_within_geofence' => true,
        ]);

        $payload = [
            'employee_id' => $this->employee->id,
            'latitude' => 3.1390,
            'longitude' => 101.6869,
        ];

        $response = $this->actingAs($this->admin)->post('/attendance/punch-out', $payload);

        $response->assertStatus(302);
        $response->assertSessionHas('status');

        $attendance->refresh();
        $this->assertNotNull($attendance->clock_out);
        $this->assertGreaterThanOrEqual(7.9, (float) $attendance->total_work_hours);
    }

    public function test_attendance_seeder_populates_verified_records(): void
    {
        $this->seed(AttendanceSeeder::class);

        $this->assertDatabaseHas('shifts', ['code' => 'STD-01']);
        $this->assertDatabaseHas('shifts', ['code' => 'ROT-NIGHT']);
    }
}
