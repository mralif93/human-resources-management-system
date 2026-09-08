<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $employee;
    protected Role $adminRole;
    protected Role $empRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminRole = Role::create([
            'name' => 'super_admin',
            'display_name' => 'Super Administrator',
            'is_system' => true,
        ]);

        $this->empRole = Role::create([
            'name' => 'employee',
            'display_name' => 'Employee',
            'is_system' => true,
        ]);

        $this->superAdmin = User::factory()->create([
            'name' => 'Root Admin',
            'email' => 'root@hrms.test',
            'role' => 'Super Admin',
            'status' => 'active',
        ]);
        $this->superAdmin->roles()->attach($this->adminRole);

        $this->employee = User::factory()->create([
            'name' => 'Normal Staff',
            'email' => 'staff@hrms.test',
            'role' => 'Employee',
            'status' => 'active',
        ]);
        $this->employee->roles()->attach($this->empRole);
    }

    public function test_super_admin_can_view_users_list(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('users.index'));

        $response->assertStatus(200);
        $response->assertSee('System User Management');
        $response->assertSee('root@hrms.test');
    }

    public function test_regular_employee_cannot_access_users_list(): void
    {
        $response = $this->actingAs($this->employee)->get(route('users.index'));

        $response->assertStatus(403);
    }

    public function test_super_admin_can_create_new_user(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('users.store'), [
            'name' => 'New Recruiter',
            'email' => 'recruiter@hrms.test',
            'employee_code' => 'EMP-REC-01',
            'password' => 'password123',
            'status' => 'active',
            'role_ids' => [$this->empRole->id],
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'recruiter@hrms.test',
            'employee_code' => 'EMP-REC-01',
        ]);
    }

    public function test_super_admin_can_toggle_user_status(): void
    {
        $targetUser = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->superAdmin)->post(route('users.toggle-status', $targetUser));

        $response->assertRedirect(route('users.index'));
        $this->assertEquals('suspended', $targetUser->fresh()->status);
    }

    public function test_super_admin_cannot_toggle_own_status(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('users.toggle-status', $this->superAdmin));

        $response->assertRedirect(route('users.index'));
        $this->assertEquals('active', $this->superAdmin->fresh()->status);
    }

    public function test_super_admin_can_reset_user_password(): void
    {
        $targetUser = User::factory()->create();

        $response = $this->actingAs($this->superAdmin)->post(route('users.reset-password', $targetUser), [
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertTrue(Hash::check('new-secret-password', $targetUser->fresh()->password));
    }
}
