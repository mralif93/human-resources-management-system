<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
     * Check if user has Super Admin privileges.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'Super Admin';
    }

    /**
     * Check if user is HR Administrator.
     */
    public function isHrAdmin(): bool
    {
        return $this->role === 'HR Administrator';
    }

    /**
     * Check if user is Department Manager.
     */
    public function isManager(): bool
    {
        return $this->role === 'Department Manager';
    }

    /**
     * Check if user is General Employee.
     */
    public function isEmployee(): bool
    {
        return $this->role === 'Employee';
    }
}
