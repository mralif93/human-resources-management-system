<?php

namespace Database\Seeders;

use App\Models\CompanyProfile;
use Illuminate\Database\Seeder;

class CompanyProfileSeeder extends Seeder
{
    /**
     * Run database seeds for Corporate Company Profile & HR Defaults.
     * Strictly 1 seeder for this module.
     */
    public function run(): void
    {
        CompanyProfile::updateOrCreate(
            ['id' => 1],
            [
                'company_name' => 'PulseHR Enterprise Solutions Sdn Bhd',
                'registration_number' => '202601008899 (152019-W)',
                'phone' => '+60 3-8899 7788',
                'email' => 'hr@pulsehr.my',
                'website' => 'https://pulsehr.my',
                'address' => 'Level 28, Menara PulseHR, Platinum Park, KLCC, 50088 Kuala Lumpur, Malaysia',
                'currency_symbol' => 'MYR',
                'default_probation_months' => 3,
                'default_notice_period_months' => 2,
                'default_annual_leave_days' => 14,
                'hr_director_name' => 'Datuk Seri Dr. Ariff Rahman',
                'hr_director_title' => 'Chief Human Resources Officer',
                'contract_terms' => 'This appointment is subject to the provisions of the Malaysian Employment Act 1955. The employee agrees to adhere to enterprise security regulations, proprietary confidentiality, and statutory EPF/SOCSO/EIS contributions.',
            ]
        );
    }
}
