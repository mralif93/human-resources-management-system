<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds for Module 1: Authentication, Security & RBAC.
     */
    public function run(): void
    {
        // 1. Super Admin
        User::updateOrCreate(
            ['email' => 'admin@hrms.test'],
            [
                'name' => 'Alexander Vance',
                'role' => 'Super Admin',
                'department' => 'Executive Office',
                'job_title' => 'Chief Technology Officer & Admin',
                'employee_code' => 'EMP-2026-0001',
                'phone' => '+1 (555) 019-2831',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // 2. HR Administrator
        User::updateOrCreate(
            ['email' => 'hr@hrms.test'],
            [
                'name' => 'Sarah Jenkins',
                'role' => 'HR Administrator',
                'department' => 'Human Resources',
                'job_title' => 'People Operations Lead',
                'employee_code' => 'EMP-2026-0002',
                'phone' => '+1 (555) 019-4421',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // 3. Department Manager
        User::updateOrCreate(
            ['email' => 'manager@hrms.test'],
            [
                'name' => 'Marcus Chen',
                'role' => 'Department Manager',
                'department' => 'Engineering',
                'job_title' => 'VP of Engineering',
                'employee_code' => 'EMP-2026-0003',
                'phone' => '+1 (555) 019-8832',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // 4. General Employee
        User::updateOrCreate(
            ['email' => 'employee@hrms.test'],
            [
                'name' => 'Emily Watson',
                'role' => 'Employee',
                'department' => 'Product & Design',
                'job_title' => 'Senior UI/UX Designer',
                'employee_code' => 'EMP-2026-0004',
                'phone' => '+1 (555) 019-7104',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
    }
}
