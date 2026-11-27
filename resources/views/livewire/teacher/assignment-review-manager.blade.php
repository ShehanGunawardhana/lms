<div class="max-w-7xl mx-auto space-y-6">
    <!-- Top Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200/80 pb-6">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('teacher.assignments') }}" class="hover:text-slate-800 transition-colors">{{ __('Assignments') }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold truncate">{{ $assignment->title }}</span>
                <span>/</span>
                <span class="text-brand-700 font-bold">{{ __('Submissions & Review') }}</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <span>{{ $assignment->title }}</span>
                <span class="text-xs px-2.5 py-0.5 rounded-full font-bold {{ $assignment->status->badgeClasses() }}">
                    {{ $assignment->status->label() }}
                </span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                {{ $assignment->schoolClass?->title ?? __('No class') }} • {{ __('Deadline:') }} {{ format_portal_datetime($assignment->deadline, 'M d, Y h:i A') }} • {{ __('Total Marks:') }} {{ $assignment->total_marks }}
                @if ($assignment->pass_marks)
                    ({{ __('Pass:') }} {{ $assignment->pass_marks }})
                @endif
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('assignments.show', $assignment) }}" target="_blank" class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 shadow-xs transition-colors inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                {{ __('Student Preview') }}
            </a>
            <a href="{{ route('teacher.assignments') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 shadow-xs transition-colors inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('All Assignments') }}
            </a>
        </div>
    </div>

    <!-- Feedback messages -->
    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-900 flex items-center justify-between shadow-xs animate-fade-in">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs font-semibold text-rose-900 flex items-center justify-between shadow-xs animate-fade-in">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Metrics Cards Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 sm:gap-4">
        <!-- Total Enrolled -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Total Enrolled') }}</p>
            <div class="flex items-baseline justify-between mt-1">
                <span class="text-2xl font-black text-slate-900">{{ $totalEnrolled }}</span>
                <span class="text-[11px] font-bold text-slate-500">{{ $submissionPercentage }}% {{ __('turned in') }}</span>
            </div>
        </div>

        <!-- Pending Review -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <p class="text-[10px] font-bold text-amber-600 uppercase tracking-wider">{{ __('Pending Review') }}</p>
            <div class="flex items-baseline justify-between mt-1">
                <span class="text-2xl font-black text-amber-600">{{ $pendingCount }}</span>
                <span class="text-[10px] font-semibold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded">{{ __('Awaiting marks') }}</span>
            </div>
        </div>

        <!-- Graded & Accepted -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">{{ __('Graded & Accepted') }}</p>
            <div class="flex items-baseline justify-between mt-1">
                <span class="text-2xl font-black text-emerald-600">{{ $gradedCount }}</span>
                @if ($averageMarks !== null)
                    <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded">Avg: {{ $averageMarks }}/{{ $assignment->total_marks }}</span>
                @endif
            </div>
        </div>

        <!-- Returned for Correction -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider">{{ __('Returned') }}</p>
            <div class="flex items-baseline justify-between mt-1">
                <span class="text-2xl font-black text-indigo-600">{{ $returnedCount }}</span>
                <span class="text-[10px] font-semibold text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded">{{ __('Needs revision') }}</span>
            </div>
        </div>

        <!-- Missing / Not Submitted -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <p class="text-[10px] font-bold text-rose-600 uppercase tracking-wider">{{ __('Missing') }}</p>
            <div class="flex items-baseline justify-between mt-1">
                <span class="text-2xl font-black text-rose-600">{{ $missingCount }}</span>
                <span class="text-[10px] font-semibold text-rose-700 bg-rose-50 px-1.5 py-0.5 rounded">{{ __('Not submitted') }}</span>
            </div>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <!-- Filter Tabs -->
        <div class="flex flex-wrap items-center gap-1.5 bg-slate-100/80 p-1 rounded-2xl border border-slate-200/80">
            <button 
                type="button" 
                wire:click="$set('statusFilter', 'all')" 
                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer {{ $statusFilter === 'all' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                {{ __('All') }} ({{ $totalEnrolled }})
            </button>
            <button 
                type="button" 
                wire:click="$set('statusFilter', 'pending')" 
                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer {{ $statusFilter === 'pending' ? 'bg-amber-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                {{ __('Pending') }} ({{ $pendingCount }})
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
                {{ __('Returned') }} ({{ $returnedCount }})
            </button>
            <button 
                type="button" 
                wire:click="$set('statusFilter', 'missing')" 
                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer {{ $statusFilter === 'missing' ? 'bg-rose-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                {{ __('Missing') }} ({{ $missingCount }})
            </button>
        </div>

        <!-- Search Input -->
        <div class="w-full sm:w-72">
            <div class="relative">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="{{ __('Search by student name or code...') }}"
                    class="w-full pl-9 pr-4 py-2 rounded-xl text-xs bg-white border border-slate-200 focus:border-brand-600 focus:ring-brand-600 shadow-xs"
                />
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Submissions Roster Table -->
    <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-bold text-[11px]">
                        <th class="py-3.5 px-4">{{ __('Student') }}</th>
                        <th class="py-3.5 px-4">{{ __('Status & Submission') }}</th>
                        <th class="py-3.5 px-4">{{ __('Coursework File') }}</th>
                        <th class="py-3.5 px-4">{{ __('Marks & Feedback') }}</th>
                        <th class="py-3.5 px-4 text-right">{{ __('Review Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($roster as $item)
                        @php
                            $student = $item['student'];
                            $submission = $item['submission'];
                            $statusKey = $item['status_key'];
                            $isLate = $item['is_late'];
                            $versionsCount = $item['versions_count'];
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <!-- Student Profile -->
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-brand-50 border border-brand-200/80 text-brand-700 font-black text-xs flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 text-sm truncate">{{ $student->full_name }}</div>
                                        <div class="text-[11px] text-slate-400 font-mono">{{ $student->admission_number ?? ($student->student_code ?? 'ID: '.$student->id) }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Status & Submission -->
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    @if ($submission)
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $submission->status->badgeClasses() }}">
                                            {{ $submission->status->label() }}
                                        </span>
                                        <span class="px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-bold text-[10px]">
                                            v{{ $submission->version }}
                                        </span>
                                        @if ($isLate)
                                            <span class="px-1.5 py-0.2 rounded bg-amber-100 text-amber-800 font-bold text-[10px]">
                                                {{ __('Late') }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                            {{ __('Not Submitted') }}
                                        </span>
                                    @endif
                                </div>
                                @if ($submission)
                                    <div class="text-[10px] text-slate-400 mt-1">
                                        {{ __('Turned in') }} {{ format_portal_datetime($submission->submitted_at, 'M d, Y h:i A') }}
                                    </div>
                                @endif
                            </td>

                            <!-- Coursework File Download -->
                            <td class="py-4 px-4">
                                @if ($submission && $submission->fileAsset)
                                    <div class="flex items-center gap-2">
                                        <a 
                                            href="{{ $submission->fileDownloadUrl() }}" 
                                            target="_blank" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-brand-700 bg-brand-50 hover:bg-brand-100 border border-brand-200/80 transition-colors cursor-pointer max-w-xs truncate"
                                            title="{{ $submission->fileOriginalName() }}">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                            <span class="truncate">{{ $submission->fileOriginalName() }}</span>
                                        </a>
                                        @if ($submission->formattedFileSize())
                                            <span class="text-[10px] text-slate-400 shrink-0">({{ $submission->formattedFileSize() }})</span>
                                        @endif
                                    </div>
                                    @if ($submission->note)
                                        <p class="text-[11px] text-slate-500 italic mt-1 line-clamp-1" title="{{ $submission->note }}">
                                            "{{ $submission->note }}"
                                        </p>
                                    @endif
                                @elseif ($submission)
                                    <span class="text-xs text-slate-400 italic">{{ __('No file attached') }}</span>
                                @else
                                    <span class="text-xs text-slate-400">—</span>
                                @endif
                            </td>

                            <!-- Marks & Feedback -->
                            <td class="py-4 px-4">
                                @if ($submission && $submission->isGraded())
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-black text-slate-900">{{ $submission->marks }} / {{ $assignment->total_marks }}</span>
                                        @if ($assignment->pass_marks !== null)
                                            @if ($submission->marks >= $assignment->pass_marks)
                                                <span class="px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800 text-[9px] font-bold uppercase">{{ __('Pass') }}</span>
                                            @else
                                                <span class="px-1.5 py-0.2 rounded bg-rose-100 text-rose-800 text-[9px] font-bold uppercase">{{ __('Fail') }}</span>
                                            @endif
                                        @endif
                                    </div>
                                    @if ($submission->feedback)
                                        <p class="text-[11px] text-slate-600 line-clamp-1 mt-0.5" title="{{ $submission->feedback }}">
                                            {{ $submission->feedback }}
                                        </p>
                                    @endif
                                @elseif ($submission && $submission->isReturned())
                                    <div class="text-[11px] font-semibold text-indigo-700 bg-indigo-50/70 p-2 rounded-xl border border-indigo-100">
                                        <span class="font-bold">{{ __('Correction Requested:') }}</span> {{ $submission->feedback }}
                                    </div>
                                @elseif ($submission)
                                    <span class="text-[11px] text-amber-600 font-semibold flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        {{ __('Not yet graded') }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">—</span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-4 text-right space-x-1.5 whitespace-nowrap">
                                @if ($submission)
                                    <!-- Grade / Accept Button -->
                                    <button 
                                        type="button" 
                                        wire:click="openGradeModal({{ $student->id }})" 
                                        class="px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 active:scale-[0.98] shadow-xs transition-all cursor-pointer inline-flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                        {{ $submission->isGraded() ? __('Edit Grade') : __('Grade & Accept') }}
                                    </button>

                                    <!-- Return for Correction Button (only if not already returned) -->
                                    @if (! $submission->isReturned())
                                        <button 
                                            type="button" 
                                            wire:click="openReturnModal({{ $student->id }})" 
                                            class="px-3 py-1.5 rounded-xl text-xs font-bold text-indigo-800 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200/80 transition-colors cursor-pointer inline-flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>
                                            {{ __('Return') }}
                                        </button>
                                    @endif

                                    <!-- Reopen Button (if graded) -->
                                    @if ($submission->isGraded())
                                        <button 
                                            type="button" 
                                            wire:click="reopenSubmission({{ $submission->id }})" 
                                            wire:confirm="{{ __('Reopen this graded submission? It will be marked pending review again.') }}"
                                            class="px-2.5 py-1.5 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 transition-colors cursor-pointer"
                                            title="{{ __('Reopen Submission') }}">
                                            {{ __('Reopen') }}
                                        </button>
                                    @endif

                                    <!-- Versions History Button -->
                                    @if ($versionsCount > 1)
                                        <button 
                                            type="button" 
                                            wire:click="openHistoryModal({{ $student->id }})" 
                                            class="px-2.5 py-1.5 rounded-xl text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors cursor-pointer inline-flex items-center gap-1"
                                            title="{{ __('View all submission versions') }}">
                                            <span>v1–v{{ $versionsCount }}</span>
                                        </button>
                                    @endif
                                @else
                                    <span class="text-xs text-slate-400 italic">{{ __('Awaiting upload') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                <div class="text-3xl mb-2">📋</div>
                                <p class="text-sm font-semibold text-slate-600">{{ __('No enrolled students found') }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ __('Adjust your search or status filter to see students.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal 1: Grade / Accept Submission Modal -->
    @if ($showGradeModal && $selectedSubmission && $selectedStudent)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full overflow-hidden animate-fade-in" @click.away="$wire.closeGradeModal()">
                <!-- Header -->
                <div class="px-6 py-4 flex items-center justify-between border-b border-slate-100 bg-slate-50/70">
                    <div>
                        <h3 class="text-base font-black text-slate-900 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            {{ __('Grade & Accept Submission') }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $selectedStudent->full_name }} • {{ __('Version :ver', ['ver' => $selectedSubmission->version]) }}</p>
                    </div>
                    <button type="button" wire:click="closeGradeModal" class="text-slate-400 hover:text-slate-600 cursor-pointer text-lg font-bold">
                        &times;
                    </button>
                </div>

                <!-- Form -->
                <form wire:submit.prevent="submitGrade" class="p-6 space-y-4">
                    <!-- Submission File Preview Link -->
                    @if ($selectedSubmission->fileAsset)
                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="text-base">📄</span>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-800 truncate">{{ $selectedSubmission->fileOriginalName() }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $selectedSubmission->formattedFileSize() }}</p>
                                </div>
                            </div>
                            <a href="{{ $selectedSubmission->fileDownloadUrl() }}" target="_blank" class="px-2.5 py-1 text-xs font-semibold text-brand-700 bg-brand-50 hover:bg-brand-100 rounded-lg shrink-0">
                                {{ __('Download') }}
                            </a>
                        </div>
                    @endif

                    @if ($selectedSubmission->note)
                        <div class="text-xs text-slate-600 bg-slate-50/50 p-3 rounded-xl border border-slate-100">
                            <span class="font-bold text-slate-500">{{ __('Student Note:') }}</span> "{{ $selectedSubmission->note }}"
                        </div>
                    @endif

                    <!-- Marks Input -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700">{{ __('Marks Awarded') }} *</label>
                            <span class="text-[11px] text-slate-500">{{ __('Total Marks: :max', ['max' => $assignment->total_marks]) }}</span>
                        </div>
                        <input 
                            type="number" 
                            wire:model="reviewMarks" 
                            min="0" 
                            max="{{ $assignment->total_marks }}" 
                            required 
                            class="w-full px-3 py-2 rounded-xl text-sm font-bold border border-slate-200 focus:border-brand-600" 
                            placeholder="{{ __('Enter marks (0 - :max)', ['max' => $assignment->total_marks]) }}"
                        />
                        @if ($assignment->pass_marks)
                            <p class="text-[10px] text-slate-400 mt-1">
                                {{ __('Pass mark is :pass marks.', ['pass' => $assignment->pass_marks]) }}
                            </p>
                        @endif
                        @error('reviewMarks') <p class="text-[10px] text-rose-600 mt-0.5">{{ $message }}</p> @enderror
                    </div>

                    <!-- Feedback Textarea -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Written Feedback & Remarks') }}</label>
                        <textarea 
                            wire:model="reviewFeedback" 
                            rows="4" 
                            placeholder="{{ __('Add constructive feedback, rubric observations, commendations, or areas to improve...') }}" 
                            class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-600 font-medium"></textarea>
                        <p class="text-[10px] text-slate-400 mt-0.5">{{ __('Visible to student and guardians on the assignment page.') }}</p>
                        @error('reviewFeedback') <p class="text-[10px] text-rose-600 mt-0.5">{{ $message }}</p> @enderror
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" wire:click="closeGradeModal" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 cursor-pointer shadow-xs">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 cursor-pointer shadow-xs inline-flex items-center gap-1.5 btn-interactive">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            {{ __('Submit Grade & Accept') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal 2: Return for Correction Modal -->
    @if ($showReturnModal && $selectedSubmission && $selectedStudent)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full overflow-hidden animate-fade-in" @click.away="$wire.closeReturnModal()">
                <!-- Header -->
                <div class="px-6 py-4 flex items-center justify-between border-b border-slate-100 bg-slate-50/70">
                    <div>
                        <h3 class="text-base font-black text-slate-900 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>
                            {{ __('Return Submission for Correction') }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $selectedStudent->full_name }} • {{ __('Version :ver', ['ver' => $selectedSubmission->version]) }}</p>
                    </div>
                    <button type="button" wire:click="closeReturnModal" class="text-slate-400 hover:text-slate-600 cursor-pointer text-lg font-bold">
                        &times;
                    </button>
                </div>

                <!-- Form -->
                <form wire:submit.prevent="confirmReturn" class="p-6 space-y-4">
                    <div class="p-3.5 rounded-2xl bg-indigo-50/70 border border-indigo-200/80 text-xs text-indigo-900 space-y-1">
                        <p class="font-bold flex items-center gap-1.5">
                            <span>ℹ️</span> {{ __('What happens when work is returned?') }}
                        </p>
                        <p class="text-[11px] text-indigo-700">
                            {{ __('The submission status changes to "Returned". The student and guardians are notified immediately and unlocked to submit a corrected version (v:next).', ['next' => $selectedSubmission->version + 1]) }}
                        </p>
                    </div>

                    <!-- Reason Textarea -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Required Reason & Correction Instructions') }} *</label>
                        <textarea 
                            wire:model="returnReason" 
                            rows="4" 
                            required 
                            placeholder="{{ __('Clearly explain what corrections are needed (e.g. Missing calculations for problem 3, please re-export PDF with higher resolution diagrams, cite your sources)...') }}" 
                            class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 focus:border-indigo-600 font-medium"></textarea>
                        <p class="text-[10px] text-slate-400 mt-0.5">{{ __('Required (minimum 5 characters). Logged in audit trail and delivered in student notification.') }}</p>
                        @error('returnReason') <p class="text-[10px] text-rose-600 mt-0.5">{{ $message }}</p> @enderror
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" wire:click="closeReturnModal" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 cursor-pointer shadow-xs">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 cursor-pointer shadow-xs inline-flex items-center gap-1.5 btn-interactive">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>
                            {{ __('Return for Correction') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal 3: Version History Modal -->
    @if ($showHistoryModal && $selectedStudent)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden animate-fade-in" @click.away="$wire.closeHistoryModal()">
                <!-- Header -->
                <div class="px-6 py-4 flex items-center justify-between border-b border-slate-100 shrink-0 bg-slate-50/70">
                    <div>
                        <h3 class="text-base font-black text-slate-900 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            {{ __('Submission Version History') }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $selectedStudent->full_name }} • {{ $assignment->title }}</p>
                    </div>
                    <button type="button" wire:click="closeHistoryModal" class="text-slate-400 hover:text-slate-600 cursor-pointer text-lg font-bold">
                        &times;
                    </button>
                </div>

                <!-- Body -->
                <div class="p-6 overflow-y-auto flex-1 space-y-4">
                    @forelse ($historyVersions as $ver)
                        <div class="p-4 rounded-2xl border {{ $loop->first ? 'border-brand-300 bg-brand-50/30 ring-1 ring-brand-200' : 'border-slate-200 bg-slate-50/60' }} space-y-3">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-900 text-white font-black text-xs">
                                        v{{ $ver->version }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $ver->status->badgeClasses() }}">
                                        {{ $ver->status->label() }}
                                    </span>
                                    @if ($ver->isLate())
                                        <span class="px-1.5 py-0.2 rounded bg-amber-100 text-amber-800 font-bold text-[10px]">
                                            {{ __('Late') }}
                                        </span>
                                    @endif
                                    @if ($loop->first)
                                        <span class="px-1.5 py-0.2 rounded bg-brand-100 text-brand-800 text-[9px] font-bold uppercase">
                                            {{ __('Latest Version') }}
                                        </span>
                                    @endif
                                </div>
                                <span class="text-[10px] text-slate-400">
                                    {{ format_portal_datetime($ver->submitted_at, 'M d, Y h:i A') }}
                                </span>
                            </div>

                            <!-- File download & note -->
                            <div class="flex items-center justify-between bg-white p-3 rounded-xl border border-slate-100 text-xs">
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-800 truncate">{{ $ver->fileOriginalName() ?? __('No file attached') }}</p>
                                    @if ($ver->formattedFileSize())
                                        <p class="text-[10px] text-slate-400">{{ $ver->formattedFileSize() }}</p>
                                    @endif
                                </div>
                                @if ($ver->fileAsset)
                                    <a href="{{ $ver->fileDownloadUrl() }}" target="_blank" class="px-3 py-1 rounded-lg text-xs font-semibold text-brand-700 bg-brand-50 hover:bg-brand-100 shrink-0">
                                        {{ __('Download') }}
                                    </a>
                                @endif
                            </div>

                            @if ($ver->note)
                                <p class="text-xs text-slate-600 bg-white p-3 rounded-xl border border-slate-100">
                                    <strong class="font-bold text-slate-500">{{ __('Student Note:') }}</strong> "{{ $ver->note }}"
                                </p>
                            @endif

                            @if ($ver->feedback)
                                <div class="text-xs text-slate-700 bg-white p-3 rounded-xl border border-slate-100">
                                    <span class="font-bold {{ $ver->isReturned() ? 'text-indigo-700' : 'text-emerald-700' }}">
                                        {{ $ver->isReturned() ? __('Returned Reason:') : __('Teacher Feedback:') }}
                                    </span>
                                    <span>{{ $ver->feedback }}</span>
                                    @if ($ver->marks !== null)
                                        <span class="block mt-1 font-bold text-slate-900">
                                            {{ __('Marks Awarded:') }} {{ $ver->marks }} / {{ $assignment->total_marks }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-center text-slate-400 text-xs py-8">{{ __('No versions found.') }}</p>
                    @endforelse
                </div>

                <!-- Footer -->
                <div class="px-6 py-3 flex items-center justify-end border-t border-slate-100 shrink-0 bg-slate-50/80">
                    <button type="button" wire:click="closeHistoryModal" class="px-4 py-1.5 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 cursor-pointer shadow-xs">
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
