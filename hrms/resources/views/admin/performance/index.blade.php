@extends('layouts.admin')

@section('page-title', 'Performance Appraisals & OKRs')

@section('content')
<div class="space-y-6">

    <!-- Page Header (matching standard) -->
    <x-page-header
        title="Talent Appraisals & OKR Matrix"
        subtitle="Manage 360-degree leadership reviews, quantifiable OKR goal progress, and evaluation ratings"
        icon="bx-target-lock"
    >
        <x-button
            type="button"
            variant="secondary"
            size="md"
            icon="bx bx-plus"
            onclick="document.getElementById('modal-create-okr').classList.remove('hidden')"
            class="bg-white/10 hover:bg-white/20 text-white border-white/20"
        >
            New OKR Goal
        </x-button>

        <!-- Cycle Selector Dropdown -->
        <form method="GET" action="{{ route('performance.index') }}" class="inline-block">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            <div class="relative">
                <select
                    name="cycle_id"
                    onchange="this.form.submit()"
                    class="h-10 pl-3.5 pr-9 rounded-xl text-xs font-bold border border-white/20 bg-white/10 text-white outline-none cursor-pointer appearance-none focus:ring-2 focus:ring-white/30 transition-all"
                >
                    @foreach($cycles as $cycle)
                        <option value="{{ $cycle->id }}" class="bg-slate-900 text-white" {{ $activeCycle?->id == $cycle->id ? 'selected' : '' }}>
                            {{ $cycle->name }} ({{ ucfirst($cycle->status) }})
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-slate-300">
                    <i class="bx bx-chevron-down text-lg"></i>
                </div>
            </div>
        </form>
    </x-page-header>

    <!-- Feedback Alerts -->
    @if (session('status'))
        <x-alert variant="success" icon="bx bx-check-circle" title="Success">
            {{ session('status') }}
        </x-alert>
    @endif

    <!-- High-Impact KPI Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card
            title="Total Appraisals"
            :value="$totalReviews"
            icon="bx bx-file"
            color="indigo"
            subtitle="{{ $activeCycle?->name ?? 'Active Cycle' }}"
        />

        <x-stat-card
            title="Avg Score Rating"
            :value="$avgScore ? number_format($avgScore, 1) . ' / 5.0' : '—'"
            icon="bx bx-star"
            color="amber"
            change="5-point scale"
            changeType="increase"
        />

        <x-stat-card
            title="OKR Success Rate"
            :value="$okrCompletionRate . '%'"
            icon="bx bx-trending-up"
            color="emerald"
            subtitle="Current year goals completed"
        />

        <x-stat-card
            title="Pending Evaluations"
            :value="$pendingEvals"
            icon="bx bx-time"
            color="rose"
            change="{{ $pendingEvals > 0 ? 'Requires Input' : 'Evaluated' }}"
            changeType="{{ $pendingEvals === 0 ? 'increase' : 'neutral' }}"
        />
    </div>

    <!-- Navigation Tabs: Review Roster vs OKR Goals -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 text-xs font-bold">
        <a
            href="{{ route('performance.index', ['tab' => 'reviews', 'cycle_id' => $activeCycle?->id]) }}"
            class="pb-3 px-4 flex items-center gap-2 border-b-2 transition {{ $activeTab === 'reviews' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-900 dark:hover:text-white' }}"
        >
            <i class="bx bx-list-check text-base"></i>
            <span>Appraisal Review Roster</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-indigo-50 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-400 font-mono">
                {{ $totalReviews }}
            </span>
        </a>

        <a
            href="{{ route('performance.index', ['tab' => 'okrs', 'cycle_id' => $activeCycle?->id]) }}"
            class="pb-3 px-4 flex items-center gap-2 border-b-2 transition {{ $activeTab === 'okrs' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-900 dark:hover:text-white' }}"
        >
            <i class="bx bx-target-lock text-base"></i>
            <span>Corporate OKRs &amp; Key Results</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-emerald-50 dark:bg-emerald-950/80 text-emerald-600 dark:text-emerald-400 font-mono">
                {{ $okrs->count() }}
            </span>
        </a>
    </div>

    @if($activeTab === 'reviews')
        <!-- Search and Filter Toolbar -->
        <x-filter-toolbar
            :action="route('performance.index')"
            :resetUrl="route('performance.index', ['tab' => 'reviews', 'cycle_id' => $activeCycle?->id])"
            :searchPlaceholder="'Search employee name, designation...'"
            :filterTitle="'Filter Appraisals'"
        >
            <input type="hidden" name="tab" value="reviews">
            <input type="hidden" name="cycle_id" value="{{ $activeCycle?->id }}">

            <x-slot:filters>
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                        Evaluation Status
                    </label>
                    <x-select name="status" class="w-full">
                        <option value="">All Review Statuses</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Self-Submitted (Needs Review)</option>
                        <option value="reviewed" {{ request('status') == 'reviewed' ? 'selected' : '' }}>Reviewed &amp; Graded</option>
                        <option value="acknowledged" {{ request('status') == 'acknowledged' ? 'selected' : '' }}>Acknowledged</option>
                    </x-select>
                </div>
            </x-slot:filters>
        </x-filter-toolbar>

        <!-- Appraisal Reviews Roster with Mobile Progressive Disclosure -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-950/50 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider text-[11px]">
                            <th class="py-3.5 px-4 sm:px-6">Staff Member</th>
                            <th class="py-3.5 px-4 hidden md:table-cell">Department &amp; Title</th>
                            <th class="py-3.5 px-4">Self Score</th>
                            <th class="py-3.5 px-4">Manager Rating</th>
                            <th class="py-3.5 px-4 hidden sm:table-cell">Status</th>
                            <th class="py-3.5 px-4 sm:px-6 text-right">Evaluate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/70 text-slate-600 dark:text-slate-300 font-medium">
                        @forelse ($reviews as $rev)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="py-3.5 px-4 sm:px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300 font-black flex items-center justify-center text-xs shrink-0">
                                            {{ strtoupper(substr($rev->employee->first_name, 0, 1) . substr($rev->employee->last_name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <span class="font-bold text-slate-900 dark:text-white block truncate max-w-[170px] sm:max-w-none">
                                                {{ $rev->employee->full_name }}
                                            </span>
                                            <div class="flex items-center gap-1.5 text-[10px] text-slate-400 font-mono">
                                                <span>{{ $rev->employee->employee_code }}</span>
                                                <span class="hidden sm:inline">&bull; Reviewer: {{ $rev->reviewer?->full_name ?? 'Pending' }}</span>
                                            </div>

                                            <div class="mt-1 block md:hidden text-[10px] text-slate-500 dark:text-slate-400">
                                                {{ $rev->employee->department?->name ?? 'General' }} &bull; {{ $rev->employee->designation?->title ?? 'Staff' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 hidden md:table-cell">
                                    <span class="font-semibold text-slate-900 dark:text-white block">{{ $rev->employee->designation?->title ?? 'Unassigned' }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $rev->employee->department?->name ?? 'General' }}</span>
                                </td>
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-700 dark:text-slate-300">
                                    {{ $rev->self_score ? number_format($rev->self_score, 1) . ' / 5.0' : 'Pending' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($rev->final_rating)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase tracking-wider
                                            @if($rev->final_rating === 'Outstanding') bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800
                                            @elseif($rev->final_rating === 'Exceeds Expectations') bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800
                                            @elseif($rev->final_rating === 'Meets Expectations') bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800
                                            @else bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 @endif
                                        ">
                                            {{ $rev->final_rating }} ({{ number_format($rev->manager_score, 1) }})
                                        </span>
                                    @else
                                        <span class="text-[10px] text-slate-400 italic">Not Graded</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 hidden sm:table-cell">
                                    @if($rev->status === 'reviewed')
                                        <x-badge variant="emerald" size="sm" :dot="true">Reviewed</x-badge>
                                    @elseif($rev->status === 'submitted')
                                        <x-badge variant="amber" size="sm" :dot="true">Awaiting Review</x-badge>
                                    @else
                                        <x-badge variant="slate" size="sm">Draft</x-badge>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 sm:px-6 text-right">
                                    <x-button
                                        type="button"
                                        variant="{{ $rev->status === 'reviewed' ? 'secondary' : 'primary' }}"
                                        size="xs"
                                        icon="bx bx-edit"
                                        onclick="openEvaluationModal({{ json_encode([
                                            'action' => route('performance.evaluate', $rev),
                                            'employee' => $rev->employee->full_name,
                                            'self_score' => $rev->self_score ?? '—',
                                            'self_remarks' => $rev->self_remarks ?? 'None submitted',
                                            'manager_score' => $rev->manager_score ?? '4.0',
                                            'manager_feedback' => $rev->manager_feedback ?? '',
                                            'key_achievements' => $rev->key_achievements ?? '',
                                            'areas_for_improvement' => $rev->areas_for_improvement ?? '',
                                        ]) }})"
                                    >
                                        {{ $rev->status === 'reviewed' ? 'Edit Rating' : 'Evaluate' }}
                                    </x-button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <i class="bx bx-file-blank text-3xl block mb-2 text-slate-300 dark:text-slate-600"></i>
                                    No performance appraisal records for this cycle.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$reviews" />
        </div>

    @else
        <!-- OKR Goals Progress Matrix (Visual Progress Bars & Cards) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse ($okrs as $okr)
                <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between space-y-4 hover:border-indigo-400/50 transition">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase font-mono bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                {{ $okr->quarter }} {{ $okr->year }}
                            </span>

                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                @if($okr->status === 'completed') bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800
                                @elseif($okr->status === 'on_track') bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800
                                @elseif($okr->status === 'at_risk') bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800
                                @else bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 @endif
                            ">
                                {{ str_replace('_', ' ', $okr->status) }}
                            </span>
                        </div>

                        <div>
                            <h4 class="font-extrabold text-sm text-slate-900 dark:text-white leading-snug">{{ $okr->title }}</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $okr->key_result_metric }}</p>
                        </div>
                    </div>

                    <!-- Progress Bar Component -->
                    <div class="space-y-1.5 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Owner: <strong class="text-slate-900 dark:text-white">{{ $okr->employee->full_name }}</strong></span>
                            <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $okr->progress_percentage }}% ({{ number_format($okr->current_value, 1) }} / {{ number_format($okr->target_value, 1) }})</span>
                        </div>
                        <div class="w-full h-2.5 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                            <div
                                class="h-full rounded-full transition-all duration-500 {{ $okr->status === 'completed' ? 'bg-emerald-500' : ($okr->status === 'at_risk' ? 'bg-amber-500' : 'bg-indigo-600') }}"
                                style="width: {{ $okr->progress_percentage }}%"
                            ></div>
                        </div>

                        <!-- Inline Quick Update Form -->
                        <form method="POST" action="{{ route('performance.okrs.progress', $okr) }}" class="mt-3 flex items-center gap-2 pt-2">
                            @csrf
                            <input
                                type="number"
                                step="0.1"
                                name="current_value"
                                value="{{ $okr->current_value }}"
                                class="w-24 h-8 px-2 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white outline-none"
                            />
                            <x-button type="submit" variant="secondary" size="xs">
                                Update Metric
                            </x-button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-2 py-12 text-center text-slate-400 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800">
                    <i class="bx bx-target-lock text-3xl block mb-2 text-slate-300 dark:text-slate-600"></i>
                    No corporate OKR goals established for this calendar year.
                </div>
            @endforelse
        </div>
    @endif

</div>

<!-- Modal: New OKR Goal -->
<x-modal name="create-okr" title="Establish New Employee OKR" size="xl">
    <form method="POST" action="{{ route('performance.okrs.store') }}" class="space-y-4">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Owner Employee *</label>
                <x-select name="employee_id" required class="w-full">
                    <option value="">Choose Employee...</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_code }})</option>
                    @endforeach
                </x-select>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Quarter *</label>
                    <x-select name="quarter" required class="w-full">
                        <option value="Q1">Q1</option>
                        <option value="Q2">Q2</option>
                        <option value="Q3">Q3</option>
                        <option value="Q4">Q4</option>
                        <option value="Annual">Annual</option>
                    </x-select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Year *</label>
                    <input type="number" name="year" value="{{ date('Y') }}" required class="w-full h-10 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none">
                </div>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Objective Title *</label>
            <input type="text" name="title" required placeholder="e.g. Optimize Microservices Performance" class="w-full h-10 px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Key Result Metric *</label>
            <input type="text" name="key_result_metric" required placeholder="e.g. Maintain 99.9% uptime across production" class="w-full h-10 px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Target Numeric Value *</label>
                <input type="number" step="0.1" name="target_value" value="100.0" required class="w-full h-10 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Current Progress Value *</label>
                <input type="number" step="0.1" name="current_value" value="0.0" required class="w-full h-10 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none">
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2">
            <x-button type="button" variant="ghost" size="sm" onclick="document.getElementById('modal-create-okr').classList.add('hidden')">Cancel</x-button>
            <x-button type="submit" variant="primary" size="sm" icon="bx bx-check">Create Goal</x-button>
        </div>
    </form>
</x-modal>

<!-- Modal: Evaluate Appraisal -->
<x-modal name="evaluate-appraisal" title="Manager Performance Appraisal" size="xl">
    <form id="form-evaluate-appraisal" method="POST" action="" class="space-y-4">
        @csrf

        <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl text-xs space-y-1">
            <p class="font-bold text-slate-900 dark:text-white" id="modal-eval-employee">Employee</p>
            <p class="text-slate-500 dark:text-slate-400">Self Score: <span id="modal-eval-self-score" class="font-bold text-indigo-600"></span></p>
            <p class="text-slate-500 dark:text-slate-400">Self Remarks: <span id="modal-eval-self-remarks" class="italic"></span></p>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Manager Rating Score (1.0 to 5.0) <span class="text-rose-500">*</span>
            </label>
            <select
                id="modal-eval-manager-score"
                name="manager_score"
                required
                class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-indigo-500"
            >
                <option value="5.0">5.0 — Outstanding Performance</option>
                <option value="4.5">4.5 — Outstanding / Exceeds</option>
                <option value="4.0" selected>4.0 — Exceeds Expectations</option>
                <option value="3.5">3.5 — Strong Meets Expectations</option>
                <option value="3.0">3.0 — Meets Expectations</option>
                <option value="2.5">2.5 — Needs Minor Improvement</option>
                <option value="2.0">2.0 — Needs Improvement</option>
                <option value="1.0">1.0 — Unsatisfactory</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Qualitative Manager Feedback *</label>
            <textarea
                id="modal-eval-manager-feedback"
                name="manager_feedback"
                rows="3"
                required
                placeholder="Comprehensive leadership review remarks..."
                class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-indigo-500"
            ></textarea>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Key Strengths &amp; Achievements</label>
            <textarea
                id="modal-eval-key-achievements"
                name="key_achievements"
                rows="2"
                placeholder="High points during cycle..."
                class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-indigo-500"
            ></textarea>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Growth &amp; Focus Areas</label>
            <textarea
                id="modal-eval-areas"
                name="areas_for_improvement"
                rows="2"
                placeholder="Identified skill gaps or areas for mentoring..."
                class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs outline-none focus:ring-2 focus:ring-indigo-500"
            ></textarea>
        </div>

        <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2">
            <x-button type="button" variant="ghost" size="sm" onclick="document.getElementById('modal-evaluate-appraisal').classList.add('hidden')">Cancel</x-button>
            <x-button type="submit" variant="primary" size="sm" icon="bx bx-check-double">Finalize Appraisal</x-button>
        </div>
    </form>
</x-modal>

@push('scripts')
<script>
    function openEvaluationModal(data) {
        document.getElementById('form-evaluate-appraisal').action = data.action;
        document.getElementById('modal-eval-employee').innerText = 'Appraising: ' + data.employee;
        document.getElementById('modal-eval-self-score').innerText = data.self_score;
        document.getElementById('modal-eval-self-remarks').innerText = data.self_remarks;
        document.getElementById('modal-eval-manager-score').value = data.manager_score || '4.0';
        document.getElementById('modal-eval-manager-feedback').value = data.manager_feedback || '';
        document.getElementById('modal-eval-key-achievements').value = data.key_achievements || '';
        document.getElementById('modal-eval-areas').value = data.areas_for_improvement || '';

        document.getElementById('modal-evaluate-appraisal').classList.remove('hidden');
    }
</script>
@endpush
@endsection
