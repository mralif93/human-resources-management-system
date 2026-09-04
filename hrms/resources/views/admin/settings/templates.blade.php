@extends('layouts.admin')

@section('page-title', 'Offer Letter Master Template & Layout')

@section('content')
<div class="space-y-6">

    <!-- Page Header (matching clinic-invoice-system standard) -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-950 via-slate-900 to-indigo-900 p-6 sm:p-7 border border-indigo-800/40 shadow-xl shadow-indigo-950/40 text-white">
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center text-indigo-300 font-bold border border-white/10">
                        <i class="bx bx-file-blank text-lg"></i>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black tracking-tight">Employment Offer Letter Design Template</h1>
                </div>
                <p class="text-xs text-slate-300">Live preview of official A4 formal letter of employment generated identically for sample and candidate offers</p>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <x-button
                    type="button"
                    variant="primary"
                    size="sm"
                    icon="bx bx-slider-alt"
                    onclick="document.getElementById('modal-edit-template').classList.remove('hidden')"
                    class="shadow-lg shadow-indigo-600/30"
                >
                    Customize Master Template
                </x-button>

                <a
                    href="{{ route('settings.profile') }}"
                    class="h-9 px-3.5 rounded-xl text-xs font-bold bg-white/10 hover:bg-white/20 text-white border border-white/20 transition inline-flex items-center gap-1.5"
                >
                    <i class="bx bx-cog text-sm"></i>
                    <span>Profile Defaults</span>
                </a>

                <x-button
                    type="button"
                    variant="secondary"
                    size="sm"
                    icon="bx bx-printer"
                    onclick="window.print()"
                >
                    Print Template
                </x-button>
            </div>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('status'))
        <x-alert variant="success" icon="bx bx-check-circle" title="Success">
            {{ session('status') }}
        </x-alert>
    @endif

    <!-- Dual Layout: Left (Controls/Summary) + Right (A4 Live Offer Letter Preview) -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">

        <!-- Left Column (4 Cols on XL): Template Metadata & Variables Card -->
        <div class="xl:col-span-4 space-y-4">
            <x-card title="Template Specifications" subtitle="Governance tokens & bindings">
                <div class="space-y-4 text-xs text-slate-600 dark:text-slate-300">
                    <div class="p-3.5 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/60 space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            <span class="font-black text-indigo-950 dark:text-indigo-200 text-xs">Live Corporate Binding</span>
                        </div>
                        <p class="text-[11px] text-indigo-900/75 dark:text-indigo-300 leading-relaxed">
                            Updates made here or in <strong>Company Profile</strong> automatically sync with candidate offer letters and official PDF/print outputs.
                        </p>
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-slate-800 text-[11px]">
                        <div class="py-2.5 flex items-center justify-between gap-3">
                            <span class="text-slate-400 font-medium">Company Entity:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200 text-right truncate max-w-[190px]">{{ $profile->company_name }}</span>
                        </div>
                        <div class="py-2.5 flex items-center justify-between gap-3">
                            <span class="text-slate-400 font-medium">Subject Title:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200 text-right truncate max-w-[190px]">{{ $profile->offer_letter_subject ?? 'Conditional Letter of Employment Offer' }}</span>
                        </div>
                        <div class="py-2.5 flex items-center justify-between gap-3">
                            <span class="text-slate-400 font-medium">Acceptance Window:</span>
                            <span class="px-2 py-0.5 rounded-md font-bold font-mono text-[10px] bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                {{ $profile->offer_validity_days ?? 5 }} Working Days
                            </span>
                        </div>
                        <div class="py-2.5 flex items-center justify-between gap-3">
                            <span class="text-slate-400 font-medium">Standard Probation:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $profile->default_probation_months }} Months</span>
                        </div>
                        <div class="py-2.5 flex items-center justify-between gap-3">
                            <span class="text-slate-400 font-medium">Standard Notice:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $profile->default_notice_period_months }} Months</span>
                        </div>
                        <div class="py-2.5 flex items-center justify-between gap-3">
                            <span class="text-slate-400 font-medium">Annual Leave:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $profile->default_annual_leave_days }} Days / Year</span>
                        </div>
                        <div class="py-2.5 flex items-center justify-between gap-3">
                            <span class="text-slate-400 font-medium">Authorized Signatory:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200 text-right truncate max-w-[180px]">{{ $profile->hr_director_name }}</span>
                        </div>
                    </div>

                    <div class="pt-2 flex flex-col gap-2.5">
                        <x-button
                            type="button"
                            variant="primary"
                            size="md"
                            icon="bx bx-edit"
                            onclick="document.getElementById('modal-edit-template').classList.remove('hidden')"
                            class="w-full justify-center font-bold shadow-md shadow-indigo-600/20"
                        >
                            Customize Template Clauses
                        </x-button>

                        <a
                            href="{{ route('recruitment.index', ['tab' => 'kanban']) }}"
                            class="w-full h-10 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition flex items-center justify-center gap-2 border border-slate-200/80 dark:border-slate-700"
                        >
                            <i class="bx bx-user-check text-base text-sky-500"></i>
                            <span>View ATS Candidates to Issue</span>
                        </a>
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Right Column (8 Cols on XL): Authentic A4 Formal Employment Offer Letter Paper Preview -->
        <div class="xl:col-span-8 w-full min-w-0">
            <!-- Preview Controls Bar -->
            <div class="mb-3.5 flex flex-wrap items-center justify-between gap-2 px-1 text-xs text-slate-500 dark:text-slate-400">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-xs shadow-emerald-500/50"></span>
                    <span class="font-bold text-slate-800 dark:text-slate-200 text-xs">Standard A4 Sheet Preview</span>
                    <span class="px-2 py-0.5 rounded-md font-mono text-[10px] font-semibold bg-slate-200/80 dark:bg-slate-800 text-slate-600 dark:text-slate-300">210mm &times; 297mm &bull; 100% Scale</span>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        onclick="window.print()"
                        class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition flex items-center gap-1.5 cursor-pointer"
                    >
                        <i class="bx bx-printer text-base"></i>
                        <span>Print A4 Document</span>
                    </button>
                </div>
            </div>

            <!-- Print Bed (Desktop Desk / Scanner Mat Simulation) -->
            <div class="a4-preview-bed rounded-3xl p-3 sm:p-6 lg:p-8 bg-slate-200/70 dark:bg-slate-950/80 border border-slate-300/80 dark:border-slate-800/80 flex justify-center items-start overflow-x-auto shadow-inner">
                
                <!-- Authentic A4 Paper Sheet (210mm x 297mm aspect ratio container) -->
                <div class="a4-paper-sheet bg-white text-slate-900 rounded-sm shadow-2xl p-8 sm:p-14 lg:p-16 font-sans text-xs space-y-6 printable-letter w-full mx-auto relative select-text transition-all">
                    
                    <!-- Paper Corner Accent / Binding Indicator (Screen only) -->
                    <div class="no-print absolute top-0 right-0 w-12 h-12 overflow-hidden pointer-events-none">
                        <div class="bg-slate-100 border-l border-b border-slate-300/80 w-16 h-16 transform rotate-45 -translate-y-8 translate-x-8 shadow-xs"></div>
                    </div>

                    <!-- Formal Corporate Letterhead -->
                    <div class="pb-6 border-b-2 border-slate-900">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6">
                            <!-- Left: Organization Identity & Title -->
                            <div class="min-w-0">
                                <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 leading-tight uppercase font-sans">
                                    {{ $profile->company_name }}
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
                                    <span>OFF-{{ date('Y') }}-DEMO</span>
                                </div>
                                <p class="text-[11px] text-slate-600 mt-1 max-w-[240px] leading-relaxed">
                                    {{ $profile->address }}
                                </p>
                                <p class="text-[10px] font-mono text-slate-500">
                                    SSM Reg: {{ $profile->registration_number }}
                                </p>
                                <div class="flex items-center sm:justify-end gap-3 text-[11px] pt-0.5 text-slate-600">
                                    <span class="inline-flex items-center gap-1">
                                        <i class="bx bx-phone text-xs text-indigo-600"></i>
                                        <span>{{ $profile->phone }}</span>
                                    </span>
                                    <span class="inline-flex items-center gap-1 text-indigo-700 font-medium">
                                        <i class="bx bx-envelope text-xs"></i>
                                        <span>{{ $profile->email }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Date & Addressee Block (Date right-aligned, no separator line below) -->
                    <div class="pt-2 text-xs flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <!-- Candidate Contact Info -->
                        <div class="space-y-0.5">
                            <p class="font-bold text-slate-900 text-sm tracking-tight">Sample Candidate Name</p>
                            <p class="text-slate-600">candidate.demo@example.com</p>
                            <p class="text-slate-600 font-mono text-[11px]">+60 12-345 6789</p>
                        </div>

                        <!-- Date (Right Aligned) -->
                        <div class="sm:text-right shrink-0">
                            <p class="font-semibold text-slate-600">Date: <span class="text-slate-900 font-bold font-sans">{{ date('d F Y') }}</span></p>
                        </div>
                    </div>

                    <!-- Subject Header (Underlined) -->
                    <div class="pt-2">
                        <h2 class="text-sm sm:text-base font-extrabold text-slate-900 uppercase tracking-wide underline underline-offset-4 decoration-2 decoration-slate-900">
                            {{ $profile->offer_letter_subject ?? 'Conditional Letter of Employment Offer' }}: Senior Software Engineer
                        </h2>
                    </div>

                    <!-- Salutation (Positioned below Subject Line) -->
                    <div class="pt-1 text-xs text-slate-800">
                        <p>
                            Dear <strong>Candidate</strong>,
                        </p>
                    </div>

                    <!-- Body Paragraphs -->
                    <div class="space-y-3.5 text-xs leading-relaxed text-slate-800">
                        <p>
                            On behalf of <strong>{{ $profile->company_name }}</strong>, we are delighted to extend to you this formal offer of employment for the position of <strong>Senior Software Engineer</strong> in our <strong>Engineering &amp; Technology</strong> department.
                        </p>
                        <p class="text-justify">
                            {{ $profile->offer_letter_intro ?? 'Based on your extensive background and outstanding performance throughout our evaluation rounds, we are confident that your technical skills and leadership mindset will be an invaluable asset to our engineering and organizational benchmarks.' }}
                        </p>

                        <!-- Key Terms Table (Formatted Cleanly for A4 Print Matrix) -->
                        <div class="my-5 rounded-xl border border-slate-300 overflow-hidden">
                            <div class="bg-slate-100 px-4 py-2.5 border-b border-slate-300 font-bold text-slate-900 text-xs uppercase tracking-wider flex items-center justify-between">
                                <span>Summary of Employment Terms</span>
                                <span class="text-[10px] font-mono font-medium text-slate-500 uppercase">Schedule A</span>
                            </div>
                            <div class="divide-y divide-slate-200 text-xs">
                                <div class="grid grid-cols-1 sm:grid-cols-3 p-3 bg-white gap-1 sm:gap-0">
                                    <span class="font-bold text-slate-600">Designation / Role</span>
                                    <span class="sm:col-span-2 font-bold text-slate-900">Senior Software Engineer</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 p-3 bg-slate-50/70 gap-1 sm:gap-0">
                                    <span class="font-bold text-slate-600">Department</span>
                                    <span class="sm:col-span-2 text-slate-900 font-medium">Engineering &amp; Technology</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 p-3 bg-white gap-1 sm:gap-0">
                                    <span class="font-bold text-slate-600">Employment Nature</span>
                                    <span class="sm:col-span-2 text-slate-900">Full-Time (Permanent)</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 p-3 bg-slate-50/70 gap-1 sm:gap-0">
                                    <span class="font-bold text-slate-600">Work Location</span>
                                    <span class="sm:col-span-2 text-slate-900">{{ $profile->address ?? 'Kuala Lumpur HQ' }}</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 p-3 bg-white gap-1 sm:gap-0">
                                    <span class="font-bold text-slate-600">Basic Monthly Remuneration</span>
                                    <span class="sm:col-span-2 font-black text-slate-900 font-mono text-sm">
                                        {{ $profile->currency_symbol }} 8,500.00 / month
                                    </span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 p-3 bg-slate-50/70 gap-1 sm:gap-0">
                                    <span class="font-bold text-slate-600">Probationary Period</span>
                                    <span class="sm:col-span-2 text-slate-900">{{ $profile->default_probation_months }} (Three) Months, subject to performance appraisal</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 p-3 bg-white gap-1 sm:gap-0">
                                    <span class="font-bold text-slate-600">Commencement Date</span>
                                    <span class="sm:col-span-2 text-slate-900">Within 14 business days from confirmation</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 p-3 bg-slate-50/70 gap-1 sm:gap-0">
                                    <span class="font-bold text-slate-600">Monthly Allowances</span>
                                    <span class="sm:col-span-2 text-slate-900 font-bold font-mono">{{ $profile->currency_symbol }} 500.00</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 p-3 bg-white gap-1 sm:gap-0">
                                    <span class="font-bold text-slate-600">Statutory Benefits</span>
                                    <span class="sm:col-span-2 text-slate-900 leading-normal">
                                        {{ $profile->offer_letter_benefits ?? 'EPF (KWSP 12-13%), SOCSO (PERKESO), EIS, Comprehensive Inpatient/Outpatient Medical Insurance' }}, {{ $profile->default_annual_leave_days }} Days Annual Leave
                                    </span>
                                </div>
                            </div>
                        </div>

                        <p class="text-justify">
                            {{ $profile->contract_terms ?? 'This appointment is subject to satisfactory reference checks, statutory verification, and signing of our standard non-disclosure and intellectual property assignment agreement upon commencement.' }}
                        </p>
                        <p>
                            Please confirm your acceptance of this offer by signing and returning a duplicate of this letter within <strong>{{ $profile->offer_validity_days ?? 5 }} (five) working days</strong> from the date of this letter.
                        </p>
                    </div>

                    <!-- Signatures & Acceptance Block -->
                    <div class="mt-8 pt-6 border-t-2 border-slate-300 grid grid-cols-1 sm:grid-cols-2 gap-8 text-xs">
                        <!-- Employer Signature -->
                        <div class="space-y-2.5">
                            <p class="font-bold text-slate-900">Signed on behalf of Company:</p>
                            <div class="h-14 flex items-end">
                                <span class="font-serif italic text-lg text-indigo-950 font-bold border-b border-slate-400 pb-1 w-48">
                                    {{ $profile->hr_director_name }}
                                </span>
                            </div>
                            <div>
                                <p class="font-bold text-slate-900">{{ $profile->hr_director_name }}</p>
                                <p class="text-slate-600 text-[11px]">{{ $profile->hr_director_title }}</p>
                                <p class="text-slate-500 text-[10px]">{{ $profile->company_name }}</p>
                            </div>
                        </div>

                        <!-- Candidate Acceptance -->
                        <div class="space-y-2.5">
                            <p class="font-bold text-slate-900">Candidate Acceptance:</p>
                            <div class="h-14 flex items-end">
                                <div class="border-b border-dashed border-slate-400 pb-1 w-48 text-slate-400 italic text-[10px]">
                                    Signature
                                </div>
                            </div>
                            <div>
                                <p class="font-bold text-slate-900">Sample Candidate Name</p>
                                <p class="text-slate-600 text-[11px]">Date: _______________________</p>
                                <p class="text-slate-500 text-[10px]">Candidate IC / Passport: _________________</p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Notice (Short & 1-line) -->
                    <div class="mt-8 pt-4 border-t border-slate-200 flex flex-wrap items-center justify-between gap-2 text-[10px] text-slate-500 font-mono">
                        <span>Confidential &bull; Malaysian Employment Act Compliant</span>
                        <span>Page 1 of 1</span>
                    </div>

                </div>
            </div>
        </div>

    </div>

</div>

<!-- Modal: Customize Offer Letter Master Template (Clean 2-Column Responsive Layout) -->
<x-modal name="edit-template" title="Customize Offer Letter Master Template" subtitle="Configure legal clauses, subject headers, and default terms" icon="bx-slider-alt" size="xl">
    <form action="{{ route('settings.templates.update') }}" method="POST" class="space-y-4 text-xs">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <x-input
                    label="Offer Letter Subject Line"
                    name="offer_letter_subject"
                    value="{{ old('offer_letter_subject', $profile->offer_letter_subject ?? 'Conditional Letter of Employment Offer') }}"
                    icon="bx bx-heading"
                    hint="Displayed as the primary bold heading across all candidate offer documents"
                    required
                />
            </div>

            <x-input
                label="Offer Acceptance Window (Days)"
                name="offer_validity_days"
                type="number"
                min="1"
                max="60"
                value="{{ old('offer_validity_days', $profile->offer_validity_days ?? 5) }}"
                icon="bx bx-time"
                hint="Days candidate has to sign and return"
                required
            />

            <x-input
                label="Currency Symbol"
                name="currency_symbol"
                value="{{ $profile->currency_symbol }}"
                icon="bx bx-money"
                disabled
                hint="Managed via Company Profile Settings"
            />
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Candidate Welcome / Evaluation Rationale Paragraph
            </label>
            <textarea
                name="offer_letter_intro"
                rows="3"
                class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-3 outline-none focus:ring-2 focus:ring-indigo-500"
            >{{ old('offer_letter_intro', $profile->offer_letter_intro ?? 'Based on your extensive background and outstanding performance throughout our evaluation rounds, we are confident that your technical skills and leadership mindset will be an invaluable asset to our engineering and organizational benchmarks.') }}</textarea>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Statutory &amp; Corporate Benefits Summary Line
            </label>
            <textarea
                name="offer_letter_benefits"
                rows="2"
                placeholder="EPF (KWSP 12-13%), SOCSO (PERKESO), EIS, Comprehensive Inpatient/Outpatient Medical Insurance..."
                class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-3 outline-none focus:ring-2 focus:ring-indigo-500"
            >{{ old('offer_letter_benefits', $profile->offer_letter_benefits ?? 'EPF (KWSP 12-13%), SOCSO (PERKESO), EIS, Comprehensive Inpatient/Outpatient Medical Insurance') }}</textarea>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Standard Contract Terms, Reference Checks &amp; Confidentiality Clause
            </label>
            <textarea
                name="contract_terms"
                rows="3"
                class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-3 outline-none focus:ring-2 focus:ring-indigo-500"
            >{{ old('contract_terms', $profile->contract_terms ?? 'This appointment is subject to satisfactory reference checks, statutory verification, and signing of our standard non-disclosure and intellectual property assignment agreement upon commencement.') }}</textarea>
        </div>

        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5">
            <x-button type="button" variant="ghost" size="md" class="w-full sm:w-auto font-bold justify-center" onclick="document.getElementById('modal-edit-template').classList.add('hidden')">
                Cancel
            </x-button>
            <x-button type="submit" variant="primary" size="md" icon="bx bx-save" class="w-full sm:w-auto font-bold justify-center shadow-md shadow-indigo-600/20">
                Save &amp; Update Master Template
            </x-button>
        </div>
    </form>
</x-modal>

<style>
    /* A4 Paper Dimensions & Screen Presentation */
    .a4-paper-sheet {
        max-width: 210mm;
        min-height: 297mm;
        box-sizing: border-box;
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(0, 0, 0, 0.05);
    }

    @media (max-width: 768px) {
        .a4-paper-sheet {
            min-height: auto;
            max-width: 100%;
        }
    }

    @media print {
        @page {
            size: A4 portrait;
            margin: 15mm 15mm 15mm 15mm;
        }
        header, aside, .relative.overflow-hidden, .xl\:col-span-4, #modal-edit-template, .mb-3\.5, .no-print, footer {
            display: none !important;
        }
        body, main, .a4-preview-bed {
            background: white !important;
            padding: 0 !important;
            margin: 0 !important;
            border: none !important;
            box-shadow: none !important;
        }
        .xl\:col-span-8 {
            width: 100% !important;
            max-width: 100% !important;
        }
        .a4-paper-sheet, .printable-letter {
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
            min-height: auto !important;
        }
    }
</style>

@endsection
