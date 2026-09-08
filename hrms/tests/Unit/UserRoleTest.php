<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    use RefreshDatabase;
    public function test_super_admin_role_check(): void
    {
        $user = new User(['role' => 'Super Admin']);

        $this->assertTrue($user->isSuperAdmin());
        $this->assertFalse($user->isHrAdmin());
        $this->assertFalse($user->isManager());
        $this->assertFalse($user->isEmployee());
    }

    public function test_hr_admin_role_check(): void
    {
        $user = new User(['role' => 'HR Administrator']);

        $this->assertTrue($user->isHrAdmin());
        $this->assertFalse($user->isSuperAdmin());
        $this->assertFalse($user->isManager());
        $this->assertFalse($user->isEmployee());
    }

    public function test_department_manager_role_check(): void
    {
        $user = new User(['role' => 'Department Manager']);

        $this->assertTrue($user->isManager());
        $this->assertFalse($user->isSuperAdmin());
        $this->assertFalse($user->isHrAdmin());
        $this->assertFalse($user->isEmployee());
    }

    public function test_employee_role_check(): void
    {
        $user = new User(['role' => 'Employee']);

        $this->assertTrue($user->isEmployee());
        $this->assertFalse($user->isSuperAdmin());
        $this->assertFalse($user->isHrAdmin());
        $this->assertFalse($user->isManager());
    }

    public function test_role_and_permission_relationships(): void
    {
        $role = \App\Models\Role::create([
            'name' => 'talent_scout',
            'display_name' => 'Talent Scout',
            'description' => 'Recruiter role',
            'is_system' => false,
        ]);

        $permission = \App\Models\Permission::create([
            'name' => 'recruitment.scout',
            'display_name' => 'Scout Candidates',
            'module' => 'recruitment',
        ]);

        $role->permissions()->attach($permission->id);

        $user = User::factory()->create([
            'name' => 'John Scout',
            'email' => 'scout@hrms.test',
        ]);

        $user->roles()->attach($role->id);

        $this->assertTrue($user->hasRole('talent_scout'));
        $this->assertTrue($user->hasRole('Talent Scout'));
        $this->assertTrue($user->hasPermission('recruitment.scout'));
        $this->assertFalse($user->hasPermission('payroll.view'));
        $this->assertEquals('Talent Scout', $user->role);
    }
}
