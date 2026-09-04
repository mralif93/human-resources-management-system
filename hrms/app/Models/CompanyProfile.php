<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'registration_number',
        'phone',
        'email',
        'website',
        'address',
        'logo_path',
        'currency_symbol',
        'default_probation_months',
        'default_notice_period_months',
        'default_annual_leave_days',
        'hr_director_name',
        'hr_director_title',
        'contract_terms',
        'offer_letter_subject',
        'offer_letter_intro',
        'offer_letter_benefits',
        'offer_validity_days',
        'office_latitude',
        'office_longitude',
        'geofence_radius_meters',
    ];

    protected $casts = [
        'default_probation_months' => 'integer',
        'default_notice_period_months' => 'integer',
        'default_annual_leave_days' => 'integer',
        'offer_validity_days' => 'integer',
        'office_latitude' => 'float',
        'office_longitude' => 'float',
        'geofence_radius_meters' => 'integer',
    ];

    /**
     * Retrieve the current corporate profile instance (singleton pattern).
     */
    public static function current(): self
    {
        return self::firstOrCreate(
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
                'contract_terms' => 'This employment contract is governed by the Employment Act 1955 of Malaysia. You shall abide by all enterprise cybersecurity protocols, confidentiality NDAs, and corporate code of conduct.',
            ]
        );
    }
}
