<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200/80 pb-6">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-800 transition-colors">{{ __('Dashboard') }}</a>
                <span>/</span>
                <span class="text-brand-700 font-bold">{{ __('Coursework & Assignments') }}</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Coursework & Assignments') }}</h1>
            <p class="text-xs text-slate-500 mt-1">
                {{ __('Track assignment deadlines, view your verified submissions, marks, and lecturer feedback (§8, §10)') }}
            </p>
        </div>

        @if ($student)
            <div class="flex items-center gap-2">
                <div class="px-3.5 py-1.5 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-brand-50 text-brand-700 font-black text-[10px] flex items-center justify-center">
                        {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                    </div>
                    <div class="text-left">
                        <span class="text-xs font-bold text-slate-900 block leading-tight">{{ $student->full_name }}</span>
                        <span class="text-[10px] text-slate-400 font-mono block leading-tight">{{ $student->student_code ?? 'ID: '.$student->id }}</span>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Multi-child switcher for parents -->
    @if ($children->count() > 1)
        <div class="bg-white p-3 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-2 overflow-x-auto">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider px-2 shrink-0">{{ __('Select Child:') }}</span>
            <div class="flex items-center gap-2">
                @foreach ($children as $c)
                    <button 
                        type="button" 
                        wire:click="selectStudent({{ $c->id }})"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer {{ $selectedStudentId === $c->id ? 'bg-brand-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        {{ $c->full_name }}
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Metrics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 sm:gap-4">
        <!-- Total Coursework -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Total Coursework') }}</p>
            <p class="text-2xl font-black text-slate-900 mt-1">{{ $totalCount }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5">{{ count($enrolledClasses) }} {{ __('enrolled classes') }}</p>
        </div>

        <!-- To Do -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <p class="text-[10px] font-bold text-rose-600 uppercase tracking-wider">{{ __('To Do') }}</p>
            <p class="text-2xl font-black text-rose-600 mt-1">{{ $todoCount }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5">{{ __('Awaiting upload') }}</p>
        </div>

        <!-- Under Review -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <p class="text-[10px] font-bold text-amber-600 uppercase tracking-wider">{{ __('Under Review') }}</p>
            <p class="text-2xl font-black text-amber-600 mt-1">{{ $submittedCount }}</p>
            <p class="text-[10px] text-slate-400 mt-0.5">{{ __('Submitted, not yet graded') }}</p>
        </div>

        <!-- Graded -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">{{ __('Graded') }}</p>
            <p class="text-2xl font-black text-emerald-600 mt-1">{{ $gradedCount }}</p>
            @if ($averagePercentage !== null)
                <p class="text-[10px] font-semibold text-emerald-700 mt-0.5">Avg: {{ $averagePercentage }}% ({{ $passedCount }} {{ __('passed') }})</p>
            @else
                <p class="text-[10px] text-slate-400 mt-0.5">{{ __('Completed') }}</p>
            @endif
        </div>

        <!-- Needs Revision -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider">{{ __('Needs Revision') }}</p>
            <p class="text-2xl font-black text-indigo-600 mt-1">{{ $returnedCount }}</p>
            <p class="text-[10px] text-indigo-600 mt-0.5 font-semibold">{{ __('Returned by lecturer') }}</p>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <!-- Status Tabs -->
        <div class="flex flex-wrap items-center gap-1.5 bg-slate-100/80 p-1 rounded-2xl border border-slate-200/80">
            <button 
                type="button" 
                wire:click="$set('statusFilter', 'all')" 
                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer {{ $statusFilter === 'all' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                {{ __('All') }} ({{ $totalCount }})
            </button>
            <button 
                type="button" 
                wire:click="$set('statusFilter', 'todo')" 
                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer {{ $statusFilter === 'todo' ? 'bg-rose-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                {{ __('To Do') }} ({{ $todoCount }})
            </button>
            <button 
                type="button" 
                wire:click="$set('statusFilter', 'submitted')" 
                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer {{ $statusFilter === 'submitted' ? 'bg-amber-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                {{ __('Under Review') }} ({{ $submittedCount }})
            </button>
            <button 
                type="button" 
                wire:click="$set('statusFilter', 'graded')" 
                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer {{ $statusFilter === 'graded' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                {{ __('Graded') }} ({{ $gradedCount }})
            </button>
            <button 
                type="button" 
                wire:click="$set('statusFilter', 'returned')" 
                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer {{ $statusFilter === 'returned' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                {{ __('Needs Revision') }} ({{ $returnedCount }})
            </button>
        </div>

        <div class="flex items-center gap-2">
            <!-- Class Filter -->
            @if ($enrolledClasses->count() > 1)
                <select 
                    wire:model.live="classFilter" 
                    class="px-3 py-2 rounded-xl text-xs bg-white border border-slate-200 focus:border-brand-600 font-medium shadow-xs">
                    <option value="all">{{ __('All Classes') }}</option>
                    @foreach ($enrolledClasses as $c)
                        <option value="{{ $c->id }}">{{ $c->title }}</option>
                    @endforeach
                </select>
            @endif

            <!-- Search -->
            <div class="w-full sm:w-64">
                <div class="relative">
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="{{ __('Search assignments...') }}"
                        class="w-full pl-9 pr-4 py-2 rounded-xl text-xs bg-white border border-slate-200 focus:border-brand-600 focus:ring-brand-600 shadow-xs"
                    />
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignments List -->
    <div class="space-y-4">
        @forelse ($items as $item)
            @php
                $assignment = $item['assignment'];
                $submission = $item['submission'];
                $effectiveDeadline = $item['effective_deadline'];
                $isExtended = $item['is_extended'];
                $isPastDeadline = $item['is_past_deadline'];
                $statusKey = $item['status_key'];
                $percentage = $item['percentage'];
                $isPassed = $item['is_passed'];
            @endphp
            <div class="bg-white rounded-3xl border border-slate-200 p-5 sm:p-6 shadow-xs hover:border-brand-300 transition-all space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 border-b border-slate-100 pb-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-bold text-brand-700 bg-brand-50 border border-brand-200/60 px-2.5 py-0.5 rounded-full">
                                {{ $assignment->schoolClass?->title ?? __('Coursework') }}
                            </span>

                            @if ($submission)
                                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full border {{ $submission->status->badgeClasses() }}">
                                    {{ $submission->status->label() }}
                                </span>
                                <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded-md">
                                    v{{ $submission->version }}
                                </span>
                                @if ($submission->isLate())
                                    <span class="text-[10px] font-bold text-amber-800 bg-amber-100 px-1.5 py-0.5 rounded-md">
                                        {{ __('Late') }}
                                    </span>
                                @endif
                            @else
                                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200/80">
                                    {{ __('Not Submitted') }}
                                </span>
                            @endif

                            @if ($isExtended)
                                <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 font-bold text-[10px] uppercase">
                                    ⏰ {{ __('Extended Deadline') }}
                                </span>
                            @endif
                        </div>

                        <h2 class="text-lg font-black text-slate-900 tracking-tight">
                            <a href="{{ route('assignments.show', ['assignment' => $assignment->id, 'student' => $student?->id]) }}" class="hover:text-brand-600 transition-colors">
                                {{ $assignment->title }}
                            </a>
                        </h2>

                        @if ($assignment->description)
                            <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">
                                {{ $assignment->description }}
                            </p>
                        @endif
                    </div>

                    <!-- Deadline Badge -->
                    <div class="text-left sm:text-right shrink-0">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Due Date') }}</p>
                        <p class="text-xs font-bold {{ $isPastDeadline ? 'text-rose-600 font-black' : ($isExtended ? 'text-amber-800 font-bold' : 'text-slate-800') }} mt-0.5">
                            {{ format_portal_datetime($effectiveDeadline, 'M d, Y • h:i A') }}
                        </p>
                        <p class="text-[10px] text-slate-400 mt-0.5">
                            {{ $isPastDeadline ? ($assignment->allow_late ? __('Late submissions allowed') : __('Closed')) : $effectiveDeadline->diffForHumans() }}
                        </p>
                    </div>
                </div>

                <!-- Graded Results Callout (§8, T-5.5) -->
                @if ($submission && $submission->isGraded())
                    <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="font-black text-emerald-900">{{ __('Official Grade Awarded:') }}</span>
                                <span class="text-base font-black text-emerald-800">{{ $submission->marks }} / {{ $assignment->total_marks }}</span>
                                <span class="text-xs font-bold text-emerald-700">({{ $percentage }}%)</span>
                                @if ($assignment->pass_marks !== null)
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider {{ $isPassed ? 'bg-emerald-200 text-emerald-950' : 'bg-rose-200 text-rose-950' }}">
                                        {{ $isPassed ? __('Passed') : __('Did Not Pass') }}
                                    </span>
                                @endif
                            </div>
                            @if ($submission->feedback)
                                <p class="text-xs text-slate-700 line-clamp-2 italic">
                                    "{{ $submission->feedback }}"
                                </p>
                            @endif
                        </div>

                        <a 
                            href="{{ route('assignments.show', ['assignment' => $assignment->id, 'student' => $student?->id]) }}" 
                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold text-emerald-900 bg-white border border-emerald-300 hover:bg-emerald-50 transition-colors shadow-xs shrink-0 self-start sm:self-auto inline-flex items-center gap-1">
                            <span>{{ __('View Feedback') }}</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                @elseif ($submission && $submission->isReturned())
                    <!-- Returned For Revision Callout -->
                    <div class="p-4 rounded-2xl bg-orange-50/80 border border-orange-200/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                        <div class="space-y-1">
                            <span class="font-black text-orange-900 flex items-center gap-1.5">
                                <span>⚠️</span> {{ __('Revision Requested by Lecturer') }}
                            </span>
                            @if ($submission->feedback)
                                <p class="text-xs text-orange-950 font-medium line-clamp-2 leading-relaxed">
                                    {{ $submission->feedback }}
                                </p>
                            @endif
                        </div>

                        <a 
                            href="{{ route('assignments.show', ['assignment' => $assignment->id, 'student' => $student?->id]) }}" 
                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold text-white bg-orange-600 hover:bg-orange-500 transition-colors shadow-xs shrink-0 self-start sm:self-auto inline-flex items-center gap-1">
                            <span>{{ __('Submit Correction') }}</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                @endif

                <!-- Footer row: metadata and primary button -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-1 text-xs text-slate-500">
                    <div class="flex items-center gap-3 flex-wrap text-[11px]">
                        <span>{{ __('Allowed:') }} <strong class="text-slate-700 font-mono">{{ $assignment->formattedAllowedFileTypes() }}</strong></span>
                        <span>&bull;</span>
                        <span>{{ __('Max:') }} <strong class="text-slate-700">{{ $assignment->max_file_size_mb }} MB</strong></span>
                        <span>&bull;</span>
                        <span>{{ __('Marks:') }} <strong class="text-slate-700">{{ $assignment->total_marks }}</strong></span>
                        @if ($assignment->attachments->isNotEmpty())
                            <span>&bull;</span>
                            <span class="text-brand-600 font-medium">📎 {{ count($assignment->attachments) }} {{ __('attachments') }}</span>
                        @endif
                    </div>

                    <div>
                        <a 
                            href="{{ route('assignments.show', ['assignment' => $assignment->id, 'student' => $student?->id]) }}" 
                            class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-500 shadow-xs transition-all inline-flex items-center gap-1.5 btn-interactive">
                            @if ($submission && $submission->isGraded())
                                <span>{{ __('View Grade & Remarks') }}</span>
                            @elseif ($submission && $submission->isReturned())
                                <span>{{ __('Revise Submission') }}</span>
                            @elseif ($submission)
                                <span>{{ __('View Submission') }}</span>
                            @else
                                <span>{{ __('Open & Submit Coursework') }}</span>
                            @endif
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-12 text-center bg-white rounded-3xl border border-slate-200 text-slate-400 space-y-2">
                <div class="text-4xl mb-2">📚</div>
                <h3 class="text-base font-bold text-slate-800">{{ __('No coursework assignments found') }}</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">
                    {{ __('There are no assignments matching your selected filter or search query. Check back later or adjust filters.') }}
                </p>
            </div>
        @endforelse
    </div>
</div>
