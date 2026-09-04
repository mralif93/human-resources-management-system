@extends('layouts.admin')

@section('title', 'Company Profile & Governance Settings - PulseHR Management System')
@section('page-title', 'Company Profile & Governance')

@section('content')
<div class="space-y-6 animate__animated animate__fadeIn">

    <!-- Page Header (matching standard) -->
    <x-page-header
        title="Company Profile & Governance"
        subtitle="Manage legal corporate identity, SSM registration, headquarters GPS geofence, and statutory defaults"
        icon="bx-buildings"
    >
        <a
            href="{{ route('settings.templates') }}"
            class="h-9 px-4 rounded-xl text-xs font-bold bg-white/10 hover:bg-white/20 text-white border border-white/20 transition inline-flex items-center gap-1.5"
        >
            <i class="bx bx-file-blank text-sm text-indigo-300"></i>
            <span>Offer Letter Template</span>
        </a>
    </x-page-header>

    <!-- Feedback Alerts -->
    @if(session('status'))
        <x-alert variant="success" icon="bx bx-check-circle" title="Success">
            {{ session('status') }}
        </x-alert>
    @endif

    @if($errors->any())
        <x-alert variant="danger" icon="bx bx-error-circle" title="Validation Failed">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <!-- KPI Metric Cards Grid (Standard 4 Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card
            title="Total Active Workforce"
            value="{{ $totalEmployees ?? 0 }}"
            icon="bx bx-user-check"
            color="indigo"
            subtitle="Enrolled staff across units"
        />

        <x-stat-card
            title="Active Departments"
            value="{{ $totalDepartments ?? 0 }}"
            icon="bx bx-network-chart"
            color="emerald"
            subtitle="Operational divisions"
        />

        <x-stat-card
            title="Geofence Perimeter"
            value="{{ $geofenceRadius ?? 100 }}m"
            icon="bx bx-radar"
            color="sky"
            subtitle="Biometric clock-in boundary"
        />

        <x-stat-card
            title="Operational Shifts"
            value="{{ $totalShifts ?? 0 }}"
            icon="bx bx-time"
            color="amber"
            subtitle="Configured rosters"
        />
    </div>

    <!-- Main Settings Form -->
    <form id="profile-settings-form" action="{{ route('settings.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Two-column grid layout for cards on large screens, single column on tablet/mobile -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

            <!-- Card 1: Official Corporate Identification -->
            <x-card title="Official Corporate Identification" subtitle="Details printed on contracts, offer letters, and statutory slips">
                <div class="space-y-4 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-input
                            label="Company Legal Name"
                            name="company_name"
                            value="{{ old('company_name', $profile->company_name) }}"
                            icon="bx bx-buildings"
                            required
                        />

                        <x-input
                            label="SSM / Corporate Reg. No."
                            name="registration_number"
                            value="{{ old('registration_number', $profile->registration_number) }}"
                            icon="bx bx-id-card"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-input
                            label="HR Contact Phone"
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
                            label="Corporate Website / Portal"
                            name="website"
                            type="url"
                            value="{{ old('website', $profile->website) }}"
                            icon="bx bx-globe"
                            placeholder="https://company.com"
                        />
                    </div>
                </div>
            </x-card>

            <!-- Card 2: Headquarters Location & GPS Geofence -->
            <x-card title="Headquarters Registered Address &amp; GPS Geofence" subtitle="Official registered office coordinates and attendance clock-in boundary">
                <div class="space-y-4 text-xs">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            Complete Registered Workplace Address <span class="text-rose-500">*</span>
                        </label>
                        <textarea
                            name="address"
                            id="office_address"
                            rows="2"
                            required
                            class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white p-3 outline-none focus:ring-2 focus:ring-indigo-500 transition-colors placeholder:text-slate-400"
                            placeholder="Full physical street address, building/suite, postal code, and city"
                        >{{ old('address', $profile->address) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <x-input
                            label="Office Latitude"
                            name="office_latitude"
                            id="office_latitude"
                            value="{{ old('office_latitude', $profile->office_latitude ?? 3.1390) }}"
                            icon="bx bx-navigation"
                            hint="e.g. 3.1390"
                        />

                        <x-input
                            label="Office Longitude"
                            name="office_longitude"
                            id="office_longitude"
                            value="{{ old('office_longitude', $profile->office_longitude ?? 101.6869) }}"
                            icon="bx bx-compass"
                            hint="e.g. 101.6869"
                        />

                        <x-input
                            label="Allowed Radius"
                            name="geofence_radius_meters"
                            type="number"
                            min="10"
                            max="5000"
                            value="{{ old('geofence_radius_meters', $profile->geofence_radius_meters ?? 100) }}"
                            icon="bx bx-radar"
                            hint="Meters (Default: 100m)"
                        />
                    </div>

                    <!-- Geolocation Quick Fill & Map Link Helpers -->
                    <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-800 space-y-2.5 text-[11px]">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>Saved GPS: <strong class="font-mono text-slate-800 dark:text-white" id="gps-display-text">{{ number_format($profile->office_latitude ?? 3.139, 4) }}, {{ number_format($profile->office_longitude ?? 101.6869, 4) }}</strong></span>
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    id="gps-fetch-btn"
                                    onclick="getCurrentGPS()"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-[11px] shadow-xs hover:shadow-indigo-500/20 transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <i class="bx bx-current-location text-sm" id="gps-btn-icon"></i>
                                    <span id="gps-btn-label">Get Current GPS</span>
                                </button>
                                <button
                                    type="button"
                                    id="preview-map-btn"
                                    onclick="openMapModal()"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 hover:dark:bg-slate-600 text-slate-700 dark:text-slate-200 font-semibold text-[11px] transition shadow-xs cursor-pointer"
                                >
                                    <i class="bx bx-map-pin text-indigo-500 text-sm"></i>
                                    <span>Preview Map</span>
                                </button>
                            </div>
                        </div>

                        <!-- Dynamic GPS Permission & Accuracy Status Feedback -->
                        <div id="gps-status-banner" class="hidden p-2.5 rounded-xl border transition-all text-[11px] flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2" id="gps-status-content">
                                <i class="bx bx-info-circle text-sm" id="gps-status-icon"></i>
                                <span id="gps-status-msg">Clicking will request your browser location permission.</span>
                            </div>
                            <span id="gps-accuracy-badge" class="hidden font-mono px-2 py-0.5 rounded-full text-[10px] font-bold"></span>
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Card 3: Employment & Statutory Standards -->
            <x-card title="Employment &amp; Statutory Standards" subtitle="Default terms pre-populated when issuing employment contracts and tracking leave">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <x-input
                        label="Default Currency Code"
                        name="currency_symbol"
                        value="{{ old('currency_symbol', $profile->currency_symbol) }}"
                        icon="bx bx-money"
                        placeholder="MYR"
                        required
                    />

                    <x-input
                        label="Default Probation Period"
                        name="default_probation_months"
                        type="number"
                        min="1"
                        max="12"
                        value="{{ old('default_probation_months', $profile->default_probation_months) }}"
                        icon="bx bx-time-five"
                        hint="Months (typically 3 to 6)"
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
                        hint="Months (typically 1 to 3)"
                        required
                    />

                    <x-input
                        label="Standard Annual Leave"
                        name="default_annual_leave_days"
                        type="number"
                        min="0"
                        max="60"
                        value="{{ old('default_annual_leave_days', $profile->default_annual_leave_days) }}"
                        icon="bx bx-calendar-check"
                        hint="Days per calendar year"
                        required
                    />
                </div>
            </x-card>

            <!-- Card 4: Authorized HR Signatory Block -->
            <x-card title="Authorized HR Signatory Block" subtitle="Executive signature details and legal terms attached to official offer letters">
                <div class="space-y-4 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-input
                            label="Signatory Full Name"
                            name="hr_director_name"
                            value="{{ old('hr_director_name', $profile->hr_director_name) }}"
                            icon="bx bx-user-pin"
                            required
                        />

                        <x-input
                            label="Official Designation / Title"
                            name="hr_director_title"
                            value="{{ old('hr_director_title', $profile->hr_director_title) }}"
                            icon="bx bx-badge-check"
                            required
                        />
                    </div>

                    <!-- Official Signature Image Upload -->
                    <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-800 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                Official Digital Signature Image
                            </label>
                            <span class="text-[10px] text-slate-400">PNG, JPG, SVG up to 2MB (transparent recommended)</span>
                        </div>

                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                            <!-- Preview Box -->
                            <div class="relative w-44 h-20 rounded-xl border-2 border-dashed border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 flex items-center justify-center p-2 overflow-hidden shadow-xs shrink-0 group">
                                @if($profile->signature_path)
                                    <img
                                        id="signature-preview-img"
                                        src="{{ asset('storage/' . $profile->signature_path) }}"
                                        alt="Official HR Signature"
                                        class="max-h-full max-w-full object-contain filter contrast-125"
                                    />
                                    <div id="signature-placeholder" class="hidden text-center text-slate-400">
                                        <i class="bx bx-pen text-2xl text-slate-300 dark:text-slate-600"></i>
                                        <p class="text-[9px] mt-0.5">No signature uploaded</p>
                                    </div>
                                @else
                                    <img
                                        id="signature-preview-img"
                                        src=""
                                        alt="Official HR Signature"
                                        class="hidden max-h-full max-w-full object-contain filter contrast-125"
                                    />
                                    <div id="signature-placeholder" class="text-center text-slate-400">
                                        <i class="bx bx-pen text-2xl text-slate-300 dark:text-slate-600"></i>
                                        <p class="text-[9px] mt-0.5">No signature uploaded</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Upload Inputs & Controls -->
                            <div class="space-y-2 flex-1 w-full">
                                <label for="signature_image_input" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs font-bold transition shadow-xs cursor-pointer w-full sm:w-auto">
                                    <i class="bx bx-upload text-sm text-indigo-600 dark:text-indigo-400"></i>
                                    <span>Upload Signature File</span>
                                </label>
                                <input
                                    type="file"
                                    name="signature_image"
                                    id="signature_image_input"
                                    accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                    class="hidden"
                                    onchange="previewSignatureImage(this)"
                                />

                                @if($profile->signature_path)
                                    <div class="flex items-center gap-2 pt-1">
                                        <input
                                            type="checkbox"
                                            name="remove_signature"
                                            id="remove_signature"
                                            value="1"
                                            class="rounded border-slate-300 text-rose-600 focus:ring-rose-500 cursor-pointer"
                                            onchange="toggleRemoveSignature(this.checked)"
                                        >
                                        <label for="remove_signature" class="text-[11px] font-semibold text-rose-600 dark:text-rose-400 cursor-pointer">
                                            Remove current signature (revert to printed font signature)
                                        </label>
                                    </div>
                                @endif

                                <p class="text-[10px] text-slate-400 leading-tight">
                                    Appears in the signatory block of official Offer Letters and generated statutory documents.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            Standard Contract Terms &amp; Confidentiality Clause
                        </label>
                        <textarea
                            name="contract_terms"
                            rows="3"
                            class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white p-3 outline-none focus:ring-2 focus:ring-indigo-500 transition-colors placeholder:text-slate-400"
                            placeholder="Enter default confidentiality, statutory compliance, and operational governance terms"
                        >{{ old('contract_terms', $profile->contract_terms) }}</textarea>
                    </div>
                </div>
            </x-card>

        </div>

        <!-- Sticky Mobile & Desktop Action Save Bar -->
        <div class="flex items-center justify-between p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                <i class="bx bx-shield-quarter text-indigo-600 dark:text-indigo-400 text-lg"></i>
                <span class="hidden sm:inline">All profile changes are logged to the central activity audit trail.</span>
                <span class="sm:hidden">Audit tracked.</span>
            </div>

            <x-button type="submit" variant="primary" size="md" icon="bx bx-save" class="shadow-md shadow-indigo-600/20 font-bold px-6">
                Save Company Profile
            </x-button>
        </div>
    </form>

</div>

@push('modals')
    <!-- Map Preview Modal with Interactive OpenStreetMap & Google Maps Quick Link -->
    <x-modal name="map-preview" title="Headquarters GPS Geofence Preview" subtitle="Real-time interactive boundary map for mobile & kiosk attendance check-ins" icon="bx-map-pin" size="4xl">
        <div class="space-y-4">
            <!-- Top Coordinate Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/80 text-xs">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 flex items-center justify-center text-lg shrink-0">
                        <i class="bx bx-radar"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-slate-500 dark:text-slate-400 text-[11px]">Pin Coordinates:</span>
                            <span class="font-mono font-bold text-slate-900 dark:text-white" id="modal-map-coords">3.1390, 101.6869</span>
                        </div>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="text-slate-500 dark:text-slate-400 text-[11px]">Geofence Radius:</span>
                            <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400" id="modal-map-radius">100 meters</span>
                        </div>
                    </div>
                </div>

                <a
                    id="modal-external-gmap-btn"
                    href="https://www.google.com/maps?q=3.1390,101.6869"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-600 text-[11px] font-bold shadow-xs transition cursor-pointer"
                >
                    <i class="bx bx-link-external text-slate-400"></i>
                    <span>Open in Google Maps</span>
                </a>
            </div>

            <!-- Map Frame with Loading State & Responsive Aspect Ratio -->
            <div class="relative w-full h-80 sm:h-[420px] rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-950 shadow-inner">
                <div id="map-loading-indicator" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-100/90 dark:bg-slate-900/90 z-10 transition-opacity">
                    <i class="bx bx-loader-alt animate-spin text-3xl text-indigo-600 mb-2"></i>
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">Rendering OpenStreetMap preview...</p>
                </div>
                <iframe
                    id="map-modal-iframe"
                    src="about:blank"
                    class="w-full h-full border-0"
                    loading="lazy"
                    title="Headquarters Geofence Map"
                    onload="document.getElementById('map-loading-indicator').classList.add('hidden')"
                ></iframe>
            </div>

            <!-- Helper Note -->
            <div class="flex items-start gap-2 p-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/40 text-amber-800 dark:text-amber-300 text-[11px] leading-relaxed">
                <i class="bx bx-info-circle text-base shrink-0 mt-0.5"></i>
                <div>
                    <strong>Attendance Verification Note:</strong> Employees attempting mobile or kiosk clock-in will have their GPS validated against this marker within the configured radius buffer.
                </div>
            </div>
        </div>

        <x-slot:footer>
            <x-button
                type="button"
                variant="secondary"
                size="sm"
                onclick="document.getElementById('modal-map-preview').classList.add('hidden')"
            >
                Close Preview
            </x-button>
        </x-slot:footer>
    </x-modal>
@endpush

@push('scripts')
<script>
    window.updateGPSBanner = function(type, message, accuracy = null) {
        const banner = document.getElementById('gps-status-banner');
        const icon = document.getElementById('gps-status-icon');
        const msg = document.getElementById('gps-status-msg');
        const badge = document.getElementById('gps-accuracy-badge');

        if (!banner || !icon || !msg) return;

        banner.className = 'p-2.5 rounded-xl border transition-all text-[11px] flex items-center justify-between gap-2';

        if (type === 'loading') {
            banner.classList.add('bg-amber-50', 'dark:bg-amber-950/40', 'border-amber-200', 'dark:border-amber-900/60', 'text-amber-800', 'dark:text-amber-300');
            icon.className = 'bx bx-loader-alt animate-spin text-sm';
            msg.innerText = message;
            if (badge) badge.classList.add('hidden');
        } else if (type === 'success') {
            banner.classList.add('bg-emerald-50', 'dark:bg-emerald-950/40', 'border-emerald-200', 'dark:border-emerald-900/60', 'text-emerald-800', 'dark:text-emerald-300');
            icon.className = 'bx bx-check-circle text-sm';
            msg.innerText = message;
            if (badge && accuracy !== null) {
                badge.classList.remove('hidden');
                badge.className = 'font-mono px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-200 border border-emerald-300 dark:border-emerald-800';
                badge.innerText = `±${Math.round(accuracy)}m accuracy`;
            }
        } else if (type === 'error') {
            banner.classList.add('bg-rose-50', 'dark:bg-rose-950/40', 'border-rose-200', 'dark:border-rose-900/60', 'text-rose-800', 'dark:text-rose-300');
            icon.className = 'bx bx-error-circle text-sm';
            msg.innerText = message;
            if (badge) badge.classList.add('hidden');
        }
    };

    window.getCurrentGPS = function() {
        console.log('[GPS] Triggering getCurrentGPS()...');

        const btn = document.getElementById('gps-fetch-btn');
        const btnLabel = document.getElementById('gps-btn-label');
        const btnIcon = document.getElementById('gps-btn-icon');

        if (!navigator.geolocation) {
            console.error('[GPS] Geolocation is not supported in this environment.');
            alert('Your browser does not support Geolocation, or this site is accessed in an insecure context (Geolocation requires HTTPS or localhost).');
            window.updateGPSBanner('error', 'Geolocation is not supported or requires HTTPS/localhost.');
            return;
        }

        // Set Loading State
        if (btn) {
            btn.disabled = true;
            if (btnLabel) btnLabel.innerText = 'Requesting...';
            if (btnIcon) btnIcon.className = 'bx bx-loader-alt animate-spin text-sm';
        }
        window.updateGPSBanner('loading', 'Please allow location permission in your browser pop-up prompt...');

        const options = {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        };

        navigator.geolocation.getCurrentPosition(
            function(position) {
                console.log('[GPS] Position acquired:', position);
                const lat = position.coords.latitude.toFixed(4);
                const lng = position.coords.longitude.toFixed(4);
                const accuracy = position.coords.accuracy;

                const latInput = document.getElementById('office_latitude');
                const lngInput = document.getElementById('office_longitude');
                const displayText = document.getElementById('gps-display-text');

                if (latInput) latInput.value = lat;
                if (lngInput) lngInput.value = lng;
                if (displayText) displayText.innerText = `${lat}, ${lng}`;

                window.updateGPSBanner('loading', `Coordinates acquired (${lat}, ${lng}). Fetching physical address...`);

                // Reverse geocoding to auto-fill address
                fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`, {
                    headers: { 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data && data.display_name) {
                        const addressTextarea = document.getElementById('office_address');
                        if (addressTextarea) {
                            addressTextarea.value = data.display_name;
                            addressTextarea.classList.add('ring-2', 'ring-emerald-500/50');
                            setTimeout(() => addressTextarea.classList.remove('ring-2', 'ring-emerald-500/50'), 3000);
                        }
                        window.updateGPSBanner('success', `GPS & address updated to ${lat}, ${lng}`, accuracy);
                    } else {
                        window.updateGPSBanner('success', `GPS updated to ${lat}, ${lng}`, accuracy);
                    }
                })
                .catch(err => {
                    console.warn('[GPS] Reverse geocoding error:', err);
                    window.updateGPSBanner('success', `GPS updated to ${lat}, ${lng}`, accuracy);
                })
                .finally(() => {
                    if (btn) {
                        btn.disabled = false;
                        if (btnLabel) btnLabel.innerText = 'Re-detect GPS';
                        if (btnIcon) btnIcon.className = 'bx bx-current-location text-sm';
                    }
                });
            },
            function(error) {
                console.warn('[GPS] Position error:', error);
                let errorMsg = 'Failed to retrieve location.';
                switch (error.code) {
                    case 1: // PERMISSION_DENIED
                        errorMsg = 'Location permission was denied. Please click the icon in your browser address bar to allow location access.';
                        break;
                    case 2: // POSITION_UNAVAILABLE
                        errorMsg = 'Location unavailable. Make sure Location Services are enabled on your computer.';
                        break;
                    case 3: // TIMEOUT
                        errorMsg = 'Location request timed out. Please try again.';
                        break;
                }

                alert(errorMsg);
                window.updateGPSBanner('error', errorMsg);

                if (btn) {
                    btn.disabled = false;
                    if (btnLabel) btnLabel.innerText = 'Get Current GPS';
                    if (btnIcon) btnIcon.className = 'bx bx-current-location text-sm';
                }
            },
            options
        );
    };

    // Signature Preview & Removal Handlers
    window.previewSignatureImage = function(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                const previewImg = document.getElementById('signature-preview-img');
                const placeholder = document.getElementById('signature-placeholder');
                const removeCheckbox = document.getElementById('remove_signature');

                if (previewImg) {
                    previewImg.src = e.target.result;
                    previewImg.classList.remove('hidden');
                    previewImg.classList.remove('opacity-30');
                }
                if (placeholder) {
                    placeholder.classList.add('hidden');
                }
                if (removeCheckbox) {
                    removeCheckbox.checked = false;
                }
            };

            reader.readAsDataURL(file);
        }
    };

    window.toggleRemoveSignature = function(checked) {
        const previewImg = document.getElementById('signature-preview-img');
        const placeholder = document.getElementById('signature-placeholder');
        const fileInput = document.getElementById('signature_image_input');

        if (checked) {
            if (fileInput) fileInput.value = ''; // clear any newly picked file
            if (previewImg) {
                previewImg.classList.add('opacity-30');
            }
        } else {
            if (previewImg && previewImg.getAttribute('src')) {
                previewImg.classList.remove('opacity-30');
            }
        }
    };

    // Geofence Map Modal Opener
    window.openMapModal = function() {
        const latInput = document.getElementById('office_latitude');
        const lngInput = document.getElementById('office_longitude');
        const radiusInput = document.querySelector('input[name="geofence_radius_meters"]');

        const lat = parseFloat(latInput ? latInput.value : 3.1390) || 3.1390;
        const lng = parseFloat(lngInput ? lngInput.value : 101.6869) || 101.6869;
        const radius = radiusInput ? (radiusInput.value || 100) : 100;

        // Update modal info text
        const coordsText = document.getElementById('modal-map-coords');
        const radiusText = document.getElementById('modal-map-radius');
        const gmapBtn = document.getElementById('modal-external-gmap-btn');

        if (coordsText) coordsText.innerText = `${lat.toFixed(4)}, ${lng.toFixed(4)}`;
        if (radiusText) radiusText.innerText = `${radius} meters`;
        if (gmapBtn) gmapBtn.href = `https://www.google.com/maps?q=${lat},${lng}`;

        // Compute appropriate bbox around marker based on radius (minimum offset approx 0.005 deg)
        const offset = Math.max(0.005, (radius / 111000) * 3);
        const bbox = `${(lng - offset).toFixed(5)},${(lat - offset).toFixed(5)},${(lng + offset).toFixed(5)},${(lat + offset).toFixed(5)}`;
        const osmEmbedUrl = `https://www.openstreetmap.org/export/embed.html?bbox=${bbox}&layer=mapnik&marker=${lat},${lng}`;

        const iframe = document.getElementById('map-modal-iframe');
        const loader = document.getElementById('map-loading-indicator');

        if (iframe) {
            if (loader) loader.classList.remove('hidden');
            iframe.src = osmEmbedUrl;
        }

        // Open Modal
        const modal = document.getElementById('modal-map-preview');
        if (modal) {
            modal.classList.remove('hidden');
        }
    };
</script>
@endpush
@endsection
