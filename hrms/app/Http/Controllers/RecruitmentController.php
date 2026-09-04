<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Designation;
use App\Models\JobApplicant;
use App\Models\JobOpening;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecruitmentController extends Controller
{
    /**
     * Display Recruitment dashboard, jobs roster, and Kanban candidate pipeline.
     */
    public function index(Request $request): View
    {
        $selectedJobId = $request->input('job_id');
        $search = $request->input('search');
        $activeTab = $request->input('tab', 'kanban'); // 'kanban' or 'jobs'

        $jobs = JobOpening::with(['department', 'designation', 'applicants'])->latest()->get();
        $activeJob = $selectedJobId ? JobOpening::find($selectedJobId) : null;

        // Applicant Query for Pipeline
        $applicantQuery = JobApplicant::with(['jobOpening.department', 'employee'])
            ->when($activeJob, fn($q) => $q->where('job_opening_id', $activeJob->id))
            ->latest();

        if ($search) {
            $applicantQuery->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('current_company', 'like', "%{$search}%");
            });
        }

        $allApplicants = $applicantQuery->get();

        // Categorize into Kanban Swimlanes (REQ-ATS-02)
        $kanban = [
            'applied' => $allApplicants->where('stage', 'applied'),
            'screened' => $allApplicants->where('stage', 'screened'),
            'interview' => $allApplicants->where('stage', 'interview'),
            'offer' => $allApplicants->where('stage', 'offer'),
            'hired' => $allApplicants->where('stage', 'hired'),
        ];

        // High Level Metrics
        $totalOpenings = JobOpening::where('status', 'published')->sum('openings_count');
        $totalCandidates = JobApplicant::count();
        $interviewsCount = JobApplicant::where('stage', 'interview')->count();
        $hiredCount = JobApplicant::where('stage', 'hired')->count();

        $departments = Department::active()->orderBy('name')->get();
        $designations = Designation::orderBy('title')->get();

        return view('admin.recruitment.index', compact(
            'jobs',
            'activeJob',
            'kanban',
            'allApplicants',
            'activeTab',
            'totalOpenings',
            'totalCandidates',
            'interviewsCount',
            'hiredCount',
            'departments',
            'designations'
        ));
    }

    /**
     * Post a new job vacancy (REQ-ATS-01).
     */
    public function storeJob(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'title' => 'required|string|max:255',
            'employment_type' => 'required|string|in:Full-Time,Part-Time,Contract,Internship',
            'experience_level' => 'required|string|in:Junior,Mid-Level,Senior,Lead,Executive',
            'location' => 'required|string|max:255',
            'openings_count' => 'required|integer|min:1|max:50',
            'description' => 'required|string|max:5000',
            'requirements' => 'nullable|string|max:3000',
        ]);

        $job = JobOpening::create(array_merge($validated, [
            'status' => 'published',
            'published_at' => now(),
        ]));

        return redirect()->route('recruitment.index', ['tab' => 'jobs'])->with('status', "Job opening '{$job->title}' has been published successfully.");
    }

    /**
     * Intake a new candidate applicant.
     */
    public function storeApplicant(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'job_opening_id' => 'required|exists:job_openings,id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'current_company' => 'nullable|string|max:150',
            'current_title' => 'nullable|string|max:150',
            'experience_years' => 'required|numeric|min:0|max:50',
            'expected_salary' => 'nullable|numeric|min:0',
            'stage' => 'required|string|in:applied,screened,interview,offer,hired',
        ]);

        $applicant = JobApplicant::create($validated);

        return redirect()->route('recruitment.index', ['tab' => 'kanban', 'job_id' => $applicant->job_opening_id])
            ->with('status', "Candidate {$applicant->full_name} added to pipeline.");
    }

    /**
     * Move candidate to another stage on the Kanban board (REQ-ATS-02).
     */
    public function updateStage(Request $request, JobApplicant $applicant): RedirectResponse
    {
        $validated = $request->validate([
            'stage' => 'required|string|in:applied,screened,interview,offer,hired,rejected',
            'rating' => 'nullable|integer|min:1|max:5',
            'interview_notes' => 'nullable|string|max:2000',
        ]);

        $applicant->update($validated);

        return back()->with('status', "Candidate {$applicant->full_name} moved to " . ucfirst($applicant->stage) . " stage.");
    }

    /**
     * One-click conversion from Hired Candidate to Employee (REQ-ATS-03).
     */
    public function convertToEmployee(Request $request, JobApplicant $applicant): RedirectResponse
    {
        if ($applicant->employee_id) {
            return back()->with('error', "Candidate {$applicant->full_name} has already been converted to an employee.");
        }

        $employee = $applicant->convertToEmployee();

        return redirect()->route('employees.show', $employee)
            ->with('status', "Candidate {$applicant->full_name} successfully converted to Employee {$employee->employee_code}!");
    }

    /**
     * Update candidate-specific offer letter terms (salary, joining date, probation, allowances, custom remarks).
     */
    public function updateOfferDetails(Request $request, JobApplicant $applicant): RedirectResponse
    {
        $validated = $request->validate([
            'offered_salary' => 'required|numeric|min:0',
            'joining_date' => 'nullable|date',
            'probation_months' => 'nullable|integer|min:1|max:12',
            'notice_period_months' => 'nullable|integer|min:1|max:12',
            'allowances' => 'nullable|numeric|min:0',
            'offer_remarks' => 'nullable|string|max:2000',
        ]);

        $applicant->update($validated);

        return back()->with('status', "Offer letter terms for {$applicant->full_name} have been updated successfully.");
    }

    /**
     * Generate printable / viewable formal employment offer letter.
     */
    public function offerLetter(JobApplicant $applicant): View
    {
        $applicant->load(['jobOpening.department', 'jobOpening.designation']);
        $companyProfile = \App\Models\CompanyProfile::current();

        return view('admin.recruitment.offer-letter', compact('applicant', 'companyProfile'));
    }
}
