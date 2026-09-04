<?php

namespace Tests\Unit;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeOkr;
use App\Models\PerformanceReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceModelTest extends TestCase
{
    use RefreshDatabase;

    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $dept = Department::create(['name' => 'Engineering', 'code' => 'ENG']);
        $desig = Designation::create(['department_id' => $dept->id, 'title' => 'Engineer', 'code' => 'ENG-01']);

        $this->employee = Employee::create([
            'first_name' => 'Linus',
            'last_name' => 'Torvalds',
            'email' => 'linus@hrms.test',
            'joining_date' => '2023-01-01',
            'department_id' => $dept->id,
            'designation_id' => $desig->id,
        ]);
    }

    public function test_okr_progress_percentage_and_status_calculation(): void
    {
        $okr = EmployeeOkr::create([
            'employee_id' => $this->employee->id,
            'quarter' => 'Q1',
            'year' => 2026,
            'title' => 'Optimize Query Performance',
            'key_result_metric' => 'Sub-50ms database queries',
            'target_value' => 100.0,
            'current_value' => 20.0,
        ]);

        $okr->updateProgress(30.0);
        $this->assertEquals(30, $okr->fresh()->progress_percentage);
        $this->assertEquals('behind', $okr->fresh()->status);

        $okr->updateProgress(75.0);
        $this->assertEquals(75, $okr->fresh()->progress_percentage);
        $this->assertEquals('on_track', $okr->fresh()->status);

        $okr->updateProgress(100.0);
        $this->assertEquals(100, $okr->fresh()->progress_percentage);
        $this->assertEquals('completed', $okr->fresh()->status);
    }

    public function test_rating_from_score_mapping(): void
    {
        $this->assertEquals('Outstanding', PerformanceReview::ratingFromScore(4.8));
        $this->assertEquals('Exceeds Expectations', PerformanceReview::ratingFromScore(4.0));
        $this->assertEquals('Meets Expectations', PerformanceReview::ratingFromScore(3.0));
        $this->assertEquals('Needs Improvement', PerformanceReview::ratingFromScore(2.2));
        $this->assertEquals('Unsatisfactory', PerformanceReview::ratingFromScore(1.5));
    }
}
