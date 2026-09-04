<?php

namespace Tests\Feature;

use App\Models\AppraisalCycle;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeOkr;
use App\Models\PerformanceReview;
use App\Models\User;
use Database\Seeders\PerformanceSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private Employee $employee;
    private AppraisalCycle $cycle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->adminUser = User::where('email', 'admin@hrms.test')->first();

        $dept = Department::create(['name' => 'Design', 'code' => 'DSN']);
        $desig = Designation::create(['department_id' => $dept->id, 'title' => 'Product Designer', 'code' => 'DSN-01']);

        $this->employee = Employee::create([
            'user_id' => $this->adminUser->id,
            'first_name' => 'Faye',
            'last_name' => 'Valentine',
            'email' => 'faye@hrms.test',
            'joining_date' => '2025-01-01',
            'department_id' => $dept->id,
            'designation_id' => $desig->id,
        ]);

        $this->cycle = AppraisalCycle::create([
            'name' => '2026 Q1 Cycle',
            'cycle_type' => 'quarterly',
            'start_date' => '2026-01-01',
            'end_date' => '2026-03-31',
            'due_date' => '2026-04-15',
            'status' => 'active',
        ]);
    }

    public function test_guest_cannot_access_performance_dashboard(): void
    {
        $response = $this->get(route('performance.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_performance_dashboard(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('performance.index'));
        $response->assertStatus(200);
        $response->assertSee('Talent Appraisals &amp; OKR Matrix', false);
    }

    public function test_admin_can_store_new_employee_okr(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('performance.okrs.store'), [
            'employee_id' => $this->employee->id,
            'quarter' => 'Q1',
            'year' => 2026,
            'title' => 'Redesign Mobile Onboarding Flows',
            'key_result_metric' => 'Boost day-1 user retention from 45% to 70%',
            'target_value' => 70.0,
            'current_value' => 55.0,
        ]);

        $response->assertRedirect(route('performance.index', ['tab' => 'okrs']));
        $this->assertDatabaseHas('employee_okrs', [
            'employee_id' => $this->employee->id,
            'title' => 'Redesign Mobile Onboarding Flows',
            'progress_percentage' => 79,
        ]);
    }

    public function test_admin_can_update_okr_progress(): void
    {
        $okr = EmployeeOkr::create([
            'employee_id' => $this->employee->id,
            'quarter' => 'Q1',
            'year' => 2026,
            'title' => 'Figma UI Kit v2.0',
            'key_result_metric' => 'Publish 50 master components',
            'target_value' => 50.0,
            'current_value' => 10.0,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('performance.okrs.progress', $okr), [
            'current_value' => 50.0,
        ]);

        $response->assertRedirect(route('performance.index', ['tab' => 'okrs']));
        $this->assertEquals(100, $okr->fresh()->progress_percentage);
        $this->assertEquals('completed', $okr->fresh()->status);
    }

    public function test_manager_can_evaluate_performance_review(): void
    {
        $review = PerformanceReview::create([
            'appraisal_cycle_id' => $this->cycle->id,
            'employee_id' => $this->employee->id,
            'self_score' => 4.0,
            'self_remarks' => 'Designed the whole UI.',
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('performance.evaluate', $review), [
            'manager_score' => 4.8,
            'manager_feedback' => 'Superb visual craftsmanship and consistent cross-browser delivery.',
            'key_achievements' => '100% component standardization in Tailwind.',
            'areas_for_improvement' => 'Improve documentation coverage.',
        ]);

        $response->assertRedirect(route('performance.index', ['tab' => 'reviews']));
        $refreshed = $review->fresh();

        $this->assertEquals('reviewed', $refreshed->status);
        $this->assertEquals('Outstanding', $refreshed->final_rating);
        $this->assertEquals(4.8, $refreshed->manager_score);
        $this->assertNotNull($refreshed->reviewed_at);
    }

    public function test_performance_seeder_populates_verified_records(): void
    {
        $this->seed(PerformanceSeeder::class);

        $this->assertDatabaseHas('appraisal_cycles', ['cycle_type' => 'quarterly']);
        $this->assertDatabaseHas('appraisal_cycles', ['cycle_type' => 'semi_annual']);
    }
}
