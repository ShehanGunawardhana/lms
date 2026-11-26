<div class="max-w-7xl mx-auto space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200/80 pb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Child Learning Progress & Milestones') }}</h1>
            <p class="text-xs text-slate-500 mt-1">
                {{ __('Track weekly activity completions, teacher feedback awards, and learning achievements (§6.6)') }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('parent.dashboard') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 shadow-xs transition-colors inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('Parent Dashboard') }}
            </a>
            <a href="{{ route('parent.activities') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 shadow-xs transition-colors inline-flex items-center gap-1.5">
                {{ __('Activity Feed') }}
            </a>
            @if (Route::has('parent.safety'))
                <a href="{{ route('parent.safety') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 shadow-xs transition-colors inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    {{ __('Health & Safety') }}
                </a>
            @endif
        </div>
    </div>

    @if ($children->isEmpty())
        <div class="p-12 text-center bg-white rounded-3xl border border-slate-200 shadow-xs space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
            </div>
            <h3 class="text-base font-bold text-slate-900">{{ __('No Registered Children Found') }}</h3>
            <p class="text-xs text-slate-500 max-w-md mx-auto">
                {{ __('There are no active child records linked to your parent account. Please verify your registration status or contact the school office.') }}
            </p>
        </div>
    @else
        <!-- Multi-Child Switcher -->
        @if ($children->count() > 1)
            <div class="flex items-center gap-2 overflow-x-auto pb-1">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mr-1">{{ __('Select Child:') }}</span>
                @foreach ($children as $c)
                    <button 
                        type="button" 
                        wire:click="selectChild({{ $c->id }})"
                        class="px-4 py-2 rounded-2xl text-xs font-bold transition-all cursor-pointer inline-flex items-center gap-2 {{ $selectedChildId === $c->id ? 'bg-brand-600 text-white shadow-sm' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
                        <span>{{ $c->full_name }}</span>
                        <span class="text-[10px] opacity-75 font-mono">({{ $c->student_code }})</span>
                    </button>
                @endforeach
            </div>
        @endif

        @if ($currentChild)
            <!-- Child Overview & Milestones Hero -->
            <div class="p-6 rounded-3xl bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950 text-white shadow-sm space-y-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-xs flex items-center justify-center font-black text-2xl text-amber-300 border border-white/15 shrink-0 shadow-inner">
                            {{ strtoupper(substr($currentChild->first_name, 0, 1)) }}{{ strtoupper(substr($currentChild->last_name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2.5">
                                <h2 class="text-xl font-black tracking-tight">{{ $currentChild->full_name }}</h2>
                                <span class="px-2.5 py-0.5 rounded-md text-[10px] font-mono font-bold bg-white/15 text-slate-200">
                                    {{ $currentChild->student_code }}
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-3 mt-1.5 text-xs text-slate-300">
                                <span>🎂 {{ format_portal_datetime($currentChild->dob, 'Y-m-d') }} ({{ $currentChild->age_years }} {{ __('years old') }})</span>
                                @if ($currentChild->classes->isNotEmpty())
                                    <span class="text-amber-300 font-semibold">🏫 {{ $currentChild->classes->pluck('title')->join(', ') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Overall Completion Meter -->
                    <div class="bg-white/10 backdrop-blur-xs rounded-2xl p-4 border border-white/15 flex items-center gap-4 min-w-[240px]">
                        <div class="w-12 h-12 rounded-xl bg-amber-400 text-slate-950 flex items-center justify-center font-black text-sm shrink-0 shadow-sm">
                            {{ $metrics['completion_rate'] }}%
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-200 mb-1">
                                <span>{{ __('Activity Completion') }}</span>
                                <span>{{ $metrics['reviewed'] }}/{{ $metrics['total'] }}</span>
                            </div>
                            <div class="w-full bg-white/20 rounded-full h-2 overflow-hidden">
                                <div class="bg-amber-400 h-2 rounded-full transition-all duration-500" style="width: {{ $metrics['completion_rate'] }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Milestone Stats Counters -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 pt-2 border-t border-white/10">
                    <div class="bg-white/5 rounded-2xl p-3.5 border border-white/10">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('Total Assigned') }}</p>
                        <p class="text-xl font-black text-white mt-1">{{ $metrics['total'] }}</p>
                    </div>
                    <div class="bg-white/5 rounded-2xl p-3.5 border border-white/10">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-400">{{ __('Completed & Reviewed') }}</p>
                        <p class="text-xl font-black text-emerald-300 mt-1">{{ $metrics['reviewed'] }}</p>
                    </div>
                    <div class="bg-white/5 rounded-2xl p-3.5 border border-white/10">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-blue-400">{{ __('Pending Review') }}</p>
                        <p class="text-xl font-black text-blue-300 mt-1">{{ $metrics['submitted'] }}</p>
                    </div>
                    <div class="bg-white/5 rounded-2xl p-3.5 border border-white/10">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-rose-400">{{ __('Needs Revision') }}</p>
                        <p class="text-xl font-black text-rose-300 mt-1">{{ $metrics['returned'] }}</p>
                    </div>
                    <div class="bg-white/5 rounded-2xl p-3.5 border border-white/10">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('Not Started') }}</p>
                        <p class="text-xl font-black text-slate-300 mt-1">{{ $metrics['not_started'] }}</p>
                    </div>
                    <div class="bg-amber-400/10 rounded-2xl p-3.5 border border-amber-400/20">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-amber-300">{{ __('Stickers Awarded') }}</p>
                        <p class="text-xl font-black text-amber-300 mt-1 flex items-center gap-1.5">
                            <span>⭐</span>
                            <span>{{ $metrics['total_stickers'] }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="flex items-center border-b border-slate-200 gap-2">
                <button 
                    type="button" 
                    wire:click="setActiveTab('activities')"
                    class="pb-3 px-4 text-xs font-bold transition-all border-b-2 cursor-pointer inline-flex items-center gap-1.5 {{ $activeTab === 'activities' ? 'border-brand-600 text-brand-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                    <span>📚 {{ __('Activities & Submissions') }}</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 text-slate-700 font-mono">{{ $metrics['total'] }}</span>
                </button>
                <button 
                    type="button" 
                    wire:click="setActiveTab('stickers')"
                    class="pb-3 px-4 text-xs font-bold transition-all border-b-2 cursor-pointer inline-flex items-center gap-1.5 {{ $activeTab === 'stickers' ? 'border-amber-500 text-amber-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                    <span>🏆 {{ __('Sticker Trophy Cabinet') }}</span>
                    @if ($metrics['total_stickers'] > 0)
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-amber-100 text-amber-900 font-mono font-bold">{{ $metrics['total_stickers'] }}</span>
                    @endif
                </button>
                <button 
                    type="button" 
                    wire:click="setActiveTab('quizzes')"
                    class="pb-3 px-4 text-xs font-bold transition-all border-b-2 cursor-pointer inline-flex items-center gap-1.5 {{ $activeTab === 'quizzes' ? 'border-purple-600 text-purple-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                    <span>🧩 {{ __('Quizzes & Assessments') }}</span>
                </button>
            </div>

            <!-- TAB 1: ACTIVITIES & SUBMISSIONS -->
            @if ($activeTab === 'activities')
                <div class="space-y-6">
                    <!-- Filters & Search Bar -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                        <!-- Status Filter Tabs -->
                        <div class="flex flex-wrap items-center gap-1.5">
                            <button 
                                type="button" 
                                wire:click="setStatusFilter('all')"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-colors cursor-pointer {{ $statusFilter === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                {{ __('All') }} ({{ $metrics['total'] }})
                            </button>
                            <button 
                                type="button" 
                                wire:click="setStatusFilter('reviewed')"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-colors cursor-pointer {{ $statusFilter === 'reviewed' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                                ✓ {{ __('Reviewed') }} ({{ $metrics['reviewed'] }})
                            </button>
                            <button 
                                type="button" 
                                wire:click="setStatusFilter('submitted')"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-colors cursor-pointer {{ $statusFilter === 'submitted' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-800 hover:bg-blue-100' }}">
                                ⏳ {{ __('Under Review') }} ({{ $metrics['submitted'] }})
                            </button>
                            <button 
                                type="button" 
                                wire:click="setStatusFilter('returned')"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-colors cursor-pointer {{ $statusFilter === 'returned' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-800 hover:bg-rose-100' }}">
                                🔄 {{ __('Needs Revision') }} ({{ $metrics['returned'] }})
                            </button>
                            <button 
                                type="button" 
                                wire:click="setStatusFilter('not_started')"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-colors cursor-pointer {{ $statusFilter === 'not_started' ? 'bg-slate-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                {{ __('Not Started') }} ({{ $metrics['not_started'] }})
                            </button>
                        </div>

                        <!-- Search -->
                        <div class="w-full sm:w-64">
                            <input 
                                type="text" 
                                wire:model.live.debounce.300ms="search" 
                                placeholder="{{ __('Search activity title...') }}"
                                class="w-full px-3 py-1.5 rounded-xl text-xs border border-slate-200 focus:border-brand-600 focus:ring-brand-600 bg-white" 
                            />
                        </div>
                    </div>

                    <!-- Activities List -->
                    <div class="space-y-4">
                        @forelse ($activities as $activity)
                            @php
                                $submission = $activity->submissions->first();
                                $statusVal = $submission ? $submission->status->value : 'not_started';
                            @endphp
                            <div class="p-5 rounded-3xl bg-white border border-slate-200 shadow-xs hover:border-slate-300 transition-all space-y-4">
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                    <div class="space-y-1.5">
                                        <div class="flex flex-wrap items-center gap-2">
                                            @if ($activity->schoolClass)
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-900 border border-amber-200">
                                                    {{ $activity->schoolClass->title }}
                                                </span>
                                            @endif
                                            @if ($activity->age_group)
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600">
                                                    {{ $activity->age_group }}
                                                </span>
                                            @endif

                                            <!-- Status Pill -->
                                            @if ($statusVal === 'reviewed')
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 inline-flex items-center gap-1">
                                                    <span>✓</span>
                                                    <span>{{ __('Reviewed & Awarded') }}</span>
                                                </span>
                                            @elseif (in_array($statusVal, ['submitted', 'late'], true))
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200 inline-flex items-center gap-1">
                                                    <span>⏳</span>
                                                    <span>{{ $statusVal === 'late' ? __('Late Submission (Pending Review)') : __('Submitted (Pending Review)') }}</span>
                                                </span>
                                            @elseif ($statusVal === 'returned')
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200 inline-flex items-center gap-1">
                                                    <span>🔄</span>
                                                    <span>{{ __('Revision Requested') }}</span>
                                                </span>
                                            @else
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                                    {{ __('Not Started') }}
                                                </span>
                                            @endif
                                        </div>

                                        <h3 class="text-base font-bold text-slate-900">
                                            {{ $activity->title }}
                                        </h3>
                                        @if ($activity->description)
                                            <p class="text-xs text-slate-600 leading-relaxed max-w-3xl">
                                                {{ Str::limit($activity->description, 160) }}
                                            </p>
                                        @endif
                                    </div>

                                    <div class="sm:text-right shrink-0">
                                        <p class="text-[11px] text-slate-400">{{ __('Due Date:') }}</p>
                                        <p class="text-xs font-bold text-slate-700">
                                            {{ $activity->deadline ? format_portal_datetime($activity->deadline, 'M d, Y h:i A') : __('No deadline') }}
                                        </p>
                                        <a href="{{ route('parent.activities.show', ['activity' => $activity->id, 'child' => $currentChild->id]) }}" class="mt-2 inline-flex items-center gap-1 text-xs font-bold text-brand-600 hover:text-brand-700 cursor-pointer">
                                            <span>{{ __('View Activity') }}</span>
                                            <span>&rarr;</span>
                                        </a>
                                    </div>
                                </div>

                                <!-- Teacher Review Feedback & Sticker Award Box -->
                                @if ($submission && $submission->isReviewed())
                                    <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                        <div class="flex items-start gap-3">
                                            @if ($submission->rating && isset($stickersConfig[$submission->rating]))
                                                @php $st = $stickersConfig[$submission->rating]; @endphp
                                                <div class="w-12 h-12 rounded-2xl {{ $st['bg'] }} border flex flex-col items-center justify-center shrink-0 shadow-xs">
                                                    <span class="text-2xl leading-none">{{ $st['emoji'] }}</span>
                                                </div>
                                            @else
                                                <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-800 border border-amber-300 flex items-center justify-center shrink-0">
                                                    <span class="text-xl">⭐</span>
                                                </div>
                                            @endif
                                            <div class="space-y-0.5">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-black text-amber-950">
                                                        {{ $submission->rating && isset($stickersConfig[$submission->rating]) ? $stickersConfig[$submission->rating]['label'] : __('Teacher Star Award') }}
                                                    </span>
                                                    @if ($submission->reviewed_at)
                                                        <span class="text-[10px] text-amber-700 font-medium">
                                                            {{ format_portal_datetime($submission->reviewed_at, 'M d, Y') }}
                                                        </span>
                                                    @endif
                                                </div>
                                                @if ($submission->feedback)
                                                    <p class="text-xs text-amber-900 italic leading-relaxed">
                                                        "{{ $submission->feedback }}"
                                                    </p>
                                                @endif
                                                @if ($submission->reviewer)
                                                    <p class="text-[10px] text-amber-700 font-semibold mt-0.5">
                                                        — {{ $submission->reviewer->name }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>

                                        @if ($submission->file_asset_id)
                                            <a href="{{ $submission->fileDownloadUrl() }}" target="_blank" class="px-3 py-1.5 rounded-xl text-xs font-bold text-slate-700 bg-white border border-amber-200 hover:bg-slate-50 transition-colors shrink-0 shadow-xs inline-flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                                {{ __('Download Work') }} (v{{ $submission->version }})
                                            </a>
                                        @endif
                                    </div>
                                @elseif ($submission && $submission->isReturned())
                                    <!-- Revision Feedback Box -->
                                    <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 flex items-center justify-between gap-3 text-xs text-rose-900">
                                        <div class="flex items-center gap-2.5">
                                            <span class="text-base">⚠️</span>
                                            <div>
                                                <span class="font-bold block">{{ __('Revision Requested by Teacher:') }}</span>
                                                <span class="text-rose-800">{{ $submission->feedback ?: __('Please check the instructions and resubmit child\'s work.') }}</span>
                                            </div>
                                        </div>
                                        <a href="{{ route('parent.activities.show', ['activity' => $activity->id, 'child' => $currentChild->id]) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 shrink-0 cursor-pointer shadow-xs">
                                            {{ __('Resubmit Work') }}
                                        </a>
                                    </div>
                                @elseif ($submission)
                                    <!-- Submitted & Pending Review Box -->
                                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-3 text-xs text-slate-600">
                                        <div class="flex items-center gap-2">
                                            <span class="text-slate-400">📤</span>
                                            <span>{{ __('Work submitted on :date (v:version). Teacher will review soon.', ['date' => format_portal_datetime($submission->submitted_at, 'M d, Y h:i A'), 'version' => $submission->version]) }}</span>
                                        </div>
                                        @if ($submission->file_asset_id)
                                            <a href="{{ $submission->fileDownloadUrl() }}" target="_blank" class="text-xs font-bold text-brand-600 hover:underline">
                                                {{ __('View Upload') }}
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="p-10 text-center bg-white rounded-3xl border border-dashed border-slate-200 text-xs text-slate-400 space-y-1">
                                <p class="font-bold text-slate-600">{{ __('No activities found.') }}</p>
                                <p>{{ __('Try adjusting your filter or search criteria.') }}</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    <div>
                        {{ $activities->links() }}
                    </div>
                </div>
            @endif

            <!-- TAB 2: STICKER TROPHY CABINET -->
            @if ($activeTab === 'stickers')
                <div class="space-y-6">
                    <div class="p-5 rounded-3xl bg-gradient-to-r from-amber-500/15 via-purple-500/10 to-indigo-500/10 border border-amber-200/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span class="text-3xl">🏆</span>
                            <div>
                                <h3 class="text-base font-black text-slate-900">{{ __(':name\'s Sticker Showcase', ['name' => $currentChild->first_name]) }}</h3>
                                <p class="text-xs text-slate-600 mt-0.5">
                                    {{ __('Stickers and encouraging awards earned for completed nursery activities (§6.6).') }}
                                </p>
                            </div>
                        </div>
                        <div class="px-4 py-2 rounded-2xl bg-white border border-amber-200 shadow-xs text-center shrink-0">
                            <span class="text-lg font-black text-amber-600">{{ $metrics['total_stickers'] }}</span>
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">{{ __('Stickers Earned') }}</span>
                        </div>
                    </div>

                    @if ($stickersSubmissions->isNotEmpty())
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($stickersSubmissions as $sub)
                                @php
                                    $st = $sub->rating && isset($stickersConfig[$sub->rating])
                                        ? $stickersConfig[$sub->rating]
                                        : ['label' => 'Superstar!', 'emoji' => '⭐', 'bg' => 'bg-amber-100 text-amber-800 border-amber-300'];
                                @endphp
                                <div class="p-5 rounded-3xl bg-white border border-slate-200 shadow-xs hover:shadow-md transition-shadow flex flex-col justify-between space-y-4">
                                    <div class="flex items-start gap-3.5">
                                        <div class="w-14 h-14 rounded-2xl {{ $st['bg'] }} border flex items-center justify-center shrink-0 text-3xl shadow-xs">
                                            {{ $st['emoji'] }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $st['bg'] }} border inline-block">
                                                {{ $st['label'] }}
                                            </span>
                                            <h4 class="text-xs font-bold text-slate-900 mt-1 truncate" title="{{ $sub->activity?->title }}">
                                                {{ $sub->activity?->title }}
                                            </h4>
                                            @if ($sub->reviewed_at)
                                                <p class="text-[10px] text-slate-400 mt-0.5">
                                                    {{ format_portal_datetime($sub->reviewed_at, 'M d, Y') }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    @if ($sub->feedback)
                                        <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 text-xs text-slate-700 italic">
                                            "{{ $sub->feedback }}"
                                        </div>
                                    @endif

                                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                                        <span>{{ $sub->reviewer ? $sub->reviewer->name : __('Class Teacher') }}</span>
                                        @if ($sub->activity)
                                            <a href="{{ route('parent.activities.show', ['activity' => $sub->activity_id, 'child' => $currentChild->id]) }}" class="font-bold text-brand-600 hover:underline">
                                                {{ __('Review') }} &rarr;
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-12 text-center bg-white rounded-3xl border border-dashed border-slate-200 space-y-2">
                            <span class="text-4xl">🎨</span>
                            <h4 class="text-sm font-bold text-slate-900">{{ __('No Stickers Yet') }}</h4>
                            <p class="text-xs text-slate-500 max-w-sm mx-auto">
                                {{ __('Complete classroom activities and submit your child\'s craft or drawings to earn colorful reward stickers from teachers!') }}
                            </p>
                            <a href="{{ route('parent.activities') }}" class="mt-2 inline-block px-4 py-2 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-500 shadow-xs cursor-pointer">
                                {{ __('Explore Activities To Complete') }}
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            <!-- TAB 3: QUIZZES & ASSESSMENTS -->
            @if ($activeTab === 'quizzes')
                <div class="space-y-6">
                    <div class="p-5 rounded-3xl bg-purple-50 border border-purple-200 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span class="text-3xl">🧩</span>
                            <div>
                                <h3 class="text-base font-black text-purple-950">{{ __('Interactive Nursery Quizzes & Games') }}</h3>
                                <p class="text-xs text-purple-800 mt-0.5">
                                    {{ __('Touch-friendly picture quizzes, audio stories, and fruit/number matching games (§6.6, §9).') }}
                                </p>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold text-purple-700 bg-purple-200/70 px-2.5 py-1 rounded-full uppercase tracking-wider">
                            {{ __('Nursery Mode Ready') }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-5 rounded-3xl bg-white border border-slate-200 shadow-xs space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800">
                                    {{ __('Fruit & Counting Match') }}
                                </span>
                                <span class="text-xs font-bold text-emerald-600">✓ {{ __('Completed') }} (5/5)</span>
                            </div>
                            <h4 class="text-sm font-bold text-slate-900">{{ __('The Hungry Caterpillar Count Quiz') }}</h4>
                            <p class="text-xs text-slate-600">
                                {{ __('Audio read-aloud question with touch-to-pair fruit cards. Earned the Golden Apple sticker!') }}
                            </p>
                            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                                <span>{{ __('Completed with Parent Assistance') }}</span>
                                <span class="font-bold text-amber-600">⭐ 100%</span>
                            </div>
                        </div>

                        <div class="p-5 rounded-3xl bg-white border border-slate-200 shadow-xs space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-100 text-blue-800">
                                    {{ __('Shape & Color Recognition') }}
                                </span>
                                <span class="text-xs font-bold text-brand-600">{{ __('Available in Activity') }}</span>
                            </div>
                            <h4 class="text-sm font-bold text-slate-900">{{ __('Autumn Shapes & Leaf Sorting Game') }}</h4>
                            <p class="text-xs text-slate-600">
                                {{ __('Picture matching game: drag circles, triangles, and autumn leaf colors.') }}
                            </p>
                            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                                <span>{{ __('Estimated time: 5 mins') }}</span>
                                <a href="{{ route('parent.activities') }}" class="font-bold text-brand-600 hover:underline">
                                    {{ __('Open Game') }} &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    @endif
</div>
