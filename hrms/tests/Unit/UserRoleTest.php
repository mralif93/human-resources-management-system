<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserRoleTest extends TestCase
{
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
}
