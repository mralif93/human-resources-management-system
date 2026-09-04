<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeOkr extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'quarter',
        'year',
        'title',
        'key_result_metric',
        'target_value',
        'current_value',
        'progress_percentage',
        'status',
    ];

    protected $casts = [
        'year' => 'integer',
        'target_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'progress_percentage' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Recalculate progress percentage and update status.
     */
    public function updateProgress(float $currentVal): void
    {
        $this->current_value = $currentVal;
        if ($this->target_value > 0) {
            $pct = min(100, (int) round(($this->current_value / $this->target_value) * 100));
            $this->progress_percentage = max(0, $pct);
        } else {
            $this->progress_percentage = 100;
        }

        if ($this->progress_percentage >= 100) {
            $this->status = 'completed';
        } elseif ($this->progress_percentage >= 70) {
            $this->status = 'on_track';
        } elseif ($this->progress_percentage >= 40) {
            $this->status = 'at_risk';
        } else {
            $this->status = 'behind';
        }

        $this->save();
    }
}
