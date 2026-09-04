<?php

namespace Tests\Unit;

use App\Models\PayrollSyncToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollSyncModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_token_creation_and_bearer_prefix(): void
    {
        $token = PayrollSyncToken::createToken('PayFlow Test Instance');

        $this->assertNotNull($token->id);
        $this->assertEquals('PayFlow Test Instance', $token->name);
        $this->assertTrue($token->is_active);
        $this->assertStringStartsWith('payflow_', $token->token);
    }
}
