<div class="max-w-5xl mx-auto space-y-6">
    <!-- Breadcrumb & Top Bar -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-2 text-xs text-slate-500">
            <a href="{{ route('dashboard') }}" class="hover:text-slate-800 transition-colors">{{ __('Dashboard') }}</a>
            <span>/</span>
            <span class="text-slate-700 font-semibold truncate">{{ $assignment->title }}</span>
        </div>
        <a href="{{ url()->previous() }}" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 shadow-xs transition-colors inline-flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            {{ __('Back') }}
        </a>
    </div>

    <!-- Multi-child switcher for parents -->
    @if ($children->count() > 1)
        <div class="bg-white p-3 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-2 overflow-x-auto">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider px-2 shrink-0">{{ __('Select Child:') }}</span>
            <div class="flex items-center gap-2">
                @foreach ($children as $c)
                    <button 
                        type="button" 
                        wire:click="selectStudent({{ $c->id }})"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer {{ $selectedStudentId === $c->id ? 'bg-brand-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        {{ $c->full_name }}
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Feedback messages -->
    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-900 flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Assignment Main Card -->
    <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="p-6 sm:p-8 space-y-6">
            <!-- Header section -->
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 border-b border-slate-100 pb-6">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-semibold text-brand-700 bg-brand-50 border border-brand-200/60 px-2.5 py-0.5 rounded-full">
                            {{ $assignment->schoolClass?->title ?? __('Course Assignment') }}
                        </span>
                        <span class="text-xs font-bold px-2.5 py-0.5 rounded-full border {{ $status->badgeClasses() }}">
                            {{ $status->label() }}
                        </span>
                    </div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ $assignment->title }}</h1>
                    @if ($assignment->creator)
                        <p class="text-xs text-slate-500">
                            {{ __('Assigned by :name', ['name' => $assignment->creator->name]) }}
                        </p>
                    @endif
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    <div class="text-right">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Total Marks') }}</p>
                        <p class="text-xl font-black text-slate-800">{{ $assignment->total_marks }}</p>
                    </div>
                    @if ($assignment->pass_marks)
                        <div class="h-8 w-px bg-slate-200"></div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Pass Marks') }}</p>
                            <p class="text-xl font-black text-slate-600">{{ $assignment->pass_marks }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Description & Instructions -->
            @if ($assignment->description)
                <div class="text-sm text-slate-700 leading-relaxed font-normal bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
                    {{ $assignment->description }}
                </div>
            @endif

            <!-- Deadline Extension Callout Banner (§8) -->
            @if ($isExtended && $latestExtension)
                <div class="p-4 rounded-2xl bg-amber-50/80 border border-amber-200/80 flex items-start gap-3 shadow-xs">
                    <div class="p-2 rounded-xl bg-amber-100 text-amber-700 shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </div>
                    <div class="min-w-0 space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-black text-amber-900">{{ __('Deadline Extended') }}</span>
                            <span class="text-[11px] text-amber-700 font-semibold">
                                {{ __('New Deadline: :date', ['date' => $deadline->format('M d, Y • h:i A')]) }}
                            </span>
                        </div>
                        <p class="text-xs text-amber-800 font-medium">
                            <strong class="font-bold text-amber-900">{{ __('Reason:') }}</strong> {{ $latestExtension->reason }}
                        </p>
                        @if ($latestExtension->extendedBy)
                            <p class="text-[10px] text-amber-600">
                                {{ __('Granted by :teacher', ['teacher' => $latestExtension->extendedBy->name]) }}
                            </p>
                        @endif
                    </div>
                </div>
            @endif

            @if ($assignment->instructions)
                <div class="space-y-2">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('Step-by-Step Instructions') }}</h3>
                    <div class="text-xs text-slate-700 leading-relaxed whitespace-pre-line bg-white p-4 rounded-2xl border border-slate-200">
                        {{ $assignment->instructions }}
                    </div>
                </div>
            @endif

            <!-- Metadata Banner (Deadline, Allowed File Types, Max Size, Resubmissions) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 pt-2">
                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100">
                    <div class="flex items-center justify-between">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Deadline') }}</p>
                        @if ($isExtended)
                            <span class="px-1.5 py-0.2 rounded-md bg-amber-100 text-amber-800 font-bold text-[9px] uppercase tracking-wider">
                                {{ __('Extended') }}
                            </span>
                        @endif
                    </div>
                    <p class="text-xs font-bold {{ $isPastDeadline ? 'text-rose-600 font-black' : ($isExtended ? 'text-amber-800 font-black' : 'text-slate-800') }} mt-0.5">
                        {{ $deadline->format('M d, Y - h:i A') }}
                    </p>
                    <p class="text-[10px] text-slate-500 mt-0.5">
                        {{ $isPastDeadline ? ($assignment->allow_late ? __('Late submissions allowed') : __('Closed')) : $deadline->diffForHumans() }}
                    </p>
                </div>

                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Allowed File Types') }}</p>
                    <p class="text-xs font-bold text-slate-800 mt-0.5 truncate">
                        {{ $assignment->formattedAllowedFileTypes() }}
                    </p>
                    <p class="text-[10px] text-slate-500 mt-0.5">{{ __('Magic-byte verified') }}</p>
                </div>

                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Max Upload Size') }}</p>
                    <p class="text-xs font-bold text-slate-800 mt-0.5">
                        {{ $assignment->max_file_size_mb }} MB
                    </p>
                    <p class="text-[10px] text-slate-500 mt-0.5">{{ __('Server enforced (§10)') }}</p>
                </div>

                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Resubmission Limit') }}</p>
                    <p class="text-xs font-bold text-slate-800 mt-0.5">
                        @if ($assignment->resubmission_limit === 0)
                            {{ __('Unlimited') }}
                        @else
                            {{ $resubmissionCount }} / {{ $assignment->resubmission_limit }} {{ __('Used') }}
                        @endif
                    </p>
                    <p class="text-[10px] text-slate-500 mt-0.5">{{ __('Historical versions saved') }}</p>
                </div>
            </div>

            <!-- Teacher Attachments -->
            @if ($assignment->attachments->isNotEmpty())
                <div class="space-y-3 pt-2">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('Learning Resources & Attachments') }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach ($assignment->attachments as $att)
                            <div class="p-3 bg-white border border-slate-200 rounded-2xl flex items-center justify-between gap-3 shadow-xs hover:border-brand-200 transition-colors">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="p-2 rounded-xl {{ $att->isLink() ? 'bg-indigo-50 text-indigo-600' : 'bg-brand-50 text-brand-600' }} shrink-0">
                                        @if ($att->isLink())
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-slate-800 truncate">{{ $att->title }}</p>
                                        <p class="text-[10px] text-slate-400">
                                            {{ $att->isLink() ? __('External Web Link') : ($att->formattedSize() ?: __('Attachment Document')) }}
                                        </p>
                                    </div>
                                </div>
                                <a 
                                    href="{{ $att->downloadUrl() }}" 
                                    target="{{ $att->isLink() ? '_blank' : '_self' }}"
                                    class="px-2.5 py-1.5 rounded-xl text-xs font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 transition-colors shrink-0 inline-flex items-center gap-1">
                                    <span>{{ $att->isLink() ? __('Open') : __('Download') }}</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Student Submission Section -->
    <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="p-6 sm:p-8 space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">{{ __('Your Submission') }}</h2>
                    @if ($student)
                        <span class="text-xs text-slate-400">({{ $student->full_name }})</span>
                    @endif
                </div>

                @if ($history->isNotEmpty())
                    <button 
                        type="button" 
                        wire:click="$set('showHistoryModal', true)"
                        class="text-xs font-bold text-brand-600 hover:text-brand-700 inline-flex items-center gap-1 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ __('Version History (:count)', ['count' => $history->count()]) }}
                    </button>
                @endif
            </div>

            <!-- Error Banner -->
            @if ($submissionError)
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs font-semibold text-rose-800 flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $submissionError }}</span>
                    </div>
                </div>
            @endif

            @if (! $submission)
                <!-- No submission yet: Upload box -->
                @if ($canSubmit)
                    <form wire:submit="submitWork" class="space-y-4">
                        <div class="border-2 border-dashed border-slate-200 rounded-3xl p-6 sm:p-8 text-center hover:border-brand-400 transition-colors bg-slate-50/50">
                            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            <div class="mt-4 flex flex-col items-center text-xs text-slate-600">
                                <label for="submissionFile" class="relative cursor-pointer rounded-xl font-bold text-brand-600 hover:text-brand-500">
                                    <span>{{ __('Choose coursework file to upload') }}</span>
                                    <input id="submissionFile" wire:model="submissionFile" type="file" class="sr-only">
                                </label>
                                <p class="text-[11px] text-slate-400 mt-1">
                                    {{ __('Allowed types: :types (up to :max MB)', ['types' => $assignment->formattedAllowedFileTypes(), 'max' => $assignment->max_file_size_mb]) }}
                                </p>
                            </div>

                            @if ($submissionFile)
                                <div class="mt-4 p-3 bg-white rounded-xl border border-brand-200 text-xs font-semibold text-brand-800 inline-flex items-center gap-2">
                                    <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>{{ $submissionFile->getClientOriginalName() }}</span>
                                </div>
                            @endif

                            @error('submissionFile')
                                <p class="text-xs text-rose-600 font-semibold mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Optional Note -->
                        <div class="space-y-1">
                            <label for="submissionNote" class="text-xs font-bold text-slate-600 uppercase tracking-wider">{{ __('Student / Parent Notes (Optional)') }}</label>
                            <textarea 
                                id="submissionNote" 
                                wire:model="submissionNote" 
                                rows="3" 
                                placeholder="{{ __('Add any notes, reflections, or context for the lecturer...') }}"
                                class="w-full text-xs rounded-2xl border-slate-200 focus:border-brand-500 focus:ring-brand-500 shadow-xs"></textarea>
                            @error('submissionNote')
                                <p class="text-xs text-rose-600 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-500 active:scale-[0.98] shadow-xs transition-all cursor-pointer inline-flex items-center gap-2">
                                <span wire:loading.remove>{{ __('Submit Coursework') }}</span>
                                <span wire:loading>{{ __('Uploading & Verifying...') }}</span>
                            </button>
                        </div>
                    </form>
                @else
                    <div class="p-8 text-center bg-slate-50 rounded-2xl border border-slate-100 text-slate-500 text-xs">
                        <p class="font-bold text-slate-700">{{ __('Submissions are currently closed for this assignment.') }}</p>
                        <p class="mt-1">{{ __('The deadline has passed and late submissions are disabled, or the assignment is closed.') }}</p>
                    </div>
                @endif
            @else
                <!-- Active Submission Card -->
                <div class="p-5 rounded-2xl border {{ $submission->isGraded() ? 'bg-brand-50/20 border-brand-200' : ($submission->isReturned() ? 'bg-amber-50/30 border-amber-200' : 'bg-slate-50/60 border-slate-200') }} space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-200/60 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold px-2.5 py-0.5 rounded-full border {{ $submission->status->badgeClasses() }}">
                                {{ $submission->status->label() }}
                            </span>
                            <span class="text-xs font-semibold text-slate-500">
                                {{ __('Version :ver', ['ver' => $submission->version]) }}
                            </span>
                            @if ($submission->is_late)
                                <span class="text-[10px] font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full">
                                    {{ __('Submitted Late') }}
                                </span>
                            @endif
                        </div>

                        <span class="text-xs text-slate-400">
                            {{ __('Submitted :time', ['time' => $submission->submitted_at->diffForHumans()]) }}
                        </span>
                    </div>

                    <!-- File Details -->
                    @if ($submission->fileAsset)
                        <div class="flex items-center justify-between p-3.5 bg-white rounded-xl border border-slate-200 shadow-xs">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="p-2.5 rounded-xl bg-brand-50 text-brand-600 shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-800 truncate">{{ $submission->fileOriginalName() }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $submission->formattedFileSize() }}</p>
                                </div>
                            </div>
                            <a 
                                href="{{ $submission->fileDownloadUrl() }}" 
                                class="px-3 py-1.5 rounded-xl text-xs font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 transition-colors inline-flex items-center gap-1.5 shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>{{ __('Download File') }}</span>
                            </a>
                        </div>
                    @endif

                    <!-- Note -->
                    @if ($submission->note)
                        <div class="space-y-1">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Your Note') }}</p>
                            <p class="text-xs text-slate-700 bg-white p-3 rounded-xl border border-slate-100 whitespace-pre-line">{{ $submission->note }}</p>
                        </div>
                    @endif

                    <!-- Teacher Returned Notice (§8, T-5.5) -->
                    @if ($submission->isReturned())
                        <div class="p-5 bg-gradient-to-r from-orange-50 to-amber-50 border border-orange-200 rounded-2xl space-y-3 text-xs">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <div class="flex items-center gap-2 text-orange-900 font-black text-sm">
                                    <span class="p-1.5 rounded-xl bg-orange-100 text-orange-700">⚠️</span>
                                    <span>{{ __('Returned for Revision by Lecturer') }}</span>
                                </div>
                                @if ($submission->reviewer)
                                    <span class="text-[11px] text-orange-700">
                                        {{ __('Returned by :name • :date', ['name' => $submission->reviewer->name, 'date' => format_portal_datetime($submission->reviewed_at, 'M d, Y')]) }}
                                    </span>
                                @endif
                            </div>

                            @if ($submission->feedback)
                                <div class="bg-white/90 p-3.5 rounded-xl border border-orange-200/80 space-y-1">
                                    <p class="text-[10px] font-bold text-orange-800 uppercase tracking-wider">{{ __('Lecturer Remarks & Required Corrections:') }}</p>
                                    <p class="text-xs text-orange-950 font-medium whitespace-pre-line leading-relaxed">{{ $submission->feedback }}</p>
                                </div>
                            @endif

                            <div class="flex items-center justify-between flex-wrap gap-3 pt-1">
                                <p class="text-[11px] text-orange-800">
                                    {{ __('Please address the remarks above and upload your corrected coursework.') }}
                                </p>
                                @if ($submission->canBeModified())
                                    <button 
                                        type="button" 
                                        wire:click="openReplaceModal"
                                        class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-orange-600 hover:bg-orange-500 shadow-xs cursor-pointer inline-flex items-center gap-1.5 btn-interactive">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
                                        {{ __('Upload Corrected Version (v:next)', ['next' => $submission->version + 1]) }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Graded Marks & Official Results Card (§8, T-5.5) -->
                    @if ($submission->isGraded())
                        <div class="p-6 bg-gradient-to-br from-emerald-50/80 via-white to-brand-50/40 border border-emerald-200 rounded-3xl space-y-4 shadow-xs">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-emerald-100 pb-4">
                                <div class="space-y-0.5">
                                    <div class="flex items-center gap-2">
                                        <span class="p-1.5 rounded-xl bg-emerald-100 text-emerald-800 text-xs font-black">🎓</span>
                                        <h3 class="text-sm font-black text-emerald-950 uppercase tracking-wider">{{ __('Official Grading & Marks Result') }}</h3>
                                    </div>
                                    @if ($submission->reviewer)
                                        <p class="text-[11px] text-slate-500">
                                            {{ __('Graded by :reviewer • :date', ['reviewer' => $submission->reviewer->name, 'date' => format_portal_datetime($submission->reviewed_at, 'M d, Y • h:i A')]) }}
                                        </p>
                                    @endif
                                </div>

                                <!-- Score and Badge -->
                                <div class="flex items-center gap-3">
                                    @php
                                        $percentage = $assignment->total_marks > 0 ? (int) round(($submission->marks / $assignment->total_marks) * 100) : 0;
                                        $passed = $assignment->pass_marks === null || $submission->marks >= $assignment->pass_marks;
                                    @endphp
                                    @if ($assignment->pass_marks !== null)
                                        <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider border {{ $passed ? 'bg-emerald-100 text-emerald-900 border-emerald-300' : 'bg-rose-100 text-rose-900 border-rose-300' }}">
                                            {{ $passed ? __('Passed') : __('Did Not Pass') }}
                                        </span>
                                    @endif
                                    <div class="text-right">
                                        <span class="text-2xl font-black text-slate-900">{{ $submission->marks }}</span>
                                        <span class="text-xs text-slate-400 font-semibold">/ {{ $assignment->total_marks }}</span>
                                        <span class="text-xs font-bold text-emerald-700 block">({{ $percentage }}%)</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Written Feedback Callout -->
                            @if ($submission->feedback)
                                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs space-y-1.5">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/></svg>
                                        {{ __('Lecturer Remarks & Feedback') }}
                                    </p>
                                    <p class="text-xs text-slate-800 whitespace-pre-line leading-relaxed font-normal">{{ $submission->feedback }}</p>
                                </div>
                            @endif

                            <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                                    {{ __('Graded submission is finalized and locked against further edits.') }}
                                </span>
                                <span>{{ __('Version :ver', ['ver' => $submission->version]) }}</span>
                            </div>
                        </div>
                    @endif

                    <!-- Action buttons (when unlocked) -->
                    @if ($submission->canBeModified())
                        <div class="flex flex-wrap items-center justify-end gap-2 pt-2 border-t border-slate-200/60">
                            <button 
                                type="button" 
                                wire:click="openEditNoteModal"
                                class="px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors inline-flex items-center gap-1 cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                {{ __('Edit Note') }}
                            </button>

                            <button 
                                type="button" 
                                wire:click="openReplaceModal"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-bold text-brand-700 bg-brand-50 border border-brand-200/60 hover:bg-brand-100 transition-colors inline-flex items-center gap-1 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                {{ __('Replace Submission') }}
                            </button>

                            <button 
                                type="button" 
                                wire:click="confirmDelete"
                                class="px-3 py-1.5 rounded-xl text-xs font-semibold text-rose-600 bg-rose-50 border border-rose-200 hover:bg-rose-100 transition-colors inline-flex items-center gap-1 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                {{ __('Delete') }}
                            </button>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Replace Submission Modal -->
    @if ($showReplaceModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-xl border border-slate-200 animate-fade-in">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-black text-slate-900">{{ __('Replace Submission') }}</h3>
                    <button type="button" wire:click="$set('showReplaceModal', false)" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit="replaceWork" class="space-y-4">
                    <p class="text-xs text-slate-500">
                        {{ __('Uploading a replacement file will create a new version and preserve the previous one in your version history.') }}
                    </p>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700">{{ __('Select New Coursework File') }} *</label>
                        <input type="file" wire:model="replacementFile" class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 cursor-pointer">
                        <p class="text-[10px] text-slate-400">{{ __('Allowed types: :types (up to :max MB)', ['types' => $assignment->formattedAllowedFileTypes(), 'max' => $assignment->max_file_size_mb]) }}</p>
                        @error('replacementFile') <p class="text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700">{{ __('Revision Note') }}</label>
                        <textarea wire:model="replacementNote" rows="3" class="w-full text-xs rounded-2xl border-slate-200 focus:border-brand-500 focus:ring-brand-500 shadow-xs" placeholder="{{ __('Explain changes made in this version...') }}"></textarea>
                        @error('replacementNote') <p class="text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showReplaceModal', false)" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-500 shadow-xs transition-all cursor-pointer">
                            <span wire:loading.remove>{{ __('Upload Replacement') }}</span>
                            <span wire:loading>{{ __('Processing...') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Edit Note Modal -->
    @if ($showEditNoteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-xl border border-slate-200 animate-fade-in">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-black text-slate-900">{{ __('Edit Submission Note') }}</h3>
                    <button type="button" wire:click="$set('showEditNoteModal', false)" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit="saveNote" class="space-y-4">
                    <div class="space-y-1">
                        <textarea wire:model="editingNote" rows="4" class="w-full text-xs rounded-2xl border-slate-200 focus:border-brand-500 focus:ring-brand-500 shadow-xs" placeholder="{{ __('Add or edit your note...') }}"></textarea>
                        @error('editingNote') <p class="text-xs text-rose-600 font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showEditNoteModal', false)" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-500 shadow-xs transition-all cursor-pointer">
                            {{ __('Save Changes') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-xl border border-slate-200 animate-fade-in">
                <div class="flex items-center gap-3 text-rose-600">
                    <div class="p-2.5 rounded-2xl bg-rose-50 shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900">{{ __('Delete Submission?') }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">{{ __('Are you sure you want to remove your submission?') }}</p>
                    </div>
                </div>

                <p class="text-xs text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-100">
                    {{ __('Your current active submission file will be removed. You may upload a fresh submission before the deadline.') }}
                </p>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" wire:click="$set('showDeleteModal', false)" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors">
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" wire:click="deleteSubmission" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 shadow-xs transition-all cursor-pointer">
                        {{ __('Confirm Delete') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Version History Modal -->
    @if ($showHistoryModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-xl w-full p-6 space-y-4 shadow-xl border border-slate-200 animate-fade-in">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-black text-slate-900">{{ __('Submission Version History') }}</h3>
                    <button type="button" wire:click="$set('showHistoryModal', false)" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                    @forelse ($history as $h)
                        <div class="p-3.5 rounded-2xl border {{ $h->trashed() ? 'bg-slate-50/50 border-slate-200 opacity-60' : 'bg-white border-slate-200 shadow-xs' }} space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="font-black text-slate-800">{{ __('Version :ver', ['ver' => $h->version]) }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $h->status->badgeClasses() }}">
                                        {{ $h->status->label() }}
                                    </span>
                                    @if ($h->trashed())
                                        <span class="text-[10px] text-slate-400 font-semibold italic">({{ __('Deleted') }})</span>
                                    @endif
                                </div>
                                <span class="text-[11px] text-slate-400">{{ $h->submitted_at->format('M d, Y - h:i A') }}</span>
                            </div>

                            @if ($h->fileAsset)
                                <div class="flex items-center justify-between text-xs pt-1">
                                    <span class="text-slate-700 truncate max-w-xs font-medium">{{ $h->fileOriginalName() }} ({{ $h->formattedFileSize() }})</span>
                                    <a href="{{ $h->fileDownloadUrl() }}" class="text-brand-600 hover:text-brand-700 font-bold inline-flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        {{ __('Download') }}
                                    </a>
                                </div>
                            @endif

                            @if ($h->note)
                                <p class="text-[11px] text-slate-500 bg-slate-50 p-2 rounded-xl border border-slate-100">
                                    <strong class="font-bold text-slate-700">{{ __('Note:') }}</strong> {{ $h->note }}
                                </p>
                            @endif

                            @if ($h->feedback)
                                <div class="text-[11px] text-slate-700 bg-slate-50/80 p-2.5 rounded-xl border border-slate-200 space-y-1">
                                    <span class="font-bold {{ $h->isReturned() ? 'text-orange-800' : 'text-emerald-800' }}">
                                        {{ $h->isReturned() ? __('Returned Reason:') : __('Lecturer Remarks:') }}
                                    </span>
                                    <p class="whitespace-pre-line">{{ $h->feedback }}</p>
                                    @if ($h->marks !== null)
                                        <p class="font-bold text-slate-900 pt-0.5">{{ __('Marks Awarded:') }} {{ $h->marks }} / {{ $assignment->total_marks }}</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-4">{{ __('No versions recorded yet.') }}</p>
                    @endforelse
                </div>

                <div class="flex justify-end pt-2">
                    <button type="button" wire:click="$set('showHistoryModal', false)" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors">
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
