<?php

namespace App\Http\Controllers;

use App\Models\AppraisalCycle;
use App\Models\Employee;
use App\Models\EmployeeOkr;
use App\Models\PerformanceReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PerformanceController extends Controller
{
    /**
     * Display performance appraisals, OKR tracking matrix, and KPI metrics.
     */
    public function index(Request $request): View
    {
        $selectedCycleId = $request->input('cycle_id');
        $search = $request->input('search');
        $status = $request->input('status');
        $activeTab = $request->input('tab', 'reviews'); // 'reviews' or 'okrs'
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100]) ? (int) $request->input('per_page') : 10;

        $cycles = AppraisalCycle::latest('start_date')->get();
        $activeCycle = $selectedCycleId ? AppraisalCycle::find($selectedCycleId) : $cycles->first();

        // Performance Reviews Query
        $reviewQuery = PerformanceReview::with(['employee.department', 'employee.designation', 'reviewer', 'appraisalCycle'])
            ->when($activeCycle, fn($q) => $q->where('appraisal_cycle_id', $activeCycle->id))
            ->latest('updated_at');

        if ($search) {
            $reviewQuery->whereHas('employee', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $reviewQuery->where('status', $status);
        }

        $reviews = $reviewQuery->paginate($perPage, ['*'], 'review_page')->withQueryString();

        // OKRs Query
        $okrQuery = EmployeeOkr::with(['employee.department', 'employee.designation'])
            ->where('year', (int) date('Y'))
            ->latest('progress_percentage');

        if ($search) {
            $okrQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('key_result_metric', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($sq) use ($search) {
                      $sq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $okrs = $okrQuery->get();

        // High Level Metrics
        $totalReviews = PerformanceReview::when($activeCycle, fn($q) => $q->where('appraisal_cycle_id', $activeCycle->id))->count();
        $pendingEvals = PerformanceReview::when($activeCycle, fn($q) => $q->where('appraisal_cycle_id', $activeCycle->id))->whereIn('status', ['draft', 'submitted'])->count();
        
        $avgScore = PerformanceReview::when($activeCycle, fn($q) => $q->where('appraisal_cycle_id', $activeCycle->id))
            ->whereNotNull('manager_score')
            ->avg('manager_score');

        $totalOkrs = EmployeeOkr::where('year', (int) date('Y'))->count();
        $completedOkrs = EmployeeOkr::where('year', (int) date('Y'))->where('status', 'completed')->count();
        $okrCompletionRate = $totalOkrs > 0 ? (int) round(($completedOkrs / $totalOkrs) * 100) : 0;

        $employees = Employee::orderBy('first_name')->get();

        return view('admin.performance.index', compact(
            'cycles',
            'activeCycle',
            'reviews',
            'okrs',
            'totalReviews',
            'pendingEvals',
            'avgScore',
            'okrCompletionRate',
            'activeTab',
            'employees'
        ));
    }

    /**
     * Submit an appraisal evaluation (manager grading & remarks).
     */
    public function evaluate(Request $request, PerformanceReview $review): RedirectResponse
    {
        $validated = $request->validate([
            'manager_score' => 'required|numeric|min:1.0|max:5.0',
            'manager_feedback' => 'required|string|max:2000',
            'key_achievements' => 'nullable|string|max:1000',
            'areas_for_improvement' => 'nullable|string|max:1000',
        ]);

        $reviewer = Employee::where('user_id', auth()->id())->first() ?? Employee::first();

        $rating = PerformanceReview::ratingFromScore((float) $validated['manager_score']);

        $review->update([
            'reviewer_id' => $reviewer?->id,
            'manager_score' => $validated['manager_score'],
            'final_rating' => $rating,
            'manager_feedback' => $validated['manager_feedback'],
            'key_achievements' => $validated['key_achievements'] ?? $review->key_achievements,
            'areas_for_improvement' => $validated['areas_for_improvement'] ?? $review->areas_for_improvement,
            'status' => 'reviewed',
            'reviewed_at' => now(),
        ]);

        return redirect()->route('performance.index', ['tab' => 'reviews'])->with('status', "Performance review for {$review->employee->full_name} completed with rating: {$rating}.");
    }

    /**
     * Create a new employee OKR goal.
     */
    public function storeOkr(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'quarter' => 'required|string|in:Q1,Q2,Q3,Q4,Annual',
            'year' => 'required|integer|min:2020|max:2035',
            'title' => 'required|string|max:255',
            'key_result_metric' => 'required|string|max:255',
            'target_value' => 'required|numeric|min:0.01',
            'current_value' => 'required|numeric|min:0',
        ]);

        $okr = EmployeeOkr::create([
            'employee_id' => $validated['employee_id'],
            'quarter' => $validated['quarter'],
            'year' => $validated['year'],
            'title' => $validated['title'],
            'key_result_metric' => $validated['key_result_metric'],
            'target_value' => $validated['target_value'],
            'current_value' => $validated['current_value'],
        ]);

        $okr->updateProgress((float) $validated['current_value']);

        return redirect()->route('performance.index', ['tab' => 'okrs'])->with('status', "OKR goal '{$okr->title}' registered successfully.");
    }

    /**
     * Update progress on an existing OKR.
     */
    public function updateOkrProgress(Request $request, EmployeeOkr $okr): RedirectResponse
    {
        $validated = $request->validate([
            'current_value' => 'required|numeric|min:0',
        ]);

        $okr->updateProgress((float) $validated['current_value']);

        return redirect()->route('performance.index', ['tab' => 'okrs'])->with('status', "Progress for OKR '{$okr->title}' updated to {$okr->progress_percentage}%.");
    }
}
