<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DepartmentAndDesignationSeeder;
use Database\Seeders\LeaveSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleBasedAccessControlTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $hrAdmin;
    private User $manager;
    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(DepartmentAndDesignationSeeder::class);
        $this->seed(LeaveSeeder::class);

        $this->superAdmin = User::where('email', 'admin@hrms.test')->first();
        $this->hrAdmin = User::where('email', 'hr@hrms.test')->first();
        $this->manager = User::where('email', 'manager@hrms.test')->first();
        $this->employee = User::where('email', 'employee@hrms.test')->first();
    }

    // 1. Dashboard: All roles can access
    public function test_all_authenticated_roles_can_access_dashboard(): void
    {
        $this->actingAs($this->superAdmin)->get(route('dashboard'))->assertStatus(200);
        $this->actingAs($this->hrAdmin)->get(route('dashboard'))->assertStatus(200);
        $this->actingAs($this->manager)->get(route('dashboard'))->assertStatus(200);
        $this->actingAs($this->employee)->get(route('dashboard'))->assertStatus(200);
    }

    // 2. Settings & Branding: Super Admin and HR Admin can access; Manager and Employee are Forbidden (403)
    public function test_settings_routes_restricted_to_super_admin_and_hr_admin(): void
    {
        // Allowed
        $this->actingAs($this->superAdmin)->get(route('settings.profile'))->assertStatus(200);
        $this->actingAs($this->hrAdmin)->get(route('settings.profile'))->assertStatus(200);
        $this->actingAs($this->superAdmin)->get(route('settings.shifts'))->assertStatus(200);
        $this->actingAs($this->hrAdmin)->get(route('settings.leave-types'))->assertStatus(200);
        $this->actingAs($this->hrAdmin)->get(route('settings.departments'))->assertStatus(200);
        $this->actingAs($this->hrAdmin)->get(route('settings.templates'))->assertStatus(200);

        // Denied (403)
        $this->actingAs($this->manager)->get(route('settings.profile'))->assertStatus(403);
        $this->actingAs($this->employee)->get(route('settings.profile'))->assertStatus(403);
        $this->actingAs($this->manager)->get(route('settings.shifts'))->assertStatus(403);
        $this->actingAs($this->employee)->get(route('settings.leave-types'))->assertStatus(403);
        $this->actingAs($this->employee)->get(route('settings.departments'))->assertStatus(403);
        $this->actingAs($this->employee)->get(route('settings.templates'))->assertStatus(403);
    }

    // 3. Connected Engines (Payroll Sync & Audit Logs): Super Admin & HR Admin allowed, others forbidden
    public function test_payroll_and_audit_restricted_from_manager_and_employee(): void
    {
        // Allowed
        $this->actingAs($this->superAdmin)->get(route('payroll-sync.index'))->assertStatus(200);
        $this->actingAs($this->hrAdmin)->get(route('payroll-sync.index'))->assertStatus(200);
        $this->actingAs($this->superAdmin)->get(route('audit-logs.index'))->assertStatus(200);
        $this->actingAs($this->hrAdmin)->get(route('audit-logs.index'))->assertStatus(200);

        // Denied (403)
        $this->actingAs($this->manager)->get(route('payroll-sync.index'))->assertStatus(403);
        $this->actingAs($this->employee)->get(route('payroll-sync.index'))->assertStatus(403);
        $this->actingAs($this->manager)->get(route('audit-logs.index'))->assertStatus(403);
        $this->actingAs($this->employee)->get(route('audit-logs.index'))->assertStatus(403);
    }

    // 4. Employee Master Directory (PIM) and Recruitment ATS: Employee is forbidden, Managers & Admins allowed
    public function test_pim_and_recruitment_restricted_from_general_employees(): void
    {
        // Allowed for Super Admin, HR Admin, Manager
        $this->actingAs($this->superAdmin)->get(route('employees.index'))->assertStatus(200);
        $this->actingAs($this->hrAdmin)->get(route('employees.index'))->assertStatus(200);
        $this->actingAs($this->manager)->get(route('employees.index'))->assertStatus(200);

        $this->actingAs($this->superAdmin)->get(route('recruitment.index'))->assertStatus(200);
        $this->actingAs($this->hrAdmin)->get(route('recruitment.index'))->assertStatus(200);
        $this->actingAs($this->manager)->get(route('recruitment.index'))->assertStatus(200);

        // Denied for general employee (403)
        $this->actingAs($this->employee)->get(route('employees.index'))->assertStatus(403);
        $this->actingAs($this->employee)->get(route('recruitment.index'))->assertStatus(403);
    }

    // 5. Sidebar Menu Visibility according to role
    public function test_sidebar_menu_visibility_per_role(): void
    {
        // General employee should not see Settings, Connected Engines, or Employee Master
        $response = $this->actingAs($this->employee)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertDontSee('Settings &amp; Branding', false);
        $response->assertDontSee('Connected Engines', false);
        $response->assertDontSee('Employee Master PIM', false);
        $response->assertSee('My Attendance');

        // Super Admin sees all groups
        $adminResponse = $this->actingAs($this->superAdmin)->get(route('dashboard'));
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee('Settings &amp; Branding', false);
        $adminResponse->assertSee('Connected Engines', false);
        $adminResponse->assertSee('Employee Master PIM', false);
        $adminResponse->assertSee('Recruitment ATS', false);
    }
}
