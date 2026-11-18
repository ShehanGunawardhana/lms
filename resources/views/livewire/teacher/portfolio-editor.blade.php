<div>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Lecturer Portfolio Editor') }}</h1>
                    @php
                        $badgeClasses = match ($portfolioStatus) {
                            'published' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'pending' => 'bg-brand-100 text-brand-800 border-brand-200',
                            'rejected' => 'bg-rose-100 text-rose-800 border-rose-200',
                            default => 'bg-amber-100 text-amber-800 border-amber-200',
                        };
                        $statusText = match ($portfolioStatus) {
                            'published' => __('Live & Published (§5.5)'),
                            'pending' => __('Under Admin Review'),
                            'rejected' => __('Revision Requested'),
                            default => __('Draft Mode'),
                        };
                    @endphp
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $badgeClasses }}">
                        {{ $statusText }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">{{ __('Curate your public faculty biography, qualifications, subjects, and sample materials (§5.5)') }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if ($portfolioStatus === 'pending')
                    <button type="button"
                            wire:click="recallToDraft"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-xs cursor-pointer">
                        {{ __('Recall to Draft') }}
                    </button>
                @endif
                <button type="button"
                        wire:click="saveDraft"
                        wire:loading.attr="disabled"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-xs cursor-pointer">
                    <span wire:loading.remove wire:target="saveDraft">{{ __('Save Draft') }}</span>
                    <span wire:loading wire:target="saveDraft">{{ __('Saving...') }}</span>
                </button>
                <button type="button"
                        wire:click="submitForApproval"
                        wire:loading.attr="disabled"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-500 shadow-sm shadow-brand-500/25 transition-all btn-interactive cursor-pointer">
                    <span wire:loading.remove wire:target="submitForApproval">{{ __('Submit for Review') }}</span>
                    <span wire:loading wire:target="submitForApproval">{{ __('Submitting...') }}</span>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Session Alerts -->
        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2 shadow-xs">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Status Informational Banner -->
        @if ($portfolioStatus === 'draft')
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-start gap-3 shadow-xs">
                <span class="text-base">📝</span>
                <div>
                    <h4 class="font-bold">{{ __('Draft Mode') }}</h4>
                    <p class="text-amber-800 mt-0.5 leading-relaxed">{{ __('Your portfolio is currently private and not visible to prospective students. Complete your profile details and submit for administrative review when ready.') }}</p>
                </div>
            </div>
        @elseif ($portfolioStatus === 'pending')
            <div class="p-4 rounded-2xl bg-brand-50 border border-brand-200 text-brand-900 text-xs flex items-start gap-3 shadow-xs">
                <span class="text-base">⏳</span>
                <div>
                    <h4 class="font-bold">{{ __('Under Administrative Review') }}</h4>
                    <p class="text-brand-800 mt-0.5 leading-relaxed">{{ __('Your portfolio has been submitted to campus administration. Once approved, it will be published to the public lecturer directory.') }}</p>
                </div>
            </div>
        @elseif ($portfolioStatus === 'published')
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs flex items-start gap-3 shadow-xs">
                <span class="text-base">🌟</span>
                <div>
                    <h4 class="font-bold">{{ __('Live & Published') }}</h4>
                    <p class="text-emerald-800 mt-0.5 leading-relaxed">{{ __('Your portfolio is live on the public website. Prospective students and parents can view your qualifications, subjects, and sample materials.') }}</p>
                </div>
            </div>
        @endif

        <!-- Section 1: Photo & Core Headline -->
        <x-card :title="__('Faculty Identity & Photo')">
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-6 items-center">
                <!-- Photo Upload & Preview -->
                <div class="sm:col-span-4 flex flex-col items-center text-center space-y-3">
                    <div class="w-28 h-28 rounded-3xl bg-slate-100 border-2 border-dashed border-slate-300 flex items-center justify-center overflow-hidden relative shadow-inner">
                        @if ($photo)
                            <img src="{{ $photo->temporaryUrl() }}" class="w-full h-full object-cover" alt="Preview" />
                        @elseif ($existingPhotoUrl)
                            <img src="{{ $existingPhotoUrl }}" class="w-full h-full object-cover" alt="{{ auth()->user()->name }}" />
                        @else
                            <div class="text-3xl text-slate-400">👨‍🏫</div>
                        @endif

                        <div wire:loading wire:target="photo" class="absolute inset-0 bg-slate-900/60 flex items-center justify-center text-white text-[10px] font-bold">
                            {{ __('Processing...') }}
                        </div>
                    </div>

                    <div>
                        <label class="cursor-pointer px-3 py-1.5 rounded-xl text-xs font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 border border-brand-200 inline-block transition-colors">
                            <span>{{ __('Upload Photo') }}</span>
                            <input type="file" wire:model="photo" accept="image/*" class="hidden" />
                        </label>
                        <p class="text-[10px] text-slate-400 mt-1">{{ __('JPG, PNG or WebP up to 10MB') }}</p>
                        @error('photo')
                            <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Headline -->
                <div class="sm:col-span-8 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Professional Headline') }} <span class="text-rose-500">*</span></label>
                        <input type="text"
                               wire:model="headline"
                               placeholder="{{ __('e.g., Senior Lecturer in Pure Mathematics & Statistics') }}"
                               class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-xs" />
                        @error('headline')
                            <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-[10px] text-slate-400 mt-1">{{ __('A concise tagline summarizing your academic focus.') }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Faculty Member Name') }}</label>
                        <input type="text"
                               value="{{ auth()->user()->name }}"
                               disabled
                               class="w-full text-xs rounded-xl border border-slate-200 p-2.5 bg-slate-50 text-slate-500" />
                    </div>
                </div>
            </div>
        </x-card>

        <!-- Section 2: Biography & Qualifications -->
        <x-card :title="__('Academic Biography & Credentials')">
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Biography / About Me') }} <span class="text-rose-500">*</span></label>
                    <textarea wire:model="bio"
                              rows="5"
                              placeholder="{{ __('Share your teaching philosophy, background, experience, and academic passion with prospective students...') }}"
                              class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-xs"></textarea>
                    @error('bio')
                        <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Academic Qualifications & Honors') }} <span class="text-rose-500">*</span></label>
                    <textarea wire:model="qualifications"
                              rows="3"
                              placeholder="{{ __('e.g., B.Sc. (Hons) in Mathematics (First Class), M.Sc. in Applied Statistics, 12+ years tertiary teaching experience...') }}"
                              class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-xs"></textarea>
                    @error('qualifications')
                        <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-card>

        <!-- Section 3: Teaching Subjects -->
        <x-card :title="__('Subjects & Curriculum Specializations')">
            <div class="space-y-3">
                <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('Subjects Taught (Comma Separated)') }} <span class="text-rose-500">*</span></label>
                <input type="text"
                       wire:model.live.debounce.300ms="subjectsInput"
                       placeholder="{{ __('e.g., Pure Mathematics, Applied Calculus, Linear Algebra, Probability') }}"
                       class="w-full text-xs rounded-xl border border-slate-200 p-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-xs" />
                @error('subjectsInput')
                    <p class="text-[11px] text-rose-600 font-bold mt-1">{{ $message }}</p>
                @enderror

                <!-- Live Tag Preview -->
                @php
                    $previewTags = array_values(array_filter(array_map('trim', explode(',', $subjectsInput))));
                @endphp
                @if (!empty($previewTags))
                    <div class="pt-2">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">{{ __('Tags Preview:') }}</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($previewTags as $tag)
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200">
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </x-card>

        <!-- Section 4: Sample Materials (§5.5) -->
        <x-card :title="__('Sample Learning Materials & Lecture Notes (§5.5)')">
            <div class="space-y-4">
                <p class="text-xs text-slate-500 leading-relaxed">
                    {{ __('Provide prospective students with samples of your lecture handouts, tutorial videos, or problem sets to preview before applying.') }}
                </p>

                @if (empty($sampleMaterials))
                    <div class="py-6 text-center border-2 border-dashed border-slate-200 rounded-2xl space-y-2">
                        <span class="text-2xl text-slate-400">📚</span>
                        <p class="text-xs font-medium text-slate-500">{{ __('No sample materials attached yet.') }}</p>
                        <button type="button"
                                wire:click="addSampleMaterial"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 border border-brand-200 inline-flex items-center gap-1 transition-colors cursor-pointer">
                            + {{ __('Add First Sample Resource') }}
                        </button>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($sampleMaterials as $index => $material)
                            <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/50 space-y-3 relative group">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-800">#{{ $index + 1 }} {{ __('Sample Material') }}</span>
                                    <button type="button"
                                            wire:click="removeSampleMaterial({{ $index }})"
                                            class="text-xs text-rose-600 hover:text-rose-800 font-semibold cursor-pointer">
                                        {{ __('Remove') }}
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                                    <div class="sm:col-span-8">
                                        <label class="block text-[11px] font-bold text-slate-600 mb-0.5">{{ __('Resource Title') }}</label>
                                        <input type="text"
                                               wire:model="sampleMaterials.{{ $index }}.title"
                                               placeholder="{{ __('e.g., Intro to Integration Notes & Formulas') }}"
                                               class="w-full text-xs rounded-xl border border-slate-200 p-2 bg-white" />
                                    </div>

                                    <div class="sm:col-span-4">
                                        <label class="block text-[11px] font-bold text-slate-600 mb-0.5">{{ __('Type') }}</label>
                                        <select wire:model="sampleMaterials.{{ $index }}.type" class="w-full text-xs rounded-xl border border-slate-200 p-2 bg-white">
                                            <option value="document">{{ __('PDF / Document') }}</option>
                                            <option value="video">{{ __('Video / Lecture') }}</option>
                                            <option value="link">{{ __('External Link') }}</option>
                                        </select>
                                    </div>

                                    <div class="sm:col-span-12">
                                        <label class="block text-[11px] font-bold text-slate-600 mb-0.5">{{ __('Resource URL / Direct Link') }}</label>
                                        <input type="url"
                                               wire:model="sampleMaterials.{{ $index }}.url"
                                               placeholder="{{ __('https://...') }}"
                                               class="w-full text-xs rounded-xl border border-slate-200 p-2 bg-white" />
                                    </div>

                                    <div class="sm:col-span-12">
                                        <label class="block text-[11px] font-bold text-slate-600 mb-0.5">{{ __('Brief Description') }}</label>
                                        <input type="text"
                                               wire:model="sampleMaterials.{{ $index }}.description"
                                               placeholder="{{ __('Short summary of what this sample covers...') }}"
                                               class="w-full text-xs rounded-xl border border-slate-200 p-2 bg-white" />
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <button type="button"
                                wire:click="addSampleMaterial"
                                class="px-3.5 py-2 rounded-xl text-xs font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 border border-brand-200 inline-flex items-center gap-1 transition-colors cursor-pointer">
                            + {{ __('Add Another Sample Resource') }}
                        </button>
                    </div>
                @endif
            </div>
        </x-card>

        <!-- Bottom Action Bar -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
            <button type="button"
                    wire:click="saveDraft"
                    wire:loading.attr="disabled"
                    class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-xs cursor-pointer">
                {{ __('Save Draft') }}
            </button>
            <button type="button"
                    wire:click="submitForApproval"
                    wire:loading.attr="disabled"
                    class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-500 shadow-md shadow-brand-500/25 transition-all btn-interactive cursor-pointer">
                {{ __('Submit for Review') }} &rarr;
            </button>
        </div>
    </div>
</div>
