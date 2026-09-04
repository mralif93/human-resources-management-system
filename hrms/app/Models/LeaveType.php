<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'days_allowed',
        'is_paid',
        'requires_attachment',
        'is_active',
        'color',
        'description',
    ];

    protected $casts = [
        'days_allowed' => 'decimal:1',
        'is_paid' => 'boolean',
        'requires_attachment' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function balances(): HasMany
    {
        return $this->hasMany(EmployeeLeaveBalance::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(LeaveApplication::class);
    }
}
