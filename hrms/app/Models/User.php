<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'department', 'job_title', 'designation', 'employee_code', 'avatar', 'phone', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * User Roles Relationship (Many-to-Many via user_roles)
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * Associated Employee Master Profile (if linked)
     */
    public function employee(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Dynamic role attribute for backward compatibility with existing Blade layouts and views.
     * Returns the assigned role display_name or fallback to stored/default role.
     */
    public function getRoleAttribute(): string
    {
        if ($this->relationLoaded('roles')) {
            $firstRole = $this->roles->first();
            if ($firstRole) {
                return $firstRole->display_name;
            }
        } elseif ($this->exists) {
            $firstRole = $this->roles()->first();
            if ($firstRole) {
                return $firstRole->display_name;
            }
        }

        return $this->attributes['role'] ?? 'Employee';
    }

    /**
     * Check if user has a specific role by name or display_name.
     */
    public function hasRole(string|array $roles): bool
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }

        // Check assigned relation if model exists in database
        if ($this->exists) {
            $slugs = array_map(function ($r) {
                return strtolower(str_replace(' ', '_', trim($r)));
            }, $roles);

            $hasDbRole = $this->roles()
                ->where(function ($query) use ($roles, $slugs) {
                    $query->whereIn('name', $roles)
                        ->orWhereIn('name', $slugs)
                        ->orWhereIn('display_name', $roles);
                })
                ->exists();

            if ($hasDbRole) {
                return true;
            }
        }

        // Check model attributes role if set (e.g. unpersisted instance or legacy)
        $attrRole = $this->attributes['role'] ?? null;
        if ($attrRole) {
            $attrSlug = strtolower(str_replace(' ', '_', trim($attrRole)));
            foreach ($roles as $r) {
                $rSlug = strtolower(str_replace(' ', '_', trim($r)));
                if ($attrRole === $r || $attrSlug === $rSlug) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if user has a specific permission via assigned roles.
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->exists) {
            return false;
        }

        return $this->roles()
            ->whereHas('permissions', fn ($query) => $query->where('name', $permission))
            ->exists();
    }

    /**
     * Check if user has Super Admin privileges.
     */
    public function isSuperAdmin(): bool
    {
        return ($this->attributes['role'] ?? '') === 'Super Admin'
            || $this->hasRole(['super_admin', 'Super Admin', 'Super Administrator']);
    }

    /**
     * Check if user is HR Administrator.
     */
    public function isHrAdmin(): bool
    {
        return ($this->attributes['role'] ?? '') === 'HR Administrator'
            || $this->hasRole(['hr_admin', 'HR Administrator']);
    }

    /**
     * Check if user is Department Manager.
     */
    public function isManager(): bool
    {
        return ($this->attributes['role'] ?? '') === 'Department Manager'
            || $this->hasRole(['department_manager', 'Department Manager']);
    }

    /**
     * Check if user is General Employee.
     */
    public function isEmployee(): bool
    {
        return ($this->attributes['role'] ?? '') === 'Employee'
            || $this->hasRole(['employee', 'Employee']);
    }
}
