<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
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
        // 1. Core Permissions Catalog (HRMS Modules)
        $permissions = [
            // PIM & Employees Module
            ['name' => 'pim.view', 'display_name' => 'View Employee Directory & Profiles', 'module' => 'pim'],
            ['name' => 'pim.create', 'display_name' => 'Create New Employee Record', 'module' => 'pim'],
            ['name' => 'pim.edit', 'display_name' => 'Edit Employee Profiles & Assignments', 'module' => 'pim'],
            ['name' => 'pim.delete', 'display_name' => 'Archive or Delete Employee Record', 'module' => 'pim'],

            // Attendance & Geofencing Module
            ['name' => 'attendance.view', 'display_name' => 'View Attendance & Punch Records', 'module' => 'attendance'],
            ['name' => 'attendance.punch', 'display_name' => 'Clock-In and Clock-Out Action', 'module' => 'attendance'],
            ['name' => 'attendance.manage', 'display_name' => 'Manage Shifts, Policies & Geofences', 'module' => 'attendance'],

            // Leaves & Time-off Module
            ['name' => 'leaves.view', 'display_name' => 'View Leave Requests & Balances', 'module' => 'leaves'],
            ['name' => 'leaves.apply', 'display_name' => 'Apply for Time-Off Request', 'module' => 'leaves'],
            ['name' => 'leaves.approve', 'display_name' => 'Approve or Reject Leave Applications', 'module' => 'leaves'],

            // Performance & OKRs Module
            ['name' => 'performance.view', 'display_name' => 'View Performance & OKRs', 'module' => 'performance'],
            ['name' => 'performance.manage', 'display_name' => 'Manage Appraisal Cycles & Reviews', 'module' => 'performance'],

            // Recruitment & ATS Module
            ['name' => 'recruitment.view', 'display_name' => 'View Job Openings & Pipeline', 'module' => 'recruitment'],
            ['name' => 'recruitment.manage', 'display_name' => 'Manage Job Postings & Candidates', 'module' => 'recruitment'],

            // Payroll Sync & Integration Module
            ['name' => 'payroll.view', 'display_name' => 'View Payroll Sync Status & Exports', 'module' => 'payroll_sync'],
            ['name' => 'payroll.export', 'display_name' => 'Export Feeder Data to PayFlow MY', 'module' => 'payroll_sync'],

            // Audit Trail Module
            ['name' => 'audit.view', 'display_name' => 'Inspect Security Audit Trails', 'module' => 'audit'],

            // Company Settings & Governance Module
            ['name' => 'settings.view', 'display_name' => 'View Organization Settings', 'module' => 'settings'],
            ['name' => 'settings.edit', 'display_name' => 'Modify Company & Template Settings', 'module' => 'settings'],
        ];

        foreach ($permissions as $p) {
            Permission::updateOrCreate(['name' => $p['name']], $p);
        }

        // 2. Roles Catalog
        $superAdminRole = Role::updateOrCreate(
            ['name' => 'super_admin'],
            [
                'display_name' => 'Super Administrator',
                'description' => 'Unrestricted universal access to all HRMS modules, settings, and audit logs.',
                'is_system' => true,
            ]
        );
        $superAdminRole->permissions()->sync(Permission::all());

        $hrAdminRole = Role::updateOrCreate(
            ['name' => 'hr_admin'],
            [
                'display_name' => 'HR Administrator',
                'description' => 'Full administration for employee lifecycle, attendance, leaves, recruitment, and payroll sync.',
                'is_system' => true,
            ]
        );
        $hrAdminRole->permissions()->sync(
            Permission::whereIn('name', [
                'pim.view', 'pim.create', 'pim.edit', 'pim.delete',
                'attendance.view', 'attendance.punch', 'attendance.manage',
                'leaves.view', 'leaves.apply', 'leaves.approve',
                'performance.view', 'performance.manage',
                'recruitment.view', 'recruitment.manage',
                'payroll.view', 'payroll.export',
                'audit.view',
                'settings.view',
            ])->pluck('id')
        );

        $managerRole = Role::updateOrCreate(
            ['name' => 'department_manager'],
            [
                'display_name' => 'Department Manager',
                'description' => 'Supervisory management for department team members, approvals, attendance, and reviews.',
                'is_system' => true,
            ]
        );
        $managerRole->permissions()->sync(
            Permission::whereIn('name', [
                'pim.view',
                'attendance.view', 'attendance.punch',
                'leaves.view', 'leaves.apply', 'leaves.approve',
                'performance.view', 'performance.manage',
                'recruitment.view',
            ])->pluck('id')
        );

        $employeeRole = Role::updateOrCreate(
            ['name' => 'employee'],
            [
                'display_name' => 'Employee',
                'description' => 'Standard employee self-service access for clocking, leave requests, and profile view.',
                'is_system' => true,
            ]
        );
        $employeeRole->permissions()->sync(
            Permission::whereIn('name', [
                'pim.view',
                'attendance.punch',
                'leaves.apply',
                'performance.view',
            ])->pluck('id')
        );

        // 3. Seed Users and Associate Roles
        // 1. Super Admin
        $admin = User::updateOrCreate(
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
        $admin->roles()->sync([$superAdminRole->id]);

        // 2. HR Administrator
        $hr = User::updateOrCreate(
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
        $hr->roles()->sync([$hrAdminRole->id]);

        // 3. Department Manager
        $manager = User::updateOrCreate(
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
        $manager->roles()->sync([$managerRole->id]);

        // 4. General Employee
        $emp = User::updateOrCreate(
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
        $emp->roles()->sync([$employeeRole->id]);
    }
}
