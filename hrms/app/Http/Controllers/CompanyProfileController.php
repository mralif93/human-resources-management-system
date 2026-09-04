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
        $totalEmployees = \App\Models\Employee::count();
        $totalDepartments = \App\Models\Department::where('is_active', true)->count();
        $totalShifts = \App\Models\Shift::count();
        $geofenceRadius = $profile->geofence_radius_meters ?? 100;

        return view('admin.settings.profile', compact(
            'profile',
            'totalEmployees',
            'totalDepartments',
            'totalShifts',
            'geofenceRadius'
        ));
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
            'signature_image' => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:2048',
            'remove_signature' => 'nullable|boolean',
            'contract_terms' => 'nullable|string|max:2000',
            'office_latitude' => 'nullable|numeric|between:-90,90',
            'office_longitude' => 'nullable|numeric|between:-180,180',
            'geofence_radius_meters' => 'nullable|integer|min:10|max:5000',
        ]);

        $profile = CompanyProfile::current();

        // Handle signature image upload or removal
        if ($request->boolean('remove_signature')) {
            if ($profile->signature_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($profile->signature_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($profile->signature_path);
            }
            $validated['signature_path'] = null;
        } elseif ($request->hasFile('signature_image')) {
            if ($profile->signature_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($profile->signature_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($profile->signature_path);
            }
            $path = $request->file('signature_image')->store('signatures', 'public');
            $validated['signature_path'] = $path;
        }

        unset($validated['signature_image'], $validated['remove_signature']);

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
