<div class="space-y-1">
    <!-- Main Dashboard -->
    <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/dashboard') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-150 {{ request()->is('dashboard') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30 font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
        <svg class="mr-3 shrink-0 h-5 w-5 {{ request()->is('dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
        <span>{{ __('Dashboard') }}</span>
    </a>

    <!-- Academic Section -->
    <div class="pt-4 pb-1">
        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('Academics') }}</p>
    </div>

    @hasanyrole('super_admin|admin')
        <a href="{{ route('admin.academics') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-150 {{ request()->is('admin/academics*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30 font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
            <svg class="mr-3 shrink-0 h-5 w-5 {{ request()->is('admin/academics*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span>{{ __('Years, Terms & Batches') }}</span>
        </a>
    @endhasanyrole

    @hasanyrole('super_admin|admin')
        <a href="{{ route('admin.classes') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-150 {{ request()->is('admin/classes*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30 font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
            <svg class="mr-3 shrink-0 h-5 w-5 {{ request()->is('admin/classes*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <span>{{ __('Classes & Groups') }}</span>
        </a>
    @else
        <a href="{{ route('public.classes') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-150 {{ request()->is('classes*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
            <svg class="mr-3 shrink-0 h-5 w-5 {{ request()->is('classes*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <span>{{ __('Classes & Groups') }}</span>
        </a>
    @endhasanyrole

    <!-- Nursery Section (Module Toggled) -->
    @if (config('portal.modules.nursery', true))
        @php
            $activitiesRoute = auth()->user()?->hasRole(\App\Enums\RoleName::Parent->value)
                ? (Route::has('parent.activities') ? route('parent.activities') : url('/parent/activities'))
                : (Route::has('teacher.activities') ? route('teacher.activities') : url('/teacher/activities'));
        @endphp
        <a href="{{ $activitiesRoute }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-150 {{ (request()->is('activities*') || request()->is('teacher/activities*') || request()->is('parent/activities*')) ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
            <svg class="mr-3 shrink-0 h-5 w-5 {{ (request()->is('activities*') || request()->is('teacher/activities*') || request()->is('parent/activities*')) ? 'text-white' : 'text-amber-400 group-hover:text-amber-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ __('Nursery Activities') }}</span>
            <span class="ml-auto text-[10px] font-bold bg-amber-500/20 text-amber-300 px-1.5 py-0.5 rounded">{{ __('Nursery') }}</span>
        </a>

        @if (auth()->user()?->hasRole(\App\Enums\RoleName::Parent->value))
            <a href="{{ route('parent.progress') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-150 {{ request()->is('parent/progress*') ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                <svg class="mr-3 shrink-0 h-5 w-5 {{ request()->is('parent/progress*') ? 'text-white' : 'text-amber-400 group-hover:text-amber-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span>{{ __('Child Progress & Awards') }}</span>
            </a>

            <a href="{{ route('parent.safety') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-150 {{ request()->is('parent/safety*') ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                <svg class="mr-3 shrink-0 h-5 w-5 {{ request()->is('parent/safety*') ? 'text-white' : 'text-amber-400 group-hover:text-amber-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span>{{ __('Health & Safety') }}</span>
            </a>
        @endif
    @endif

    <!-- Assignments & Quizzes -->
    @php
        $assignmentsRoute = auth()->user()?->hasRole(\App\Enums\RoleName::Student->value)
            ? (Route::has('student.assignments') ? route('student.assignments') : url('/assignments'))
            : (Route::has('teacher.assignments') ? route('teacher.assignments') : url('/assignments'));
    @endphp
    <a href="{{ $assignmentsRoute }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-150 {{ (request()->is('assignments*') || request()->is('teacher/assignments*') || request()->is('student/assignments*')) ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30 font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
        <svg class="mr-3 shrink-0 h-5 w-5 {{ (request()->is('assignments*') || request()->is('teacher/assignments*') || request()->is('student/assignments*')) ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <span>{{ __('Assignments') }}</span>
    </a>

    <a href="{{ Route::has('quizzes.index') ? route('quizzes.index') : url('/quizzes') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-150 {{ request()->is('quizzes*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
        <svg class="mr-3 shrink-0 h-5 w-5 {{ request()->is('quizzes*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>{{ __('Quizzes & Papers') }}</span>
    </a>

    @hasanyrole('super_admin|admin|teacher')
        <a href="{{ route('teacher.questions.index') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-150 {{ request()->is('teacher/question-bank*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30 font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
            <svg class="mr-3 shrink-0 h-5 w-5 {{ request()->is('teacher/question-bank*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <span>{{ __('Question Bank') }}</span>
        </a>
    @endhasanyrole

    <!-- Operations Section (Institute Module) -->
    @if (config('portal.modules.institute', true))
        <div class="pt-4 pb-1">
            <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('Operations') }}</p>
        </div>

        <a href="{{ Route::has('attendance.index') ? route('attendance.index') : url('/attendance') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-150 {{ request()->is('attendance*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
            <svg class="mr-3 shrink-0 h-5 w-5 {{ request()->is('attendance*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            <span>{{ __('QR Attendance') }}</span>
        </a>

        <a href="{{ Route::has('finance.payments') ? route('finance.payments') : url('/finance/payments') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-150 {{ request()->is('finance*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
            <svg class="mr-3 shrink-0 h-5 w-5 {{ request()->is('finance*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ __('Fees & Slips') }}</span>
        </a>

        <a href="{{ Route::has('printing.index') ? route('printing.index') : url('/printing') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-150 {{ request()->is('printing*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
            <svg class="mr-3 shrink-0 h-5 w-5 {{ request()->is('printing*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            <span>{{ __('Print Counter') }}</span>
        </a>
    @endif

    <!-- Communication -->
    <div class="pt-4 pb-1">
        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('Communication') }}</p>
    </div>

    <a href="{{ Route::has('chat.index') ? route('chat.index') : url('/chat') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-150 {{ request()->is('chat*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
        <svg class="mr-3 shrink-0 h-5 w-5 {{ request()->is('chat*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        <span>{{ __('Private Chat') }}</span>
    </a>

    <!-- Admin Tools -->
    <div class="pt-4 pb-1">
        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('Administration') }}</p>
    </div>

    <a href="{{ Route::has('admin.users') ? route('admin.users') : url('/admin/users') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-150 {{ request()->is('admin/users*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
        <svg class="mr-3 shrink-0 h-5 w-5 {{ request()->is('admin/users*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        <span>{{ __('Users & Students') }}</span>
    </a>

    <a href="{{ Route::has('admin.settings') ? route('admin.settings') : url('/admin/settings') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-150 {{ request()->is('admin/settings*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
        <svg class="mr-3 shrink-0 h-5 w-5 {{ request()->is('admin/settings*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <span>{{ __('Portal Settings') }}</span>
    </a>
</div>
