<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Designation;
use App\Models\JobApplicant;
use App\Models\JobOpening;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RecruitmentSeeder extends Seeder
{
    /**
     * Run database seeds for Module 6: Recruitment & Applicant Tracking (ATS).
     * Strictly 1 seeder for this module.
     */
    public function run(): void
    {
        $engineering = Department::where('code', 'ENG')->first();
        $hr = Department::where('code', 'HR')->first();
        $design = Department::where('name', 'like', '%Product%')->first() ?? $engineering;

        $seniorDevDesig = Designation::where('code', 'ENG-SR-FS')->first() ?? Designation::first();
        $recruiterDesig = Designation::where('code', 'HR-TA-SPEC')->first() ?? Designation::skip(1)->first();

        // 1. Seed Active Job Openings (REQ-ATS-01)
        $jobs = [
            [
                'title' => 'Senior Full-Stack Laravel & Vue Engineer',
                'department_id' => $engineering?->id ?? 1,
                'designation_id' => $seniorDevDesig?->id,
                'slug' => 'senior-full-stack-laravel-engineer',
                'employment_type' => 'Full-Time',
                'experience_level' => 'Senior',
                'location' => 'Kuala Lumpur, Malaysia (Hybrid)',
                'openings_count' => 2,
                'description' => 'Architect and scale mission-critical HRMS & payroll feeder microservices with modern PHP 8.4, Laravel, and reactive Tailwind interfaces.',
                'requirements' => '5+ years experience in enterprise PHP/Laravel, RESTful microservices, MySQL/SQLite performance tuning, and automated test-driven development.',
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(20),
            ],
            [
                'title' => 'Talent Acquisition & HR Operations Specialist',
                'department_id' => $hr?->id ?? 1,
                'designation_id' => $recruiterDesig?->id,
                'slug' => 'talent-acquisition-specialist',
                'employment_type' => 'Full-Time',
                'experience_level' => 'Mid-Level',
                'location' => 'Kuala Lumpur, Malaysia (On-Site)',
                'openings_count' => 1,
                'description' => 'Lead technical recruitment pipelines, candidate interviews, onboarding governance, and payroll statutory coordination.',
                'requirements' => '3+ years corporate recruiting experience, familiar with Malaysian Employment Act and technical hiring benchmarks.',
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(15),
            ],
            [
                'title' => 'Senior UI/UX Design System Lead',
                'department_id' => $design?->id ?? 1,
                'designation_id' => null,
                'slug' => 'senior-ui-ux-design-lead',
                'employment_type' => 'Full-Time',
                'experience_level' => 'Lead',
                'location' => 'Remote / Flexible (Malaysia)',
                'openings_count' => 1,
                'description' => 'Establish unified atomic design system tokens, WCAG AA accessibility patterns, and interactive micro-animations.',
                'requirements' => 'Expertise in Figma design systems, TailwindCSS responsive tokens, and user journey mapping.',
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(10),
            ],
        ];

        $seededJobs = [];
        foreach ($jobs as $jobData) {
            $seededJobs[] = JobOpening::updateOrCreate(
                ['slug' => $jobData['slug']],
                $jobData
            );
        }

        $engJob = $seededJobs[0];
        $hrJob = $seededJobs[1];
        $designJob = $seededJobs[2];

        // 2. Seed Candidates across ATS Pipeline Stages (REQ-ATS-02)
        $applicants = [
            // Engineering Candidates
            [
                'job_opening_id' => $engJob->id,
                'first_name' => 'Farhan',
                'last_name' => 'Hafiz',
                'email' => 'farhan.hafiz@candidate.test',
                'phone' => '+60 11-2345 6789',
                'current_company' => 'Grab Holdings',
                'current_title' => 'Software Engineer II',
                'experience_years' => 6.5,
                'expected_salary' => 9500.00,
                'stage' => 'offer', // Candidate received job offer
                'rating' => 5,
                'interview_notes' => 'Outstanding performance during technical live coding assessment. Deep understanding of Redis and SQL indexing.',
                'applied_at' => Carbon::now()->subDays(14),
            ],
            [
                'job_opening_id' => $engJob->id,
                'first_name' => 'Nadia',
                'last_name' => 'Sulaiman',
                'email' => 'nadia.sulaiman@candidate.test',
                'phone' => '+60 17-8889 9001',
                'current_company' => 'Axiata Digital',
                'current_title' => 'Backend Developer',
                'experience_years' => 4.0,
                'expected_salary' => 7800.00,
                'stage' => 'interview', // Scheduled for System Design round
                'rating' => 4,
                'interview_notes' => 'Strong architectural fundamentals. Next round scheduled with CTO Alexander Vance.',
                'applied_at' => Carbon::now()->subDays(9),
            ],
            [
                'job_opening_id' => $engJob->id,
                'first_name' => 'Kenji',
                'last_name' => 'Tanaka',
                'email' => 'kenji.tanaka@candidate.test',
                'phone' => '+60 12-4455 6677',
                'current_company' => 'Rakuten Asia',
                'current_title' => 'Full-Stack Specialist',
                'experience_years' => 3.5,
                'expected_salary' => 7200.00,
                'stage' => 'screened',
                'rating' => 3,
                'interview_notes' => 'Resume matches all technical criteria. Recruiter phone screen scheduled.',
                'applied_at' => Carbon::now()->subDays(5),
            ],
            [
                'job_opening_id' => $engJob->id,
                'first_name' => 'Arun',
                'last_name' => 'Kumar',
                'email' => 'arun.kumar@candidate.test',
                'phone' => '+60 13-9991 2233',
                'current_company' => 'Fintech Labs',
                'current_title' => 'Junior Developer',
                'experience_years' => 1.5,
                'expected_salary' => 5500.00,
                'stage' => 'applied',
                'rating' => null,
                'interview_notes' => null,
                'applied_at' => Carbon::now()->subDays(2),
            ],

            // HR Specialist Candidate
            [
                'job_opening_id' => $hrJob->id,
                'first_name' => 'Melissa',
                'last_name' => 'Cheong',
                'email' => 'melissa.cheong@candidate.test',
                'phone' => '+60 19-3322 1100',
                'current_company' => 'Shopee Malaysia',
                'current_title' => 'Senior Recruiter',
                'experience_years' => 5.0,
                'expected_salary' => 6800.00,
                'stage' => 'interview',
                'rating' => 4,
                'interview_notes' => 'Extensive experience in high-volume tech hiring. Culture alignment round complete.',
                'applied_at' => Carbon::now()->subDays(7),
            ],

            // Design Lead Candidate
            [
                'job_opening_id' => $designJob->id,
                'first_name' => 'Zack',
                'last_name' => 'Fairuz',
                'email' => 'zack.fairuz@candidate.test',
                'phone' => '+60 16-7788 9900',
                'current_company' => 'Monstar Design Studio',
                'current_title' => 'Lead Product Designer',
                'experience_years' => 7.0,
                'expected_salary' => 10500.00,
                'stage' => 'offer',
                'rating' => 5,
                'interview_notes' => 'Phenomenal portfolio showcasing responsive web design systems and WCAG accessibility standards.',
                'applied_at' => Carbon::now()->subDays(8),
            ],
        ];

        foreach ($applicants as $appData) {
            JobApplicant::updateOrCreate(
                ['email' => $appData['email']],
                $appData
            );
        }
    }
}
