<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'appraisal_cycle_id',
        'employee_id',
        'reviewer_id',
        'self_score',
        'manager_score',
        'final_rating',
        'self_remarks',
        'manager_feedback',
        'key_achievements',
        'areas_for_improvement',
        'status',
        'submitted_at',
        'reviewed_at',
    ];

    protected $casts = [
        'self_score' => 'decimal:1',
        'manager_score' => 'decimal:1',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function appraisalCycle(): BelongsTo
    {
        return $this->belongsTo(AppraisalCycle::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reviewer_id');
    }

    /**
     * Compute standardized final rating from numeric manager score.
     */
    public static function ratingFromScore(float $score): string
    {
        return match (true) {
            $score >= 4.5 => 'Outstanding',
            $score >= 3.8 => 'Exceeds Expectations',
            $score >= 2.8 => 'Meets Expectations',
            $score >= 2.0 => 'Needs Improvement',
            default => 'Unsatisfactory',
        };
    }
}
