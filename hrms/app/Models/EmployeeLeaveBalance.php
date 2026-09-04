<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'year',
        'entitled_days',
        'used_days',
        'pending_days',
        'remaining_days',
    ];

    protected $casts = [
        'year' => 'integer',
        'entitled_days' => 'decimal:1',
        'used_days' => 'decimal:1',
        'pending_days' => 'decimal:1',
        'remaining_days' => 'decimal:1',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * Deduct approved days from balance.
     */
    public function deductDays(float $days): void
    {
        $this->used_days += $days;
        $this->pending_days = max(0, $this->pending_days - $days);
        $this->remaining_days = max(0, $this->entitled_days - $this->used_days);
        $this->save();
    }

    /**
     * Reserve days while application is pending.
     */
    public function reserveDays(float $days): void
    {
        $this->pending_days += $days;
        $this->save();
    }

    /**
     * Release reserved days upon rejection or cancellation.
     */
    public function releasePendingDays(float $days): void
    {
        $this->pending_days = max(0, $this->pending_days - $days);
        $this->save();
    }
}
