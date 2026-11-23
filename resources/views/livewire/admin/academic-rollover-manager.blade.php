<div class="space-y-6">
    <!-- Header with Breadcrumbs & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">
                <a href="{{ route('admin.academics') }}" class="hover:text-brand-600 transition-colors">{{ __('Academics') }}</a>
                <span>/</span>
                <span class="text-brand-600">{{ __('Year-End Rollover Wizard') }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
                <svg class="w-7 h-7 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                {{ __('Year-End Academic Rollover Wizard') }}
            </h1>
            <p class="text-sm text-slate-600 mt-1">
                {{ __('Archive past academic cycles, duplicate classes and weekly timetable schedules, and transition into a new academic year.') }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a 
                href="{{ route('admin.academics') }}"
                class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-2xs inline-flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                {{ __('Back to Academics') }}
            </a>
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

    @if (session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 8.25h.008v.008H12v-.008Z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-700">✕</button>
        </div>
    @endif

    <!-- Rollover Results Card -->
    @if ($rolloverResult)
        <div class="p-6 rounded-3xl bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-200 text-emerald-900 shadow-sm animate-in fade-in duration-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-start gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-bold text-xl shrink-0 shadow-md shadow-emerald-500/20">
                        ✓
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-emerald-950">{{ __('Academic Rollover Completed Successfully!') }}</h3>
                        <p class="text-xs text-emerald-800 mt-0.5">{{ $rolloverResult['message'] }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a 
                        href="{{ route('admin.classes') }}"
                        class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-700 hover:bg-emerald-800 shadow-sm transition-all inline-flex items-center gap-2">
                        {{ __('View Classes Directory →') }}
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4 mt-6 pt-5 border-t border-emerald-200/60 text-center">
                <div class="p-3 bg-white/60 rounded-xl">
                    <div class="text-2xl font-black text-emerald-800">{{ $rolloverResult['copied_classes'] }}</div>
                    <div class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider mt-0.5">{{ __('Classes Created') }}</div>
                </div>
                <div class="p-3 bg-white/60 rounded-xl">
                    <div class="text-2xl font-black text-emerald-800">{{ $rolloverResult['copied_schedules'] }}</div>
                    <div class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider mt-0.5">{{ __('Weekly Slots Copied') }}</div>
                </div>
                <div class="p-3 bg-white/60 rounded-xl">
                    <div class="text-2xl font-black text-emerald-800">{{ $rolloverResult['rolled_over_students'] }}</div>
                    <div class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider mt-0.5">{{ __('Students Enrolled') }}</div>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Configuration Steps -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Step 1 & 2: Years Selection Card -->
            <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-xs space-y-5">
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-brand-100 text-brand-700 text-xs font-black flex items-center justify-center">1</span>
                    {{ __('Select Source & Target Academic Years') }}
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            {{ __('Source Year (Year to Archive)') }} <span class="text-rose-500">*</span>
                        </label>
                        <select 
                            wire:model.live="sourceYearId"
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-semibold">
                            <option value="">{{ __('— Select Source Year —') }}</option>
                            @foreach ($allYears as $y)
                                <option value="{{ $y->id }}">
                                    {{ $y->name }} ({{ $y->classes_count }} {{ __('classes') }}) {{ $y->is_current ? '• Current' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('sourceYearId') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-xs font-bold text-slate-700">
                                {{ __('Target Year (New Year)') }} <span class="text-rose-500">*</span>
                            </label>
                            <button 
                                type="button" 
                                wire:click="openCreateYearModal"
                                class="text-[11px] font-bold text-brand-600 hover:text-brand-800">
                                {{ __('+ Create New Year') }}
                            </button>
                        </div>
                        <select 
                            wire:model.live="targetYearId"
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-semibold">
                            <option value="">{{ __('— Select Target Year —') }}</option>
                            @foreach ($allYears as $y)
                                @if ($y->id !== $sourceYearId)
                                    <option value="{{ $y->id }}">
                                        {{ $y->name }} ({{ $y->starts_on->format('Y') }}) [{{ ucfirst($y->status) }}]
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('targetYearId') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Step 3: Class Selection Checklist -->
            <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-brand-100 text-brand-700 text-xs font-black flex items-center justify-center">2</span>
                        {{ __('Classes to Duplicate into New Year') }}
                    </h3>
                    @if (!empty($sourceClasses))
                        <div class="flex items-center gap-2 text-xs font-bold">
                            <button type="button" wire:click="selectAllClasses" class="text-brand-600 hover:text-brand-800">{{ __('Select All') }}</button>
                            <span class="text-slate-300">|</span>
                            <button type="button" wire:click="deselectAllClasses" class="text-slate-500 hover:text-slate-800">{{ __('Deselect All') }}</button>
                        </div>
                    @endif
                </div>

                @if (!empty($sourceClasses) && $sourceClasses->isNotEmpty())
                    <div class="space-y-2.5 max-h-96 overflow-y-auto pr-1">
                        @foreach ($sourceClasses as $sc)
                            <label class="p-3.5 rounded-2xl border transition-all flex items-center justify-between cursor-pointer {{ in_array($sc->id, $selectedClassIds, true) ? 'bg-brand-50/50 border-brand-200 shadow-xs' : 'bg-white border-slate-200 hover:border-slate-300' }}">
                                <div class="flex items-center gap-3">
                                    <input 
                                        type="checkbox" 
                                        value="{{ $sc->id }}"
                                        wire:model.live="selectedClassIds"
                                        class="rounded-md border-slate-300 text-brand-600 focus:ring-brand-500/20">
                                    <div>
                                        <div class="font-bold text-slate-900 text-sm">{{ $sc->title }}</div>
                                        <div class="text-[11px] text-slate-500 flex items-center gap-2">
                                            <span class="font-semibold text-slate-700">{{ $sc->subject }}</span>
                                            <span>•</span>
                                            <span>{{ $sc->teacher?->name ?? __('No Lecturer') }}</span>
                                            <span>•</span>
                                            <span>{{ $sc->enrollments_count }} {{ __('students') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $sc->kind === 'nursery_group' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-brand-50 text-brand-700 border border-brand-200' }}">
                                        {{ $sc->kind === 'nursery_group' ? __('Nursery') : __('Institute') }}
                                    </span>
                                    <div class="text-[10px] text-slate-400 mt-1 font-mono">
                                        {{ $sc->schedules->count() }} {{ __('timetable slots') }}
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center text-slate-500 bg-slate-50 rounded-2xl border border-slate-100">
                        <p class="font-bold text-slate-800 text-xs">{{ __('No classes in selected source year.') }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">{{ __('Select a source year that has active classes to rollover.') }}</p>
                    </div>
                @endif
            </div>

            <!-- Step 4: Rollover Options -->
            <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-xs space-y-4">
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-brand-100 text-brand-700 text-xs font-black flex items-center justify-center">3</span>
                    {{ __('Rollover Options & Data Safety') }}
                </h3>

                <div class="space-y-3 text-xs">
                    <label class="flex items-start gap-3 p-3 rounded-2xl border border-slate-100 hover:bg-slate-50 cursor-pointer">
                        <input type="checkbox" wire:model="copySchedules" class="rounded-md border-slate-300 text-brand-600 focus:ring-brand-500/20 mt-0.5">
                        <div>
                            <span class="font-bold text-slate-900">{{ __('Copy Weekly Timetable Schedules') }}</span>
                            <p class="text-slate-500 mt-0.5">{{ __('Replicates weekly day and time slots into each new class.') }}</p>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3 rounded-2xl border border-slate-100 hover:bg-slate-50 cursor-pointer">
                        <input type="checkbox" wire:model="rolloverStudents" class="rounded-md border-slate-300 text-brand-600 focus:ring-brand-500/20 mt-0.5">
                        <div>
                            <span class="font-bold text-slate-900">{{ __('Rollover Active Student Enrollments') }}</span>
                            <p class="text-slate-500 mt-0.5">{{ __('Enrolls existing active students into the newly copied classes for continuous enrollment. Leave unchecked for fresh admissions.') }}</p>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3 rounded-2xl border border-slate-100 hover:bg-slate-50 cursor-pointer">
                        <input type="checkbox" wire:model="archiveSourceClasses" class="rounded-md border-slate-300 text-brand-600 focus:ring-brand-500/20 mt-0.5">
                        <div>
                            <span class="font-bold text-slate-900">{{ __('Archive Source Classes') }}</span>
                            <p class="text-slate-500 mt-0.5">{{ __('Changes source year classes to Archived status, keeping full historical data.') }}</p>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3 rounded-2xl border border-slate-100 hover:bg-slate-50 cursor-pointer">
                        <input type="checkbox" wire:model="completeSourceEnrollments" class="rounded-md border-slate-300 text-brand-600 focus:ring-brand-500/20 mt-0.5">
                        <div>
                            <span class="font-bold text-slate-900">{{ __('Mark Source Enrollments as Completed') }}</span>
                            <p class="text-slate-500 mt-0.5">{{ __('Marks current active student enrollments in the source classes as Completed.') }}</p>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3 rounded-2xl border border-slate-100 hover:bg-slate-50 cursor-pointer">
                        <input type="checkbox" wire:model="setTargetAsCurrent" class="rounded-md border-slate-300 text-brand-600 focus:ring-brand-500/20 mt-0.5">
                        <div>
                            <span class="font-bold text-slate-900">{{ __('Set Target Year as Current Active Cycle') }}</span>
                            <p class="text-slate-500 mt-0.5">{{ __('Unsets current flag on past years and sets the new target year as is_current.') }}</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Right: Execution Summary Card -->
        <div class="space-y-6">
            <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-xs space-y-5">
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    {{ __('Rollover Summary') }}
                </h3>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-3 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-semibold">{{ __('Classes Selected:') }}</span>
                        <span class="font-bold text-slate-900">{{ count($selectedClassIds) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-semibold">{{ __('Copy Schedules:') }}</span>
                        <span class="font-bold {{ $copySchedules ? 'text-emerald-700' : 'text-slate-500' }}">{{ $copySchedules ? __('Yes') : __('No') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-semibold">{{ __('Rollover Students:') }}</span>
                        <span class="font-bold {{ $rolloverStudents ? 'text-emerald-700' : 'text-slate-500' }}">{{ $rolloverStudents ? __('Yes') : __('No') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-semibold">{{ __('Archive Old Data:') }}</span>
                        <span class="font-bold {{ $archiveSourceClasses ? 'text-emerald-700' : 'text-slate-500' }}">{{ $archiveSourceClasses ? __('Yes') : __('No') }}</span>
                    </div>
                </div>

                <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs">
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                        <p class="text-[11px] leading-relaxed">
                            {{ __('This operation safely archives old data without deletion. Historical attendance, payments, and submissions remain preserved.') }}
                        </p>
                    </div>
                </div>

                <button 
                    type="button" 
                    wire:click="executeRollover"
                    wire:confirm="{{ __('Execute Year-End Rollover? This will duplicate selected classes into the new academic year.') }}"
                    wire:loading.attr="disabled"
                    @disabled(!$sourceYearId || !$targetYearId || empty($selectedClassIds))
                    class="w-full py-3 rounded-2xl text-xs font-bold text-white bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 active:scale-[0.98] shadow-md shadow-purple-500/20 transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed text-center">
                    {{ __('Execute Year-End Rollover') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Create Year Modal -->
    @if ($showCreateYearModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 md:p-6">
            <div class="relative bg-white rounded-3xl max-w-md w-full max-h-[92vh] flex flex-col shadow-2xl border border-slate-100 my-auto overflow-hidden animate-in fade-in zoom-in duration-200">
                <div class="px-6 py-4 border-b border-slate-100 shrink-0 flex items-center justify-between">
                    <h3 class="text-lg font-extrabold text-slate-900">{{ __('Create Target Academic Year') }}</h3>
                    <button type="button" wire:click="closeCreateYearModal" class="p-1 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100">✕</button>
                </div>

                <form wire:submit="createTargetYear" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                    <div class="p-6 overflow-y-auto flex-1 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Academic Year Name') }} <span class="text-rose-500">*</span></label>
                            <input 
                                type="text" 
                                wire:model="newYearName"
                                placeholder="e.g. 2027 Academic Year"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-semibold">
                            @error('newYearName') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Starts On') }} <span class="text-rose-500">*</span></label>
                                <input 
                                    type="date" 
                                    wire:model="newYearStartsOn"
                                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-semibold">
                                @error('newYearStartsOn') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Ends On') }} <span class="text-rose-500">*</span></label>
                                <input 
                                    type="date" 
                                    wire:model="newYearEndsOn"
                                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-semibold">
                                @error('newYearEndsOn') <span class="text-[11px] text-rose-500 font-semibold block mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-slate-100 shrink-0 flex items-center justify-end gap-3 bg-slate-50/80">
                        <button 
                            type="button" 
                            wire:click="closeCreateYearModal"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                            {{ __('Cancel') }}
                        </button>
                        <button 
                            type="submit"
                            wire:loading.attr="disabled"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 active:scale-[0.98] shadow-md shadow-brand-500/20 transition-all btn-interactive">
                            {{ __('Save & Select') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
