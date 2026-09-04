<?php

namespace Tests\Feature;

use App\Models\CompanyProfile;
use App\Models\User;
use Database\Seeders\CompanyProfileSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyProfileSettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
        $this->adminUser = User::where('email', 'admin@hrms.test')->first();
    }

    public function test_guest_cannot_access_company_profile(): void
    {
        $response = $this->get(route('settings.profile'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_admin_can_view_profile_settings(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('settings.profile'));
        $response->assertStatus(200);
        $response->assertSee('Company Profile &amp; Governance Settings', false);
    }

    public function test_admin_can_update_company_profile(): void
    {
        $response = $this->actingAs($this->adminUser)->put(route('settings.profile.update'), [
            'company_name' => 'PulseHR Global Technologies',
            'registration_number' => '202699887766 (999999-X)',
            'phone' => '+60 3-7788 9900',
            'email' => 'corp@pulsehr.my',
            'website' => 'https://pulsehr.my',
            'address' => 'Level 35, Corporate Tower, KLCC',
            'currency_symbol' => 'MYR',
            'default_probation_months' => 6,
            'default_notice_period_months' => 3,
            'default_annual_leave_days' => 18,
            'hr_director_name' => 'Datin Sarah Connor',
            'hr_director_title' => 'VP Human Capital',
            'contract_terms' => 'Updated enterprise terms clause.',
        ]);

        $response->assertRedirect(route('settings.profile'));
        $this->assertDatabaseHas('company_profiles', [
            'company_name' => 'PulseHR Global Technologies',
            'default_annual_leave_days' => 18,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'company_profile.updated',
        ]);
    }

    public function test_admin_can_upload_and_remove_signature_image(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $file = \Illuminate\Http\UploadedFile::fake()->image('signature.png', 300, 100);

        $response = $this->actingAs($this->adminUser)->put(route('settings.profile.update'), [
            'company_name' => 'PulseHR Signature Test Corp',
            'registration_number' => '123456-A',
            'phone' => '+60 3-1111 2222',
            'email' => 'hr@pulsehr.my',
            'address' => 'Signature Avenue, Cyberjaya',
            'currency_symbol' => 'MYR',
            'default_probation_months' => 3,
            'default_notice_period_months' => 2,
            'default_annual_leave_days' => 14,
            'hr_director_name' => 'Michael Scott',
            'hr_director_title' => 'Regional Director',
            'signature_image' => $file,
        ]);

        $response->assertRedirect(route('settings.profile'));

        $profile = CompanyProfile::current();
        $this->assertNotNull($profile->signature_path);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($profile->signature_path);

        // Test template displays the uploaded signature
        $templateResponse = $this->actingAs($this->adminUser)->get(route('settings.templates'));
        $templateResponse->assertStatus(200);
        $templateResponse->assertSee('storage/' . $profile->signature_path, false);

        // Test removing the signature
        $removeResponse = $this->actingAs($this->adminUser)->put(route('settings.profile.update'), [
            'company_name' => 'PulseHR Signature Test Corp',
            'registration_number' => '123456-A',
            'phone' => '+60 3-1111 2222',
            'email' => 'hr@pulsehr.my',
            'address' => 'Signature Avenue, Cyberjaya',
            'currency_symbol' => 'MYR',
            'default_probation_months' => 3,
            'default_notice_period_months' => 2,
            'default_annual_leave_days' => 14,
            'hr_director_name' => 'Michael Scott',
            'hr_director_title' => 'Regional Director',
            'remove_signature' => 1,
        ]);

        $removeResponse->assertRedirect(route('settings.profile'));
        $profile->refresh();
        $this->assertNull($profile->signature_path);
    }

    public function test_authenticated_admin_can_view_offer_letter_template_preview(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('settings.templates'));
        $response->assertStatus(200);
        $response->assertSee('Employment Offer Letter Design Template', false);
        $response->assertSee('Summary of Employment Terms', false);
    }

    public function test_admin_can_update_offer_letter_template(): void
    {
        $response = $this->actingAs($this->adminUser)->put(route('settings.templates.update'), [
            'offer_letter_subject' => 'Official Letter of Executive Employment Offer',
            'offer_letter_intro' => 'We are thrilled to welcome you to our core team!',
            'offer_letter_benefits' => 'Comprehensive Premium Medical Care, EPF, SOCSO, EIS',
            'offer_validity_days' => 7,
            'contract_terms' => 'Standard non-disclosure and intellectual property agreement.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('company_profiles', [
            'offer_letter_subject' => 'Official Letter of Executive Employment Offer',
            'offer_validity_days' => 7,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'offer_template.updated',
        ]);
    }

    public function test_company_profile_seeder_populates_verified_record(): void
    {
        $this->seed(CompanyProfileSeeder::class);

        $this->assertDatabaseHas('company_profiles', [
            'id' => 1,
            'company_name' => 'PulseHR Enterprise Solutions Sdn Bhd',
        ]);
    }
}
