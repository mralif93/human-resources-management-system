<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DepartmentAndDesignationSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_seeder_populates_four_standard_roles(): void
    {
        $this->seed(UserSeeder::class);

        $this->assertDatabaseHas('users', ['email' => 'admin@hrms.test', 'role' => 'Super Admin']);
        $this->assertDatabaseHas('users', ['email' => 'hr@hrms.test', 'role' => 'HR Administrator']);
        $this->assertDatabaseHas('users', ['email' => 'manager@hrms.test', 'role' => 'Department Manager']);
        $this->assertDatabaseHas('users', ['email' => 'employee@hrms.test', 'role' => 'Employee']);
    }

    public function test_department_and_designation_seeder_populates_org_structure_and_employees(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(DepartmentAndDesignationSeeder::class);

        // Assert departments
        $this->assertDatabaseHas('departments', ['code' => 'EXEC']);
        $this->assertDatabaseHas('departments', ['code' => 'HR']);
        $this->assertDatabaseHas('departments', ['code' => 'ENG']);
        $this->assertDatabaseHas('departments', ['code' => 'PROD']);

        // Assert designations
        $this->assertDatabaseHas('designations', ['code' => 'CTO-01']);
        $this->assertDatabaseHas('designations', ['code' => 'HR-LEAD']);
        $this->assertDatabaseHas('designations', ['code' => 'ENG-VP']);
        $this->assertDatabaseHas('designations', ['code' => 'UI-SR']);

        // Assert master employee profiles
        $this->assertDatabaseHas('employees', ['email' => 'admin@hrms.test', 'employee_code' => 'EMP-2026-0001']);
        $this->assertDatabaseHas('employees', ['email' => 'hr@hrms.test', 'employee_code' => 'EMP-2026-0002']);
        $this->assertDatabaseHas('employees', ['email' => 'manager@hrms.test', 'employee_code' => 'EMP-2026-0003']);
        $this->assertDatabaseHas('employees', ['email' => 'employee@hrms.test', 'employee_code' => 'EMP-2026-0004']);
    }

    public function test_database_master_seeder_orchestrator(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertEquals(4, User::count());
        $this->assertEquals(4, Department::count());
        $this->assertEquals(4, Designation::count());
        $this->assertEquals(4, Employee::count());
    }
}
