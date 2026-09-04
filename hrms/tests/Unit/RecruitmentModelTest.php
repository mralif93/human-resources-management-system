<?php

namespace Tests\Unit;

use App\Models\Department;
use App\Models\Designation;
use App\Models\JobApplicant;
use App\Models\JobOpening;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecruitmentModelTest extends TestCase
{
    use RefreshDatabase;

    private JobOpening $job;

    protected function setUp(): void
    {
        parent::setUp();

        $dept = Department::create(['name' => 'Data Science', 'code' => 'DS']);
        $desig = Designation::create(['department_id' => $dept->id, 'title' => 'AI Engineer', 'code' => 'DS-AI-01']);

        $this->job = JobOpening::create([
            'department_id' => $dept->id,
            'designation_id' => $desig->id,
            'title' => 'Senior Machine Learning Specialist',
            'employment_type' => 'Full-Time',
            'experience_level' => 'Senior',
            'location' => 'Kuala Lumpur',
            'openings_count' => 1,
            'description' => 'Build high-performance prediction pipelines.',
            'status' => 'published',
        ]);
    }

    public function test_candidate_full_name_and_one_click_conversion(): void
    {
        $applicant = JobApplicant::create([
            'job_opening_id' => $this->job->id,
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada.lovelace@test.my',
            'phone' => '+60 12-9988 7766',
            'experience_years' => 5.0,
            'expected_salary' => 9500.00,
            'stage' => 'offer',
        ]);

        $this->assertEquals('Ada Lovelace', $applicant->full_name);

        // One-click conversion to Employee (REQ-ATS-03)
        $employee = $applicant->convertToEmployee();

        $this->assertNotNull($employee->id);
        $this->assertEquals('Ada', $employee->first_name);
        $this->assertEquals('Lovelace', $employee->last_name);
        $this->assertEquals('ada.lovelace@test.my', $employee->email);
        $this->assertStringStartsWith('EMP-', $employee->employee_code);

        $this->assertEquals('hired', $applicant->fresh()->stage);
        $this->assertEquals($employee->id, $applicant->fresh()->employee_id);
    }
}
