<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Designation;
use App\Models\JobApplicant;
use App\Models\JobOpening;
use App\Models\User;
use Database\Seeders\RecruitmentSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecruitmentManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private Department $department;
    private JobOpening $job;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->adminUser = User::where('email', 'admin@hrms.test')->first();

        $this->department = Department::create(['name' => 'Cybersecurity', 'code' => 'CYB']);
        $desig = Designation::create(['department_id' => $this->department->id, 'title' => 'Security Architect', 'code' => 'CYB-01']);

        $this->job = JobOpening::create([
            'department_id' => $this->department->id,
            'designation_id' => $desig->id,
            'title' => 'Security Architect',
            'employment_type' => 'Full-Time',
            'experience_level' => 'Senior',
            'location' => 'Kuala Lumpur',
            'openings_count' => 1,
            'description' => 'Security audit and governance.',
            'status' => 'published',
        ]);
    }

    public function test_guest_cannot_access_recruitment(): void
    {
        $response = $this->get(route('recruitment.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_recruitment_dashboard(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('recruitment.index'));
        $response->assertStatus(200);
        $response->assertSee('Applicant Tracking System (ATS)', false);
    }

    public function test_admin_can_post_new_job_opening(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('recruitment.jobs.store'), [
            'department_id' => $this->department->id,
            'title' => 'Senior Penetration Tester',
            'employment_type' => 'Full-Time',
            'experience_level' => 'Senior',
            'location' => 'Penang, Malaysia (Remote)',
            'openings_count' => 2,
            'description' => 'Red team testing and threat vulnerability detection.',
        ]);

        $response->assertRedirect(route('recruitment.index', ['tab' => 'jobs']));
        $this->assertDatabaseHas('job_openings', [
            'title' => 'Senior Penetration Tester',
            'openings_count' => 2,
            'status' => 'published',
        ]);
    }

    public function test_admin_can_intake_candidate_applicant(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('recruitment.applicants.store'), [
            'job_opening_id' => $this->job->id,
            'first_name' => 'Ahmad',
            'last_name' => 'Fauzi',
            'email' => 'ahmad.fauzi@sec.test',
            'phone' => '+60 18-9999 1111',
            'experience_years' => 4.5,
            'expected_salary' => 8500,
            'stage' => 'applied',
        ]);

        $this->assertDatabaseHas('job_applicants', [
            'email' => 'ahmad.fauzi@sec.test',
            'stage' => 'applied',
        ]);
    }

    public function test_admin_can_update_applicant_pipeline_stage(): void
    {
        $applicant = JobApplicant::create([
            'job_opening_id' => $this->job->id,
            'first_name' => 'Bruce',
            'last_name' => 'Wayne',
            'email' => 'bruce@wayne.test',
            'experience_years' => 10.0,
            'stage' => 'applied',
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('recruitment.applicants.stage', $applicant), [
            'stage' => 'interview',
            'rating' => 5,
            'interview_notes' => 'Exceptional tactical leadership.',
        ]);

        $this->assertEquals('interview', $applicant->fresh()->stage);
        $this->assertEquals(5, $applicant->fresh()->rating);
    }

    public function test_admin_can_one_click_convert_candidate_to_employee(): void
    {
        $applicant = JobApplicant::create([
            'job_opening_id' => $this->job->id,
            'first_name' => 'Clark',
            'last_name' => 'Kent',
            'email' => 'clark.kent@daily.test',
            'phone' => '+60 12-3333 4444',
            'experience_years' => 7.0,
            'expected_salary' => 9000,
            'stage' => 'offer',
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('recruitment.applicants.convert', $applicant));
        
        $applicant->refresh();
        $this->assertEquals('hired', $applicant->stage);
        $this->assertNotNull($applicant->employee_id);

        $this->assertDatabaseHas('employees', [
            'id' => $applicant->employee_id,
            'first_name' => 'Clark',
            'last_name' => 'Kent',
            'email' => 'clark.kent@daily.test',
            'employment_status' => 'Probation',
        ]);
    }

    public function test_recruitment_seeder_populates_verified_records(): void
    {
        $this->seed(RecruitmentSeeder::class);

        $this->assertDatabaseHas('job_openings', ['slug' => 'senior-full-stack-laravel-engineer']);
        $this->assertDatabaseHas('job_applicants', ['email' => 'farhan.hafiz@candidate.test']);
    }

    public function test_admin_can_customize_offer_details(): void
    {
        $applicant = JobApplicant::create([
            'job_opening_id' => $this->job->id,
            'first_name' => 'Natasha',
            'last_name' => 'Romanoff',
            'email' => 'natasha@avengers.test',
            'experience_years' => 7.0,
            'expected_salary' => 8000,
            'stage' => 'offer',
        ]);

        $response = $this->actingAs($this->adminUser)->put(route('recruitment.applicants.offer-details', $applicant), [
            'offered_salary' => 9500.00,
            'joining_date' => '2026-10-01',
            'probation_months' => 6,
            'notice_period_months' => 3,
            'allowances' => 800.00,
            'offer_remarks' => 'Hybrid remote work 3 days/week with tech allowance.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('job_applicants', [
            'id' => $applicant->id,
            'offered_salary' => 9500.00,
            'probation_months' => 6,
            'allowances' => 800.00,
        ]);

        // When onboarded, basic salary uses the offered salary
        $employee = $applicant->fresh()->convertToEmployee();
        $this->assertEquals(9500.00, (float) $employee->basic_salary);
    }
}
