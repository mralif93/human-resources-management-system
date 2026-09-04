<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_with_standard_branding_and_modules(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Pulse');
        $response->assertSee('HR');
        $response->assertSee('Precision Workforce Governance');
        $response->assertSee('Employee Lifecycle (PIM)');
        $response->assertSee('Geofenced Attendance');
        $response->assertSee('Leave &amp; Absence Governance', false);
        $response->assertSee('External Payroll Feeder');
        $response->assertSee('Role-Based Access Matrix (RBAC)');
        $response->assertSee('toggleTheme()');
    }

    public function test_forgot_password_page_renders_with_recovery_form(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
        $response->assertSee('Recover Password');
        $response->assertSee('Send Reset Instructions');
    }

    public function test_forgot_password_handles_submission(): void
    {
        $response = $this->post('/forgot-password', [
            'email' => 'admin@hrms.test',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('status');
    }

    public function test_dashboard_renders_admin_layout_and_kpis(): void
    {
        $user = User::factory()->create([
            'name' => 'Sarah Jenkins',
            'role' => 'HR Administrator',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Sarah Jenkins');
        $response->assertSee('HR Administrator');
        $response->assertSee('Active Workforce');
        $response->assertSee('Digital Punch Terminal');
        $response->assertSee('Active Personnel Directory');
        $response->assertSee('PayFlow MY');
    }
}
