@extends('layouts.admin')

@section('page-title', 'Recruitment ATS & Careers')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar & Summary Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">Applicant Tracking System (ATS)</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Manage job postings, visual candidate hiring pipelines, and 1-click employee conversions.</p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <x-button
                type="button"
                variant="secondary"
                size="md"
                icon="bx bx-user-plus"
                onclick="document.getElementById('modal-create-applicant').classList.remove('hidden')"
            >
                Add Candidate
            </x-button>

            <x-button
                type="button"
                variant="primary"
                size="md"
                icon="bx bx-briefcase"
                onclick="document.getElementById('modal-create-job').classList.remove('hidden')"
                class="shadow-md shadow-indigo-600/20"
            >
                Post Job Opening
            </x-button>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if (session('status'))
        <x-alert variant="success" icon="bx bx-check-circle" title="Success">
            {{ session('status') }}
        </x-alert>
    @endif

    @if (session('error'))
        <x-alert variant="danger" icon="bx bx-error-circle" title="Error">
            {{ session('error') }}
        </x-alert>
    @endif

    <!-- High-Impact KPI Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card
            title="Open Vacancies"
            :value="$totalOpenings"
            icon="bx bx-briefcase-alt-2"
            color="indigo"
            subtitle="Active published listings"
        />

        <x-stat-card
            title="Candidate Pipeline"
            :value="$totalCandidates"
            icon="bx bx-user-pin"
            color="purple"
            subtitle="Total applicants tracked"
        />

        <x-stat-card
            title="Interviews Stage"
            :value="$interviewsCount"
            icon="bx bx-calendar"
            color="amber"
            change="Active pipeline"
            changeType="increase"
        />

        <x-stat-card
            title="Hired / Converted"
            :value="$hiredCount"
            icon="bx bx-check-shield"
            color="emerald"
            subtitle="Onboarded to workforce"
        />
    </div>

    <!-- Navigation Tabs: Kanban Board vs Vacancies List -->
    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 text-xs font-bold gap-4 flex-wrap">
        <div class="flex items-center gap-2">
            <a
                href="{{ route('recruitment.index', ['tab' => 'kanban', 'job_id' => $activeJob?->id]) }}"
                class="pb-3 px-4 flex items-center gap-2 border-b-2 transition {{ $activeTab === 'kanban' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-900 dark:hover:text-white' }}"
            >
                <i class="bx bx-columns text-base"></i>
                <span>Kanban Pipeline</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-indigo-50 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-400 font-mono">
                    {{ $allApplicants->count() }}
                </span>
            </a>

            <a
                href="{{ route('recruitment.index', ['tab' => 'jobs']) }}"
                class="pb-3 px-4 flex items-center gap-2 border-b-2 transition {{ $activeTab === 'jobs' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-900 dark:hover:text-white' }}"
            >
                <i class="bx bx-list-ul text-base"></i>
                <span>Job Openings Directory</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-emerald-50 dark:bg-emerald-950/80 text-emerald-600 dark:text-emerald-400 font-mono">
                    {{ $jobs->count() }}
                </span>
            </a>
        </div>

        <!-- Filter by specific Job Opening -->
        @if($activeTab === 'kanban')
            <form method="GET" action="{{ route('recruitment.index') }}" class="mb-2">
                <input type="hidden" name="tab" value="kanban">
                <div class="relative">
                    <select
                        name="job_id"
                        onchange="this.form.submit()"
                        class="h-10 pl-3.5 pr-10 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-bold focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 appearance-none transition-all cursor-pointer shadow-2xs"
                    >
                        <option value="">All Job Openings (All Roles)</option>
                        @foreach($jobs as $job)
                            <option value="{{ $job->id }}" {{ $activeJob?->id == $job->id ? 'selected' : '' }}>
                                {{ $job->title }} ({{ $job->applicants_count ?? $job->applicants->count() }} candidates)
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400 dark:text-slate-500">
                        <i class="bx bx-chevron-down text-lg"></i>
                    </div>
                </div>
            </form>
        @endif
    </div>

    @if($activeTab === 'kanban')
        <!-- KANBAN PIPELINE VIEW (REQ-ATS-02) -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 overflow-x-auto pb-4">
            
            @php
                $stages = [
                    'applied' => ['title' => 'Applied', 'color' => 'slate', 'icon' => 'bx bx-envelope'],
                    'screened' => ['title' => 'Screened', 'color' => 'blue', 'icon' => 'bx bx-filter-alt'],
                    'interview' => ['title' => 'Interviewing', 'color' => 'amber', 'icon' => 'bx bx-conversation'],
                    'offer' => ['title' => 'Job Offer', 'color' => 'purple', 'icon' => 'bx bx-award'],
                    'hired' => ['title' => 'Hired Staff', 'color' => 'emerald', 'icon' => 'bx bx-check-double'],
                ];
            @endphp

            @foreach($stages as $stageKey => $meta)
                <div class="bg-slate-100/70 dark:bg-slate-950/40 rounded-2xl p-3.5 border border-slate-200/80 dark:border-slate-800/80 flex flex-col min-w-[240px]">
                    <!-- Swimlane Header -->
                    <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-200 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <i class="{{ $meta['icon'] }} text-base text-slate-500"></i>
                            <span class="font-extrabold text-xs text-slate-800 dark:text-white tracking-tight">{{ $meta['title'] }}</span>
                        </div>
                        <span class="w-5 h-5 rounded-full text-[10px] font-mono font-bold flex items-center justify-center bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 shadow-2xs">
                            {{ $kanban[$stageKey]->count() }}
                        </span>
                    </div>

                    <!-- Candidate Cards Container -->
                    <div class="space-y-3 flex-1 overflow-y-auto max-h-[68vh] custom-scrollbar pr-0.5">
                        @forelse($kanban[$stageKey] as $candidate)
                            <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-200 dark:border-slate-800 shadow-2xs hover:border-indigo-400 dark:hover:border-indigo-600 transition space-y-2.5">
                                <div>
                                    <div class="flex items-center justify-between gap-1">
                                        <h5 class="font-bold text-xs text-slate-900 dark:text-white leading-tight">
                                            {{ $candidate->full_name }}
                                        </h5>
                                        @if($candidate->rating)
                                            <span class="flex items-center text-amber-400 text-xs">
                                                <i class="bx bxs-star"></i>
                                                <span class="text-[10px] font-mono font-bold text-slate-600 dark:text-slate-400 ml-0.5">{{ $candidate->rating }}</span>
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-[10px] text-slate-400 font-mono truncate">{{ $candidate->email }}</p>
                                </div>

                                <div class="text-[11px] text-slate-600 dark:text-slate-300 space-y-0.5">
                                    <p class="font-semibold text-indigo-600 dark:text-indigo-400 truncate">{{ $candidate->jobOpening->title }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $candidate->current_company ?? 'Individual' }} &bull; {{ $candidate->experience_years }} yrs exp</p>
                                </div>

                                @if($candidate->interview_notes)
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 italic bg-slate-50 dark:bg-slate-800/50 p-2 rounded-lg leading-tight line-clamp-2">
                                        "{{ $candidate->interview_notes }}"
                                    </p>
                                @endif

                                <!-- Card Action Controls -->
                                <div class="pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                                    <!-- Standardized Stage Mover Dropdown -->
                                    <form method="POST" action="{{ route('recruitment.applicants.stage', $candidate) }}" class="flex-1 min-w-0">
                                        @csrf
                                        <div class="relative w-full">
                                            <select
                                                name="stage"
                                                onchange="this.form.submit()"
                                                class="w-full h-8 py-1.5 pl-2.5 pr-7 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-[11px] font-bold focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 appearance-none transition-all cursor-pointer truncate"
                                            >
                                                <option value="applied" {{ $candidate->stage === 'applied' ? 'selected' : '' }}>Stage: Applied</option>
                                                <option value="screened" {{ $candidate->stage === 'screened' ? 'selected' : '' }}>Stage: Screened</option>
                                                <option value="interview" {{ $candidate->stage === 'interview' ? 'selected' : '' }}>Stage: Interview</option>
                                                <option value="offer" {{ $candidate->stage === 'offer' ? 'selected' : '' }}>Stage: Offer</option>
                                                <option value="hired" {{ $candidate->stage === 'hired' ? 'selected' : '' }}>Stage: Hired</option>
                                                <option value="rejected" {{ $candidate->stage === 'rejected' ? 'selected' : '' }}>Stage: Reject</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-slate-400 dark:text-slate-500">
                                                <i class="bx bx-chevron-down text-base"></i>
                                            </div>
                                        </div>
                                    </form>

                                    <!-- Offer Letter & 1-Click Conversion Buttons -->
                                    @if($candidate->stage === 'offer' || $candidate->stage === 'hired')
                                        <button
                                            type="button"
                                            title="Customize Offer Terms & Details"
                                            onclick="openCustomizeOfferModal({{ json_encode([
                                                'id' => $candidate->id,
                                                'name' => $candidate->full_name,
                                                'salary' => $candidate->offered_salary ?? ($candidate->expected_salary ?? 6500),
                                                'joiningDate' => $candidate->joining_date?->toDateString() ?? date('Y-m-d', strtotime('+2 weeks')),
                                                'probation' => $candidate->probation_months ?? 3,
                                                'notice' => $candidate->notice_period_months ?? 2,
                                                'allowances' => $candidate->allowances ?? 0,
                                                'remarks' => $candidate->offer_remarks ?? '',
                                            ]) }})"
                                            class="h-8 px-2 rounded-xl text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/60 border border-amber-200 dark:border-amber-800 text-[11px] font-bold transition inline-flex items-center gap-1 shrink-0 cursor-pointer"
                                        >
                                            <i class="bx bx-edit text-sm"></i>
                                            <span>Adjust</span>
                                        </button>

                                        <button
                                            type="button"
                                            title="View Formal Offer Letter Modal"
                                            onclick="openOfferLetterModal({{ json_encode([
                                                'name' => $candidate->full_name,
                                                'firstName' => $candidate->first_name,
                                                'email' => $candidate->email,
                                                'phone' => $candidate->phone ?? 'Contact on file',
                                                'refNo' => 'OFF-' . date('Y') . '-' . str_pad($candidate->id, 4, '0', STR_PAD_LEFT),
                                                'date' => date('d F Y'),
                                                'jobTitle' => $candidate->jobOpening->title,
                                                'department' => $candidate->jobOpening->department->name,
                                                'employmentType' => $candidate->jobOpening->employment_type . ' (' . $candidate->jobOpening->experience_level . ')',
                                                'location' => $candidate->jobOpening->location,
                                                'salary' => number_format($candidate->offered_salary ?? ($candidate->expected_salary ?? 6500), 2),
                                                'joiningDate' => $candidate->joining_date ? $candidate->joining_date->format('d F Y') : 'Immediate / Within 14 Days',
                                                'probation' => ($candidate->probation_months ?? 3) . ' Months subject to appraisal',
                                                'allowances' => $candidate->allowances > 0 ? 'MYR ' . number_format($candidate->allowances, 2) . ' monthly allowance' : 'Standard package',
                                                'remarks' => $candidate->offer_remarks,
                                                'printUrl' => route('recruitment.applicants.offer-letter', $candidate),
                                            ]) }})"
                                            class="h-8 px-2.5 rounded-xl text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800 text-[11px] font-bold transition inline-flex items-center gap-1 shrink-0 cursor-pointer"
                                        >
                                            <i class="bx bx-file text-sm"></i>
                                            <span>Offer</span>
                                        </button>

                                        @if(!$candidate->employee_id)
                                            <form method="POST" action="{{ route('recruitment.applicants.convert', $candidate) }}" class="shrink-0">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    title="One-click Convert Candidate to Employee Roster"
                                                    class="h-8 px-2.5 rounded-xl text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-[11px] font-bold transition cursor-pointer inline-flex items-center gap-1"
                                                >
                                                    <i class="bx bx-user-check text-sm"></i>
                                                    <span>Onboard</span>
                                                </button>
                                            </form>
                                        @else
                                            <a
                                                href="{{ route('employees.show', $candidate->employee_id) }}"
                                                class="h-8 px-2.5 rounded-xl text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950/60 text-[11px] font-bold border border-indigo-200 dark:border-indigo-800 inline-flex items-center shrink-0"
                                                title="View Onboarded Employee Dossier"
                                            >
                                                Staff
                                            </a>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-slate-400 text-xs italic">
                                No candidates
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach

        </div>

    @else
        <!-- VACANCIES DIRECTORY TABLE VIEW (REQ-ATS-01) -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-950/50 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[11px]">
                            <th class="py-3.5 px-4 sm:px-6">Job Role &amp; Title</th>
                            <th class="py-3.5 px-4 hidden md:table-cell">Department</th>
                            <th class="py-3.5 px-4 hidden sm:table-cell">Type &amp; Level</th>
                            <th class="py-3.5 px-4">Openings</th>
                            <th class="py-3.5 px-4">Applicants</th>
                            <th class="py-3.5 px-4 hidden lg:table-cell">Status</th>
                            <th class="py-3.5 px-4 sm:px-6 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/70 text-slate-600 dark:text-slate-300 font-medium">
                        @forelse ($jobs as $job)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="py-3.5 px-4 sm:px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300 font-black flex items-center justify-center text-xs shrink-0">
                                            <i class="bx bx-briefcase text-base"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <span class="font-bold text-slate-900 dark:text-white block truncate max-w-[200px] sm:max-w-none">
                                                {{ $job->title }}
                                            </span>
                                            <span class="text-[10px] text-slate-400">{{ $job->location }}</span>
                                            
                                            <div class="mt-1 block md:hidden text-[10px] text-slate-500">
                                                {{ $job->department->name }} &bull; {{ $job->employment_type }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 hidden md:table-cell font-semibold text-slate-800 dark:text-slate-200">
                                    {{ $job->department->name }}
                                </td>
                                <td class="py-3.5 px-4 hidden sm:table-cell">
                                    <span class="font-medium text-slate-700 dark:text-slate-300 block">{{ $job->employment_type }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $job->experience_level }}</span>
                                </td>
                                <td class="py-3.5 px-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                    {{ $job->openings_count }}
                                </td>
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-700 dark:text-slate-300">
                                    {{ $job->applicants->count() }} candidates
                                </td>
                                <td class="py-3.5 px-4 hidden lg:table-cell">
                                    <x-badge variant="emerald" size="sm" :dot="true">
                                        Published
                                    </x-badge>
                                </td>
                                <td class="py-3.5 px-4 sm:px-6 text-right">
                                    <a
                                        href="{{ route('recruitment.index', ['tab' => 'kanban', 'job_id' => $job->id]) }}"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl font-bold text-xs text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 transition"
                                    >
                                        <span>View Pipeline</span>
                                        <i class="bx bx-right-arrow-alt text-base"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <i class="bx bx-briefcase text-3xl block mb-2 text-slate-300 dark:text-slate-600"></i>
                                    No job openings currently published.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>

<!-- Modal: Post Job Opening -->
<x-modal name="create-job" title="Publish New Job Vacancy" size="2xl">
    <form method="POST" action="{{ route('recruitment.jobs.store') }}" class="space-y-4">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Job Title *</label>
                <input type="text" name="title" required placeholder="e.g. Lead DevOps Engineer" class="w-full h-10 px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Department *</label>
                <x-select name="department_id" required class="w-full">
                    <option value="">Select Department...</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </x-select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Employment Type *</label>
                <x-select name="employment_type" required class="w-full">
                    <option value="Full-Time">Full-Time</option>
                    <option value="Part-Time">Part-Time</option>
                    <option value="Contract">Contract</option>
                    <option value="Internship">Internship</option>
                </x-select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Experience Level *</label>
                <x-select name="experience_level" required class="w-full">
                    <option value="Junior">Junior</option>
                    <option value="Mid-Level" selected>Mid-Level</option>
                    <option value="Senior">Senior</option>
                    <option value="Lead">Lead</option>
                    <option value="Executive">Executive</option>
                </x-select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Openings Count *</label>
                <input type="number" name="openings_count" value="1" min="1" max="20" required class="w-full h-10 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Location *</label>
                <input type="text" name="location" value="Kuala Lumpur, Malaysia (Hybrid)" required class="w-full h-10 px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Job Description *</label>
            <textarea name="description" rows="3" required placeholder="Role summary, responsibilities..." class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Candidate Requirements</label>
            <textarea name="requirements" rows="2" placeholder="Required qualifications, frameworks, stack..." class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
        </div>

        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2">
            <x-button type="button" variant="ghost" size="sm" onclick="document.getElementById('modal-create-job').classList.add('hidden')">Cancel</x-button>
            <x-button type="submit" variant="primary" size="sm" icon="bx bx-check">Publish Opening</x-button>
        </div>
    </form>
</x-modal>

<!-- Modal: Intake Candidate Applicant -->
<x-modal name="create-applicant" title="Intake Candidate Application" size="xl">
    <form method="POST" action="{{ route('recruitment.applicants.store') }}" class="space-y-4">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Job Role Applying For *</label>
                <x-select name="job_opening_id" required class="w-full">
                    <option value="">Select Opening...</option>
                    @foreach($jobs as $j)
                        <option value="{{ $j->id }}">{{ $j->title }} ({{ $j->department->name }})</option>
                    @endforeach
                </x-select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">First Name *</label>
                <input type="text" name="first_name" required class="w-full h-10 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Last Name *</label>
                <input type="text" name="last_name" required class="w-full h-10 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Email Address *</label>
                <input type="email" name="email" required class="w-full h-10 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Phone Number</label>
                <input type="text" name="phone" placeholder="+60 12-345 6789" class="w-full h-10 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Experience (Years) *</label>
                <input type="number" step="0.5" name="experience_years" value="3.0" required class="w-full h-10 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Expected Salary (MYR)</label>
                <input type="number" step="100" name="expected_salary" placeholder="7500" class="w-full h-10 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Initial Stage *</label>
                <x-select name="stage" required class="w-full">
                    <option value="applied" selected>Applied</option>
                    <option value="screened">Screened</option>
                    <option value="interview">Interview</option>
                    <option value="offer">Offer</option>
                    <option value="hired">Hired</option>
                </x-select>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2">
            <x-button type="button" variant="ghost" size="sm" onclick="document.getElementById('modal-create-applicant').classList.add('hidden')">Cancel</x-button>
            <x-button type="submit" variant="primary" size="sm" icon="bx bx-check">Add Candidate</x-button>
        </div>
    </form>
</x-modal>

<!-- Modal: Offer Letter Document Preview (4xl) -->
<x-modal name="offer-letter" title="Employment Offer Letter" size="4xl">
    <div class="space-y-6">
        <!-- Document Toolbar inside Modal -->
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800 flex-wrap gap-2">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 rounded-lg text-xs font-bold font-mono bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                    STATUS: FORMAL OFFER ISSUED
                </span>
                <span id="ol-ref-badge" class="text-xs text-slate-400 font-mono"></span>
            </div>

            <div class="flex items-center gap-2">
                <a
                    id="ol-print-link"
                    href="#"
                    target="_blank"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-600/20 transition cursor-pointer"
                >
                    <i class="bx bx-printer text-base"></i>
                    <span>Print / PDF</span>
                </a>
            </div>
        </div>

        <!-- Rendered Letter Preview Paper -->
        <div class="bg-white text-slate-900 rounded-2xl p-6 sm:p-10 border border-slate-200 shadow-inner space-y-6 text-xs max-h-[60vh] overflow-y-auto custom-scrollbar">
            <!-- Letterhead -->
            <div class="flex items-center justify-between pb-6 border-b border-slate-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-black text-lg">
                        <i class="bx bx-cube-alt"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-black text-slate-900">PulseHR Enterprise Solutions</h4>
                        <p class="text-[10px] text-slate-500">Corporate Human Capital Division</p>
                    </div>
                </div>
                <div class="text-right text-[10px] text-slate-500">
                    <p class="font-bold text-slate-900" id="ol-ref-no"></p>
                    <p>Level 28, Menara Pulse, KL Sentral</p>
                    <p>50470 Kuala Lumpur, Malaysia</p>
                </div>
            </div>

            <!-- Addressee Details -->
            <div class="space-y-1">
                <p class="text-slate-500">Date: <strong class="text-slate-900" id="ol-date"></strong></p>
                <div class="pt-1 font-medium">
                    <p class="font-bold text-slate-900 text-sm" id="ol-cand-name"></p>
                    <p class="text-slate-600" id="ol-cand-email"></p>
                    <p class="text-slate-600" id="ol-cand-phone"></p>
                </div>
            </div>

            <!-- Letter Title -->
            <div class="pt-2 border-t border-slate-100">
                <h5 class="font-black text-xs text-slate-900 uppercase tracking-wide" id="ol-subject"></h5>
            </div>

            <!-- Body Content -->
            <div class="space-y-3 text-slate-700 leading-relaxed text-xs">
                <p>Dear <strong id="ol-first-name"></strong>,</p>
                <p>
                    On behalf of <strong>PulseHR Enterprise Solutions Sdn. Bhd.</strong>, we are delighted to formally offer you the position of <strong id="ol-role-title"></strong> in our <strong id="ol-dept-title"></strong> department.
                </p>
                <p>
                    Based on your comprehensive track record and technical competency evaluated during our hiring process, we believe you will be an invaluable asset to our engineering and organizational benchmarks.
                </p>

                <!-- Employment Terms Matrix -->
                <div class="my-4 rounded-xl border border-slate-200 overflow-hidden">
                    <div class="bg-slate-50 px-3.5 py-2 border-b border-slate-200 font-bold text-slate-900 text-[11px] uppercase tracking-wider">
                        Key Employment Terms
                    </div>
                    <div class="divide-y divide-slate-200 text-xs">
                        <div class="grid grid-cols-3 p-2.5 bg-white">
                            <span class="font-bold text-slate-500">Role / Position</span>
                            <span class="col-span-2 font-bold text-slate-900" id="ol-term-role"></span>
                        </div>
                        <div class="grid grid-cols-3 p-2.5 bg-slate-50/50">
                            <span class="font-bold text-slate-500">Department</span>
                            <span class="col-span-2 text-slate-800" id="ol-term-dept"></span>
                        </div>
                        <div class="grid grid-cols-3 p-2.5 bg-white">
                            <span class="font-bold text-slate-500">Employment Nature</span>
                            <span class="col-span-2 text-slate-800" id="ol-term-type"></span>
                        </div>
                        <div class="grid grid-cols-3 p-2.5 bg-slate-50/50">
                            <span class="font-bold text-slate-500">Work Location</span>
                            <span class="col-span-2 text-slate-800" id="ol-term-loc"></span>
                        </div>
                        <div class="grid grid-cols-3 p-2.5 bg-white">
                            <span class="font-bold text-slate-500">Monthly Remuneration</span>
                            <span class="col-span-2 font-black text-indigo-700 font-mono text-sm" id="ol-term-salary"></span>
                        </div>
                        <div class="grid grid-cols-3 p-2.5 bg-slate-50/50">
                            <span class="font-bold text-slate-500">Probationary Period</span>
                            <span class="col-span-2 text-slate-800" id="ol-term-probation"></span>
                        </div>
                        <div class="grid grid-cols-3 p-2.5 bg-white">
                            <span class="font-bold text-slate-500">Commencement / Joining</span>
                            <span class="col-span-2 text-slate-800" id="ol-term-joining"></span>
                        </div>
                        <div class="grid grid-cols-3 p-2.5 bg-slate-50/50">
                            <span class="font-bold text-slate-500">Special Allowances</span>
                            <span class="col-span-2 text-slate-800" id="ol-term-allowance"></span>
                        </div>
                        <div class="grid grid-cols-3 p-2.5 bg-white">
                            <span class="font-bold text-slate-500">Statutory Benefits</span>
                            <span class="col-span-2 text-slate-800">EPF (KWSP 12-13%), SOCSO (PERKESO), EIS, Medical Insurance, 14 Days Annual Leave</span>
                        </div>
                    </div>
                </div>

                <div id="ol-remarks-container" class="hidden p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs italic">
                    <p id="ol-remarks-text"></p>
                </div>

                <p>
                    Please review this document and indicate your acceptance within <strong>5 working days</strong>.
                </p>
            </div>

            <!-- Signature block -->
            <div class="pt-6 border-t border-slate-200 grid grid-cols-2 gap-6 text-xs">
                <div>
                    <p class="font-bold text-slate-900">Signed on behalf of Company:</p>
                    <div class="h-10 flex items-end">
                        <span class="font-serif italic text-base text-indigo-900 font-bold border-b border-slate-400 pb-0.5 w-48">
                            {{ \App\Models\CompanyProfile::current()->hr_director_name ?? 'HR Director' }}
                        </span>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1">{{ \App\Models\CompanyProfile::current()->hr_director_title ?? 'Head of People Operations' }}</p>
                </div>
                <div>
                    <p class="font-bold text-slate-900">Candidate Acceptance:</p>
                    <div class="h-10 flex items-end">
                        <span class="border-b border-dashed border-slate-400 pb-0.5 w-36 text-slate-400 text-[10px]">
                            Pending Signature
                        </span>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1" id="ol-sign-name"></p>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
            <x-button type="button" variant="ghost" size="sm" onclick="document.getElementById('modal-offer-letter').classList.add('hidden')">
                Close
            </x-button>
        </div>
    </div>
</x-modal>

<!-- Modal: Customize Candidate Offer Details (Admin / Manager) -->
<x-modal name="customize-offer" title="Adjust Employment Offer Terms" size="lg">
    <form id="customize-offer-form" method="POST" action="" class="space-y-4 text-xs">
        @csrf
        @method('PUT')

        <div class="p-3 bg-indigo-50/70 dark:bg-indigo-950/40 rounded-xl border border-indigo-100 dark:border-indigo-900/60">
            <p class="text-[10px] font-bold text-indigo-500 uppercase">Customizing Offer Terms For</p>
            <h4 class="font-black text-sm text-indigo-950 dark:text-white" id="co-cand-name"></h4>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input label="Basic Monthly Salary (MYR)" name="offered_salary" id="co-salary" type="number" step="0.01" required />
            <x-input label="Monthly Allowances (MYR)" name="allowances" id="co-allowances" type="number" step="0.01" placeholder="0.00" />

            <x-input label="Target Joining Date" name="joining_date" id="co-joining-date" type="date" />
            <x-input label="Probation Period (Months)" name="probation_months" id="co-probation" type="number" min="1" max="12" value="3" />

            <div class="sm:col-span-2">
                <x-input label="Notice Period (Months)" name="notice_period_months" id="co-notice" type="number" min="1" max="12" value="2" />
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Custom Terms / Special Remarks (e.g. Remote work, non-compete clause)
            </label>
            <textarea
                name="offer_remarks"
                id="co-remarks"
                rows="2"
                placeholder="e.g. Hybrid working arrangement: 3 days in-office, 2 days remote."
                class="w-full text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white p-2.5 outline-none focus:ring-2 focus:ring-indigo-500"
            ></textarea>
        </div>

        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2">
            <x-button type="button" variant="ghost" size="sm" onclick="document.getElementById('modal-customize-offer').classList.add('hidden')">
                Cancel
            </x-button>
            <x-button type="submit" variant="primary" size="sm" icon="bx bx-save">
                Save &amp; Update Offer
            </x-button>
        </div>
    </form>
</x-modal>

@push('scripts')
<script>
    function openCustomizeOfferModal(data) {
        const form = document.getElementById('customize-offer-form');
        form.action = `/recruitment/applicants/${data.id}/offer-details`;

        document.getElementById('co-cand-name').innerText = data.name;
        document.getElementById('co-salary').value = data.salary;
        document.getElementById('co-allowances').value = data.allowances || 0;
        document.getElementById('co-joining-date').value = data.joiningDate || '';
        document.getElementById('co-probation').value = data.probation || 3;
        document.getElementById('co-notice').value = data.notice || 2;
        document.getElementById('co-remarks').value = data.remarks || '';

        document.getElementById('modal-customize-offer').classList.remove('hidden');
    }

    function openOfferLetterModal(data) {
        document.getElementById('ol-ref-badge').innerText = data.refNo;
        document.getElementById('ol-ref-no').innerText = 'Ref: ' + data.refNo;
        document.getElementById('ol-date').innerText = data.date;
        document.getElementById('ol-cand-name').innerText = data.name;
        document.getElementById('ol-cand-email').innerText = data.email;
        document.getElementById('ol-cand-phone').innerText = data.phone;
        document.getElementById('ol-subject').innerText = 'Conditional Letter of Employment Offer: ' + data.jobTitle;
        document.getElementById('ol-first-name').innerText = data.firstName;
        document.getElementById('ol-role-title').innerText = data.jobTitle;
        document.getElementById('ol-dept-title').innerText = data.department;
        document.getElementById('ol-term-role').innerText = data.jobTitle;
        document.getElementById('ol-term-dept').innerText = data.department;
        document.getElementById('ol-term-type').innerText = data.employmentType;
        document.getElementById('ol-term-loc').innerText = data.location;
        document.getElementById('ol-term-salary').innerText = 'MYR ' + data.salary + ' / month';
        document.getElementById('ol-term-probation').innerText = data.probation;
        document.getElementById('ol-term-joining').innerText = data.joiningDate;
        document.getElementById('ol-term-allowance').innerText = data.allowances;

        const remarksContainer = document.getElementById('ol-remarks-container');
        const remarksText = document.getElementById('ol-remarks-text');
        if (data.remarks && data.remarks.trim() !== '') {
            remarksText.innerText = data.remarks;
            remarksContainer.classList.remove('hidden');
        } else {
            remarksContainer.classList.add('hidden');
        }

        document.getElementById('ol-sign-name').innerText = data.name;
        document.getElementById('ol-print-link').href = data.printUrl;

        document.getElementById('modal-offer-letter').classList.remove('hidden');
    }
</script>
@endpush

@endsection
