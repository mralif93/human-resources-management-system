<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplicant extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_opening_id',
        'employee_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'current_company',
        'current_title',
        'experience_years',
        'expected_salary',
        'resume_path',
        'stage',
        'rating',
        'interview_notes',
        'applied_at',
        'offered_salary',
        'joining_date',
        'probation_months',
        'notice_period_months',
        'allowances',
        'offer_remarks',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
        'joining_date' => 'date',
        'experience_years' => 'decimal:1',
        'expected_salary' => 'decimal:2',
        'offered_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'probation_months' => 'integer',
        'notice_period_months' => 'integer',
        'rating' => 'integer',
    ];

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function jobOpening(): BelongsTo
    {
        return $this->belongsTo(JobOpening::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Convert candidate to an active pre-onboarding employee record (REQ-ATS-03).
     */
    public function convertToEmployee(): Employee
    {
        if ($this->employee_id) {
            return $this->employee;
        }

        $employee = Employee::create([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'department_id' => $this->jobOpening->department_id,
            'designation_id' => $this->jobOpening->designation_id,
            'employment_status' => 'Probation',
            'joining_date' => $this->joining_date?->toDateString() ?? now()->toDateString(),
            'branch_location' => $this->jobOpening->location,
            'basic_salary' => $this->offered_salary ?? ($this->expected_salary ?? 5000.00),
        ]);

        $this->update([
            'employee_id' => $employee->id,
            'stage' => 'hired',
        ]);

        return $employee;
    }
}
