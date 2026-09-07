<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Staff Portal Sign In');
        $response->assertSee('Sign in with CentraFlow SSO');
        $response->assertSee('Enterprise Identity Protection Enforced');
    }

    public function test_sso_redirect_initiates_oauth_with_pkce(): void
    {
        $response = $this->get('/auth/centraflow');

        $response->assertStatus(302);
        $this->assertStringContainsString('/oauth/authorize', $response->headers->get('Location'));
        $this->assertStringContainsString('code_challenge=', $response->headers->get('Location'));
        $this->assertNotNull(session('centraflow_oauth_state'));
        $this->assertNotNull(session('centraflow_code_verifier'));
    }

    public function test_authenticated_user_can_logout_via_federated_slo(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@hrms.test',
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertStatus(302);
        $this->assertStringContainsString('/logout?redirect_uri=', $response->headers->get('Location'));
        $this->assertStringContainsString('logged_out%3D1', $response->headers->get('Location'));
    }

    public function test_guest_cannot_access_protected_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::factory()->create([
            'name' => 'Alexander Vance',
            'email' => 'admin@hrms.test',
            'role' => 'Super Admin',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Workforce Operations & Governance');
        $response->assertSee('Alexander Vance');
    }
}
