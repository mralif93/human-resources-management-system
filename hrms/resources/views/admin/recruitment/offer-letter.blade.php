<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employment Offer Letter — {{ $applicant->full_name }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
                padding: 0 !important;
            }
            .print-card {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 font-sans antialiased min-h-screen py-8 px-4 sm:px-6">

    <!-- Top Action Bar (Hidden when printing) -->
    <div class="max-w-4xl mx-auto mb-6 flex items-center justify-between no-print">
        <a
            href="{{ route('recruitment.index', ['tab' => 'kanban', 'job_id' => $applicant->job_opening_id]) }}"
            class="inline-flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-slate-900 transition"
        >
            <i class="bx bx-arrow-back text-base"></i>
            <span>Back to ATS Kanban</span>
        </a>

        <div class="flex items-center gap-3">
            <button
                type="button"
                onclick="window.print()"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-600/20 transition cursor-pointer"
            >
                <i class="bx bx-printer text-base"></i>
                <span>Print Offer Letter</span>
            </button>
        </div>
    </div>

    <!-- Official Offer Letter Document -->
    <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-xl border border-slate-200/80 p-8 sm:p-14 print-card">
        
        <!-- Header & Company Brand -->
        <div class="pb-8 border-b-2 border-slate-900">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6">
                <!-- Left: Organization Identity & Title -->
                <div class="min-w-0">
                    <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 leading-tight uppercase font-sans">
                        {{ $companyProfile->company_name ?? 'PulseHR Enterprise Solutions' }}
                    </h1>
                    <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                        <span class="text-xs font-semibold text-slate-600">People Operations &amp; Talent Acquisition</span>
                        <span class="text-slate-300 hidden sm:inline">&bull;</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-slate-100 text-slate-600 border border-slate-200 uppercase">
                            Official Document
                        </span>
                    </div>
                </div>
                
                <!-- Right: Reference & Official Registry Block -->
                <div class="sm:text-right text-xs shrink-0 flex flex-col sm:items-end space-y-1 text-slate-600">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-950 font-mono font-bold text-xs border border-indigo-200/80 shadow-2xs">
                        <span class="text-[10px] uppercase tracking-wider text-indigo-600 font-sans">Ref:</span>
                        <span>OFF-{{ date('Y') }}-{{ str_pad($applicant->id, 4, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <p class="text-[11px] text-slate-600 mt-1 max-w-[240px] leading-relaxed">
                        {{ $companyProfile->address ?? 'Level 28, Menara Pulse, KL Sentral, 50470 Kuala Lumpur, Malaysia' }}
                    </p>
                    <p class="text-[10px] font-mono text-slate-500">
                        SSM Reg: {{ $companyProfile->registration_number ?? '202601008899 (152019-W)' }}
                    </p>
                    <div class="flex items-center sm:justify-end gap-3 text-[11px] pt-0.5 text-slate-600">
                        <span class="inline-flex items-center gap-1">
                            <i class="bx bx-phone text-xs text-indigo-600"></i>
                            <span>{{ $companyProfile->phone ?? '+60 3-8899 7788' }}</span>
                        </span>
                        <span class="inline-flex items-center gap-1 text-indigo-700 font-medium">
                            <i class="bx bx-envelope text-xs"></i>
                            <span>{{ $companyProfile->email ?? 'hr@pulsehr.my' }}</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Date & Addressee Block (Date right-aligned, no separator line below) -->
        <div class="mt-6 text-xs flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <!-- Candidate Contact Info -->
            <div class="space-y-0.5">
                <p class="font-bold text-slate-900 text-sm tracking-tight">{{ $applicant->full_name }}</p>
                <p class="text-slate-600">{{ $applicant->email }}</p>
                <p class="text-slate-600 font-mono text-[11px]">{{ $applicant->phone ?? 'Contact on file' }}</p>
            </div>

            <!-- Date (Right Aligned) -->
            <div class="sm:text-right shrink-0">
                <p class="font-semibold text-slate-600">Date: <span class="text-slate-900 font-bold font-sans">{{ date('d F Y') }}</span></p>
            </div>
        </div>

        <!-- Subject Header (Underlined) -->
        <div class="mt-6">
            <h2 class="text-base font-extrabold text-slate-900 uppercase tracking-wide underline underline-offset-4 decoration-2 decoration-slate-900">
                {{ $companyProfile->offer_letter_subject ?? 'Conditional Letter of Employment Offer' }}: {{ $applicant->jobOpening->title }}
            </h2>
        </div>

        <!-- Salutation (Positioned below Subject Line) -->
        <div class="mt-4 text-xs text-slate-800">
            <p>
                Dear <strong>{{ $applicant->first_name }}</strong>,
            </p>
        </div>

        <!-- Body Paragraphs -->
        <div class="mt-3 space-y-4 text-xs leading-relaxed text-slate-700">
            <p>
                On behalf of <strong>{{ $companyProfile->company_name ?? 'PulseHR Enterprise Solutions Sdn. Bhd.' }}</strong>, we are delighted to extend to you this formal offer of employment for the position of <strong>{{ $applicant->jobOpening->title }}</strong> in our <strong>{{ $applicant->jobOpening->department->name }}</strong> department.
            </p>
            <p>
                {{ $companyProfile->offer_letter_intro ?? 'Based on your extensive background and outstanding performance throughout our evaluation rounds, we are confident that your technical skills and leadership mindset will be an invaluable asset to our engineering and organizational benchmarks.' }}
            </p>

            <!-- Key Terms Table -->
            <div class="my-6 rounded-xl border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 px-4 py-2.5 border-b border-slate-200 font-bold text-slate-900 text-xs uppercase tracking-wider">
                    Summary of Employment Terms
                </div>
                <div class="divide-y divide-slate-200 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-3 p-3 bg-white">
                        <span class="font-bold text-slate-500">Designation / Role</span>
                        <span class="sm:col-span-2 font-bold text-slate-900">{{ $applicant->jobOpening->title }}</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 p-3 bg-slate-50/50">
                        <span class="font-bold text-slate-500">Department</span>
                        <span class="sm:col-span-2 text-slate-800">{{ $applicant->jobOpening->department->name }}</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 p-3 bg-white">
                        <span class="font-bold text-slate-500">Employment Nature</span>
                        <span class="sm:col-span-2 text-slate-800">{{ $applicant->jobOpening->employment_type }} ({{ $applicant->jobOpening->experience_level }})</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 p-3 bg-slate-50/50">
                        <span class="font-bold text-slate-500">Work Location</span>
                        <span class="sm:col-span-2 text-slate-800">{{ $applicant->jobOpening->location }}</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 p-3 bg-white">
                        <span class="font-bold text-slate-500">Basic Monthly Remuneration</span>
                        <span class="sm:col-span-2 font-black text-indigo-700 font-mono text-sm">
                            {{ $companyProfile->currency_symbol ?? 'MYR' }} {{ number_format($applicant->offered_salary ?? ($applicant->expected_salary ?? 6500), 2) }} / month
                        </span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 p-3 bg-slate-50/50">
                        <span class="font-bold text-slate-500">Probationary Period</span>
                        <span class="sm:col-span-2 text-slate-800">{{ $applicant->probation_months ?? ($companyProfile->default_probation_months ?? 3) }} (Three) Months, subject to performance appraisal</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 p-3 bg-white">
                        <span class="font-bold text-slate-500">Commencement Date</span>
                        <span class="sm:col-span-2 text-slate-800">{{ $applicant->joining_date ? $applicant->joining_date->format('d F Y') : 'Within 14 business days from confirmation' }}</span>
                    </div>
                    @if($applicant->allowances > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-3 p-3 bg-slate-50/50">
                            <span class="font-bold text-slate-500">Monthly Allowances</span>
                            <span class="sm:col-span-2 text-emerald-700 font-bold font-mono">{{ $companyProfile->currency_symbol ?? 'MYR' }} {{ number_format($applicant->allowances, 2) }}</span>
                        </div>
                    @endif
                    <div class="grid grid-cols-1 sm:grid-cols-3 p-3 bg-white">
                        <span class="font-bold text-slate-500">Statutory Benefits</span>
                        <span class="sm:col-span-2 text-slate-800">
                            {{ $companyProfile->offer_letter_benefits ?? 'EPF (KWSP 12-13%), SOCSO (PERKESO), EIS, Comprehensive Inpatient/Outpatient Medical Insurance' }}, {{ $companyProfile->default_annual_leave_days ?? 14 }} Days Annual Leave
                        </span>
                    </div>
                </div>
            </div>

            @if($applicant->offer_remarks)
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs italic">
                    <strong>Special Terms / Operating Arrangement:</strong> {{ $applicant->offer_remarks }}
                </div>
            @endif

            <p>
                {{ $companyProfile->contract_terms ?? 'This appointment is subject to satisfactory reference checks, statutory verification, and signing of our standard non-disclosure and intellectual property assignment agreement upon commencement.' }}
            </p>
            <p>
                Please confirm your acceptance of this offer by signing and returning a duplicate of this letter within <strong>{{ $companyProfile->offer_validity_days ?? 5 }} (five) working days</strong> from the date of this letter.
            </p>
        </div>

        <!-- Signatures & Acceptance Block -->
        <div class="mt-12 pt-8 border-t border-slate-200 grid grid-cols-1 sm:grid-cols-2 gap-10 text-xs">
            <!-- Employer Signature -->
            <div class="space-y-4">
                <p class="font-bold text-slate-900">Signed on behalf of Company:</p>
                <div class="h-16 flex items-end">
                    <span class="font-serif italic text-lg text-indigo-900 font-bold border-b border-slate-400 pb-1 w-48">
                        {{ $companyProfile->hr_director_name ?? 'Datuk Seri Dr. Ariff Rahman' }}
                    </span>
                </div>
                <div>
                    <p class="font-bold text-slate-900">{{ $companyProfile->hr_director_name ?? 'Datuk Seri Dr. Ariff Rahman' }}</p>
                    <p class="text-slate-500">{{ $companyProfile->hr_director_title ?? 'Chief Human Resources Officer' }}</p>
                    <p class="text-slate-400 text-[10px]">{{ $companyProfile->company_name ?? 'PulseHR Enterprise Solutions Sdn. Bhd.' }}</p>
                </div>
            </div>

            <!-- Candidate Acceptance -->
            <div class="space-y-4">
                <p class="font-bold text-slate-900">Candidate Acceptance:</p>
                <div class="h-16 flex items-end">
                    <div class="border-b border-dashed border-slate-400 pb-1 w-48 text-slate-400 italic text-[10px]">
                        Signature
                    </div>
                </div>
                <div>
                    <p class="font-bold text-slate-900">{{ $applicant->full_name }}</p>
                    <p class="text-slate-500">Date: _______________________</p>
                    <p class="text-slate-400 text-[10px]">Candidate IC / Passport: _________________</p>
                </div>
            </div>
        </div>

        <!-- Footer Notice (Short & 1-line) -->
        <div class="mt-8 pt-4 border-t border-slate-200 flex flex-wrap items-center justify-between gap-2 text-[10px] text-slate-500 font-mono">
            <span>Confidential &bull; Malaysian Employment Act Compliant</span>
            <span>Page 1 of 1</span>
        </div>

    </div>

</body>
</html>
