<div class="space-y-6">
    <!-- Top Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('teacher.activities') }}" class="hover:text-brand-600 transition-colors">
                    ← {{ __('Back to Nursery Activities') }}
                </a>
                <span>/</span>
                <span class="text-slate-700 dark:text-slate-300">{{ $activity->schoolClass?->title ?? __('Nursery Class') }}</span>
                <span>/</span>
                <span class="text-brand-600 font-bold">{{ __('Review Submissions') }}</span>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                    {{ $activity->title }}
                </h1>
                <span class="px-2.5 py-1 rounded-full text-xs font-extrabold uppercase tracking-wide {{ $activity->status->badgeClasses() }}">
                    {{ $activity->status->label() }}
                </span>
                @if ($activity->age_group)
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        👶 {{ $activity->age_group }}
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-1 flex flex-wrap items-center gap-2">
                <span>🗓️ {{ __('Deadline:') }} {{ $activity->deadline ? $activity->deadline->format('M d, Y h:i A') : __('No deadline set') }}</span>
                @if ($activity->allow_late)
                    <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 text-[10px] font-bold border border-amber-200">
                        {{ __('Late Submissions Allowed') }}
                    </span>
                @endif
            </p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <a
                href="{{ route('teacher.activities') }}"
                class="px-4 py-2 text-xs font-bold rounded-xl text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 shadow-xs transition"
            >
                {{ __('Manage Activities') }}
            </a>
        </div>
    </div>

    <!-- Flash Notifications -->
    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-bold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <span class="text-lg">🎉</span>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 text-sm">✕</button>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-bold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <span class="text-lg">⚠️</span>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-rose-700 hover:text-rose-900 text-sm">✕</button>
        </div>
    @endif

    <!-- Metrics Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
        <!-- Total Enrolled Card -->
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('Class Roster') }}</span>
                <span class="text-base">🎒</span>
            </div>
            <div class="text-2xl font-black text-slate-900 mt-2">{{ $totalEnrolled }}</div>
            <div class="mt-2 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                <div class="bg-brand-600 h-1.5 rounded-full transition-all duration-300" style="width: {{ $submissionPercentage }}%"></div>
            </div>
            <div class="text-[10px] text-slate-400 mt-1 font-semibold">{{ __(':pct% submissions received', ['pct' => $submissionPercentage]) }}</div>
        </div>

        <!-- Pending Review Card -->
        <div class="p-4 rounded-2xl bg-sky-50/60 border border-sky-100 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-sky-800 uppercase tracking-wider">{{ __('Needs Review') }}</span>
                <span class="text-base">⏳</span>
            </div>
            <div class="text-2xl font-black text-sky-900 mt-2">{{ $pendingCount }}</div>
            <p class="text-[10px] text-sky-700 mt-1 font-medium">{{ __('Submitted work awaiting feedback') }}</p>
        </div>

        <!-- Reviewed Card -->
        <div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-100 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider">{{ __('Reviewed') }}</span>
                <span class="text-base">🌟</span>
            </div>
            <div class="text-2xl font-black text-emerald-900 mt-2">{{ $reviewedCount }}</div>
            <p class="text-[10px] text-emerald-700 mt-1 font-medium">{{ __('Graded with sticker feedback') }}</p>
        </div>

        <!-- Returned Card -->
        <div class="p-4 rounded-2xl bg-rose-50/60 border border-rose-100 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-rose-800 uppercase tracking-wider">{{ __('Returned') }}</span>
                <span class="text-base">🔄</span>
            </div>
            <div class="text-2xl font-black text-rose-900 mt-2">{{ $returnedCount }}</div>
            <p class="text-[10px] text-rose-700 mt-1 font-medium">{{ __('Awaiting revision from parent') }}</p>
        </div>

        <!-- Missing / Not Submitted Card -->
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 shadow-xs col-span-2 sm:col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('Not Submitted') }}</span>
                <span class="text-base">⏳</span>
            </div>
            <div class="text-2xl font-black text-slate-700 mt-2">{{ $missingCount }}</div>
            <p class="text-[10px] text-slate-500 mt-1 font-medium">{{ __('Yet to upload activity work') }}</p>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-200/80 shadow-xs space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Filter Tabs -->
            <div class="flex flex-wrap items-center gap-1.5 p-1 bg-slate-100/80 rounded-2xl">
                <button
                    type="button"
                    wire:click="$set('statusFilter', 'all')"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ $statusFilter === 'all' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}"
                >
                    {{ __('All Students') }} ({{ $totalEnrolled }})
                </button>
                <button
                    type="button"
                    wire:click="$set('statusFilter', 'pending')"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $statusFilter === 'pending' ? 'bg-sky-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}"
                >
                    <span>{{ __('Pending Review') }}</span>
                    @if ($pendingCount > 0)
                        <span class="px-1.5 py-0.2 text-[10px] rounded-full {{ $statusFilter === 'pending' ? 'bg-white/20 text-white' : 'bg-sky-100 text-sky-800' }} font-bold">
                            {{ $pendingCount }}
                        </span>
                    @endif
                </button>
                <button
                    type="button"
                    wire:click="$set('statusFilter', 'reviewed')"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $statusFilter === 'reviewed' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}"
                >
                    <span>{{ __('Reviewed') }}</span>
                    @if ($reviewedCount > 0)
                        <span class="px-1.5 py-0.2 text-[10px] rounded-full {{ $statusFilter === 'reviewed' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' }} font-bold">
                            {{ $reviewedCount }}
                        </span>
                    @endif
                </button>
                <button
                    type="button"
                    wire:click="$set('statusFilter', 'returned')"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $statusFilter === 'returned' ? 'bg-rose-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}"
                >
                    <span>{{ __('Returned') }}</span>
                    @if ($returnedCount > 0)
                        <span class="px-1.5 py-0.2 text-[10px] rounded-full {{ $statusFilter === 'returned' ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-800' }} font-bold">
                            {{ $returnedCount }}
                        </span>
                    @endif
                </button>
                <button
                    type="button"
                    wire:click="$set('statusFilter', 'missing')"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all {{ $statusFilter === 'missing' ? 'bg-slate-700 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}"
                >
                    {{ __('Not Submitted') }} ({{ $missingCount }})
                </button>
            </div>

            <!-- Search Field -->
            <div class="relative w-full md:w-72">
                <input
                    type="text"
                    wire:model.live.debounce.250ms="search"
                    placeholder="{{ __('Search student by name or code...') }}"
                    class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-medium pl-8"
                />
                <span class="absolute left-2.5 top-2.5 text-slate-400 text-xs">🔍</span>
            </div>
        </div>

        <!-- Student Roster Listing -->
        <div class="divide-y divide-slate-100">
            @forelse ($roster as $item)
                @php
                    $student = $item['student'];
                    $sub = $item['submission'];
                    $statusKey = $item['status_key'];
                    $versionsCount = $item['versions_count'];
                @endphp
                <div class="py-4 first:pt-2 last:pb-2 flex flex-col md:flex-row md:items-center justify-between gap-4 transition hover:bg-slate-50/60 rounded-2xl px-3">
                    <!-- Left: Student Info & Submission Details -->
                    <div class="flex items-start gap-3.5 flex-1 min-w-0">
                        <!-- Student Avatar -->
                        <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-indigo-100 to-brand-100 text-brand-700 font-extrabold flex items-center justify-center text-sm shadow-inner shrink-0">
                            {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-sm font-extrabold text-slate-900 truncate">
                                    {{ $student->full_name }}
                                </h3>
                                <span class="font-mono text-[11px] font-semibold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-md">
                                    {{ $student->student_code }}
                                </span>

                                <!-- Status Badge -->
                                @if ($sub !== null)
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $sub->status->badgeClasses() }}">
                                        {{ $sub->status->label() }}
                                    </span>
                                    @if ($sub->version > 1)
                                        <span class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 text-[10px] font-mono font-bold border border-purple-200">
                                            v{{ $sub->version }}
                                        </span>
                                    @endif
                                    @if ($sub->is_late)
                                        <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 text-[10px] font-bold">
                                            ⚠️ {{ __('Late') }}
                                        </span>
                                    @endif
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                        ⏳ {{ __('Not Submitted') }}
                                    </span>
                                @endif
                            </div>

                            <!-- Guardian info & submission timestamp -->
                            <div class="flex flex-wrap items-center gap-3 text-[11px] text-slate-500 mt-1">
                                @if ($student->guardians->isNotEmpty())
                                    <span>👨‍👩‍👧 {{ __('Guardian:') }} <strong class="text-slate-700">{{ $student->guardians->first()->full_name }}</strong></span>
                                    @if ($student->guardians->first()->phone)
                                        <span>•</span>
                                        <span>📞 {{ $student->guardians->first()->phone }}</span>
                                    @endif
                                @endif

                                @if ($sub !== null)
                                    <span>•</span>
                                    <span>🕒 {{ __('Submitted') }} {{ $sub->submitted_at->diffForHumans() }} ({{ $sub->submitted_at->format('M d, h:i A') }})</span>
                                @endif
                            </div>

                            <!-- Parent Note -->
                            @if ($sub && $sub->note)
                                <div class="mt-2 p-2.5 rounded-xl bg-slate-50 text-slate-700 text-xs border border-slate-100 max-w-xl">
                                    <span class="font-bold text-slate-400 text-[10px] uppercase tracking-wider block mb-0.5">{{ __('Parent Note:') }}</span>
                                    <p class="italic">"{{ $sub->note }}"</p>
                                </div>
                            @endif

                            <!-- Teacher Review / Sticker -->
                            @if ($sub && $sub->isReviewed() && $sub->rating)
                                @php
                                    $st = $stickers[$sub->rating] ?? null;
                                @endphp
                                <div class="mt-2 flex items-center gap-2">
                                    @if ($st)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold border {{ $st['bg'] }}">
                                            <span>{{ $st['emoji'] }}</span>
                                            <span>{{ $st['label'] }}</span>
                                        </span>
                                    @endif
                                    @if ($sub->feedback)
                                        <span class="text-xs text-slate-600 bg-emerald-50/80 px-2.5 py-1 rounded-xl border border-emerald-100">
                                            💬 "{{ $sub->feedback }}"
                                        </span>
                                    @endif
                                </div>
                            @endif

                            <!-- Returned reason feedback -->
                            @if ($sub && $sub->isReturned() && $sub->feedback)
                                <div class="mt-2 p-2.5 rounded-xl bg-rose-50 text-rose-800 text-xs border border-rose-100 max-w-xl">
                                    <span class="font-bold text-rose-600 text-[10px] uppercase tracking-wider block mb-0.5">{{ __('Returned Reason / Required Adjustments:') }}</span>
                                    <p>{{ $sub->feedback }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Right: Attachment preview & Actions -->
                    <div class="flex items-center md:flex-col items-end gap-2 shrink-0">
                        <!-- Submission file preview pill -->
                        @if ($sub && $sub->fileAsset)
                            <a
                                href="{{ $sub->fileDownloadUrl() }}"
                                target="_blank"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition"
                                title="{{ __('Download / View submitted file') }}"
                            >
                                <span>📎</span>
                                <span class="max-w-[120px] truncate">{{ $sub->fileOriginalName() }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">({{ $sub->formattedFileSize() }})</span>
                            </a>
                        @endif

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2">
                            @if ($sub !== null)
                                @if (! $sub->isReviewed())
                                    <button
                                        type="button"
                                        wire:click="openReviewModal({{ $student->id }})"
                                        class="px-3.5 py-1.5 text-xs font-bold rounded-xl text-white bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 shadow-sm transition active:scale-95 cursor-pointer flex items-center gap-1"
                                    >
                                        <span>⭐</span>
                                        <span>{{ __('Review & Grade') }}</span>
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="openReturnModal({{ $student->id }})"
                                        class="px-3 py-1.5 text-xs font-bold rounded-xl text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition active:scale-95 cursor-pointer flex items-center gap-1"
                                        title="{{ __('Return to parent for correction') }}"
                                    >
                                        <span>↩️</span>
                                        <span>{{ __('Return') }}</span>
                                    </button>
                                @else
                                    <button
                                        type="button"
                                        wire:click="openReviewModal({{ $student->id }})"
                                        class="px-3 py-1.5 text-xs font-bold rounded-xl text-brand-700 bg-brand-50 hover:bg-brand-100 border border-brand-200 transition active:scale-95 cursor-pointer"
                                    >
                                        ✏️ {{ __('Edit Feedback') }}
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="reopenSubmission({{ $sub->id }})"
                                        wire:confirm="{{ __('Are you sure you want to reopen this reviewed submission?') }}"
                                        class="px-3 py-1.5 text-xs font-bold rounded-xl text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200 transition active:scale-95 cursor-pointer"
                                        title="{{ __('Revert status back to Submitted') }}"
                                    >
                                        🔓 {{ __('Reopen') }}
                                    </button>
                                @endif

                                @if ($versionsCount > 1)
                                    <button
                                        type="button"
                                        wire:click="openHistoryModal({{ $student->id }})"
                                        class="px-2.5 py-1.5 text-xs font-semibold rounded-xl text-purple-700 bg-purple-50 hover:bg-purple-100 border border-purple-200 transition"
                                        title="{{ __('View all revision versions') }}"
                                    >
                                        📜 {{ __('History (:count)', ['count' => $versionsCount]) }}
                                    </button>
                                @endif
                            @else
                                <span class="text-xs text-slate-400 font-medium italic">
                                    {{ __('Awaiting submission') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center">
                    <span class="text-3xl block mb-2">🔍</span>
                    <h4 class="text-sm font-bold text-slate-800">{{ __('No student submissions match your query.') }}</h4>
                    <p class="text-xs text-slate-500 mt-1">{{ __('Try adjusting your filter or search keywords.') }}</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 1. REVIEW & GRADE MODAL (RESPONSIVE & SCROLLABLE) -->
    <!-- ============================================================== -->
    @if ($showReviewModal && $selectedStudent && $selectedSubmission)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 md:p-6">
            <div class="relative bg-white rounded-3xl max-w-2xl w-full max-h-[92vh] sm:max-h-[90vh] flex flex-col shadow-2xl border border-slate-100 my-auto overflow-hidden animate-in fade-in zoom-in duration-200">
                <!-- Modal Sticky Header -->
                <div class="px-6 py-4 border-b border-slate-100 shrink-0 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-900">{{ __('Review Activity Submission') }}</h3>
                        <p class="text-xs text-slate-500">
                            {{ $selectedStudent->full_name }} • {{ $activity->title }} (v{{ $selectedSubmission->version }})
                        </p>
                    </div>
                    <button type="button" wire:click="closeReviewModal" class="p-1 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100">✕</button>
                </div>

                <!-- Form with Scrollable Body -->
                <form wire:submit="submitReview" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                    <div class="p-6 overflow-y-auto flex-1 space-y-5">
                        <!-- Student & Submission Context Box -->
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-3">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-700">👤 {{ $selectedStudent->full_name }} ({{ $selectedStudent->student_code }})</span>
                                <span class="text-slate-500">🕒 {{ $selectedSubmission->submitted_at->format('M d, Y h:i A') }}</span>
                            </div>

                            @if ($selectedSubmission->note)
                                <div class="pt-2 border-t border-slate-200/60">
                                    <span class="text-[10px] font-extrabold uppercase text-slate-400 block mb-0.5">{{ __('Parent Note:') }}</span>
                                    <p class="text-xs text-slate-700 italic">"{{ $selectedSubmission->note }}"</p>
                                </div>
                            @endif

                            <!-- Attachment Inspector -->
                            @if ($selectedSubmission->fileAsset)
                                <div class="pt-2 border-t border-slate-200/60">
                                    <span class="text-[10px] font-extrabold uppercase text-slate-400 block mb-1.5">{{ __('Submitted Work Attachment:') }}</span>
                                    
                                    @php
                                        $mime = $selectedSubmission->fileAsset->mime_type;
                                        $url = $selectedSubmission->fileDownloadUrl();
                                    @endphp

                                    @if (str_starts_with($mime, 'image/'))
                                        <div class="rounded-2xl overflow-hidden border border-slate-200 bg-slate-900/5 max-h-72 flex items-center justify-center p-2">
                                            <img src="{{ $url }}" alt="{{ $selectedSubmission->fileOriginalName() }}" class="max-h-64 rounded-xl object-contain shadow-xs" />
                                        </div>
                                    @elseif (str_starts_with($mime, 'video/'))
                                        <div class="rounded-2xl overflow-hidden border border-slate-200 bg-slate-950 p-1">
                                            <video controls class="w-full max-h-64 rounded-xl" preload="metadata">
                                                <source src="{{ $url }}" type="{{ $mime }}">
                                                {{ __('Your browser does not support the video tag.') }}
                                            </video>
                                        </div>
                                    @else
                                        <div class="p-3 rounded-2xl bg-white border border-slate-200 flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xl">📄</span>
                                                <div>
                                                    <div class="font-bold text-xs text-slate-900">{{ $selectedSubmission->fileOriginalName() }}</div>
                                                    <div class="text-[10px] text-slate-500 font-mono">{{ $selectedSubmission->formattedFileSize() }}</div>
                                                </div>
                                            </div>
                                            <a href="{{ $url }}" target="_blank" class="px-3 py-1.5 rounded-xl text-xs font-bold bg-brand-50 text-brand-700 hover:bg-brand-100 transition">
                                                {{ __('Download File') }}
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Sticker Picker (§6.4) -->
                        <div>
                            <label class="block text-xs font-extrabold text-slate-800 mb-2">
                                {{ __('Award Nursery Sticker / Star Rating') }} <span class="text-rose-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                @foreach ($stickers as $key => $st)
                                    <button
                                        type="button"
                                        wire:click="setSticker('{{ $key }}')"
                                        class="p-2.5 rounded-2xl text-xs font-bold border transition-all flex flex-col items-center gap-1 cursor-pointer {{ $reviewRating === $key ? 'ring-2 ring-brand-500 shadow-md scale-[1.02] ' . $st['bg'] : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100' }}"
                                    >
                                        <span class="text-2xl">{{ $st['emoji'] }}</span>
                                        <span class="text-[11px] truncate">{{ $st['label'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                            @error('reviewRating') <span class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Feedback Comments Textarea -->
                        <div>
                            <label class="block text-xs font-extrabold text-slate-800 mb-1">
                                {{ __('Encouraging Feedback for Child & Parent') }}
                            </label>
                            <textarea
                                wire:model="reviewFeedback"
                                rows="3"
                                placeholder="{{ __('e.g. Wonderful painting! Great motor skill coordination and color choices. Keep it up! 🌟') }}"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs font-medium"
                            ></textarea>
                            @error('reviewFeedback') <span class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Sticky Modal Footer -->
                    <div class="px-6 py-4 border-t border-slate-100 shrink-0 flex items-center justify-end gap-3 bg-slate-50/80">
                        <button
                            type="button"
                            wire:click="closeReviewModal"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors"
                        >
                            {{ __('Cancel') }}
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 shadow-md shadow-emerald-500/20 transition-all btn-interactive"
                        >
                            {{ __('Save Feedback & Mark Reviewed') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- ============================================================== -->
    <!-- 2. RETURN FOR REVISION MODAL -->
    <!-- ============================================================== -->
    @if ($showReturnModal && $selectedStudent && $selectedSubmission)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 md:p-6">
            <div class="relative bg-white rounded-3xl max-w-md w-full max-h-[92vh] flex flex-col shadow-2xl border border-slate-100 my-auto overflow-hidden animate-in fade-in zoom-in duration-200">
                <div class="px-6 py-4 border-b border-slate-100 shrink-0 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-900">{{ __('Return Submission for Revision') }}</h3>
                        <p class="text-xs text-slate-500">{{ $selectedStudent->full_name }}</p>
                    </div>
                    <button type="button" wire:click="closeReturnModal" class="p-1 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100">✕</button>
                </div>

                <form wire:submit="confirmReturn" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                    <div class="p-6 overflow-y-auto flex-1 space-y-4">
                        <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs space-y-1">
                            <span class="font-bold">⚠️ {{ __('Unlocks Submission for Parent') }}</span>
                            <p class="text-[11px] text-amber-800">
                                {{ __('Returning this submission allows the parent to replace the file or update their note. Please specify constructive instructions on what needs revision.') }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                {{ __('Reason for Returning / Adjustments Needed') }} <span class="text-rose-500">*</span>
                            </label>
                            <textarea
                                wire:model="returnReason"
                                rows="3"
                                placeholder="{{ __('e.g. The uploaded video was blurry, please record the drawing closer; or please attach the missing second page of the worksheet.') }}"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-hidden focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 shadow-2xs font-medium"
                            ></textarea>
                            @error('returnReason') <span class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-slate-100 shrink-0 flex items-center justify-end gap-3 bg-slate-50/80">
                        <button
                            type="button"
                            wire:click="closeReturnModal"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors"
                        >
                            {{ __('Cancel') }}
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-md shadow-rose-500/20 transition-all cursor-pointer"
                        >
                            {{ __('Confirm Return & Notify Parent') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- ============================================================== -->
    <!-- 3. VERSION HISTORY MODAL -->
    <!-- ============================================================== -->
    @if ($showHistoryModal && $selectedStudent)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 md:p-6">
            <div class="relative bg-white rounded-3xl max-w-xl w-full max-h-[92vh] flex flex-col shadow-2xl border border-slate-100 my-auto overflow-hidden animate-in fade-in zoom-in duration-200">
                <div class="px-6 py-4 border-b border-slate-100 shrink-0 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-900">{{ __('Submission Revision History') }}</h3>
                        <p class="text-xs text-slate-500">{{ $selectedStudent->full_name }} • {{ $activity->title }}</p>
                    </div>
                    <button type="button" wire:click="closeHistoryModal" class="p-1 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100">✕</button>
                </div>

                <div class="p-6 overflow-y-auto flex-1 space-y-4">
                    @forelse ($historyVersions as $ver)
                        <div class="p-4 rounded-2xl border {{ $ver->trashed() ? 'bg-slate-50 border-slate-200 opacity-60' : 'bg-white border-brand-200/80 shadow-xs' }} space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-md bg-purple-100 text-purple-800 font-mono font-bold text-xs">
                                        v{{ $ver->version }}
                                    </span>
                                    @if ($ver->trashed())
                                        <span class="px-2 py-0.5 rounded-md bg-slate-200 text-slate-700 text-[10px] font-bold">
                                            {{ __('Replaced / Deleted') }}
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                                            {{ __('Active Version') }}
                                        </span>
                                    @endif
                                </div>
                                <span class="text-xs text-slate-400 font-mono">
                                    {{ $ver->submitted_at->format('M d, Y h:i A') }}
                                </span>
                            </div>

                            @if ($ver->note)
                                <p class="text-xs text-slate-700 italic bg-slate-100/60 p-2 rounded-xl">
                                    "{{ $ver->note }}"
                                </p>
                            @endif

                            @if ($ver->fileAsset)
                                <div class="flex items-center justify-between text-xs pt-1">
                                    <span class="text-slate-600 truncate max-w-[240px]">📎 {{ $ver->fileOriginalName() }}</span>
                                    <a href="{{ $ver->fileDownloadUrl() }}" target="_blank" class="text-brand-600 font-bold hover:underline">
                                        {{ __('Download') }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 text-center py-6">{{ __('No previous versions recorded.') }}</p>
                    @endforelse
                </div>

                <div class="px-6 py-4 border-t border-slate-100 shrink-0 flex items-center justify-end bg-slate-50/80">
                    <button
                        type="button"
                        wire:click="closeHistoryModal"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors"
                    >
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
