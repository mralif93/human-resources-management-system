<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\JobApplicant;
use App\Models\JobOpening;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ModuleImportExportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Department $dept;
    private Designation $desig;
    private Shift $shift;
    private LeaveType $leaveType;
    private JobOpening $jobOpening;
    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin@hrms.test',
            'role' => 'Super Admin',
        ]);

        $this->dept = Department::create([
            'name' => 'Engineering & Technology',
            'code' => 'ENG',
        ]);

        $this->desig = Designation::create([
            'department_id' => $this->dept->id,
            'title' => 'Software Engineer',
            'code' => 'ENG-SE',
        ]);

        $this->shift = Shift::create([
            'name' => 'Standard Office Shift',
            'code' => 'STD-01',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'late_grace_minutes' => 15,
            'is_default' => true,
        ]);

        $this->leaveType = LeaveType::create([
            'name' => 'Annual Leave',
            'code' => 'AL',
            'days_allowed' => 14.0,
            'is_paid' => true,
        ]);

        $this->jobOpening = JobOpening::create([
            'department_id' => $this->dept->id,
            'designation_id' => $this->desig->id,
            'title' => 'Senior Backend Engineer',
            'description' => 'Lead our backend services team.',
            'openings_count' => 2,
            'status' => 'published',
        ]);

        $this->employee = Employee::create([
            'employee_code' => 'EMP-2026-0001',
            'first_name' => 'Alexander',
            'last_name' => 'Vance',
            'email' => 'alexander.vance@hrms.test',
            'department_id' => $this->dept->id,
            'designation_id' => $this->desig->id,
            'national_id' => '880101-14-1234',
            'gender' => 'Male',
            'employment_status' => 'Permanent',
            'joining_date' => '2026-01-01',
            'branch_location' => 'Headquarters (Kuala Lumpur)',
            'basic_salary' => 8500.00,
        ]);
    }

    // 1. Employee PIM
    public function test_employee_template_download(): void
    {
        $response = $this->actingAs($this->admin)->get(route('employees.template'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename="sample_employees_template.csv"');
    }

    public function test_employee_export(): void
    {
        $response = $this->actingAs($this->admin)->get(route('employees.export'));
        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
    }

    // 2. Attendance & Shifts
    public function test_attendance_template_download(): void
    {
        $response = $this->actingAs($this->admin)->get(route('attendance.template'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename="sample_attendance_template.csv"');
    }

    public function test_attendance_export_and_import(): void
    {
        // Seed an attendance
        Attendance::create([
            'employee_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'date' => Carbon::today()->toDateString(),
            'clock_in' => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(18, 0),
            'total_work_hours' => 9.0,
            'status' => 'on_time',
            'is_within_geofence' => true,
        ]);

        $exportResponse = $this->actingAs($this->admin)->get(route('attendance.export'));
        $exportResponse->assertStatus(200);
        $this->assertStringContainsString('text/csv', $exportResponse->headers->get('Content-Type'));

        // Test Import
        $csvContent = "Employee Code,Employee Name,Date,Shift,Clock In,Clock Out,Total Hours,Overtime Hours,Status,Late Minutes,Geofence Verified,Remarks\n";
        $csvContent .= "EMP-2026-0001,Alexander Vance,2026-09-01,Standard Shift,2026-09-01 08:50:00,2026-09-01 18:00:00,8.5,0.5,on_time,0,Yes,Automated test\n";

        $file = UploadedFile::fake()->createWithContent('attendance_upload.csv', $csvContent);

        $importResponse = $this->actingAs($this->admin)->post(route('attendance.import'), [
            'csv_file' => $file,
        ]);

        $importResponse->assertRedirect(route('attendance.index'));
        $this->assertDatabaseHas('attendances', [
            'employee_id' => $this->employee->id,
            'date' => '2026-09-01',
            'status' => 'on_time',
        ]);
    }

    // 3. Leave & Absence
    public function test_leave_template_download(): void
    {
        $response = $this->actingAs($this->admin)->get(route('leaves.template'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename="sample_leave_template.csv"');
    }

    public function test_leave_export_and_import(): void
    {
        // Seed a leave application
        LeaveApplication::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => '2026-08-10',
            'end_date' => '2026-08-11',
            'total_days' => 2.0,
            'status' => 'approved',
            'reason' => 'Annual family trip',
        ]);

        $exportResponse = $this->actingAs($this->admin)->get(route('leaves.export', ['year' => 2026]));
        $exportResponse->assertStatus(200);
        $this->assertStringContainsString('text/csv', $exportResponse->headers->get('Content-Type'));

        // Test Import
        $csvContent = "Employee Code,Employee Name,Leave Type Code,Start Date,End Date,Total Days,Status,Reason\n";
        $csvContent .= "EMP-2026-0001,Alexander Vance,AL,2026-09-15,2026-09-16,2.0,approved,Imported Leave\n";

        $file = UploadedFile::fake()->createWithContent('leave_upload.csv', $csvContent);

        $importResponse = $this->actingAs($this->admin)->post(route('leaves.import'), [
            'csv_file' => $file,
        ]);

        $importResponse->assertRedirect(route('leaves.index'));
        $this->assertDatabaseHas('leave_applications', [
            'employee_id' => $this->employee->id,
            'status' => 'approved',
            'reason' => 'Imported Leave',
        ]);
    }

    // 4. Recruitment ATS
    public function test_recruitment_template_download(): void
    {
        $response = $this->actingAs($this->admin)->get(route('recruitment.template'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename="sample_candidate_template.csv"');
    }

    public function test_recruitment_export_and_import(): void
    {
        JobApplicant::create([
            'job_opening_id' => $this->jobOpening->id,
            'first_name' => 'Tariq',
            'last_name' => 'Ramli',
            'email' => 'tariq.ramli@example.com',
            'phone' => '+60 11-1234 5678',
            'current_company' => 'CloudScale Asia',
            'current_title' => 'DevOps Specialist',
            'experience_years' => 3.5,
            'expected_salary' => 7000.00,
            'stage' => 'interview',
            'rating' => 5,
        ]);

        $exportResponse = $this->actingAs($this->admin)->get(route('recruitment.export'));
        $exportResponse->assertStatus(200);
        $this->assertStringContainsString('text/csv', $exportResponse->headers->get('Content-Type'));

        // Test Import
        $csvContent = "First Name,Last Name,Email,Phone,Target Job Title,Current Company,Current Title,Experience Years,Expected Salary,Stage,Rating\n";
        $csvContent .= "Zul,Helmi,zul.helmi@example.com,+60 12-444 5555,Senior Backend Engineer,Tech Stack,Backend Dev,4.0,7800.00,applied,4\n";

        $file = UploadedFile::fake()->createWithContent('candidate_upload.csv', $csvContent);

        $importResponse = $this->actingAs($this->admin)->post(route('recruitment.import'), [
            'csv_file' => $file,
        ]);

        $importResponse->assertRedirect(route('recruitment.index'));
        $this->assertDatabaseHas('job_applicants', [
            'email' => 'zul.helmi@example.com',
            'first_name' => 'Zul',
        ]);
    }

    // 5. Activity Audit Trail Export
    public function test_audit_log_export(): void
    {
        AuditLog::record(
            'auth.login',
            'auth',
            'User logged into admin portal successfully.',
            ['ip' => '127.0.0.1']
        );

        $response = $this->actingAs($this->admin)->get(route('audit-logs.export'));
        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename="activity_audit_trail_', $response->headers->get('Content-Disposition'));
    }
}
