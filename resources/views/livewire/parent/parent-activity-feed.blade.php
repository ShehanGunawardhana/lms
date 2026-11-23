<div>
    {{-- Header Banner --}}
    <div class="mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 mb-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    {{ __('Nursery Learning Portal (§6.6)') }}
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                    {{ __('Nursery Activities & Home Fun') }}
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    {{ __('Explore creative tasks, video lessons, and interactive learning designed for your children.') }}
                </p>
            </div>

            {{-- Children Selector Switcher --}}
            @if($children->count() > 1)
                <div class="bg-white p-1.5 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-1.5 overflow-x-auto">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider px-2">{{ __('Child:') }}</span>
                    @foreach($children as $child)
                        <button
                            type="button"
                            wire:click="selectChild({{ $child->id }})"
                            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ $selectedChild?->id === $child->id ? 'bg-brand-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}"
                        >
                            <span class="w-6 h-6 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center text-[10px] font-black uppercase">
                                {{ substr($child->first_name, 0, 1) }}
                            </span>
                            <span>{{ $child->full_name }}</span>
                            @if($child->enrollments->where('status', 'active')->first())
                                <span class="text-[10px] opacity-75 font-normal">({{ $child->enrollments->where('status', 'active')->first()->schoolClass?->name }})</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    @if($children->isEmpty())
        {{-- No Children Registered State --}}
        <div class="bg-white rounded-3xl border border-slate-200 p-8 text-center max-w-lg mx-auto my-12 shadow-xs">
            <div class="w-16 h-16 rounded-2xl bg-amber-100 text-amber-600 mx-auto flex items-center justify-center text-3xl mb-4">
                🧸
            </div>
            <h3 class="text-lg font-bold text-slate-900">{{ __('No Enrolled Children Found') }}</h3>
            <p class="text-sm text-slate-500 mt-2">
                {{ __('Your guardian account is not linked to any enrolled students yet. Please contact the preschool administration for enrollment assistance.') }}
            </p>
            <div class="mt-6">
                <a href="{{ route('parent.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-sm font-bold hover:bg-slate-200 transition-colors">
                    ← {{ __('Back to Dashboard') }}
                </a>
            </div>
        </div>
    @else
        {{-- Active Child Banner --}}
        @if($selectedChild)
            <div class="bg-gradient-to-r from-brand-900 via-brand-800 to-indigo-900 rounded-3xl p-6 text-white mb-6 shadow-sm relative overflow-hidden">
                <div class="absolute -right-8 -bottom-8 w-48 h-48 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-amber-400 text-slate-900 font-black text-xl flex items-center justify-center shadow-md">
                            {{ substr($selectedChild->first_name, 0, 1) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h2 class="text-xl font-black text-white">{{ $selectedChild->full_name }}</h2>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-white/10 text-cyan-300 border border-white/10">
                                    {{ $selectedChild->student_code }}
                                </span>
                            </div>
                            <p class="text-xs text-indigo-200 mt-1 flex items-center gap-2">
                                <span>🎂 {{ __('Age: :years yrs', ['years' => $selectedChild->age_years]) }}</span>
                                <span>•</span>
                                <span>🏫 {{ $selectedChild->enrollments->where('status', 'active')->first()?->schoolClass?->name ?? __('Unassigned Class') }}</span>
                            </p>
                        </div>
                    </div>

                    {{-- Metrics Pill Badges --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                        <div class="bg-white/10 backdrop-blur-xs rounded-xl p-3 border border-white/10 text-center">
                            <p class="text-[10px] font-bold text-indigo-200 uppercase tracking-wider">{{ __('Total') }}</p>
                            <p class="text-lg font-black text-white mt-0.5">{{ $metrics['total'] }}</p>
                        </div>
                        <div class="bg-amber-400/20 backdrop-blur-xs rounded-xl p-3 border border-amber-400/30 text-center">
                            <p class="text-[10px] font-bold text-amber-200 uppercase tracking-wider">{{ __('To Do') }}</p>
                            <p class="text-lg font-black text-amber-300 mt-0.5">{{ $metrics['todo'] }}</p>
                        </div>
                        <div class="bg-sky-400/20 backdrop-blur-xs rounded-xl p-3 border border-sky-400/30 text-center">
                            <p class="text-[10px] font-bold text-sky-200 uppercase tracking-wider">{{ __('Submitted') }}</p>
                            <p class="text-lg font-black text-sky-300 mt-0.5">{{ $metrics['submitted'] }}</p>
                        </div>
                        <div class="bg-emerald-400/20 backdrop-blur-xs rounded-xl p-3 border border-emerald-400/30 text-center">
                            <p class="text-[10px] font-bold text-emerald-200 uppercase tracking-wider">{{ __('Reviewed') }}</p>
                            <p class="text-lg font-black text-emerald-300 mt-0.5">{{ $metrics['reviewed'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Filters & Search Toolbar --}}
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            {{-- Status Filter Tabs --}}
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0">
                @php
                    $filters = [
                        'all' => __('All Activities'),
                        'todo' => __('⏳ To Do'),
                        'submitted' => __('✓ Submitted'),
                        'reviewed' => __('⭐ Reviewed'),
                        'returned' => __('↺ Returned'),
                    ];
                @endphp
                @foreach($filters as $key => $label)
                    <button
                        type="button"
                        wire:click="setStatusFilter('{{ $key }}')"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $statusFilter === $key ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            {{-- Search Bar --}}
            <div class="w-full md:w-72 relative">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('Search activities...') }}"
                    class="w-full pl-9 pr-4 py-2 rounded-xl text-xs border border-slate-200 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors"
                />
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>

        {{-- Activities Cards Grid --}}
        @if($activities && $activities->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($activities as $activity)
                    @php
                        $childStatus = $selectedChild ? $activity->statusForStudent($selectedChild->id) : \App\Enums\ActivitySubmissionStatus::NotStarted;
                    @endphp
                    <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-xs hover:shadow-lg hover:border-brand-300 transition-all duration-200 flex flex-col justify-between group">
                        <div>
                            {{-- Top Banner / Metadata --}}
                            <div class="p-5 pb-3">
                                <div class="flex items-center justify-between gap-2 mb-3">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        {{-- Class badge --}}
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-brand-50 text-brand-700 border border-brand-200">
                                            {{ $activity->schoolClass?->name ?? __('Preschool') }}
                                        </span>

                                        {{-- Age group badge --}}
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                            👶 {{ ucfirst($activity->age_group) }}
                                        </span>
                                    </div>

                                    {{-- Submission status pill for selected child --}}
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $childStatus->badgeClasses() }}">
                                        @if($childStatus === \App\Enums\ActivitySubmissionStatus::NotStarted)
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        @elseif($childStatus === \App\Enums\ActivitySubmissionStatus::Submitted)
                                            <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                                        @elseif($childStatus === \App\Enums\ActivitySubmissionStatus::Late)
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        @elseif($childStatus === \App\Enums\ActivitySubmissionStatus::Reviewed)
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        @elseif($childStatus === \App\Enums\ActivitySubmissionStatus::Returned)
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        @endif
                                        {{ $childStatus->label() }}
                                    </span>
                                </div>

                                {{-- Title --}}
                                <h3 class="text-base font-bold text-slate-900 group-hover:text-brand-600 transition-colors line-clamp-2">
                                    <a href="{{ route('parent.activities.show', ['activity' => $activity->id, 'child' => $selectedChild?->id]) }}">
                                        {{ $activity->title }}
                                    </a>
                                </h3>

                                {{-- Description --}}
                                @if($activity->description)
                                    <p class="text-xs text-slate-500 mt-2 line-clamp-2 leading-relaxed">
                                        {{ $activity->description }}
                                    </p>
                                @endif
                            </div>

                            {{-- Media & Resource Indicators --}}
                            <div class="px-5 py-2.5 bg-slate-50 border-y border-slate-100 flex items-center justify-between text-xs text-slate-600">
                                <div class="flex items-center gap-3">
                                    {{-- Videos count --}}
                                    @if($activity->videos->count() > 0)
                                        <span class="inline-flex items-center gap-1 font-semibold text-rose-600">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/>
                                            </svg>
                                            {{ __(':count Lesson', ['count' => $activity->videos->count()]) }}
                                        </span>
                                    @endif

                                    {{-- Materials count --}}
                                    @if($activity->materials->count() > 0)
                                        <span class="inline-flex items-center gap-1 font-semibold text-brand-600">
                                            <span>📎</span>
                                            {{ __(':count Resource', ['count' => $activity->materials->count()]) }}
                                        </span>
                                    @endif

                                    @if($activity->videos->isEmpty() && $activity->materials->isEmpty())
                                        <span class="text-slate-400 text-[11px]">{{ __('Step-by-step activity') }}</span>
                                    @endif
                                </div>

                                {{-- Activity Status Tag --}}
                                @if($activity->isClosed())
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Closed') }}</span>
                                @endif
                            </div>

                            {{-- Live Countdown / Deadline Strip --}}
                            <div class="p-5 pt-3">
                                @php
                                    $effectiveDeadline = $selectedChild ? $activity->effectiveDeadlineForStudent($selectedChild->id) : $activity->deadline;
                                    $isExtended = $selectedChild ? $activity->isExtendedForStudent($selectedChild->id) : false;
                                @endphp
                                @if($effectiveDeadline)
                                    <div class="mb-4">
                                        <div class="flex items-center justify-between text-[11px] mb-1.5">
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-slate-400 font-semibold">{{ __('Due Date:') }}</span>
                                                @if($isExtended)
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-black uppercase bg-amber-100 text-amber-800 border border-amber-300">
                                                        ✨ {{ __('Extended') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <span class="font-bold text-slate-700">{{ $effectiveDeadline->format('M j, Y • g:i A') }}</span>
                                        </div>
                                        <x-countdown :target="$effectiveDeadline" />
                                    </div>
                                @else
                                    <div class="mb-4 p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-center text-xs text-slate-500">
                                        {{ __('No fixed deadline • Self-paced fun') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Footer CTA --}}
                        <div class="px-5 pb-5">
                            <a
                                href="{{ route('parent.activities.show', ['activity' => $activity->id, 'child' => $selectedChild?->id]) }}"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-slate-900 text-white text-xs font-bold hover:bg-brand-600 shadow-xs hover:shadow-md transition-all duration-200"
                            >
                                <span>{{ __('Open Activity & Lessons') }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $activities->links() }}
            </div>
        @else
            {{-- Empty Activities State --}}
            <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center max-w-md mx-auto my-8 shadow-xs">
                <div class="w-16 h-16 rounded-2xl bg-brand-50 text-brand-600 mx-auto flex items-center justify-center text-3xl mb-4">
                    🎨
                </div>
                <h3 class="text-base font-bold text-slate-900">{{ __('No Activities Match Your Filter') }}</h3>
                <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                    {{ __('There are no learning activities found matching the selected status or search keywords for :child.', ['child' => $selectedChild->first_name]) }}
                </p>
                <div class="mt-6 flex items-center justify-center gap-2">
                    <button
                        type="button"
                        wire:click="setStatusFilter('all')"
                        class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition-colors"
                    >
                        {{ __('Show All Activities') }}
                    </button>
                </div>
            </div>
        @endif
    @endif
</div>
