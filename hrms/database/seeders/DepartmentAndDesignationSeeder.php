<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;

class DepartmentAndDesignationSeeder extends Seeder
{
    /**
     * Run seeds for Module 2: Personnel Information Management (PIM) and Org Structure.
     */
    public function run(): void
    {
        $adminUser = User::where('email', 'admin@hrms.test')->first();
        $hrUser = User::where('email', 'hr@hrms.test')->first();
        $managerUser = User::where('email', 'manager@hrms.test')->first();
        $empUser = User::where('email', 'employee@hrms.test')->first();

        // 1. Create Core Departments
        $deptExec = Department::updateOrCreate(
            ['code' => 'EXEC'],
            [
                'name' => 'Executive Office',
                'description' => 'Corporate leadership, C-suite, and organizational strategy.',
                'manager_id' => $adminUser?->id,
            ]
        );

        $deptHr = Department::updateOrCreate(
            ['code' => 'HR'],
            [
                'name' => 'Human Resources',
                'description' => 'Talent acquisition, employee welfare, relations, and compliance.',
                'manager_id' => $hrUser?->id,
            ]
        );

        $deptEng = Department::updateOrCreate(
            ['code' => 'ENG'],
            [
                'name' => 'Engineering & Technology',
                'description' => 'Software engineering, cloud infrastructure, and QA automation.',
                'manager_id' => $managerUser?->id,
            ]
        );

        $deptDesign = Department::updateOrCreate(
            ['code' => 'PROD'],
            [
                'name' => 'Product & Design',
                'description' => 'Product management, user experience, and visual brand identity.',
                'manager_id' => $managerUser?->id,
            ]
        );

        // 2. Create Designations
        $desigCto = Designation::updateOrCreate(
            ['code' => 'CTO-01'],
            [
                'department_id' => $deptExec->id,
                'title' => 'Chief Technology Officer',
                'description' => 'Executive technical leadership and governance.',
            ]
        );

        $desigHrLead = Designation::updateOrCreate(
            ['code' => 'HR-LEAD'],
            [
                'department_id' => $deptHr->id,
                'title' => 'People Operations Lead',
                'description' => 'Head of HR operations and employee policy.',
            ]
        );

        $desigVpEng = Designation::updateOrCreate(
            ['code' => 'ENG-VP'],
            [
                'department_id' => $deptEng->id,
                'title' => 'VP of Engineering',
                'description' => 'Oversees technical architecture and engineering managers.',
            ]
        );

        $desigUiLead = Designation::updateOrCreate(
            ['code' => 'UI-SR'],
            [
                'department_id' => $deptDesign->id,
                'title' => 'Senior UI/UX Designer',
                'description' => 'User interface prototyping and design system lead.',
            ]
        );

        // 3. Seed Master Employee Profiles with Encrypted PII
        $empAdmin = Employee::updateOrCreate(
            ['email' => 'admin@hrms.test'],
            [
                'user_id' => $adminUser?->id,
                'department_id' => $deptExec->id,
                'designation_id' => $desigCto->id,
                'employee_code' => 'EMP-2026-0001',
                'first_name' => 'Alexander',
                'last_name' => 'Vance',
                'phone' => '+60 12-345 6789',
                'national_id' => '850101-14-5561', // Encrypted via cast
                'date_of_birth' => '1985-01-01',
                'gender' => 'Male',
                'employment_status' => 'Permanent',
                'joining_date' => '2020-01-15',
                'branch_location' => 'Headquarters (Kuala Lumpur)',
                'bank_name' => 'Maybank',
                'bank_account_number' => '514012345678', // Encrypted via cast
                'basic_salary' => 15000.00,
            ]
        );

        $empHr = Employee::updateOrCreate(
            ['email' => 'hr@hrms.test'],
            [
                'user_id' => $hrUser?->id,
                'department_id' => $deptHr->id,
                'designation_id' => $desigHrLead->id,
                'manager_id' => $empAdmin->id,
                'employee_code' => 'EMP-2026-0002',
                'first_name' => 'Sarah',
                'last_name' => 'Jenkins',
                'phone' => '+60 13-987 6543',
                'national_id' => '900415-10-5824',
                'date_of_birth' => '1990-04-15',
                'gender' => 'Female',
                'employment_status' => 'Permanent',
                'joining_date' => '2021-03-01',
                'branch_location' => 'Headquarters (Kuala Lumpur)',
                'bank_name' => 'CIMB Bank',
                'bank_account_number' => '701234567890',
                'basic_salary' => 8500.00,
            ]
        );

        $empManager = Employee::updateOrCreate(
            ['email' => 'manager@hrms.test'],
            [
                'user_id' => $managerUser?->id,
                'department_id' => $deptEng->id,
                'designation_id' => $desigVpEng->id,
                'manager_id' => $empAdmin->id,
                'employee_code' => 'EMP-2026-0003',
                'first_name' => 'Marcus',
                'last_name' => 'Chen',
                'phone' => '+60 17-222 3344',
                'national_id' => '880820-08-6113',
                'date_of_birth' => '1988-08-20',
                'gender' => 'Male',
                'employment_status' => 'Permanent',
                'joining_date' => '2022-06-15',
                'branch_location' => 'Cyberjaya Technology Center',
                'bank_name' => 'Public Bank',
                'bank_account_number' => '319876543210',
                'basic_salary' => 12000.00,
            ]
        );

        Employee::updateOrCreate(
            ['email' => 'employee@hrms.test'],
            [
                'user_id' => $empUser?->id,
                'department_id' => $deptDesign->id,
                'designation_id' => $desigUiLead->id,
                'manager_id' => $empManager->id,
                'employee_code' => 'EMP-2026-0004',
                'first_name' => 'Emily',
                'last_name' => 'Watson',
                'phone' => '+60 19-444 5566',
                'national_id' => '951210-14-6332',
                'date_of_birth' => '1995-12-10',
                'gender' => 'Female',
                'employment_status' => 'Probation',
                'joining_date' => '2026-01-10',
                'branch_location' => 'Headquarters (Kuala Lumpur)',
                'bank_name' => 'Hong Leong Bank',
                'bank_account_number' => '246813579012',
                'basic_salary' => 5500.00,
            ]
        );
    }
}
