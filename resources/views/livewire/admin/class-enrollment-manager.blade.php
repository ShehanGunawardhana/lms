<div class="space-y-6">
    <!-- Header with Breadcrumbs & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">
                <a href="{{ route('admin.classes') }}" class="hover:text-brand-600 transition-colors">{{ __('Classes & Groups') }}</a>
                <span>/</span>
                <span class="text-slate-700 font-bold">{{ $class->title }}</span>
                <span>/</span>
                <span class="text-brand-600">{{ __('Enrollments') }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
                    <svg class="w-7 h-7 text-brand-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                    {{ __('Class Student Roster & Enrollments') }}
                </h1>
                @if (($class->kind instanceof \App\Enums\ClassKind ? $class->kind->value : (string) $class->kind) === 'nursery_group')
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                        {{ __('Nursery Group') }}
                    </span>
                @else
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200">
                        {{ __('Institute Class') }}
                    </span>
                @endif
            </div>
            <p class="text-sm text-slate-600 mt-1">
                {{ __('Subject:') }} <strong class="text-slate-800">{{ $class->subject }}</strong>
                @if ($class->teacher)
                    <span class="mx-1 text-slate-400">•</span>
                    {{ __('Lecturer:') }} <strong class="text-slate-800">{{ $class->teacher->name }}</strong>
                @endif
                @if ($class->academicYear)
                    <span class="mx-1 text-slate-400">•</span>
                    {{ __('Academic Year:') }} <span class="text-slate-700">{{ $class->academicYear->name }}</span>
                @endif
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a 
                href="{{ route('admin.classes') }}"
                class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-2xs inline-flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                {{ __('Back to Classes') }}
            </a>
            <button 
                type="button" 
                wire:click="openEnrollModal"
                class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 active:scale-[0.98] shadow-md shadow-brand-500/20 transition-all cursor-pointer inline-flex items-center gap-2 btn-interactive">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z"/></svg>
                {{ __('Enroll Student') }}
            </button>
        </div>
    </div>

    <!-- Flash Notifications -->
    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">✕</button>
        </div>
    @endif

    @if (session('info'))
        <div class="p-4 rounded-2xl bg-blue-50 border border-blue-200 text-blue-800 text-sm font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                <span>{{ session('info') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-blue-500 hover:text-blue-700">✕</button>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 8.25h.008v.008H12v-.008Z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-700">✕</button>
        </div>
    @endif

    <!-- Capacity & Enrollment Metrics Card -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">{{ __('Active Enrolled') }}</div>
            <div class="flex items-baseline justify-between">
                <div class="text-2xl font-extrabold text-slate-900">{{ $activeCount }} <span class="text-sm font-semibold text-slate-400">/ {{ $class->capacity }}</span></div>
                @php
                    $capPercent = $class->capacity > 0 ? min(100, round(($activeCount / $class->capacity) * 100)) : 0;
                @endphp
                <span class="text-xs font-bold {{ $capPercent >= 100 ? 'text-rose-600' : ($capPercent >= 80 ? 'text-amber-600' : 'text-brand-600') }}">
                    {{ $capPercent }}%
                </span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-2 mt-2 overflow-hidden">
                <div class="h-full rounded-full transition-all duration-300 {{ $capPercent >= 100 ? 'bg-rose-500' : ($capPercent >= 80 ? 'bg-amber-500' : 'bg-brand-600') }}" style="width: {{ $capPercent }}%"></div>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">{{ __('Available Seats') }}</div>
            <div class="flex items-center gap-2">
                <div class="text-2xl font-extrabold {{ $availableSeats > 0 ? 'text-emerald-600' : 'text-rose-500' }}">
                    {{ $availableSeats }}
                </div>
                @if ($availableSeats === 0)
                    <span class="px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 border border-rose-200 text-[10px] font-bold">
                        {{ __('Full Capacity') }}
                    </span>
                @else
                    <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">
                        {{ __('Seats Open') }}
                    </span>
                @endif
            </div>
            <div class="text-xs text-slate-500 mt-1">
                {{ __('Maximum capacity:') }} {{ $class->capacity }}
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">{{ __('Waiting List') }}</div>
            <div class="flex items-center gap-2">
                <div class="text-2xl font-extrabold text-amber-600">{{ $waitlistCount }}</div>
                @if ($waitlistCount > 0 && $availableSeats > 0)
                    <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold animate-pulse">
                        {{ __('Promotion Ready!') }}
                    </span>
                @endif
            </div>
            <div class="text-xs text-slate-500 mt-1">
                {{ __('Queued students awaiting opening') }}
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">{{ __('Dropped / Inactive') }}</div>
            <div class="text-2xl font-extrabold text-slate-600">{{ $droppedCount }}</div>
            <div class="text-xs text-slate-500 mt-1">
                {{ __('Students removed or dropped') }}
            </div>
        </div>
    </div>

    <!-- Navigation Tabs & Search -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-2">
        <div class="flex items-center gap-2 overflow-x-auto">
            <button 
                type="button" 
                wire:click="setActiveTab('active')"
                class="px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer inline-flex items-center gap-2 {{ $activeTab === 'active' ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                {{ __('Active Students') }}
                <span class="ml-1 px-1.5 py-0.5 rounded-md text-[10px] {{ $activeTab === 'active' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $activeCount }}</span>
            </button>

            <button 
                type="button" 
                wire:click="setActiveTab('waitlisted')"
                class="px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer inline-flex items-center gap-2 {{ $activeTab === 'waitlisted' ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                {{ __('Waiting List') }}
                <span class="ml-1 px-1.5 py-0.5 rounded-md text-[10px] {{ $activeTab === 'waitlisted' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $waitlistCount }}</span>
            </button>

            <button 
                type="button" 
                wire:click="setActiveTab('dropped')"
                class="px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer inline-flex items-center gap-2 {{ $activeTab === 'dropped' ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                {{ __('Dropped / Inactive') }}
                <span class="ml-1 px-1.5 py-0.5 rounded-md text-[10px] {{ $activeTab === 'dropped' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $droppedCount }}</span>
            </button>
        </div>

        <div class="w-full sm:w-72">
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('Search student by name or code...') }}"
                class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-medium">
        </div>
    </div>

    <!-- Enrollments Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                    <tr>
                        @if ($activeTab === 'waitlisted')
                            <th class="px-4 py-3.5 w-14">{{ __('Pos') }}</th>
                        @endif
                        <th class="px-5 py-3.5">{{ __('Student') }}</th>
                        <th class="px-4 py-3.5">{{ __('Student Code') }}</th>
                        <th class="px-4 py-3.5">{{ __('Date & Source') }}</th>
                        <th class="px-4 py-3.5">{{ __('Status') }}</th>
                        @if ($activeTab === 'dropped')
                            <th class="px-4 py-3.5">{{ __('Drop Reason / Notes') }}</th>
                        @endif
                        <th class="px-5 py-3.5 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($enrollments as $index => $enrollment)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            @if ($activeTab === 'waitlisted')
                                <td class="px-4 py-4">
                                    <span class="w-7 h-7 rounded-full bg-amber-100 text-amber-800 font-extrabold flex items-center justify-center text-xs">
                                        #{{ ($enrollments->currentPage() - 1) * $enrollments->perPage() + $index + 1 }}
                                    </span>
                                </td>
                            @endif

                            <td class="px-5 py-4">
                                @if ($enrollment->student)
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-brand-100 text-brand-700 font-bold flex items-center justify-center text-xs shrink-0">
                                            {{ strtoupper(substr($enrollment->student->first_name, 0, 1) . substr($enrollment->student->last_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 text-sm">{{ $enrollment->student->full_name }}</div>
                                            <div class="text-[11px] text-slate-500 flex items-center gap-2">
                                                <span>{{ $enrollment->student->age_years }} {{ __('years old') }}</span>
                                                @if ($enrollment->student->user)
                                                    <span>•</span>
                                                    <span>{{ $enrollment->student->user->email }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">{{ __('Unknown Student') }}</span>
                                @endif
                            </td>

                            <td class="px-4 py-4 font-mono font-bold text-slate-800">
                                {{ $enrollment->student?->student_code ?? '—' }}
                            </td>

                            <td class="px-4 py-4 whitespace-nowrap text-slate-600">
                                <div class="font-semibold text-slate-900">{{ $enrollment->enrolled_at->format('M d, Y') }}</div>
                                <div class="text-[11px] text-slate-500 capitalize">{{ __('Source:') }} {{ str_replace('_', ' ', $enrollment->source) }}</div>
                            </td>

                            <td class="px-4 py-4">
                                @if ($enrollment->isActive())
                                    <span class="px-2.5 py-1 rounded-md bg-emerald-50 border border-emerald-200 text-[10px] font-bold text-emerald-700 inline-flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        {{ __('Active') }}
                                    </span>
                                @elseif ($enrollment->isWaitlisted())
                                    <span class="px-2.5 py-1 rounded-md bg-amber-50 border border-amber-200 text-[10px] font-bold text-amber-700 inline-flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        {{ __('Waitlisted') }}
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-md bg-slate-100 border border-slate-200 text-[10px] font-bold text-slate-600 inline-flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        {{ __('Dropped') }}
                                    </span>
                                @endif
                            </td>

                            @if ($activeTab === 'dropped')
                                <td class="px-4 py-4 text-xs text-slate-600 max-w-xs">
                                    {{ $enrollment->notes ?: '—' }}
                                    @if ($enrollment->dropped_at)
                                        <div class="text-[10px] text-slate-400 mt-0.5">{{ __('Dropped on') }} {{ $enrollment->dropped_at->format('M d, Y') }}</div>
                                    @endif
                                </td>
                            @endif

                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($enrollment->isActive())
                                        <button 
                                            type="button" 
                                            wire:click="openDropModal({{ $enrollment->id }})"
                                            class="px-2.5 py-1 rounded-lg text-xs font-bold text-rose-600 hover:text-rose-700 hover:bg-rose-50 transition-colors">
                                            {{ __('Drop') }}
                                        </button>
                                    @elseif ($enrollment->isWaitlisted())
                                        <button 
                                            type="button" 
                                            wire:click="promoteWaitlisted({{ $enrollment->id }})"
                                            class="px-3 py-1 rounded-lg text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] shadow-xs transition-all cursor-pointer">
                                            {{ __('Promote to Active') }}
                                        </button>
                                        <button 
                                            type="button" 
                                            wire:click="openDropModal({{ $enrollment->id }})"
                                            class="px-2.5 py-1 rounded-lg text-xs font-bold text-slate-500 hover:text-rose-600 hover:bg-slate-100 transition-colors">
                                            {{ __('Remove') }}
                                        </button>
                                    @else
                                        <button 
                                            type="button" 
                                            wire:click="restoreDropped({{ $enrollment->id }})"
                                            class="px-3 py-1 rounded-lg text-xs font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 active:scale-[0.98] transition-all cursor-pointer">
                                            {{ __('Re-enroll') }}
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $activeTab === 'waitlisted' ? 6 : ($activeTab === 'dropped' ? 6 : 5) }}" class="px-5 py-12 text-center">
                                <div class="max-w-xs mx-auto space-y-2">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
                                    </div>
                                    <p class="font-bold text-slate-800 text-sm">
                                        @if ($activeTab === 'active')
                                            {{ __('No active students in this class') }}
                                        @elseif ($activeTab === 'waitlisted')
                                            {{ __('No students on the waiting list') }}
                                        @else
                                            {{ __('No dropped students') }}
                                        @endif
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        @if ($activeTab === 'active')
                                            {{ __('Click "Enroll Student" above to admit students.') }}
                                        @elseif ($activeTab === 'waitlisted')
                                            {{ __('When the class reaches capacity, new enrollments queue here.') }}
                                        @else
                                            {{ __('Any dropped student records will be archived here.') }}
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($enrollments->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50">
                {{ $enrollments->links() }}
            </div>
        @endif
    </div>

    <!-- ============================================================== -->
    <!-- ENROLL STUDENT MODAL WITH LIVE CLASH DETECTION -->
    <!-- ============================================================== -->
    @if ($showEnrollModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 md:p-6">
            <div class="relative bg-white rounded-3xl max-w-lg w-full max-h-[92vh] sm:max-h-[90vh] flex flex-col shadow-2xl border border-slate-100 my-auto overflow-hidden animate-in fade-in zoom-in duration-200">
                <div class="px-6 py-4 border-b border-slate-100 shrink-0 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-900">{{ __('Enroll Student in Class') }}</h3>
                        <p class="text-xs text-slate-500">{{ $class->title }} ({{ $class->subject }})</p>
                    </div>
                    <button type="button" wire:click="closeEnrollModal" class="p-1 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100">✕</button>
                </div>

                <form wire:submit="enrollStudent" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                    <div class="p-6 overflow-y-auto flex-1 space-y-4">
                    <!-- Student Selector -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Select Student') }} <span class="text-rose-500">*</span></label>
                        
                        @if ($selectedStudentId)
                            <div class="p-3 rounded-2xl bg-brand-50/80 border border-brand-200 flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-brand-600 text-white font-bold flex items-center justify-center text-xs">✓</div>
                                    <div>
                                        <div class="font-bold text-xs text-brand-900">{{ $studentSearch }}</div>
                                        <div class="text-[10px] text-brand-700 font-semibold">{{ __('Selected for enrollment') }}</div>
                                    </div>
                                </div>
                                <button 
                                    type="button" 
                                    wire:click="clearSelectedStudent"
                                    class="text-xs font-bold text-brand-700 hover:text-brand-900 px-2 py-1 rounded-lg hover:bg-brand-100">
                                    {{ __('Change') }}
                                </button>
                            </div>
                        @else
                            <div class="relative">
                                <input 
                                    type="text" 
                                    wire:model.live.debounce.250ms="studentSearch"
                                    placeholder="{{ __('Type student name or ID code (e.g. STU-001)...') }}"
                                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-medium">

                                @if (!empty($candidateStudents))
                                    <div class="absolute left-0 right-0 mt-1 bg-white rounded-2xl border border-slate-200 shadow-xl overflow-hidden z-20 max-h-56 overflow-y-auto">
                                        <div class="p-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50 border-b border-slate-100">
                                            {{ __('Matching Students') }}
                                        </div>
                                        @foreach ($candidateStudents as $candidate)
                                            <button 
                                                type="button" 
                                                wire:click="selectStudent({{ $candidate->id }})"
                                                class="w-full px-3 py-2 text-left hover:bg-brand-50 flex items-center justify-between text-xs border-b border-slate-50 transition-colors">
                                                <div>
                                                    <div class="font-bold text-slate-900">{{ $candidate->full_name }}</div>
                                                    <div class="text-[11px] text-slate-500 font-mono">{{ $candidate->student_code }}</div>
                                                </div>
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-600">
                                                    {{ $candidate->kind instanceof \App\Enums\StudentKind ? $candidate->kind->label() : ucfirst($candidate->kind) }}
                                                </span>
                                            </button>
                                        @endforeach
                                    </div>
                                @elseif (strlen(trim($studentSearch)) >= 2)
                                    <div class="p-2.5 mt-1 bg-slate-50 rounded-xl text-xs text-slate-500 text-center border border-slate-100">
                                        {{ __('No non-enrolled students matching query.') }}
                                    </div>
                                @endif
                            </div>
                        @endif

                        @error('selectedStudentId') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Live Clash Alert -->
                    @if (!empty($detectedClashes))
                        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 space-y-2.5 animate-in fade-in duration-200">
                            <div class="flex items-start gap-2.5">
                                <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                </svg>
                                <div>
                                    <h4 class="font-bold text-xs text-amber-900">{{ __('Schedule Clash Warning!') }}</h4>
                                    <p class="text-[11px] text-amber-800 mt-0.5">
                                        {{ __('This student has conflicting weekly timetable slots with existing active classes:') }}
                                    </p>
                                </div>
                            </div>

                            <ul class="text-[11px] space-y-1.5 pl-7 list-disc text-amber-800">
                                @foreach ($detectedClashes as $clash)
                                    <li>
                                        <strong>{{ $clash['conflicting_class_title'] }}</strong>: {{ $clash['weekday_name'] }}
                                        <span class="font-mono font-bold">({{ $clash['conflicting_time'] }})</span>
                                        {{ __('overlaps with this class') }}
                                        <span class="font-mono font-bold">({{ $clash['target_time'] }})</span>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="pt-2 border-t border-amber-200/80 flex items-center gap-2">
                                <input 
                                    type="checkbox" 
                                    id="acknowledgeClash" 
                                    wire:model="acknowledgeClash"
                                    class="rounded-md border-amber-300 text-amber-600 focus:ring-amber-500/20">
                                <label for="acknowledgeClash" class="text-xs font-bold text-amber-900 cursor-pointer">
                                    {{ __('I acknowledge this schedule clash and wish to proceed.') }}
                                </label>
                            </div>
                            @error('acknowledgeClash') <span class="text-[11px] text-rose-600 font-semibold block">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <!-- Capacity / Waitlist Status Notice -->
                    @if ($availableSeats === 0)
                        <div class="p-3.5 rounded-2xl bg-blue-50 border border-blue-200 text-blue-900 text-xs flex items-center gap-2.5">
                            <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                            <div>
                                <span class="font-bold">{{ __('Class is at Full Capacity') }} ({{ $activeCount }}/{{ $class->capacity }})</span>
                                <p class="text-[11px] text-blue-700 mt-0.5">{{ __('Student will be placed at the end of the Waiting List.') }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Enrollment Source') }} <span class="text-rose-500">*</span></label>
                            <select 
                                wire:model="enrollSource"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-semibold">
                                <option value="admin">{{ __('Admin Assignment') }}</option>
                                <option value="application">{{ __('Approved Application') }}</option>
                                <option value="direct">{{ __('Direct Registration') }}</option>
                                <option value="transfer">{{ __('Class Transfer') }}</option>
                            </select>
                            @error('enrollSource') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Monthly Fee') }}</label>
                            <div class="px-3.5 py-2.5 text-xs rounded-xl border border-slate-100 bg-slate-50 text-slate-700 font-bold font-mono">
                                @if ($class->fee_amount > 0)
                                    LKR {{ number_format((float) $class->fee_amount, 2) }}
                                @else
                                    {{ __('Free / Included') }}
                                @endif
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Internal Notes') }}</label>
                        <textarea 
                            wire:model="enrollNotes"
                            rows="2"
                            placeholder="{{ __('Optional notes regarding this enrollment...') }}"
                            class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs"></textarea>
                        @error('enrollNotes') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    </div>

                    <div class="px-6 py-4 border-t border-slate-100 shrink-0 flex items-center justify-end gap-3 bg-slate-50/80">
                        <button 
                            type="button" 
                            wire:click="closeEnrollModal"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                            {{ __('Cancel') }}
                        </button>
                        <button 
                            type="submit"
                            wire:loading.attr="disabled"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 active:scale-[0.98] shadow-md shadow-brand-500/20 transition-all btn-interactive">
                            {{ $availableSeats === 0 ? __('Add to Waiting List') : __('Confirm Enrollment') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- ============================================================== -->
    <!-- DROP ENROLLMENT MODAL -->
    <!-- ============================================================== -->
    @if ($showDropModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 md:p-6">
            <div class="relative bg-white rounded-3xl max-w-md w-full max-h-[92vh] flex flex-col shadow-2xl border border-slate-100 my-auto overflow-hidden animate-in fade-in zoom-in duration-200">
                <div class="px-6 py-4 border-b border-slate-100 shrink-0 flex items-center justify-between">
                    <h3 class="text-lg font-extrabold text-slate-900">{{ __('Drop Student Enrollment') }}</h3>
                    <button type="button" wire:click="closeDropModal" class="p-1 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100">✕</button>
                </div>

                <div class="p-6 overflow-y-auto flex-1 space-y-4">
                    <p class="text-xs text-slate-600">
                        {{ __('Are you sure you want to drop this student from') }} <strong class="text-slate-900">{{ $class->title }}</strong>?
                        {{ __('Their status will become Dropped and a seat will be freed for waiting list candidates.') }}
                    </p>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Reason / Notes (Optional)') }}</label>
                        <textarea 
                            wire:model="dropReason"
                            rows="2"
                            placeholder="e.g. Student requested withdrawal or transferred to another subject"
                            class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs"></textarea>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-slate-100 shrink-0 flex items-center justify-end gap-3 bg-slate-50/80">
                    <button 
                        type="button" 
                        wire:click="closeDropModal"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                        {{ __('Cancel') }}
                    </button>
                    <button 
                        type="button" 
                        wire:click="confirmDrop"
                        wire:loading.attr="disabled"
                        class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 active:scale-[0.98] shadow-md shadow-rose-500/20 transition-all cursor-pointer">
                        {{ __('Confirm Drop') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
