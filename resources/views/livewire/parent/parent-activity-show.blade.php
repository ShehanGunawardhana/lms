<div>
    {{-- Session Flash Notifications --}}
    @if (session()->has('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <span class="text-base">✅</span>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center justify-between shadow-2xs">
            <div class="flex items-center gap-2">
                <span class="text-base">⚠️</span>
                <span class="font-semibold">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    {{-- Breadcrumb Navigation --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <nav class="flex items-center gap-2 text-xs text-slate-500">
            <a href="{{ route('parent.dashboard') }}" class="hover:text-slate-900 transition-colors">{{ __('Dashboard') }}</a>
            <span>/</span>
            <a href="{{ route('parent.activities', ['child' => $selectedChild?->id]) }}" class="hover:text-slate-900 transition-colors">{{ __('Nursery Activities') }}</a>
            <span>/</span>
            <span class="text-slate-900 font-bold truncate max-w-xs">{{ $activity->title }}</span>
        </nav>

        <a
            href="{{ route('parent.activities', ['child' => $selectedChild?->id]) }}"
            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs self-start"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>{{ __('Back to Activities Feed') }}</span>
        </a>
    </div>

    {{-- Multi-Child Switcher for this Activity Class --}}
    @if($eligibleChildren->count() > 1)
        <div class="mb-6 bg-white p-3 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('Viewing activity for child:') }}</span>
                <div class="flex items-center gap-2">
                    @foreach($eligibleChildren as $child)
                        <button
                            type="button"
                            wire:click="selectChild({{ $child->id }})"
                            class="inline-flex items-center gap-2 px-3 py-1 rounded-xl text-xs font-bold transition-all {{ $selectedChild?->id === $child->id ? 'bg-brand-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
                        >
                            <span>{{ $child->full_name }}</span>
                            <span class="text-[10px] opacity-75">({{ $activity->statusForStudent($child->id)->label() }})</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Main Activity Header Hero --}}
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden mb-6">
        <div class="p-6 sm:p-8">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                <div class="space-y-4 max-w-3xl">
                    {{-- Badges Strip --}}
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-black uppercase tracking-wider bg-brand-50 text-brand-700 border border-brand-200">
                            🏫 {{ $activity->schoolClass?->name ?? __('Preschool Class') }}
                        </span>

                        <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                            👶 {{ __('Age:') }} {{ ucfirst($activity->age_group) }}
                        </span>

                        @if($activity->isPublished())
                            <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5"></span>
                                {{ __('Active Activity') }}
                            </span>
                        @elseif($activity->isClosed())
                            <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                {{ __('Closed') }}
                            </span>
                        @endif
                    </div>

                    {{-- Activity Title --}}
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        {{ $activity->title }}
                    </h1>

                    @if($activity->description)
                        <p class="text-sm text-slate-600 leading-relaxed">
                            {{ $activity->description }}
                        </p>
                    @endif

                    {{-- Teacher & Publish Info --}}
                    <div class="flex items-center gap-4 text-xs text-slate-500 pt-2 border-t border-slate-100">
                        @if($activity->creator)
                            <div class="flex items-center gap-1.5">
                                <span class="font-bold text-slate-700">👩‍🏫 {{ $activity->creator->name }}</span>
                            </div>
                        @endif
                        @if($activity->publish_date)
                            <div>
                                {{ __('Published on :date', ['date' => $activity->publish_date->format('M j, Y')]) }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Live Countdown & Deadline Card --}}
                @php
                    $effectiveDeadline = $selectedChild ? $activity->effectiveDeadlineForStudent($selectedChild->id) : $activity->deadline;
                    $isPastDeadline = $selectedChild ? $activity->isPastDeadlineForStudent($selectedChild->id) : $activity->isPastDeadline();
                    $latestExtension = $selectedChild ? $activity->latestExtensionForStudent($selectedChild->id) : $activity->latestExtensionForStudent(null);
                @endphp
                <div class="lg:w-80 shrink-0">
                    <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-900 to-brand-950 text-white shadow-sm border border-slate-800 space-y-3">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold uppercase tracking-wider text-indigo-300">{{ __('Activity Deadline') }}</span>
                            @if($latestExtension)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-400/20 text-amber-300 border border-amber-400/30">
                                    ✨ {{ __('Extended') }}
                                </span>
                            @elseif($effectiveDeadline && $isPastDeadline)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                    {{ __('Passed') }}
                                </span>
                            @elseif($effectiveDeadline)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                    {{ __('Active') }}
                                </span>
                            @endif
                        </div>

                        @if($effectiveDeadline)
                            <div class="py-1">
                                <p class="text-xs text-indigo-200 mb-2">
                                    {{ $effectiveDeadline->format('l, M j, Y • g:i A') }}
                                </p>
                                <x-countdown :target="$effectiveDeadline" />
                            </div>

                            @if($latestExtension)
                                <div class="p-2.5 rounded-xl bg-amber-500/10 border border-amber-400/20 text-amber-200 text-[11px] leading-relaxed">
                                    <span class="font-bold block text-amber-300">✨ {{ __('Extended Deadline') }}</span>
                                    <span>"{{ $latestExtension->reason }}"</span>
                                </div>
                            @endif

                            <div class="text-[11px] pt-2 border-t border-white/10 text-slate-300">
                                @if($isPastDeadline)
                                    @if($activity->allow_late)
                                        <p class="text-amber-300 flex items-center gap-1">
                                            <span>⚠️</span>
                                            <span>{{ __('Late submissions are accepted.') }}</span>
                                        </p>
                                    @else
                                        <p class="text-rose-300 flex items-center gap-1">
                                            <span>🚫</span>
                                            <span>{{ __('Submission window is now closed.') }}</span>
                                        </p>
                                    @endif
                                @else
                                    <p class="text-cyan-300 flex items-center gap-1">
                                        <span>⏰</span>
                                        <span>{{ __('Complete and submit before deadline.') }}</span>
                                    </p>
                                @endif
                            </div>
                        @else
                            <div class="p-3 rounded-xl bg-white/5 border border-white/10 text-center text-xs text-indigo-200">
                                🌟 {{ __('Open self-paced activity. No fixed deadline.') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Child Submission Status Hero Strip --}}
        @if($selectedChild)
            <div class="p-6 bg-slate-50 border-t border-slate-200">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-400 text-slate-900 font-black flex items-center justify-center text-base">
                            {{ substr($selectedChild->first_name, 0, 1) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-slate-900">{{ $selectedChild->full_name }}</h3>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $submissionStatus->badgeClasses() }}">
                                    {{ $submissionStatus->label() }}
                                </span>
                                @if($submission)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700">
                                        v{{ $submission->version }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">
                                @if($submissionStatus === \App\Enums\ActivitySubmissionStatus::NotStarted)
                                    {{ __('Not submitted yet. Follow the steps below and prepare your child’s submission.') }}
                                @elseif($submissionStatus === \App\Enums\ActivitySubmissionStatus::Submitted)
                                    {{ __('Submitted on :date. Teacher review pending.', ['date' => $submission?->submitted_at?->format('M j, Y • g:i A')]) }}
                                @elseif($submissionStatus === \App\Enums\ActivitySubmissionStatus::Late)
                                    {{ __('Submitted late on :date. Teacher review pending.', ['date' => $submission?->submitted_at?->format('M j, Y • g:i A')]) }}
                                @elseif($submissionStatus === \App\Enums\ActivitySubmissionStatus::Reviewed)
                                    {{ __('Reviewed by teacher on :date.', ['date' => $submission?->reviewed_at?->format('M j, Y')]) }}
                                @elseif($submissionStatus === \App\Enums\ActivitySubmissionStatus::Returned)
                                    {{ __('Teacher requested corrections. Please review the feedback below.') }}
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- Submission Hero Actions --}}
                    <div class="flex items-center gap-2 flex-wrap">
                        @if($submissionStatus === \App\Enums\ActivitySubmissionStatus::NotStarted)
                            @if($activity->canAcceptSubmissionsForStudent($selectedChild->id))
                                <button
                                    type="button"
                                    wire:click="openSubmitModal"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-xs transition-all"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                    <span>{{ __('Submit Child\'s Work') }}</span>
                                </button>
                            @else
                                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-200 text-slate-600 text-xs font-semibold">
                                    <span>🔒</span>
                                    <span>{{ __('Submissions Closed') }}</span>
                                </div>
                            @endif
                        @elseif($submission)
                            @if($submission->canBeModifiedByParent())
                                @if($submissionStatus === \App\Enums\ActivitySubmissionStatus::Returned)
                                    <button
                                        type="button"
                                        wire:click="openReplaceModal"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition-all"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        <span>{{ __('Submit Revised Work') }}</span>
                                    </button>
                                @else
                                    <button
                                        type="button"
                                        wire:click="openReplaceModal"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-2xs transition-all"
                                    >
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        <span>{{ __('Replace File') }}</span>
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="openEditNoteModal"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-2xs transition-all"
                                    >
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                        <span>{{ __('Edit Note') }}</span>
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="confirmDelete"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-rose-200 hover:bg-rose-50 text-rose-600 font-bold text-xs shadow-2xs transition-all"
                                    >
                                        <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        <span>{{ __('Delete') }}</span>
                                    </button>
                                @endif
                            @elseif($submission->isLocked())
                                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-200 text-slate-600 text-xs font-semibold">
                                    <span>🔒</span>
                                    <span>{{ __('Submission Locked') }}</span>
                                </div>
                            @endif

                            @if($submissionHistory->count() > 1)
                                <button
                                    type="button"
                                    wire:click="openHistoryModal"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-all"
                                >
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>{{ __('History (:count)', ['count' => $submissionHistory->count()]) }}</span>
                                </button>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- Submitted File Details & Teacher Review --}}
                @if($submission)
                    <div class="mt-4 pt-4 border-t border-slate-200 grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Active File Box --}}
                        <div class="p-3.5 rounded-2xl bg-white border border-slate-200 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center font-black text-sm shrink-0">
                                    📎
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-900 truncate">
                                        {{ $submission->fileOriginalName() ?: __('Submitted File') }}
                                    </p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">
                                        {{ $submission->formattedFileSize() }} • {{ __('Version :v', ['v' => $submission->version]) }}
                                    </p>
                                </div>
                            </div>
                            @if($submission->fileDownloadUrl())
                                <a
                                    href="{{ $submission->fileDownloadUrl() }}"
                                    target="_blank"
                                    download
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-brand-50 text-brand-700 hover:bg-brand-600 hover:text-white text-xs font-bold transition-all shadow-2xs shrink-0"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    <span>{{ __('Download') }}</span>
                                </a>
                            @endif
                        </div>

                        {{-- Parent Note or Teacher Feedback Box --}}
                        @if($submission->feedback)
                            <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-900 space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold">👩‍🏫 {{ __('Teacher Feedback:') }}</span>
                                    @if($submission->rating)
                                        <span class="font-black text-amber-800 bg-amber-200/60 px-2 py-0.5 rounded-lg">{{ $submission->rating }}</span>
                                    @endif
                                </div>
                                <p class="italic text-[11px] leading-relaxed">"{{ $submission->feedback }}"</p>
                            </div>
                        @elseif($submission->note)
                            <div class="p-3.5 rounded-2xl bg-white border border-slate-200 text-xs text-slate-700 space-y-1">
                                <span class="font-bold text-slate-500 block text-[10px] uppercase tracking-wider">{{ __('Your Note to Teacher:') }}</span>
                                <p class="text-xs italic leading-relaxed text-slate-800">"{{ $submission->note }}"</p>
                            </div>
                        @else
                            <div class="p-3.5 rounded-2xl bg-white border border-dashed border-slate-200 text-xs text-slate-400 flex items-center justify-center">
                                <span>{{ __('No parent note provided.') }}</span>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Instructions & Activities Details (Left 2 cols) --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Step by Step Instructions Card --}}
            <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs">
                <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100">
                    <span class="text-xl">📝</span>
                    <h2 class="text-lg font-bold text-slate-900">{{ __('Step-by-Step Instructions') }}</h2>
                </div>

                <div class="prose prose-slate max-w-none text-sm text-slate-700 leading-relaxed space-y-3">
                    {!! nl2br(e($activity->instructions)) !!}
                </div>
            </div>

            {{-- Video Lessons Section --}}
            @if($activity->videos->isNotEmpty())
                <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-rose-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/>
                            </svg>
                            <h2 class="text-lg font-bold text-slate-900">{{ __('Video Lessons & Guides') }}</h2>
                        </div>
                        <span class="text-xs font-bold text-slate-400">
                            {{ __(':count Lesson(s)', ['count' => $activity->videos->count()]) }}
                        </span>
                    </div>

                    <div class="space-y-6">
                        @foreach($activity->videos as $index => $video)
                            <div class="rounded-2xl border border-slate-200 overflow-hidden bg-slate-50 p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-rose-100 text-rose-700 text-xs font-black flex items-center justify-center">
                                            {{ $index + 1 }}
                                        </span>
                                        <span>{{ $video->title ?: __('Lesson Video :num', ['num' => $index + 1]) }}</span>
                                    </h3>
                                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
                                        YouTube
                                    </span>
                                </div>

                                {{-- Embedded YouTube Player --}}
                                <div class="rounded-xl overflow-hidden shadow-xs bg-black">
                                    <x-youtube-player :videoId="$video->video_id" :title="$video->title" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar: Materials & Quick Info (Right 1 col) --}}
        <div class="space-y-6">
            {{-- Additional Learning Materials Card --}}
            <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-xs space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                    <span class="text-lg">📎</span>
                    <h2 class="text-base font-bold text-slate-900">{{ __('Learning Materials') }}</h2>
                </div>

                @if($activity->materials->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($activity->materials as $material)
                            <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/70 hover:bg-white hover:border-brand-300 transition-all flex items-center justify-between gap-3 group">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="text-2xl shrink-0">{{ $material->icon() }}</span>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-slate-900 truncate group-hover:text-brand-600 transition-colors">
                                            {{ $material->title }}
                                        </p>
                                        <p class="text-[10px] text-slate-400 mt-0.5">
                                            @if($material->isFile())
                                                {{ $material->formattedSize() }}
                                            @else
                                                {{ __('Web Resource') }}
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <div>
                                    @if($material->isFile() && $material->downloadUrl())
                                        <a
                                            href="{{ $material->downloadUrl() }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-brand-50 text-brand-700 hover:bg-brand-600 hover:text-white text-xs font-bold transition-all shadow-2xs"
                                            download
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                            <span>{{ __('Get') }}</span>
                                        </a>
                                    @elseif($material->isLink() && $material->url)
                                        <a
                                            href="{{ $material->url }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-bold transition-all"
                                        >
                                            <span>{{ __('Open') }}</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-400 text-center py-4">
                        {{ __('No additional files or links attached to this activity.') }}
                    </p>
                @endif
            </div>

            {{-- Parent Guidance Card --}}
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-3xl border border-amber-200 p-6 space-y-3">
                <div class="flex items-center gap-2 text-amber-900 font-bold text-sm">
                    <span>💡</span>
                    <span>{{ __('Parent Tips for Nursery Activities') }}</span>
                </div>
                <ul class="text-xs text-amber-800 space-y-2 list-disc list-inside leading-relaxed">
                    <li>{{ __('Let your child explore at their own pace and encourage hands-on discovery.') }}</li>
                    <li>{{ __('Watch the short lesson video together before starting the task.') }}</li>
                    <li>{{ __('Snap photos or short clips of their artwork or process to share with their teacher.') }}</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Upload / Submit Modal --}}
    @if($showSubmitModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-xl max-w-lg w-full overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-black text-slate-900">{{ __('Submit Work for :child', ['child' => $selectedChild?->full_name]) }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">{{ __('Upload your child\'s completed artwork, video, or worksheet.') }}</p>
                    </div>
                    <button type="button" wire:click="closeSubmitModal" class="p-1 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit="submitWork" class="p-6 space-y-4">
                    @if($submissionError)
                        <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold">
                            {{ $submissionError }}
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            {{ __('File (Photo, Video, or PDF — max 50MB)') }} <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="file"
                            wire:model="submissionFile"
                            accept=".mp4,.mov,.webm,.pdf,.jpg,.jpeg,.png,.webp"
                            class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 border border-slate-200 rounded-xl cursor-pointer"
                        />
                        <div wire:loading wire:target="submissionFile" class="text-xs text-brand-600 font-semibold mt-1.5 flex items-center gap-1.5">
                            <svg class="animate-spin h-3.5 w-3.5 text-brand-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>{{ __('Uploading file...') }}</span>
                        </div>
                        @error('submissionFile') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            {{ __('Optional Note to Teacher') }}
                        </label>
                        <textarea
                            wire:model="submissionNote"
                            rows="3"
                            placeholder="{{ __('Add observations or notes about how your child enjoyed the activity...') }}"
                            class="w-full text-xs rounded-xl border-slate-200 focus:border-brand-500 focus:ring-brand-500 placeholder:text-slate-400"
                        ></textarea>
                        @error('submissionNote') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <button
                            type="button"
                            wire:click="closeSubmitModal"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors"
                        >
                            {{ __('Cancel') }}
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="submissionFile, submitWork"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-xs transition-all disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="submitWork">{{ __('Submit Work') }}</span>
                            <span wire:loading wire:target="submitWork">{{ __('Submitting...') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Edit Note Modal --}}
    @if($showEditNoteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-xl max-w-lg w-full overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-black text-slate-900">{{ __('Edit Submission Note') }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">{{ __('Update your comments or reflection for the teacher.') }}</p>
                    </div>
                    <button type="button" wire:click="closeEditNoteModal" class="p-1 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit="saveNote" class="p-6 space-y-4">
                    @if($submissionError)
                        <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold">
                            {{ $submissionError }}
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            {{ __('Note to Teacher') }}
                        </label>
                        <textarea
                            wire:model="editingNote"
                            rows="4"
                            placeholder="{{ __('Add observations or notes about how your child enjoyed the activity...') }}"
                            class="w-full text-xs rounded-xl border-slate-200 focus:border-brand-500 focus:ring-brand-500"
                        ></textarea>
                        @error('editingNote') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <button
                            type="button"
                            wire:click="closeEditNoteModal"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors"
                        >
                            {{ __('Cancel') }}
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="saveNote"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-xs transition-all disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="saveNote">{{ __('Save Note') }}</span>
                            <span wire:loading wire:target="saveNote">{{ __('Saving...') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Replace Work Modal (creates new version) --}}
    @if($showReplaceModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-xl max-w-lg w-full overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-black text-slate-900">{{ __('Replace Submission (New Version)') }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ __('Uploading a new file will create version :ver. The teacher will review the latest version.', ['ver' => 'v' . (($submission?->version ?? 1) + 1)]) }}
                        </p>
                    </div>
                    <button type="button" wire:click="closeReplaceModal" class="p-1 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit="replaceWork" class="p-6 space-y-4">
                    @if($submissionError)
                        <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold">
                            {{ $submissionError }}
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            {{ __('New File (Photo, Video, or PDF — max 50MB)') }} <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="file"
                            wire:model="replacementFile"
                            accept=".mp4,.mov,.webm,.pdf,.jpg,.jpeg,.png,.webp"
                            class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 border border-slate-200 rounded-xl cursor-pointer"
                        />
                        <div wire:loading wire:target="replacementFile" class="text-xs text-brand-600 font-semibold mt-1.5 flex items-center gap-1.5">
                            <svg class="animate-spin h-3.5 w-3.5 text-brand-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>{{ __('Uploading file...') }}</span>
                        </div>
                        @error('replacementFile') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            {{ __('Updated Note to Teacher') }}
                        </label>
                        <textarea
                            wire:model="replacementNote"
                            rows="3"
                            placeholder="{{ __('Add observations or notes about how your child enjoyed the activity...') }}"
                            class="w-full text-xs rounded-xl border-slate-200 focus:border-brand-500 focus:ring-brand-500"
                        ></textarea>
                        @error('replacementNote') <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <button
                            type="button"
                            wire:click="closeReplaceModal"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors"
                        >
                            {{ __('Cancel') }}
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="replacementFile, replaceWork"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-xs transition-all disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="replaceWork">{{ __('Upload & Replace') }}</span>
                            <span wire:loading wire:target="replaceWork">{{ __('Uploading...') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-xl max-w-md w-full overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <div class="p-6 text-center space-y-3">
                    <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto text-xl">
                        🗑️
                    </div>
                    <h3 class="text-base font-black text-slate-900">{{ __('Delete This Submission?') }}</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        {{ __('Are you sure you want to delete version :ver for :child? You can re-upload fresh work anytime before the deadline closes.', ['ver' => 'v' . ($submission?->version ?? 1), 'child' => $selectedChild?->full_name]) }}
                    </p>
                </div>

                <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button
                        type="button"
                        wire:click="closeDeleteModal"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-200 transition-colors"
                    >
                        {{ __('Cancel') }}
                    </button>
                    <button
                        type="button"
                        wire:click="deleteSubmission"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-xs transition-all disabled:opacity-50"
                    >
                        <span>{{ __('Yes, Delete Submission') }}</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Version History Modal --}}
    @if($showHistoryModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-xl max-w-2xl w-full max-h-[85vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between shrink-0">
                    <div>
                        <h3 class="text-base font-black text-slate-900">{{ __('Submission History: :child', ['child' => $selectedChild?->full_name]) }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">{{ __('All submitted versions and teacher reviews for this activity.') }}</p>
                    </div>
                    <button type="button" wire:click="closeHistoryModal" class="p-1 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto space-y-4">
                    @forelse($submissionHistory as $hist)
                        <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/60 space-y-3">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-brand-100 text-brand-800">
                                        v{{ $hist->version }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $hist->status->badgeClasses() }}">
                                        {{ $hist->status->label() }}
                                    </span>
                                    @if($hist->is_late)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                            {{ __('Late') }}
                                        </span>
                                    @endif
                                </div>
                                <span class="text-[11px] text-slate-400">
                                    {{ $hist->submitted_at?->format('M j, Y • g:i A') }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between gap-3 bg-white p-3 rounded-xl border border-slate-100">
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-900 truncate">
                                        {{ $hist->fileOriginalName() ?: __('Uploaded File') }}
                                    </p>
                                    <p class="text-[10px] text-slate-400">
                                        {{ $hist->formattedFileSize() }}
                                    </p>
                                </div>
                                @if($hist->fileDownloadUrl())
                                    <a
                                        href="{{ $hist->fileDownloadUrl() }}"
                                        target="_blank"
                                        download
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-brand-50 text-brand-700 hover:bg-brand-600 hover:text-white text-xs font-bold transition-all shrink-0"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        <span>{{ __('Download') }}</span>
                                    </a>
                                @endif
                            </div>

                            @if($hist->note)
                                <div class="text-xs text-slate-600 italic">
                                    <span class="font-bold text-slate-500 not-italic">{{ __('Note:') }}</span> "{{ $hist->note }}"
                                </div>
                            @endif

                            @if($hist->feedback)
                                <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900 space-y-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold">👩‍🏫 {{ __('Teacher Feedback:') }}</span>
                                        @if($hist->rating)
                                            <span class="font-black text-amber-800 bg-amber-200/60 px-1.5 py-0.5 rounded text-[10px]">{{ $hist->rating }}</span>
                                        @endif
                                    </div>
                                    <p class="italic text-[11px] leading-relaxed">"{{ $hist->feedback }}"</p>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-6">
                            {{ __('No submission history found.') }}
                        </p>
                    @endforelse
                </div>

                <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end shrink-0">
                    <button
                        type="button"
                        wire:click="closeHistoryModal"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-200 transition-colors"
                    >
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
