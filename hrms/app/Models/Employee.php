<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'department_id',
        'designation_id',
        'manager_id',
        'employee_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'national_id',
        'date_of_birth',
        'gender',
        'employment_status',
        'joining_date',
        'confirmation_date',
        'termination_date',
        'branch_location',
        'bank_name',
        'bank_account_number',
        'basic_salary',
    ];

    /**
     * The attributes that should be cast.
     * Enforcing Laravel's Crypt facade via 'encrypted' cast for PII and bank details (REQ-PIM-01, TC-PIM-03).
     */
    protected function casts(): array
    {
        return [
            'national_id' => 'encrypted',
            'bank_account_number' => 'encrypted',
            'date_of_birth' => 'date',
            'joining_date' => 'date',
            'confirmation_date' => 'date',
            'termination_date' => 'date',
            'basic_salary' => 'decimal:2',
        ];
    }

    /**
     * Auto-generate sequential Employee Code (EMP-YYYY-XXXX) during creation (TC-PIM-01).
     */
    protected static function booted(): void
    {
        static::creating(function (Employee $employee) {
            if (empty($employee->employee_code)) {
                $year = date('Y');
                $count = static::whereYear('created_at', $year)->count() + 1;
                $employee->employee_code = sprintf('EMP-%s-%04d', $year, $count);
            }
        });
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(EmployeeLeaveBalance::class);
    }

    public function leaveApplications(): HasMany
    {
        return $this->hasMany(LeaveApplication::class);
    }

    public function performanceReviews(): HasMany
    {
        return $this->hasMany(PerformanceReview::class);
    }

    public function okrs(): HasMany
    {
        return $this->hasMany(EmployeeOkr::class);
    }

    public function jobApplicant(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(JobApplicant::class);
    }
}
