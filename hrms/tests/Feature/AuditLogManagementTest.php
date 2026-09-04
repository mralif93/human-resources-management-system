<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Database\Seeders\AuditTrailSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->adminUser = User::where('email', 'admin@hrms.test')->first();
    }

    public function test_guest_cannot_access_audit_logs(): void
    {
        $response = $this->get(route('audit-logs.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_admin_can_view_audit_logs(): void
    {
        $this->seed(AuditTrailSeeder::class);

        $response = $this->actingAs($this->adminUser)->get(route('audit-logs.index'));
        $response->assertStatus(200);
        $response->assertSee('Enterprise Activity Audit Trail', false);
        $response->assertSee('Immutable Log', false);
    }

    public function test_admin_can_filter_audit_logs_by_category(): void
    {
        $this->seed(AuditTrailSeeder::class);

        $response = $this->actingAs($this->adminUser)->get(route('audit-logs.index', ['category' => 'payroll']));
        $response->assertStatus(200);
        $response->assertSee('payroll.feeder.synced', false);
    }

    public function test_audit_trail_seeder_populates_verified_records(): void
    {
        $this->seed(AuditTrailSeeder::class);

        $this->assertDatabaseHas('audit_logs', ['category' => 'auth']);
        $this->assertDatabaseHas('audit_logs', ['category' => 'leaves']);
        $this->assertDatabaseHas('audit_logs', ['category' => 'performance']);
        $this->assertDatabaseHas('audit_logs', ['category' => 'payroll']);
    }
}
