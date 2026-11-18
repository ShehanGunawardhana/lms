<div>
    <!-- Hero Header -->
    <div class="bg-gradient-to-b from-brand-900 via-brand-800 to-indigo-950 text-white py-14 lg:py-20 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center space-y-4">
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-white/10 text-brand-200 border border-white/20 inline-block">
                {{ __('Distinguished Faculty') }}
            </span>
            <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white">
                {{ __('Lecturers & Teaching Faculty') }}
            </h1>
            <p class="text-sm sm:text-base text-brand-200 max-w-2xl mx-auto leading-relaxed">
                {{ __('Meet our experienced educators and researchers. Explore their credentials, view course offerings, and review sample tutorial materials before applying.') }}
            </p>
        </div>
    </div>

    <!-- Main Directory Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
        <!-- Search & Filter Controls -->
        <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Search -->
            <div class="relative md:w-80">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                </span>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="{{ __('Search lecturer by name, subject, headline...') }}"
                       class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white" />
            </div>

            <!-- Subject Filter Pills -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0">
                <button type="button"
                        wire:click="$set('selectedSubject', 'all')"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer whitespace-nowrap {{ $selectedSubject === 'all' ? 'bg-brand-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    {{ __('All Subjects') }}
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

        <!-- Lecturer Cards Grid -->
        @if ($lecturers->isEmpty())
            <div class="py-16 text-center space-y-3 bg-white rounded-3xl border border-slate-200 shadow-xs">
                <div class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center font-bold text-xl">
                    👨‍🏫
                </div>
                <h3 class="text-base font-bold text-slate-800">{{ __('No lecturers found') }}</h3>
                <p class="text-xs text-slate-400 max-w-sm mx-auto">{{ __('Try selecting a different subject or clearing your search criteria.') }}</p>
                <button type="button"
                        wire:click="$set('search', ''); $set('selectedSubject', 'all')"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 border border-brand-200 inline-block transition-colors cursor-pointer">
                    {{ __('Reset Filters') }}
                </button>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($lecturers as $lecturer)
                    <div class="p-6 rounded-3xl bg-white border border-slate-200/90 shadow-xs hover:border-brand-300 hover:shadow-lg transition-all card-interactive flex flex-col justify-between">
                        <div class="space-y-4">
                            <!-- Avatar & Core Identity -->
                            <div class="flex items-start gap-4">
                                <div class="w-16 h-16 rounded-2xl bg-brand-50 text-brand-700 font-bold flex items-center justify-center text-xl shrink-0 overflow-hidden shadow-inner border border-brand-100">
                                    @if ($lecturer->photo)
                                        <img src="{{ $lecturer->photo->temporarySignedUrl(now()->addHour()) }}" class="w-full h-full object-cover" alt="{{ $lecturer->user->name }}" />
                                    @else
                                        {{ strtoupper(substr($lecturer->user->name, 0, 1)) }}
                                    @endif
                                </div>
                                <div>
                                    <h3 class="text-base font-black text-slate-900 group-hover:text-brand-600 transition-colors">
                                        {{ $lecturer->user->name }}
                                    </h3>
                                    <p class="text-xs text-brand-600 font-semibold line-clamp-1 mt-0.5">
                                        {{ $lecturer->headline ?: __('Faculty Lecturer') }}
                                    </p>
                                    <p class="text-[10px] text-slate-400 mt-1">
                                        {{ $lecturer->classes->count() }} {{ __('Active Classes') }}
                                    </p>
                                </div>
                            </div>

                            <!-- Bio Teaser -->
                            <p class="text-xs text-slate-600 leading-relaxed line-clamp-3">
                                {{ $lecturer->bio ?: __('Experienced educator committed to excellence in student learning and academic mentorship.') }}
                            </p>

                            <!-- Subjects Tags -->
                            @if (is_array($lecturer->subjects) && !empty($lecturer->subjects))
                                <div class="flex flex-wrap gap-1 pt-1">
                                    @foreach (array_slice($lecturer->subjects, 0, 3) as $subject)
                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-slate-100 text-slate-700">
                                            {{ $subject }}
                                        </span>
                                    @endforeach
                                    @if (count($lecturer->subjects) > 3)
                                        <span class="px-1.5 py-0.5 rounded-lg text-[10px] font-semibold bg-slate-50 text-slate-400">
                                            +{{ count($lecturer->subjects) - 3 }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Card Footer -->
                        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                {{ __('Portfolio & Classes') }}
                            </span>
                            <a href="{{ route('public.lecturers.show', $lecturer->id) }}"
                               class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-500 shadow-sm shadow-brand-500/20 transition-all btn-interactive inline-flex items-center gap-1">
                                <span>{{ __('View Profile') }}</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pt-6">
                {{ $lecturers->links() }}
            </div>
        @endif
    </div>
</div>
