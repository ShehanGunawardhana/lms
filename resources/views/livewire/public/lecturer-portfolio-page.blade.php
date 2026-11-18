<div>
    <!-- Hero / Profile Banner -->
    <div class="bg-gradient-to-r from-brand-950 via-brand-900 to-indigo-950 text-white py-14 lg:py-20 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-8 text-center md:text-left">
                <!-- Large Faculty Photo -->
                <div class="w-32 h-32 sm:w-40 sm:h-40 rounded-3xl bg-white/10 backdrop-blur-md border-2 border-white/20 shadow-2xl flex items-center justify-center font-bold text-4xl shrink-0 overflow-hidden">
                    @if ($profile->photo)
                        <img src="{{ $profile->photo->temporarySignedUrl(now()->addHour()) }}" class="w-full h-full object-cover" alt="{{ $profile->user->name }}" />
                    @else
                        {{ strtoupper(substr($profile->user->name, 0, 1)) }}
                    @endif
                </div>

                <div class="space-y-3 max-w-3xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/20 text-brand-200 text-xs font-semibold">
                        <span>🎓 {{ __('Faculty Member') }}</span>
                        @if (!$profile->isPublished())
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                {{ __('Private Preview Mode') }}
                            </span>
                        @endif
                    </div>

                    <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white">
                        {{ $profile->user->name }}
                    </h1>

                    <p class="text-base sm:text-lg text-brand-200 font-medium">
                        {{ $profile->headline ?: __('Faculty Lecturer') }}
                    </p>

                    <!-- Subjects Pills -->
                    @if (is_array($profile->subjects) && !empty($profile->subjects))
                        <div class="flex flex-wrap justify-center md:justify-start gap-1.5 pt-1">
                            @foreach ($profile->subjects as $subject)
                                <span class="px-3 py-1 rounded-xl text-xs font-bold bg-white/10 text-white border border-white/15">
                                    {{ $subject }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Main Profile Content Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">
        <!-- Toast / Alerts -->
        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2 shadow-xs">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left 2 Cols: Bio & Sample Materials -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Biography -->
                <div class="p-6 sm:p-8 rounded-3xl bg-white border border-slate-200 shadow-xs space-y-4">
                    <h2 class="text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <span>📖</span>
                        <span>{{ __('Academic Biography & Teaching Philosophy') }}</span>
                    </h2>
                    <div class="text-xs sm:text-sm text-slate-600 leading-relaxed space-y-3">
                        <p class="whitespace-pre-line">{{ $profile->bio ?: __('Biography details are being finalized by faculty.') }}</p>
                    </div>
                </div>

                <!-- Academic Credentials & Qualifications -->
                <div class="p-6 sm:p-8 rounded-3xl bg-white border border-slate-200 shadow-xs space-y-4">
                    <h2 class="text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <span>🏆</span>
                        <span>{{ __('Qualifications & Academic Background') }}</span>
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed whitespace-pre-line bg-slate-50/70 p-4 rounded-2xl border border-slate-100">
                        {{ $profile->qualifications ?: __('Qualifications on file with administration.') }}
                    </p>
                </div>

                <!-- Sample Learning Materials (§5.5) -->
                <div class="p-6 sm:p-8 rounded-3xl bg-white border border-slate-200 shadow-xs space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                            <span>📂</span>
                            <span>{{ __('Sample Learning Materials & Notes (§5.5)') }}</span>
                        </h2>
                    </div>

                    @if (is_array($profile->sample_materials) && !empty($profile->sample_materials))
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach ($profile->sample_materials as $material)
                                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/50 hover:border-brand-200 hover:bg-white transition-all space-y-2 flex flex-col justify-between">
                                    <div class="space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-brand-100 text-brand-800">
                                                {{ $material['type'] ?? 'document' }}
                                            </span>
                                        </div>
                                        <h4 class="text-xs font-bold text-slate-900">{{ $material['title'] ?? 'Material' }}</h4>
                                        @if (!empty($material['description']))
                                            <p class="text-[11px] text-slate-500 leading-normal">{{ $material['description'] }}</p>
                                        @endif
                                    </div>
                                    @if (!empty($material['url']))
                                        <a href="{{ $material['url'] }}" target="_blank" class="text-xs font-bold text-brand-600 hover:text-brand-800 inline-flex items-center gap-1 pt-1">
                                            <span>{{ __('Open Sample Resource') }}</span>
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-slate-400 italic">{{ __('No sample materials published yet.') }}</p>
                    @endif
                </div>
            </div>

            <!-- Right Col: Classes Taught with Apply Button (§5.3, §5.5) -->
            <div class="space-y-6">
                <div class="p-6 sm:p-8 rounded-3xl bg-white border border-slate-200 shadow-xs space-y-4">
                    <h3 class="text-base font-black text-slate-900 tracking-tight flex items-center justify-between">
                        <span>{{ __('Offered Classes & Schedules') }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-brand-50 text-brand-700">
                            {{ $profile->classes->count() }} {{ __('Total') }}
                        </span>
                    </h3>

                    @if ($profile->classes->isEmpty())
                        <div class="py-8 text-center text-slate-400 text-xs space-y-1">
                            <p>{{ __('No active class groups assigned at this time.') }}</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach ($profile->classes as $class)
                                <div class="p-4 rounded-2xl border border-slate-200 hover:border-brand-300 transition-colors bg-slate-50/30 space-y-3">
                                    <div>
                                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-slate-200 text-slate-700">
                                            {{ $class->subject ?: __('General') }}
                                        </span>
                                        <h4 class="text-sm font-bold text-slate-900 mt-1">{{ $class->title }}</h4>
                                        @if ($class->description)
                                            <p class="text-xs text-slate-500 mt-0.5 line-clamp-2">{{ $class->description }}</p>
                                        @endif
                                    </div>

                                    <div class="grid grid-cols-2 gap-2 text-[11px] text-slate-600 border-t border-slate-100 pt-2 font-mono">
                                        <div>
                                            <span class="text-slate-400 block text-[9px] uppercase">{{ __('Fee') }}</span>
                                            <span class="font-bold text-slate-900">LKR {{ number_format((float) $class->fee_amount, 2) }}</span>
                                        </div>
                                        <div>
                                            <span class="text-slate-400 block text-[9px] uppercase">{{ __('Room / Mode') }}</span>
                                            <span class="font-bold text-slate-900">{{ $class->room ?: ($class->online_url ? __('Online') : __('Campus Hall')) }}</span>
                                        </div>
                                    </div>

                                    <!-- Apply Button (§5.3, §5.5) -->
                                    <div class="pt-2">
                                        <button type="button"
                                                wire:click="openApplyModal({{ $class->id }})"
                                                class="w-full py-2.5 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-500 shadow-sm shadow-brand-500/20 transition-all btn-interactive text-center cursor-pointer">
                                            {{ __('Apply for this Class') }} &rarr;
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Contact & Questions Box -->
                <div class="p-6 rounded-3xl bg-gradient-to-br from-brand-50 to-indigo-50/50 border border-brand-100 text-xs space-y-2">
                    <h4 class="font-bold text-brand-900">{{ __('Admissions Assistance') }}</h4>
                    <p class="text-brand-800 leading-relaxed">
                        {{ __('Have inquiries about enrollment prerequisites or course syllabus? Reach out to campus admissions for consultation.') }}
                    </p>
                    <a href="mailto:{{ $profile->user->email }}" class="font-mono text-brand-600 font-bold block hover:underline">
                        {{ $profile->user->email }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Apply Modal (§5.3, §5.5, wired for T-7.1) -->
    @if ($showApplyModal && $selectedClass)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center font-bold">
                        ✍️
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900">{{ __('Class Admission Request') }}</h3>
                        <p class="text-xs text-brand-600 font-semibold">{{ $selectedClass->title }}</p>
                    </div>
                </div>

                <p class="text-xs text-slate-500 leading-relaxed">
                    {{ __('Submit your details to request enrollment. The lecturer and administration will review your intake and issue your admission pass.') }}
                </p>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Student / Applicant Full Name') }} <span class="text-rose-500">*</span></label>
                        <input type="text"
                               wire:model="applicantName"
                               placeholder="{{ __('Enter full name') }}"
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
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Contact Phone (WhatsApp / SMS)') }} <span class="text-rose-500">*</span></label>
                        <input type="tel"
                               wire:model="applicantPhone"
                               placeholder="{{ __('+94770000000') }}"
                               class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500" />
                        @error('applicantPhone')
                            <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Additional Notes / Background (Optional)') }}</label>
                        <textarea wire:model="applicationNotes"
                                  rows="2"
                                  placeholder="{{ __('e.g., previous grade, specific questions...') }}"
                                  class="w-full text-xs rounded-xl border border-slate-200 p-2 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"></textarea>
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
