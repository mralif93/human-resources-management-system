<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class JobOpening extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'designation_id',
        'title',
        'slug',
        'employment_type',
        'experience_level',
        'location',
        'openings_count',
        'description',
        'requirements',
        'status',
        'published_at',
        'closed_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'closed_at' => 'datetime',
        'openings_count' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (JobOpening $job) {
            if (empty($job->slug)) {
                $job->slug = Str::slug($job->title) . '-' . Str::random(5);
            }
        });
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function applicants(): HasMany
    {
        return $this->hasMany(JobApplicant::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
