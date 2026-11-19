<div class="space-y-6">
    <!-- Header with Welcome & Quick Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">
                <span>{{ __('Faculty Portal') }}</span>
                <span>/</span>
                <span class="text-brand-600">{{ __('Teacher Dashboard') }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
                <svg class="w-7 h-7 text-brand-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                </svg>
                {{ __('Teacher & Lecturer Workspace') }}
            </h1>
            <p class="text-sm text-slate-600 mt-1">
                {{ __('Welcome back, :name! Manage your classes, student rosters, and Faculty Workflows.', ['name' => auth()->user()->name]) }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a 
                href="{{ route('teacher.portfolio') }}"
                class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 active:scale-[0.98] shadow-md shadow-brand-500/20 transition-all cursor-pointer inline-flex items-center gap-2 btn-interactive">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                {{ __('Edit Public Portfolio') }}
            </a>
        </div>
    </div>

    <!-- Metrics Overview -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs border-l-4 border-l-brand-600">
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('My Classes') }}</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $totalClasses }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">{{ __('Active assigned classes') }}</div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs border-l-4 border-l-emerald-500">
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Enrolled Students') }}</div>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ $totalActiveStudents }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">{{ __('Across all your classes') }}</div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs border-l-4 border-l-amber-500">
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Today\'s Sessions') }}</div>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ $todaySessionsCount }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5">{{ __('Scheduled for today') }}</div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs border-l-4 border-l-purple-500">
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Portfolio Status') }}</div>
            <div class="mt-1.5">
                @php
                    $pStatus = $teacherProfile?->portfolio_status ?? 'draft';
                @endphp
                @if ($pStatus === 'published')
                    <span class="px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold inline-flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        {{ __('Published') }}
                    </span>
                @elseif ($pStatus === 'pending')
                    <span class="px-2.5 py-1 rounded-md bg-amber-50 text-amber-700 border border-amber-200 text-xs font-bold inline-flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        {{ __('Pending Review') }}
                    </span>
                @else
                    <span class="px-2.5 py-1 rounded-md bg-slate-100 text-slate-600 text-xs font-bold">
                        {{ ucfirst($pStatus) }}
                    </span>
                @endif
            </div>
            <div class="text-[11px] text-brand-600 hover:underline mt-1 font-semibold">
                <a href="{{ route('teacher.portfolio') }}">{{ __('Manage profile →') }}</a>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-200 flex items-center gap-2 overflow-x-auto pb-1">
        <button 
            type="button" 
            wire:click="setSection('overview')"
            class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer inline-flex items-center gap-2 {{ $activeSection === 'overview' ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
            {{ __('Overview') }}
        </button>

        <button 
            type="button" 
            wire:click="setSection('classes')"
            class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer inline-flex items-center gap-2 {{ $activeSection === 'classes' ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/></svg>
            {{ __('My Classes') }}
            <span class="ml-1 px-1.5 py-0.5 rounded-md text-[10px] {{ $activeSection === 'classes' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $totalClasses }}</span>
        </button>

        <button 
            type="button" 
            wire:click="setSection('sessions')"
            class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer inline-flex items-center gap-2 {{ $activeSection === 'sessions' ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
            {{ __('Upcoming Sessions') }}
            <span class="ml-1 px-1.5 py-0.5 rounded-md text-[10px] {{ $activeSection === 'sessions' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $upcomingSessions->count() }}</span>
        </button>

        <button 
            type="button" 
            wire:click="setSection('students')"
            class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer inline-flex items-center gap-2 {{ $activeSection === 'students' ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
            {{ __('Student Rosters') }}
            <span class="ml-1 px-1.5 py-0.5 rounded-md text-[10px] {{ $activeSection === 'students' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $totalActiveStudents }}</span>
        </button>
    </div>

    <!-- ============================================================== -->
    <!-- SECTION 1: OVERVIEW -->
    <!-- ============================================================== -->
    @if ($activeSection === 'overview')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Upcoming Sessions Schedule -->
            <div class="lg:col-span-2 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                        {{ __('Upcoming Class Sessions') }}
                    </h2>
                    <button type="button" wire:click="setSection('sessions')" class="text-xs font-bold text-brand-600 hover:text-brand-800">
                        {{ __('View All Sessions →') }}
                    </button>
                </div>

                <div class="space-y-3">
                    @forelse ($upcomingSessions as $session)
                        @php
                            $isToday = $session->date->format('Y-m-d') === $todayDate;
                        @endphp
                        <div class="p-4 rounded-2xl bg-white border {{ $isToday ? 'border-amber-300 ring-2 ring-amber-400/20' : 'border-slate-200' }} shadow-xs hover:shadow-md transition-all flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <div class="w-12 h-12 rounded-xl {{ $isToday ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700' }} flex flex-col items-center justify-center text-center shrink-0">
                                    <span class="text-[10px] font-bold uppercase leading-none">{{ $session->date->format('M') }}</span>
                                    <span class="text-lg font-black leading-none mt-0.5">{{ $session->date->format('d') }}</span>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-extrabold text-slate-900 text-sm">{{ $session->schoolClass?->title ?? 'Class Session' }}</h3>
                                        @if ($isToday)
                                            <span class="px-2 py-0.5 rounded-full bg-amber-500 text-white font-black text-[10px] animate-pulse">
                                                {{ __('TODAY') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-slate-500 mt-0.5 flex flex-wrap items-center gap-x-2">
                                        <span class="font-semibold text-slate-700">{{ $session->schoolClass?->subject }}</span>
                                        <span>•</span>
                                        <span class="font-mono text-slate-600">{{ date('h:i A', strtotime($session->starts_at)) }} - {{ date('h:i A', strtotime($session->ends_at)) }}</span>
                                        @if ($session->room)
                                            <span>•</span>
                                            <span class="text-slate-600">{{ $session->room }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 self-end sm:self-center">
                                @if ($session->online_url)
                                    <a 
                                        href="{{ $session->online_url }}" 
                                        target="_blank" 
                                        rel="noopener noreferrer"
                                        class="px-3 py-1.5 rounded-xl text-xs font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 transition-colors inline-flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                                        {{ __('Join Online') }}
                                    </a>
                                @endif
                                <button 
                                    type="button" 
                                    wire:click="selectClassForRoster({{ $session->class_id }})"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors">
                                    {{ __('Students') }}
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 rounded-2xl bg-white border border-slate-200 text-center text-slate-500">
                            <p class="font-bold text-slate-800">{{ __('No upcoming sessions scheduled') }}</p>
                            <p class="text-xs text-slate-400 mt-1">{{ __('Sessions will automatically appear here once timetables are generated.') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Right: My Classes Mini Grid -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/></svg>
                        {{ __('Assigned Classes') }}
                    </h2>
                    <button type="button" wire:click="setSection('classes')" class="text-xs font-bold text-brand-600 hover:text-brand-800">
                        {{ __('View All →') }}
                    </button>
                </div>

                <div class="space-y-3">
                    @forelse ($myClasses->take(4) as $c)
                        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs hover:border-brand-300 transition-all">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h4 class="font-extrabold text-slate-900 text-sm leading-tight">{{ $c->title }}</h4>
                                    <p class="text-xs text-brand-600 font-semibold">{{ $c->subject }}</p>
                                </div>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $c->kind === 'nursery_group' ? 'bg-amber-50 text-amber-700' : 'bg-brand-50 text-brand-700' }}">
                                    {{ $c->kind === 'nursery_group' ? __('Nursery') : __('Institute') }}
                                </span>
                            </div>

                            <div class="mt-3">
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="text-slate-500 font-semibold">{{ __('Students:') }}</span>
                                    <span class="font-bold text-slate-900">{{ $c->enrollments_count }} / {{ $c->capacity }}</span>
                                </div>
                                @php
                                    $p = $c->capacity > 0 ? min(100, round(($c->enrollments_count / $c->capacity) * 100)) : 0;
                                @endphp
                                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-full rounded-full {{ $p >= 100 ? 'bg-rose-500' : ($p >= 80 ? 'bg-amber-500' : 'bg-brand-600') }}" style="width: {{ $p }}%"></div>
                                </div>
                            </div>

                            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-[11px] text-slate-500">{{ $c->academicYear?->name ?? '—' }}</span>
                                <button 
                                    type="button" 
                                    wire:click="selectClassForRoster({{ $c->id }})"
                                    class="text-xs font-bold text-brand-600 hover:text-brand-800">
                                    {{ __('View Roster →') }}
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 rounded-2xl bg-white border border-slate-200 text-center text-slate-500">
                            <p class="font-bold text-slate-800 text-sm">{{ __('No classes assigned yet') }}</p>
                            <p class="text-xs text-slate-400 mt-1">{{ __('An administrator will assign you to classes or groups.') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    <!-- ============================================================== -->
    <!-- SECTION 2: MY CLASSES -->
    <!-- ============================================================== -->
    @if ($activeSection === 'classes')
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-extrabold text-slate-900">{{ __('My Assigned Classes & Nursery Groups') }}</h2>
                <span class="text-xs font-semibold text-slate-500">{{ $myClasses->count() }} {{ __('Total Classes') }}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse ($myClasses as $class)
                    <div class="p-5 rounded-3xl bg-white border border-slate-200 shadow-xs hover:shadow-md hover:border-brand-300 transition-all flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-black text-slate-900 text-base leading-snug">{{ $class->title }}</h3>
                                    <p class="text-xs font-bold text-brand-600 mt-0.5">{{ $class->subject }}</p>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold shrink-0 {{ $class->kind === 'nursery_group' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-brand-50 text-brand-700 border border-brand-200' }}">
                                    {{ $class->kind === 'nursery_group' ? __('Nursery Group') : __('Institute Class') }}
                                </span>
                            </div>

                            <div class="mt-4 space-y-2 text-xs text-slate-600">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-400 font-semibold">{{ __('Academic Cycle:') }}</span>
                                    <span class="font-bold text-slate-800">{{ $class->academicYear?->name ?? '—' }}</span>
                                </div>
                                @if ($class->batch)
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-400 font-semibold">{{ __('Cohort Batch:') }}</span>
                                        <span class="font-bold text-slate-800">{{ $class->batch->name }}</span>
                                    </div>
                                @endif
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-400 font-semibold">{{ __('Venue / Room:') }}</span>
                                    <span class="font-bold text-slate-800">{{ $class->room ?: ($class->online_url ? __('Online Stream') : __('Unassigned')) }}</span>
                                </div>
                            </div>

                            <!-- Weekly Timetable Slots -->
                            @if ($class->schedules->isNotEmpty())
                                <div class="mt-4 pt-3 border-t border-slate-100">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">{{ __('Weekly Timetable') }}</div>
                                    <div class="space-y-1">
                                        @foreach ($class->schedules as $slot)
                                            <div class="text-xs font-semibold text-slate-700 flex items-center justify-between">
                                                <span>{{ $slot->weekday_name }}</span>
                                                <span class="font-mono text-[11px] text-slate-500">{{ substr((string)$slot->starts_at, 0, 5) }} - {{ substr((string)$slot->ends_at, 0, 5) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Capacity Meter -->
                            <div class="mt-4 pt-3 border-t border-slate-100">
                                <div class="flex items-center justify-between text-xs mb-1.5">
                                    <span class="text-slate-500 font-semibold">{{ __('Enrolled Capacity') }}</span>
                                    <span class="font-bold text-slate-900">{{ $class->enrollments_count }} / {{ $class->capacity }}</span>
                                </div>
                                @php
                                    $percent = $class->capacity > 0 ? min(100, round(($class->enrollments_count / $class->capacity) * 100)) : 0;
                                @endphp
                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="h-full rounded-full {{ $percent >= 100 ? 'bg-rose-500' : ($percent >= 80 ? 'bg-amber-500' : 'bg-brand-600') }}" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-end">
                            <button 
                                type="button" 
                                wire:click="selectClassForRoster({{ $class->id }})"
                                class="w-full py-2.5 rounded-xl text-xs font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 active:scale-[0.98] transition-all text-center">
                                {{ __('View Student Roster (:count) →', ['count' => $class->enrollments_count]) }}
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 p-12 rounded-3xl bg-white border border-slate-200 text-center text-slate-500">
                        <p class="font-bold text-slate-800 text-sm">{{ __('No classes assigned to your faculty profile.') }}</p>
                        <p class="text-xs text-slate-400 mt-1">{{ __('Please contact the institute administrator to link your classes.') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    @endif

    <!-- ============================================================== -->
    <!-- SECTION 3: UPCOMING SESSIONS -->
    <!-- ============================================================== -->
    @if ($activeSection === 'sessions')
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-extrabold text-slate-900">{{ __('Upcoming Dated Sessions') }}</h2>
                <span class="text-xs font-semibold text-slate-500">{{ $upcomingSessions->count() }} {{ __('Sessions Scheduled') }}</span>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3.5">{{ __('Date') }}</th>
                                <th class="px-4 py-3.5">{{ __('Time') }}</th>
                                <th class="px-4 py-3.5">{{ __('Class & Subject') }}</th>
                                <th class="px-4 py-3.5">{{ __('Venue / Link') }}</th>
                                <th class="px-4 py-3.5">{{ __('Status') }}</th>
                                <th class="px-5 py-3.5 text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse ($upcomingSessions as $sess)
                                @php
                                    $isToday = $sess->date->format('Y-m-d') === $todayDate;
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition-colors {{ $isToday ? 'bg-amber-50/30' : '' }}">
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-900">{{ $sess->date->format('M d, Y') }}</span>
                                            <span class="text-slate-400">({{ $sess->date->format('l') }})</span>
                                            @if ($isToday)
                                                <span class="px-2 py-0.5 rounded-full bg-amber-500 text-white font-black text-[9px] animate-pulse">
                                                    {{ __('TODAY') }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 font-mono font-semibold text-slate-700 whitespace-nowrap">
                                        {{ date('h:i A', strtotime($sess->starts_at)) }} - {{ date('h:i A', strtotime($sess->ends_at)) }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="font-bold text-slate-900">{{ $sess->schoolClass?->title ?? 'Class Session' }}</div>
                                        <div class="text-[11px] text-slate-500">{{ $sess->schoolClass?->subject }}</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        @if ($sess->online_url)
                                            <a 
                                                href="{{ $sess->online_url }}" 
                                                target="_blank" 
                                                rel="noopener noreferrer"
                                                class="text-brand-600 hover:text-brand-800 font-bold inline-flex items-center gap-1">
                                                <span>{{ __('Online Meeting') }}</span>
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                                            </a>
                                        @else
                                            <span class="font-semibold text-slate-700">{{ $sess->room ?: __('Standard Venue') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="px-2.5 py-1 rounded-md bg-emerald-50 border border-emerald-200 text-[10px] font-bold text-emerald-700 inline-flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            {{ __('Scheduled') }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <button 
                                            type="button" 
                                            wire:click="selectClassForRoster({{ $sess->class_id }})"
                                            class="px-2.5 py-1 rounded-lg text-xs font-bold text-brand-600 hover:text-brand-800 hover:bg-brand-50 transition-colors">
                                            {{ __('View Students') }}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-12 text-center text-slate-500">
                                        <p class="font-bold text-slate-800">{{ __('No scheduled sessions found.') }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- ============================================================== -->
    <!-- SECTION 4: STUDENT ROSTERS -->
    <!-- ============================================================== -->
    @if ($activeSection === 'students')
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900">{{ __('Class Student Rosters') }}</h2>
                    <p class="text-xs text-slate-500">{{ __('Active enrolled students in your classes.') }}</p>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <!-- Class Filter Dropdown -->
                    <select 
                        wire:model.live="selectedClassId"
                        class="px-3.5 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-semibold">
                        <option value="">{{ __('— All My Classes (:count) —', ['count' => $totalClasses]) }}</option>
                        @foreach ($myClasses as $c)
                            <option value="{{ $c->id }}">{{ $c->title }} ({{ $c->enrollments_count }} {{ __('students') }})</option>
                        @endforeach
                    </select>

                    <!-- Student Search Input -->
                    <div class="w-full sm:w-64">
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="studentSearch"
                            placeholder="{{ __('Search student name or code...') }}"
                            class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs">
                    </div>
                </div>
            </div>

            <!-- Students Table Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3.5">{{ __('Student') }}</th>
                                <th class="px-4 py-3.5">{{ __('Student Code') }}</th>
                                <th class="px-4 py-3.5">{{ __('Class') }}</th>
                                <th class="px-4 py-3.5">{{ __('Enrolled On') }}</th>
                                <th class="px-4 py-3.5">{{ __('Guardian / Contact') }}</th>
                                <th class="px-5 py-3.5 text-right">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse ($enrolledStudents as $enr)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-5 py-4">
                                        @if ($enr->student)
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 font-bold flex items-center justify-center text-xs shrink-0">
                                                    {{ strtoupper(substr($enr->student->first_name, 0, 1) . substr($enr->student->last_name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-900 text-sm">{{ $enr->student->full_name }}</div>
                                                    <div class="text-[11px] text-slate-500">
                                                        <span>{{ $enr->student->age_years }} {{ __('yrs') }}</span>
                                                        @if ($enr->student->user)
                                                            <span>•</span>
                                                            <span>{{ $enr->student->user->email }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-slate-400 italic">{{ __('Unknown') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 font-mono font-bold text-slate-800">
                                        {{ $enr->student?->student_code ?? '—' }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="font-bold text-slate-800">{{ $enr->schoolClass?->title }}</div>
                                        <div class="text-[11px] text-slate-500">{{ $enr->schoolClass?->subject }}</div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-slate-600">
                                        {{ $enr->enrolled_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-4 py-4">
                                        @if ($enr->student && $enr->student->guardians->isNotEmpty())
                                            @php
                                                $g = $enr->student->guardians->first();
                                            @endphp
                                            <div class="font-semibold text-slate-800">{{ $g->name }}</div>
                                            <div class="text-[11px] text-slate-500">{{ $g->phone ?: ($g->user?->email ?? '—') }}</div>
                                        @else
                                            <span class="text-slate-400 italic text-[11px]">{{ __('None registered') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <span class="px-2.5 py-1 rounded-md bg-emerald-50 border border-emerald-200 text-[10px] font-bold text-emerald-700 inline-flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            {{ __('Active') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-12 text-center text-slate-500">
                                        <div class="max-w-xs mx-auto space-y-2">
                                            <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
                                            </div>
                                            <p class="font-bold text-slate-800 text-sm">{{ __('No enrolled students found') }}</p>
                                            <p class="text-xs text-slate-400">{{ __('No active student enrollments match your current filters.') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($enrolledStudents->hasPages())
                    <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50">
                        {{ $enrolledStudents->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
