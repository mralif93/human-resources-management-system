<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CompanyProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyProfileController extends Controller
{
    /**
     * Show company profile settings edit form.
     */
    public function edit(): View
    {
        $profile = CompanyProfile::current();
        return view('admin.settings.profile', compact('profile'));
    }

    /**
     * Update corporate profile identity & HR defaults.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'registration_number' => 'required|string|max:100',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'website' => 'nullable|url|max:255',
            'address' => 'required|string|max:500',
            'currency_symbol' => 'required|string|max:10',
            'default_probation_months' => 'required|integer|min:1|max:12',
            'default_notice_period_months' => 'required|integer|min:1|max:12',
            'default_annual_leave_days' => 'required|integer|min:0|max:60',
            'hr_director_name' => 'required|string|max:150',
            'hr_director_title' => 'required|string|max:150',
            'contract_terms' => 'nullable|string|max:2000',
        ]);

        $profile = CompanyProfile::current();
        $profile->update($validated);

        AuditLog::record(
            'company_profile.updated',
            'security',
            "Company profile identity updated: {$profile->company_name}.",
            $validated
        );

        return redirect()->route('settings.profile')->with('status', 'Company profile and employment governance settings successfully updated.');
    }

    /**
     * Display live Offer Letter design template preview.
     */
    public function templates(): View
    {
        $profile = CompanyProfile::current();
        return view('admin.settings.templates', compact('profile'));
    }

    /**
     * Update Offer Letter Template text, subject, benefits, and statutory terms.
     */
    public function updateTemplate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'offer_letter_subject' => 'required|string|max:255',
            'offer_letter_intro' => 'nullable|string|max:2000',
            'offer_letter_benefits' => 'nullable|string|max:1000',
            'offer_validity_days' => 'required|integer|min:1|max:60',
            'contract_terms' => 'nullable|string|max:2000',
        ]);

        $profile = CompanyProfile::current();
        $profile->update($validated);

        AuditLog::record(
            'offer_template.updated',
            'security',
            "Master Employment Offer Letter template parameters updated.",
            $validated
        );

        return back()->with('status', 'Offer letter master template layout and legal terms successfully updated.');
    }
}
