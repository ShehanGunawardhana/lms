<div>
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
                <div class="lg:w-80 shrink-0">
                    <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-900 to-brand-950 text-white shadow-sm border border-slate-800 space-y-3">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold uppercase tracking-wider text-indigo-300">{{ __('Activity Deadline') }}</span>
                            @if($activity->deadline && $activity->isPastDeadline())
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                    {{ __('Passed') }}
                                </span>
                            @elseif($activity->deadline)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                    {{ __('Active') }}
                                </span>
                            @endif
                        </div>

                        @if($activity->deadline)
                            <div class="py-1">
                                <p class="text-xs text-indigo-200 mb-2">
                                    {{ $activity->deadline->format('l, M j, Y • g:i A') }}
                                </p>
                                <x-countdown :target="$activity->deadline" />
                            </div>

                            <div class="text-[11px] pt-2 border-t border-white/10 text-slate-300">
                                @if($activity->isPastDeadline())
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
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
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

                    {{-- Review Feedback Callout if reviewed or returned --}}
                    @if($submission && $submission->feedback)
                        <div class="sm:max-w-xs p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900">
                            <span class="font-bold block mb-1">👩‍🏫 {{ __('Teacher Feedback:') }}</span>
                            <p class="text-xs italic leading-relaxed">"{{ $submission->feedback }}"</p>
                            @if($submission->rating)
                                <p class="mt-1 text-sm font-black">{{ $submission->rating }}</p>
                            @endif
                        </div>
                    @endif
                </div>
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
</div>
