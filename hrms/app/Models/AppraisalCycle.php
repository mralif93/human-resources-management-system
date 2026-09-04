<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AppraisalCycle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'cycle_type',
        'start_date',
        'end_date',
        'due_date',
        'status',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'due_date' => 'date',
    ];

    public function reviews(): HasMany
    {
        return $this->hasMany(PerformanceReview::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
