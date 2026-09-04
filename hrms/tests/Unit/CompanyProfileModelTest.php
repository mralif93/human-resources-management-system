<?php

namespace Tests\Unit;

use App\Models\CompanyProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyProfileModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_singleton_current_profile_retrieval(): void
    {
        $profile = CompanyProfile::current();

        $this->assertNotNull($profile->id);
        $this->assertEquals('PulseHR Enterprise Solutions Sdn Bhd', $profile->company_name);
        $this->assertEquals(3, $profile->default_probation_months);
        $this->assertEquals(14, $profile->default_annual_leave_days);
    }
}
