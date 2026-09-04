<?php

namespace Database\Seeders;

use App\Models\AppraisalCycle;
use App\Models\Employee;
use App\Models\EmployeeOkr;
use App\Models\PerformanceReview;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PerformanceSeeder extends Seeder
{
    /**
     * Run database seeds for Module 5: Performance Appraisals & OKRs.
     * Strictly 1 seeder for this module.
     */
    public function run(): void
    {
        $currentYear = (int) date('Y');

        // 1. Create Appraisal Review Cycles
        $q1Cycle = AppraisalCycle::updateOrCreate(
            ['name' => "{$currentYear} Q1 Performance Evaluation Cycle"],
            [
                'cycle_type' => 'quarterly',
                'start_date' => Carbon::create($currentYear, 1, 1),
                'end_date' => Carbon::create($currentYear, 3, 31),
                'due_date' => Carbon::create($currentYear, 4, 15),
                'status' => 'active',
                'description' => 'Quarterly goal check-in, key results assessment, and 360-degree leadership review.',
            ]
        );

        $midYearCycle = AppraisalCycle::updateOrCreate(
            ['name' => "{$currentYear} Mid-Year Organizational Review"],
            [
                'cycle_type' => 'semi_annual',
                'start_date' => Carbon::create($currentYear, 1, 1),
                'end_date' => Carbon::create($currentYear, 6, 30),
                'due_date' => Carbon::create($currentYear, 7, 15),
                'status' => 'upcoming',
                'description' => 'Mid-year strategic review covering organization-wide OKR achievements and promotion benchmarks.',
            ]
        );

        // 2. Fetch Seeded Core Staff
        $superAdmin = Employee::where('first_name', 'Alexander')->first();
        $sarah = Employee::where('first_name', 'Sarah')->first();
        $marcus = Employee::where('first_name', 'Marcus')->first();
        $emily = Employee::where('first_name', 'Emily')->first();

        // 3. Seed Employee OKRs (Objectives & Key Results)
        if ($marcus) {
            EmployeeOkr::updateOrCreate(
                ['employee_id' => $marcus->id, 'title' => 'Core Infrastructure & Microservices 99.9% Uptime'],
                [
                    'quarter' => 'Q1',
                    'year' => $currentYear,
                    'key_result_metric' => 'Maintain SLA uptime percentage across production microservices',
                    'target_value' => 99.90,
                    'current_value' => 99.95,
                    'progress_percentage' => 100,
                    'status' => 'completed',
                ]
            );

            EmployeeOkr::updateOrCreate(
                ['employee_id' => $marcus->id, 'title' => 'Accelerate CI/CD Pipeline Build Times'],
                [
                    'quarter' => 'Q1',
                    'year' => $currentYear,
                    'key_result_metric' => 'Reduce pipeline execution time under 8 minutes across 20 services',
                    'target_value' => 20.00,
                    'current_value' => 16.00,
                    'progress_percentage' => 80,
                    'status' => 'on_track',
                ]
            );
        }

        if ($sarah) {
            EmployeeOkr::updateOrCreate(
                ['employee_id' => $sarah->id, 'title' => 'High-Velocity Technical Recruitment Drive'],
                [
                    'quarter' => 'Q1',
                    'year' => $currentYear,
                    'key_result_metric' => 'Hire 8 senior engineering and product specialists with < 30 days time-to-hire',
                    'target_value' => 8.00,
                    'current_value' => 6.00,
                    'progress_percentage' => 75,
                    'status' => 'on_track',
                ]
            );

            EmployeeOkr::updateOrCreate(
                ['employee_id' => $sarah->id, 'title' => 'Employee Wellness & Engagement Index'],
                [
                    'quarter' => 'Q1',
                    'year' => $currentYear,
                    'key_result_metric' => 'Achieve eNPS rating of 85+ across all departments',
                    'target_value' => 85.00,
                    'current_value' => 88.00,
                    'progress_percentage' => 100,
                    'status' => 'completed',
                ]
            );
        }

        if ($emily) {
            EmployeeOkr::updateOrCreate(
                ['employee_id' => $emily->id, 'title' => 'PulseHR Unified Design System Revamp'],
                [
                    'quarter' => 'Q1',
                    'year' => $currentYear,
                    'key_result_metric' => 'Deliver 40 reusable Figma tokens and component specs adhering to WCAG AA',
                    'target_value' => 40.00,
                    'current_value' => 25.00,
                    'progress_percentage' => 63,
                    'status' => 'at_risk',
                ]
            );
        }

        // 4. Seed Performance Review Evaluations
        if ($marcus && $superAdmin) {
            PerformanceReview::updateOrCreate(
                [
                    'appraisal_cycle_id' => $q1Cycle->id,
                    'employee_id' => $marcus->id,
                ],
                [
                    'reviewer_id' => $superAdmin->id,
                    'self_score' => 4.6,
                    'manager_score' => 4.8,
                    'final_rating' => 'Outstanding',
                    'self_remarks' => 'Successfully orchestrated database migrations and zero-downtime microservices cutover.',
                    'manager_feedback' => 'Exceptional technical leadership and rapid incident remediation throughout Q1.',
                    'key_achievements' => 'Achieved 99.95% system uptime; spearheaded cross-repo API contracts with PayFlow MY.',
                    'areas_for_improvement' => 'Delegate junior architecture mentoring tasks to senior engineering leads.',
                    'status' => 'reviewed',
                    'submitted_at' => Carbon::now()->subDays(10),
                    'reviewed_at' => Carbon::now()->subDays(2),
                ]
            );
        }

        if ($sarah && $superAdmin) {
            PerformanceReview::updateOrCreate(
                [
                    'appraisal_cycle_id' => $q1Cycle->id,
                    'employee_id' => $sarah->id,
                ],
                [
                    'reviewer_id' => $superAdmin->id,
                    'self_score' => 4.2,
                    'manager_score' => 4.3,
                    'final_rating' => 'Exceeds Expectations',
                    'self_remarks' => 'Completed organizational restructure and standardized HR onboarding policies.',
                    'manager_feedback' => 'Outstanding empathy and workforce alignment. Time-to-hire metrics improved by 32%.',
                    'key_achievements' => 'Organized company-wide hackathon and wellness retreat.',
                    'areas_for_improvement' => 'Automate exit interview synthesis dashboards.',
                    'status' => 'reviewed',
                    'submitted_at' => Carbon::now()->subDays(8),
                    'reviewed_at' => Carbon::now()->subDays(1),
                ]
            );
        }

        if ($emily && $sarah) {
            PerformanceReview::updateOrCreate(
                [
                    'appraisal_cycle_id' => $q1Cycle->id,
                    'employee_id' => $emily->id,
                ],
                [
                    'reviewer_id' => $sarah->id,
                    'self_score' => 4.0,
                    'manager_score' => null,
                    'final_rating' => null,
                    'self_remarks' => 'Revamped entire component library in TailwindCSS; awaiting manager assessment.',
                    'manager_feedback' => null,
                    'status' => 'submitted',
                    'submitted_at' => Carbon::now()->subDays(3),
                    'reviewed_at' => null,
                ]
            );
        }
    }
}
