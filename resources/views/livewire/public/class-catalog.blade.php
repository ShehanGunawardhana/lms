<div>
    <!-- Hero Banner -->
    <div class="bg-gradient-to-b from-brand-900 via-brand-800 to-indigo-950 text-white py-14 lg:py-20 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center space-y-4">
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-white/10 text-brand-200 border border-white/20 inline-block">
                {{ __('Curriculum & Classes') }}
            </span>
            <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white">
                {{ __('Academic Classes & Programs') }}
            </h1>
            <p class="text-sm sm:text-base text-brand-200 max-w-2xl mx-auto leading-relaxed">
                {{ __('Explore active institutional programs, review course fees, lecture schedules, and submit applications directly online.') }}
            </p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
        <!-- Toast / Alerts -->
        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2 shadow-xs">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Filter and Search Bar -->
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Search -->
            <div class="relative md:w-80">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                </span>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="{{ __('Search classes by title, subject, lecturer...') }}"
                       class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white" />
            </div>

            <!-- Subject Filter Pills -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0">
                <button type="button"
                        wire:click="$set('selectedSubject', 'all')"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer whitespace-nowrap {{ $selectedSubject === 'all' ? 'bg-brand-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    {{ __('All Disciplines') }}
                </button>
                @foreach ($availableSubjects as $subj)
                    <button type="button"
                            wire:click="$set('selectedSubject', '{{ $subj }}')"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer whitespace-nowrap {{ $selectedSubject === $subj ? 'bg-brand-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ $subj }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Classes Grid -->
        @if ($classes->isEmpty())
            <div class="py-16 text-center space-y-3 bg-white rounded-3xl border border-slate-200 shadow-xs">
                <div class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center font-bold text-xl">
                    📚
                </div>
                <h3 class="text-base font-bold text-slate-800">{{ __('No classes found') }}</h3>
                <p class="text-xs text-slate-400 max-w-sm mx-auto">{{ __('Try selecting a different subject or adjusting your search keywords.') }}</p>
                <button type="button"
                        wire:click="$set('search', ''); $set('selectedSubject', 'all')"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 border border-brand-200 inline-block transition-colors cursor-pointer">
                    {{ __('Reset Filters') }}
                </button>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($classes as $item)
                    <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-xs hover:border-brand-300 hover:shadow-lg transition-all card-interactive flex flex-col justify-between">
                        <div class="space-y-4">
                            <!-- Category Badge & Mode -->
                            <div class="flex items-center justify-between">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider bg-brand-50 text-brand-700 border border-brand-200">
                                    {{ $item->subject ?: __('General') }}
                                </span>
                                <span class="text-[11px] font-mono text-slate-400">
                                    {{ $item->room ?: ($item->online_url ? '🌐 ' . __('Online') : '🏫 ' . __('Campus')) }}
                                </span>
                            </div>

                            <div>
                                <h3 class="text-base font-black text-slate-900">{{ $item->title }}</h3>
                                @if ($item->description)
                                    <p class="text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">{{ $item->description }}</p>
                                @endif
                            </div>

                            <!-- Lecturer Preview -->
                            @if ($item->teacher)
                                <div class="flex items-center gap-2.5 pt-2 border-t border-slate-100">
                                    <div class="w-8 h-8 rounded-full bg-brand-100 text-brand-700 font-bold flex items-center justify-center text-xs shrink-0 overflow-hidden">
                                        @if ($item->teacher->teacherProfile?->photo)
                                            <img src="{{ $item->teacher->teacherProfile->photo->temporarySignedUrl(now()->addHour()) }}" class="w-full h-full object-cover" alt="" />
                                        @else
                                            {{ strtoupper(substr($item->teacher->name, 0, 1)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-800">{{ $item->teacher->name }}</p>
                                        <p class="text-[10px] text-slate-400">{{ $item->teacher->teacherProfile?->headline ?: __('Faculty Lecturer') }}</p>
                                    </div>
                                </div>
                            @endif

                            <!-- Fee & Details -->
                            <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100 flex items-center justify-between text-xs font-mono">
                                <span class="text-slate-500 font-sans text-[11px]">{{ __('Monthly Fee:') }}</span>
                                <span class="font-bold text-slate-900">LKR {{ number_format((float) $item->fee_amount, 2) }}</span>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                            @if ($item->teacher?->teacherProfile)
                                <a href="{{ route('public.lecturers.show', $item->teacher->teacherProfile->id) }}"
                                   class="text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
                                    {{ __('Faculty Bio') }}
                                </a>
                            @else
                                <span></span>
                            @endif

                            <button type="button"
                                    wire:click="openApplyModal({{ $item->id }})"
                                    class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-500 shadow-sm shadow-brand-500/20 transition-all btn-interactive cursor-pointer">
                                {{ __('Apply Now') }} &rarr;
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pt-6">
                {{ $classes->links() }}
            </div>
        @endif
    </div>

    <!-- Apply Modal -->
    @if ($showApplyModal && $selectedClass)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center font-bold">
                        ✍️
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900">{{ __('Class Admission Application') }}</h3>
                        <p class="text-xs text-brand-600 font-semibold">{{ $selectedClass->title }}</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Full Name') }} <span class="text-rose-500">*</span></label>
                        <input type="text"
                               wire:model="applicantName"
                               placeholder="{{ __('Enter applicant name') }}"
                               class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" />
                        @error('applicantName')
                            <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Email Address') }} <span class="text-rose-500">*</span></label>
                        <input type="email"
                               wire:model="applicantEmail"
                               placeholder="{{ __('Enter email address') }}"
                               class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" />
                        @error('applicantEmail')
                            <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Phone Number') }} <span class="text-rose-500">*</span></label>
                        <input type="tel"
                               wire:model="applicantPhone"
                               placeholder="{{ __('+94770000000') }}"
                               class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" />
                        @error('applicantPhone')
                            <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Notes (Optional)') }}</label>
                        <textarea wire:model="applicationNotes"
                                  rows="2"
                                  class="w-full text-xs rounded-xl border border-slate-200 p-2 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"
                                  placeholder="{{ __('Optional message for admissions...') }}"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button"
                            wire:click="closeApplyModal"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                        {{ __('Cancel') }}
                    </button>
                    <button type="button"
                            wire:click="submitApplication"
                            class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-500 transition-all shadow-md shadow-brand-500/20 btn-interactive cursor-pointer">
                        {{ __('Submit Application') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
