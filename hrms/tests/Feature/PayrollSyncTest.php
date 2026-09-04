<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\PayrollSyncToken;
use App\Models\User;
use Database\Seeders\AttendanceSeeder;
use Database\Seeders\DepartmentAndDesignationSeeder;
use Database\Seeders\LeaveSeeder;
use Database\Seeders\PayrollSyncSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollSyncTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private PayrollSyncToken $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->seed(DepartmentAndDesignationSeeder::class);
        $this->adminUser = User::where('email', 'admin@hrms.test')->first();

        $this->token = PayrollSyncToken::createToken('PayFlow Test');
    }

    public function test_api_endpoint_rejects_unauthorized_requests(): void
    {
        // No header
        $response = $this->getJson(route('api.payroll.feeder'));
        $response->assertStatus(401);
        $response->assertJson(['status' => 'error']);

        // Invalid Bearer token
        $response2 = $this->withHeader('Authorization', 'Bearer invalid_token_123')
            ->getJson(route('api.payroll.feeder'));
        $response2->assertStatus(403);
    }

    public function test_api_endpoint_outputs_payroll_feeder_json(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token->token)
            ->getJson(route('api.payroll.feeder', ['month' => date('Y-m')]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'payroll_period',
            'client_system',
            'records_count',
            'data' => [
                '*' => [
                    'employee_id',
                    'employee_code',
                    'full_name',
                    'basic_salary',
                    'verified_work_hours',
                    'overtime_hours',
                    'unpaid_leave_days',
                    'unpaid_leave_deduction',
                ]
            ]
        ]);
        $this->assertNotNull($this->token->fresh()->last_used_at);
    }

    public function test_guest_cannot_access_payroll_sync_hub(): void
    {
        $response = $this->get(route('payroll-sync.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_admin_can_view_payroll_sync_hub(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('payroll-sync.index'));
        $response->assertStatus(200);
        $response->assertSee('Payroll Feeder &amp; External Sync Hub', false);
        $response->assertSee('PayFlow MY Connected', false);
    }

    public function test_admin_can_export_csv_feeder_dataset(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('payroll-sync.export', ['month' => date('Y-m')]));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_generate_and_toggle_feeder_tokens(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('payroll-sync.tokens.store'), [
            'name' => 'PayFlow Cloud Instance',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('payroll_sync_tokens', ['name' => 'PayFlow Cloud Instance', 'is_active' => true]);

        $newToken = PayrollSyncToken::where('name', 'PayFlow Cloud Instance')->first();
        $responseToggle = $this->actingAs($this->adminUser)->post(route('payroll-sync.tokens.toggle', $newToken));
        $responseToggle->assertRedirect();
        $this->assertFalse($newToken->fresh()->is_active);
    }

    public function test_payroll_sync_seeder_populates_verified_records(): void
    {
        $this->seed(PayrollSyncSeeder::class);

        $this->assertDatabaseHas('payroll_sync_tokens', ['name' => 'PayFlow MY Production Engine']);
        $this->assertDatabaseHas('payroll_export_logs', ['export_format' => 'csv']);
    }
}
