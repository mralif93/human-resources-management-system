<?php

namespace Tests\Unit;

use App\Models\AuditLog;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_audit_log_captures_attributes(): void
    {
        $this->seed(UserSeeder::class);
        $user = User::first();

        $log = AuditLog::record(
            'security.test_event',
            'security',
            'Test automated security audit record',
            ['key' => 'value'],
            $user->id
        );

        $this->assertNotNull($log->id);
        $this->assertEquals('security.test_event', $log->event);
        $this->assertEquals('security', $log->category);
        $this->assertEquals($user->id, $log->user_id);
        $this->assertEquals(['key' => 'value'], $log->payload);
        $this->assertEquals('127.0.0.1', $log->ip_address);
    }
}
