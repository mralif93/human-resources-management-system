@extends('layouts.admin')

@section('page-title', 'Company Profile & Governance Settings')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Page Header (matching clinic-invoice-system standard) -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-950 via-slate-900 to-indigo-900 p-6 sm:p-7 border border-indigo-800/40 shadow-xl shadow-indigo-950/40 text-white">
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center text-indigo-300 font-bold border border-white/10">
                        <i class="bx bx-buildings text-lg"></i>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black tracking-tight">Company Profile &amp; Governance Settings</h1>
                </div>
                <p class="text-xs text-slate-300">Manage corporate legal identity, official SSM registration, HR signatory blocks, and default employment terms</p>
            </div>

            <div class="flex items-center gap-2">
                <a
                    href="{{ route('settings.templates') }}"
                    class="h-9 px-4 rounded-xl text-xs font-bold bg-white/10 hover:bg-white/20 text-white border border-white/20 transition inline-flex items-center gap-1.5"
                >
                    <i class="bx bx-file-blank text-sm"></i>
                    <span>Preview Offer Letter</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('status'))
        <x-alert variant="success" icon="bx bx-check-circle" title="Success">
            {{ session('status') }}
        </x-alert>
    @endif

    @if($errors->any())
        <x-alert variant="danger" icon="bx bx-error" title="Validation Failed">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <!-- Profile Form -->
    <form action="{{ route('settings.profile.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- 1. Legal Corporate Entity -->
        <x-card title="Official Corporate Identification" subtitle="Details printed in the header of employment contracts, offer letters, and statutory reports">
            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-input
                        label="Company Name"
                        name="company_name"
                        value="{{ old('company_name', $profile->company_name) }}"
                        icon="bx bx-buildings"
                        required
                    />

                    <x-input
                        label="SSM / Corporate Registration No."
                        name="registration_number"
                        value="{{ old('registration_number', $profile->registration_number) }}"
                        icon="bx bx-id-card"
                        required
                    />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-input
                        label="Official HR Contact Phone"
                        name="phone"
                        value="{{ old('phone', $profile->phone) }}"
                        icon="bx bx-phone"
                        required
                    />

                    <x-input
                        label="Official HR Inquiries Email"
                        name="email"
                        type="email"
                        value="{{ old('email', $profile->email) }}"
                        icon="bx bx-envelope"
                        required
                    />
                </div>

                <div>
                    <x-input
                        label="Corporate Website Portal"
                        name="website"
                        type="url"
                        value="{{ old('website', $profile->website) }}"
                        icon="bx bx-globe"
                    />
                </div>
            </div>
        </x-card>

        <!-- 2. Headquarters Location & Address -->
        <x-card title="Headquarters Registered Address" subtitle="Official registered workplace for geofencing and employment contract jurisdiction">
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Complete Address <span class="text-rose-500">*</span>
                </label>
                <textarea
                    name="address"
                    rows="3"
                    required
                    class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-3 outline-none focus:ring-2 focus:ring-indigo-500"
                >{{ old('address', $profile->address) }}</textarea>
            </div>
        </x-card>

        <!-- 3. Employment & Statutory Defaults -->
        <x-card title="Employment &amp; Statutory Standards" subtitle="Default terms pre-populated when issuing offers and tracking leave">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <x-input
                    label="Currency Code"
                    name="currency_symbol"
                    value="{{ old('currency_symbol', $profile->currency_symbol) }}"
                    icon="bx bx-money"
                    required
                />

                <x-input
                    label="Default Probation"
                    name="default_probation_months"
                    type="number"
                    min="1"
                    max="12"
                    value="{{ old('default_probation_months', $profile->default_probation_months) }}"
                    icon="bx bx-time-five"
                    hint="In Months"
                    required
                />

                <x-input
                    label="Default Notice Period"
                    name="default_notice_period_months"
                    type="number"
                    min="1"
                    max="12"
                    value="{{ old('default_notice_period_months', $profile->default_notice_period_months) }}"
                    icon="bx bx-calendar-event"
                    hint="In Months"
                    required
                />

                <x-input
                    label="Default Annual Leave"
                    name="default_annual_leave_days"
                    type="number"
                    min="0"
                    max="60"
                    value="{{ old('default_annual_leave_days', $profile->default_annual_leave_days) }}"
                    icon="bx bx-calendar-check"
                    hint="Days per annum"
                    required
                />
            </div>
        </x-card>

        <!-- 4. Authorized Signatory / HR Director Block -->
        <x-card title="Authorized HR Signatory Block" subtitle="Executive signature and legal terms printed on employment offer letters">
            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-input
                        label="Authorized Signatory Name"
                        name="hr_director_name"
                        value="{{ old('hr_director_name', $profile->hr_director_name) }}"
                        icon="bx bx-user-pin"
                        required
                    />

                    <x-input
                        label="Official Title / Designation"
                        name="hr_director_title"
                        value="{{ old('hr_director_title', $profile->hr_director_title) }}"
                        icon="bx bx-badge-check"
                        required
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Standard Contract Terms &amp; Confidentiality Clause
                    </label>
                    <textarea
                        name="contract_terms"
                        rows="3"
                        class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-3 outline-none focus:ring-2 focus:ring-indigo-500"
                    >{{ old('contract_terms', $profile->contract_terms) }}</textarea>
                </div>
            </div>
        </x-card>

        <!-- Submit Button Bar -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <x-button type="submit" variant="primary" size="md" icon="bx bx-save" class="shadow-lg shadow-indigo-600/30">
                Save Company Profile Settings
            </x-button>
        </div>
    </form>

</div>
@endsection
