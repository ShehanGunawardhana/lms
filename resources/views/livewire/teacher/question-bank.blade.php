<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                <svg class="h-7 w-7 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ __('Question Bank') }}</span>
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                {{ __('Create, tag, and organize reusable questions across all 8 question types for quizzes and assessments.') }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button
                type="button"
                wire:click="openCreateModal"
                class="inline-flex items-center px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow-sm shadow-brand-600/30 transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2"
            >
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>{{ __('New Question') }}</span>
            </button>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 p-4 flex items-center gap-3">
            <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Metrics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Total Questions') }}</span>
                <span class="p-2 rounded-lg bg-brand-50 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ $totalQuestions }}</div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Auto-Marked') }}</span>
                <span class="p-2 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ $autoMarkedCount }}</div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('MCQs, Matching, Blanks, Ordering') }}</p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Manual Review') }}</span>
                <span class="p-2 rounded-lg bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ $manualMarkedCount }}</div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Short answers') }}</p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">{{ __('Question Tags') }}</span>
                <span class="p-2 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                </span>
            </div>
            <div class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ $totalTagsCount }}</div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Categorization taxonomy') }}</p>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
            <!-- Search -->
            <div class="md:col-span-4 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('Search questions, text, or tags...') }}"
                    class="block w-full pl-9 pr-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500"
                />
            </div>

            <!-- Question Type Filter -->
            <div class="md:col-span-3">
                <select
                    wire:model.live="typeFilter"
                    class="block w-full py-2 px-3 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500"
                >
                    <option value="all">{{ __('All Types (8 types)') }}</option>
                    @foreach ($questionTypes as $qType)
                        <option value="{{ $qType->value }}">{{ $qType->label() }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Difficulty Filter -->
            <div class="md:col-span-2">
                <select
                    wire:model.live="difficultyFilter"
                    class="block w-full py-2 px-3 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500"
                >
                    <option value="all">{{ __('All Difficulties') }}</option>
                    @foreach ($difficulties as $diff)
                        <option value="{{ $diff->value }}">{{ $diff->label() }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Tag Filter -->
            <div class="md:col-span-2">
                <select
                    wire:model.live="tagFilter"
                    class="block w-full py-2 px-3 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500"
                >
                    <option value="all">{{ __('All Tags') }}</option>
                    @foreach ($availableTags as $tag)
                        <option value="{{ $tag->id }}">{{ $tag->name }} ({{ $tag->questions_count }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div class="md:col-span-1">
                <select
                    wire:model.live="statusFilter"
                    class="block w-full py-2 px-2 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500"
                >
                    <option value="all">{{ __('Status: All') }}</option>
                    <option value="active">{{ __('Active') }}</option>
                    <option value="inactive">{{ __('Inactive') }}</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Questions List Grid -->
    <div class="space-y-3">
        @forelse ($questions as $question)
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm hover:border-brand-300 dark:hover:border-brand-700 transition-colors">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                    <!-- Left: Badges, Title, Excerpt & Tags -->
                    <div class="space-y-2 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Type Badge -->
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $question->type->badgeClass() }}">
                                {{ $question->type->label() }}
                            </span>

                            <!-- Difficulty Badge -->
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $question->difficulty->badgeClass() }}">
                                {{ $question->difficulty->label() }}
                            </span>

                            <!-- Marks Pill -->
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                {{ number_format($question->default_marks, 1) }} {{ __('pts') }}
                                @if ($question->default_penalty_marks > 0)
                                    <span class="ml-1 text-rose-500">(-{{ number_format($question->default_penalty_marks, 1) }})</span>
                                @endif
                            </span>

                            <!-- Active / Inactive Badge -->
                            <button
                                type="button"
                                wire:click="toggleActive({{ $question->id }})"
                                title="{{ __('Toggle active status') }}"
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium transition-colors {{ $question->is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400' }}"
                            >
                                <span class="h-1.5 w-1.5 rounded-full {{ $question->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                {{ $question->is_active ? __('Active') : __('Inactive') }}
                            </button>
                        </div>

                        <!-- Title & Prompt -->
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">
                                {{ $question->title }}
                            </h3>
                            <p class="text-sm text-slate-600 dark:text-slate-300 mt-1 line-clamp-2">
                                {{ $question->question_text }}
                            </p>
                        </div>

                        <!-- Options summary -->
                        <div class="text-xs text-slate-500 dark:text-slate-400 flex flex-wrap items-center gap-x-4 gap-y-1">
                            @if ($question->type === \App\Enums\QuestionType::Matching)
                                <span>{{ $question->options->count() }} {{ __('pairs') }}</span>
                            @elseif ($question->type === \App\Enums\QuestionType::Ordering)
                                <span>{{ $question->options->count() }} {{ __('sequence steps') }}</span>
                            @elseif ($question->type === \App\Enums\QuestionType::FillInTheBlank)
                                <span>{{ $question->options->count() }} {{ __('blanks') }}</span>
                            @elseif ($question->type === \App\Enums\QuestionType::ShortAnswer)
                                <span>{{ __('Open-ended text') }}</span>
                            @else
                                <span>{{ $question->options->count() }} {{ __('options') }}</span>
                            @endif

                            @if ($question->hint)
                                <span class="flex items-center gap-1 text-amber-600 dark:text-amber-400">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ __('Has hint') }}
                                </span>
                            @endif

                            @if ($question->explanation)
                                <span class="flex items-center gap-1 text-blue-600 dark:text-blue-400">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    {{ __('Has explanation') }}
                                </span>
                            @endif

                            <span>{{ __('Created by') }} {{ $question->creator?->name ?? __('Staff') }}</span>
                        </div>

                        <!-- Tags -->
                        @if ($question->tags->isNotEmpty())
                            <div class="flex flex-wrap gap-1.5 pt-1">
                                @foreach ($question->tags as $t)
                                    <button
                                        type="button"
                                        wire:click="$set('tagFilter', '{{ $t->id }}')"
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 hover:bg-slate-200 dark:bg-slate-700/60 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition-colors"
                                    >
                                        #{{ $t->name }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Right: Actions -->
                    <div class="flex items-center gap-1.5 self-end lg:self-center shrink-0">
                        <!-- Preview -->
                        <button
                            type="button"
                            wire:click="openPreview({{ $question->id }})"
                            title="{{ __('Preview Question') }}"
                            class="p-2 text-slate-500 hover:text-brand-600 dark:text-slate-400 dark:hover:text-brand-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 rounded-xl transition-colors"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>

                        <!-- Edit -->
                        <button
                            type="button"
                            wire:click="editQuestion({{ $question->id }})"
                            title="{{ __('Edit Question') }}"
                            class="p-2 text-slate-500 hover:text-brand-600 dark:text-slate-400 dark:hover:text-brand-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 rounded-xl transition-colors"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>

                        <!-- Duplicate -->
                        <button
                            type="button"
                            wire:click="duplicateQuestion({{ $question->id }})"
                            title="{{ __('Duplicate Question') }}"
                            class="p-2 text-slate-500 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 rounded-xl transition-colors"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </button>

                        <!-- Delete -->
                        <button
                            type="button"
                            wire:click="confirmDelete({{ $question->id }})"
                            title="{{ __('Delete Question') }}"
                            class="p-2 text-slate-500 hover:text-rose-600 dark:text-slate-400 dark:hover:text-rose-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 rounded-xl transition-colors"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-3 text-base font-semibold text-slate-900 dark:text-white">{{ __('No questions found') }}</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ __('Try adjusting your search criteria, or add your first question to the question bank.') }}
                </p>
                <div class="mt-6">
                    <button
                        type="button"
                        wire:click="openCreateModal"
                        class="inline-flex items-center px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors"
                    >
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>{{ __('Create Question') }}</span>
                    </button>
                </div>
            </div>
        @endforelse

        <!-- Pagination -->
        @if ($questions->hasPages())
            <div class="pt-4">
                {{ $questions->links() }}
            </div>
        @endif
    </div>

    <!-- Create / Edit Question Modal -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" wire:click="$set('showModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-slate-200 dark:border-slate-700">
                    <form wire:submit.prevent="saveQuestion">
                        <!-- Modal Header -->
                        <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <svg class="h-5 w-5 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                <span>{{ $isEditing ? __('Edit Question') : __('Create New Question') }}</span>
                            </h2>
                            <button
                                type="button"
                                wire:click="$set('showModal', false)"
                                class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Modal Body -->
                        <div class="px-6 py-6 max-h-[75vh] overflow-y-auto space-y-6">
                            <!-- Question Type Selector -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-2">
                                    {{ __('Question Type') }} <span class="text-rose-500">*</span>
                                </label>
                                <select
                                    wire:model.live="type"
                                    class="block w-full py-2.5 px-3 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-brand-500"
                                >
                                    @foreach ($questionTypes as $qType)
                                        <option value="{{ $qType->value }}">{{ $qType->label() }} — {{ $qType->description() }}</option>
                                    @endforeach
                                </select>
                                @error('type') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Basic Info Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Title / Internal Reference -->
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">
                                        {{ __('Title / Internal Reference') }} <span class="text-rose-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        wire:model="title"
                                        placeholder="{{ __('e.g., Photosynthesis - Light Dependent Stage') }}"
                                        class="block w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                                    />
                                    @error('title') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- Question Prompt / Text -->
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">
                                        {{ __('Question Prompt') }} <span class="text-rose-500">*</span>
                                    </label>
                                    <textarea
                                        wire:model="question_text"
                                        rows="3"
                                        placeholder="{{ __('Write the question prompt or instructions for students...') }}"
                                        class="block w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                                    ></textarea>
                                    @error('question_text') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- Difficulty -->
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">
                                        {{ __('Difficulty') }} <span class="text-rose-500">*</span>
                                    </label>
                                    <select
                                        wire:model="difficulty"
                                        class="block w-full py-2 px-3 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                                    >
                                        @foreach ($difficulties as $diff)
                                            <option value="{{ $diff->value }}">{{ $diff->label() }}</option>
                                        @endforeach
                                    </select>
                                    @error('difficulty') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- Marks -->
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">
                                            {{ __('Marks') }} <span class="text-rose-500">*</span>
                                        </label>
                                        <input
                                            type="number"
                                            step="0.5"
                                            min="0.5"
                                            wire:model="default_marks"
                                            class="block w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                                        />
                                        @error('default_marks') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">
                                            {{ __('Penalty') }}
                                        </label>
                                        <input
                                            type="number"
                                            step="0.25"
                                            min="0"
                                            wire:model="default_penalty_marks"
                                            class="block w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                                        />
                                        @error('default_penalty_marks') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- Tags Input -->
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">
                                        {{ __('Tags') }}
                                    </label>
                                    <input
                                        type="text"
                                        wire:model="tagsInput"
                                        placeholder="{{ __('biology, grade-10, term-1, photosynthesis (comma-separated)') }}"
                                        class="block w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                                    />
                                    <p class="text-xs text-slate-400 mt-1">{{ __('Separate tags with commas. New tags will be created automatically in your question bank.') }}</p>
                                </div>
                            </div>

                            <!-- Dynamic Answers & Options Section -->
                            <div class="border-t border-slate-200 dark:border-slate-700 pt-5 space-y-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                        {{ __('Answer Configuration') }}
                                    </h3>
                                    @if (in_array($type, ['single_choice', 'multiple_choice', 'matching', 'picture_choice', 'fill_in_the_blank', 'ordering']))
                                        <button
                                            type="button"
                                            wire:click="addOption"
                                            class="inline-flex items-center text-xs font-semibold text-brand-600 dark:text-brand-400 hover:text-brand-700"
                                        >
                                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                            <span>{{ __('Add Item') }}</span>
                                        </button>
                                    @endif
                                </div>

                                @error('options')
                                    <div class="rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 p-3 text-xs text-rose-700 dark:text-rose-300">
                                        {{ $message }}
                                    </div>
                                @enderror

                                <!-- TYPE 1: Single Choice -->
                                @if ($type === \App\Enums\QuestionType::SingleChoice->value)
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Enter options and select the radio button for the single correct answer:') }}</p>
                                    <div class="space-y-2.5">
                                        @foreach ($options as $index => $opt)
                                            <div class="flex items-center gap-3 bg-slate-50 dark:bg-slate-900/50 p-2.5 rounded-xl border border-slate-200 dark:border-slate-700">
                                                <input
                                                    type="radio"
                                                    name="single_choice_radio"
                                                    wire:click="setSingleCorrect({{ $index }})"
                                                    @checked(!empty($opt['is_correct']))
                                                    class="h-4 w-4 text-brand-600 focus:ring-brand-500 border-slate-300 cursor-pointer"
                                                />
                                                <input
                                                    type="text"
                                                    wire:model="options.{{ $index }}.text"
                                                    placeholder="{{ __('Option :num text...', ['num' => chr(65 + $index)]) }}"
                                                    class="flex-1 px-3 py-1.5 text-sm rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                                                />
                                                <span class="text-xs font-semibold px-2 py-1 rounded {{ !empty($opt['is_correct']) ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' : 'text-slate-400' }}">
                                                    {{ !empty($opt['is_correct']) ? __('Correct') : '' }}
                                                </span>
                                                @if (count($options) > 2)
                                                    <button
                                                        type="button"
                                                        wire:click="removeOption({{ $index }})"
                                                        class="text-slate-400 hover:text-rose-600 p-1"
                                                    >
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>

                                <!-- TYPE 2: Multiple Choice -->
                                @elseif ($type === \App\Enums\QuestionType::MultipleChoice->value)
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Check all options that are correct:') }}</p>
                                    <div class="space-y-2.5">
                                        @foreach ($options as $index => $opt)
                                            <div class="flex items-center gap-3 bg-slate-50 dark:bg-slate-900/50 p-2.5 rounded-xl border border-slate-200 dark:border-slate-700">
                                                <input
                                                    type="checkbox"
                                                    wire:model="options.{{ $index }}.is_correct"
                                                    class="h-4 w-4 text-brand-600 focus:ring-brand-500 border-slate-300 rounded cursor-pointer"
                                                />
                                                <input
                                                    type="text"
                                                    wire:model="options.{{ $index }}.text"
                                                    placeholder="{{ __('Option :num text...', ['num' => chr(65 + $index)]) }}"
                                                    class="flex-1 px-3 py-1.5 text-sm rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                                                />
                                                @if (count($options) > 2)
                                                    <button
                                                        type="button"
                                                        wire:click="removeOption({{ $index }})"
                                                        class="text-slate-400 hover:text-rose-600 p-1"
                                                    >
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>

                                <!-- TYPE 3: True / False -->
                                @elseif ($type === \App\Enums\QuestionType::TrueFalse->value)
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Select the correct statement result:') }}</p>
                                    <div class="grid grid-cols-2 gap-4">
                                        <button
                                            type="button"
                                            wire:click="setTrueFalseCorrect(true)"
                                            class="p-4 rounded-2xl border text-center font-bold text-sm transition-all {{ !empty($options[0]['is_correct']) ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-500 text-emerald-800 dark:text-emerald-200 ring-2 ring-emerald-500' : 'bg-white dark:bg-slate-700 border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200' }}"
                                        >
                                            <span class="block text-xl mb-1">✓</span>
                                            {{ __('TRUE is Correct') }}
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="setTrueFalseCorrect(false)"
                                            class="p-4 rounded-2xl border text-center font-bold text-sm transition-all {{ !empty($options[1]['is_correct']) ? 'bg-rose-50 dark:bg-rose-950/40 border-rose-500 text-rose-800 dark:text-rose-200 ring-2 ring-rose-500' : 'bg-white dark:bg-slate-700 border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200' }}"
                                        >
                                            <span class="block text-xl mb-1">✗</span>
                                            {{ __('FALSE is Correct') }}
                                        </button>
                                    </div>

                                <!-- TYPE 4: Matching -->
                                @elseif ($type === \App\Enums\QuestionType::Matching->value)
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Set up matching pairs (Left premise <---> Right target):') }}</p>
                                    <div class="space-y-2.5">
                                        @foreach ($options as $index => $opt)
                                            <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-900/50 p-2.5 rounded-xl border border-slate-200 dark:border-slate-700">
                                                <span class="text-xs font-bold text-slate-400 w-6 text-center">#{{ $index + 1 }}</span>
                                                <input
                                                    type="text"
                                                    wire:model="options.{{ $index }}.left"
                                                    placeholder="{{ __('Left item (Premise)...') }}"
                                                    class="flex-1 px-3 py-1.5 text-sm rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                                                />
                                                <span class="text-slate-400">↔</span>
                                                <input
                                                    type="text"
                                                    wire:model="options.{{ $index }}.right"
                                                    placeholder="{{ __('Right match (Target)...') }}"
                                                    class="flex-1 px-3 py-1.5 text-sm rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                                                />
                                                @if (count($options) > 2)
                                                    <button
                                                        type="button"
                                                        wire:click="removeOption({{ $index }})"
                                                        class="text-slate-400 hover:text-rose-600 p-1"
                                                    >
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>

                                <!-- TYPE 5: Picture Choice -->
                                @elseif ($type === \App\Enums\QuestionType::PictureChoice->value)
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Provide picture choices with label/caption and mark the correct image answer(s):') }}</p>
                                    <div class="space-y-3">
                                        @foreach ($options as $index => $opt)
                                            <div class="flex items-center gap-3 bg-slate-50 dark:bg-slate-900/50 p-3 rounded-xl border border-slate-200 dark:border-slate-700">
                                                <input
                                                    type="checkbox"
                                                    wire:model="options.{{ $index }}.is_correct"
                                                    class="h-4 w-4 text-brand-600 focus:ring-brand-500 border-slate-300 rounded cursor-pointer"
                                                />
                                                <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                    <input
                                                        type="text"
                                                        wire:model="options.{{ $index }}.text"
                                                        placeholder="{{ __('Label / Caption (e.g. Cat, Lion, Dog)') }}"
                                                        class="px-3 py-1.5 text-sm rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                                    />
                                                    <input
                                                        type="text"
                                                        wire:model="options.{{ $index }}.image_path"
                                                        placeholder="{{ __('Image URL or asset path...') }}"
                                                        class="px-3 py-1.5 text-sm rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                                    />
                                                </div>
                                                @if (count($options) > 2)
                                                    <button
                                                        type="button"
                                                        wire:click="removeOption({{ $index }})"
                                                        class="text-slate-400 hover:text-rose-600 p-1"
                                                    >
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>

                                <!-- TYPE 6: Fill in the Blank -->
                                @elseif ($type === \App\Enums\QuestionType::FillInTheBlank->value)
                                    <div class="flex items-center justify-between">
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Provide accepted answers for the blank (comma-separated for spelling variants):') }}</p>
                                        <label class="inline-flex items-center text-xs text-slate-600 dark:text-slate-300 cursor-pointer">
                                            <input type="checkbox" wire:model="caseSensitive" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500 mr-1.5" />
                                            {{ __('Case sensitive matching') }}
                                        </label>
                                    </div>
                                    <div class="space-y-2.5">
                                        @foreach ($options as $index => $opt)
                                            <div class="space-y-1 bg-slate-50 dark:bg-slate-900/50 p-3 rounded-xl border border-slate-200 dark:border-slate-700">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-bold text-slate-400 w-16">{{ __('Blank #:num', ['num' => $index + 1]) }}</span>
                                                    <input
                                                        type="text"
                                                        wire:model="options.{{ $index }}.acceptable_answers"
                                                        placeholder="{{ __('Accepted answers (e.g., mitochondria, Mitochondria, the powerhouse)') }}"
                                                        class="flex-1 px-3 py-1.5 text-sm rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                                                    />
                                                    @if (count($options) > 1)
                                                        <button
                                                            type="button"
                                                            wire:click="removeOption({{ $index }})"
                                                            class="text-slate-400 hover:text-rose-600 p-1"
                                                        >
                                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                <!-- TYPE 7: Ordering -->
                                @elseif ($type === \App\Enums\QuestionType::Ordering->value)
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Arrange items in their correct sequential order (top to bottom):') }}</p>
                                    <div class="space-y-2">
                                        @foreach ($options as $index => $opt)
                                            <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-900/50 p-2.5 rounded-xl border border-slate-200 dark:border-slate-700">
                                                <span class="text-xs font-bold px-2 py-1 bg-brand-100 text-brand-800 dark:bg-brand-900/40 dark:text-brand-300 rounded-md">
                                                    {{ $index + 1 }}
                                                </span>
                                                <input
                                                    type="text"
                                                    wire:model="options.{{ $index }}.text"
                                                    placeholder="{{ __('Step / sequence item :num...', ['num' => $index + 1]) }}"
                                                    class="flex-1 px-3 py-1.5 text-sm rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                                                />
                                                <div class="flex items-center gap-0.5">
                                                    <button
                                                        type="button"
                                                        wire:click="moveOptionUp({{ $index }})"
                                                        @disabled($index === 0)
                                                        class="p-1 text-slate-400 hover:text-slate-700 disabled:opacity-30"
                                                    >
                                                        ▲
                                                    </button>
                                                    <button
                                                        type="button"
                                                        wire:click="moveOptionDown({{ $index }})"
                                                        @disabled($index === count($options) - 1)
                                                        class="p-1 text-slate-400 hover:text-slate-700 disabled:opacity-30"
                                                    >
                                                        ▼
                                                    </button>
                                                </div>
                                                @if (count($options) > 2)
                                                    <button
                                                        type="button"
                                                        wire:click="removeOption({{ $index }})"
                                                        class="text-slate-400 hover:text-rose-600 p-1"
                                                    >
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>

                                <!-- TYPE 8: Short Answer -->
                                @elseif ($type === \App\Enums\QuestionType::ShortAnswer->value)
                                    <div class="space-y-3 bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700">
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ __('Short answer questions are routed to the manual grading queue. Provide a sample answer and grading rubric notes for the marker:') }}
                                        </p>
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">
                                                {{ __('Sample / Model Answer') }}
                                            </label>
                                            <textarea
                                                wire:model="options.0.sample_answer"
                                                rows="2"
                                                placeholder="{{ __('Expected key points or model response...') }}"
                                                class="block w-full px-3 py-1.5 text-sm rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                            ></textarea>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">
                                                {{ __('Marker Guidance & Rubric') }}
                                            </label>
                                            <textarea
                                                wire:model="options.0.feedback"
                                                rows="2"
                                                placeholder="{{ __('Award 1 mark for mentioning X, 1 mark for Y...') }}"
                                                class="block w-full px-3 py-1.5 text-sm rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                            ></textarea>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Optional Hint & Explanation -->
                            <div class="border-t border-slate-200 dark:border-slate-700 pt-5 space-y-4">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                    {{ __('Hints & Explanations') }}
                                </h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">
                                            {{ __('Student Hint (Optional)') }}
                                        </label>
                                        <textarea
                                            wire:model="hint"
                                            rows="2"
                                            placeholder="{{ __('Optional clue shown during the quiz if hints are enabled...') }}"
                                            class="block w-full px-3 py-1.5 text-sm rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                        ></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">
                                            {{ __('Explanation (Post-Submission)') }}
                                        </label>
                                        <textarea
                                            wire:model="explanation"
                                            rows="2"
                                            placeholder="{{ __('Rationale or solution walkthrough shown after submission...') }}"
                                            class="block w-full px-3 py-1.5 text-sm rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700 flex items-center justify-end gap-3">
                            <button
                                type="button"
                                wire:click="$set('showModal', false)"
                                class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors"
                            >
                                {{ __('Cancel') }}
                            </button>
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow-sm shadow-brand-600/30 transition-colors focus:ring-2 focus:ring-brand-500"
                            >
                                <span wire:loading.remove>{{ $isEditing ? __('Update Question') : __('Save Question') }}</span>
                                <span wire:loading>{{ __('Saving...') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Question Preview Modal -->
    @if ($showPreviewModal && $previewQuestion)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="preview-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" wire:click="$set('showPreviewModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
                    <!-- Preview Header -->
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $previewQuestion->type->badgeClass() }}">
                                {{ $previewQuestion->type->label() }}
                            </span>
                            <span class="text-xs text-slate-500 dark:text-slate-400">
                                {{ number_format($previewQuestion->default_marks, 1) }} {{ __('Marks') }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                wire:click="togglePreviewAnswers"
                                class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-lg border {{ $showAnswersInPreview ? 'bg-emerald-50 text-emerald-700 border-emerald-300 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-slate-100 text-slate-700 border-slate-300 dark:bg-slate-700 dark:text-slate-300' }}"
                            >
                                👁 {{ $showAnswersInPreview ? __('Hide Answers') : __('Show Answers') }}
                            </button>
                            <button
                                type="button"
                                wire:click="$set('showPreviewModal', false)"
                                class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Preview Content -->
                    <div class="p-6 space-y-5">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">{{ $previewQuestion->title }}</h3>
                            <p class="text-sm text-slate-800 dark:text-slate-200 whitespace-pre-line">{{ $previewQuestion->question_text }}</p>
                        </div>

                        <!-- Render Options according to type -->
                        <div class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-700">
                            @if ($previewQuestion->type === \App\Enums\QuestionType::SingleChoice)
                                @foreach ($previewQuestion->options as $index => $opt)
                                    <div class="flex items-center justify-between p-3 rounded-xl border {{ $showAnswersInPreview && $opt->is_correct ? 'bg-emerald-50 dark:bg-emerald-950/30 border-emerald-400 text-emerald-900 dark:text-emerald-100' : 'bg-slate-50 dark:bg-slate-700/50 border-slate-200 dark:border-slate-600' }}">
                                        <div class="flex items-center gap-3">
                                            <span class="h-5 w-5 rounded-full border border-slate-300 dark:border-slate-500 flex items-center justify-center text-xs font-bold {{ $showAnswersInPreview && $opt->is_correct ? 'border-emerald-500 bg-emerald-500 text-white' : '' }}">
                                                {{ chr(65 + $index) }}
                                            </span>
                                            <span class="text-sm">{{ $opt->option_text }}</span>
                                        </div>
                                        @if ($showAnswersInPreview && $opt->is_correct)
                                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">✓ {{ __('Correct Answer') }}</span>
                                        @endif
                                    </div>
                                @endforeach

                            @elseif ($previewQuestion->type === \App\Enums\QuestionType::MultipleChoice)
                                @foreach ($previewQuestion->options as $index => $opt)
                                    <div class="flex items-center justify-between p-3 rounded-xl border {{ $showAnswersInPreview && $opt->is_correct ? 'bg-emerald-50 dark:bg-emerald-950/30 border-emerald-400 text-emerald-900 dark:text-emerald-100' : 'bg-slate-50 dark:bg-slate-700/50 border-slate-200 dark:border-slate-600' }}">
                                        <div class="flex items-center gap-3">
                                            <span class="h-4 w-4 rounded border border-slate-300 flex items-center justify-center text-xs {{ $showAnswersInPreview && $opt->is_correct ? 'bg-emerald-500 text-white border-emerald-500' : '' }}">
                                                {{ $showAnswersInPreview && $opt->is_correct ? '✓' : '' }}
                                            </span>
                                            <span class="text-sm">{{ $opt->option_text }}</span>
                                        </div>
                                        @if ($showAnswersInPreview && $opt->is_correct)
                                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">✓ {{ __('Correct') }}</span>
                                        @endif
                                    </div>
                                @endforeach

                            @elseif ($previewQuestion->type === \App\Enums\QuestionType::TrueFalse)
                                <div class="grid grid-cols-2 gap-3">
                                    @foreach ($previewQuestion->options as $opt)
                                        <div class="p-3 rounded-xl border text-center font-bold text-sm {{ $showAnswersInPreview && $opt->is_correct ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-500 text-emerald-800 dark:text-emerald-200 ring-2 ring-emerald-500' : 'bg-slate-50 dark:bg-slate-700/50 border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300' }}">
                                            {{ $opt->option_text }}
                                            @if ($showAnswersInPreview && $opt->is_correct)
                                                <span class="block text-xs font-normal text-emerald-600 dark:text-emerald-400 mt-1">✓ {{ __('Correct') }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                            @elseif ($previewQuestion->type === \App\Enums\QuestionType::Matching)
                                <div class="space-y-2">
                                    @foreach ($previewQuestion->options as $opt)
                                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 text-sm">
                                            <span class="font-medium text-slate-800 dark:text-slate-200">{{ $opt->option_text }}</span>
                                            <span class="text-slate-400 px-2">↔</span>
                                            <span class="font-medium {{ $showAnswersInPreview ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-slate-800 dark:text-slate-200' }}">
                                                {{ $opt->match_text }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>

                            @elseif ($previewQuestion->type === \App\Enums\QuestionType::Ordering)
                                <div class="space-y-2">
                                    @foreach ($previewQuestion->options as $index => $opt)
                                        <div class="flex items-center gap-3 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 text-sm">
                                            <span class="h-6 w-6 rounded-md bg-brand-100 text-brand-800 dark:bg-brand-900/40 dark:text-brand-300 font-bold flex items-center justify-center text-xs">
                                                {{ $showAnswersInPreview ? ($index + 1) : '?' }}
                                            </span>
                                            <span class="text-slate-800 dark:text-slate-200">{{ $opt->option_text }}</span>
                                        </div>
                                    @endforeach
                                </div>

                            @elseif ($previewQuestion->type === \App\Enums\QuestionType::FillInTheBlank)
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 text-sm">
                                    <span class="font-bold text-slate-600 dark:text-slate-300">{{ __('Accepted answers:') }}</span>
                                    @foreach ($previewQuestion->options as $opt)
                                        <span class="ml-2 font-mono text-emerald-600 dark:text-emerald-400">
                                            [{{ is_array($opt->acceptable_answers) ? implode(' / ', $opt->acceptable_answers) : $opt->option_text }}]
                                        </span>
                                    @endforeach
                                </div>

                            @elseif ($previewQuestion->type === \App\Enums\QuestionType::ShortAnswer)
                                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 text-sm space-y-2">
                                    <p class="font-bold text-slate-700 dark:text-slate-300">{{ __('Marker Rubric & Model Answer:') }}</p>
                                    <p class="text-slate-600 dark:text-slate-300 italic">{{ $previewQuestion->options->first()?->option_text ?? __('None specified.') }}</p>
                                    @if ($previewQuestion->options->first()?->feedback)
                                        <p class="text-xs text-slate-500">{{ $previewQuestion->options->first()?->feedback }}</p>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Hint & Explanation if visible -->
                        @if ($showAnswersInPreview)
                            @if ($previewQuestion->hint)
                                <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 text-xs text-amber-900 dark:text-amber-200">
                                    <span class="font-bold">{{ __('Hint:') }}</span> {{ $previewQuestion->hint }}
                                </div>
                            @endif
                            @if ($previewQuestion->explanation)
                                <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 text-xs text-blue-900 dark:text-blue-200">
                                    <span class="font-bold">{{ __('Explanation:') }}</span> {{ $previewQuestion->explanation }}
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="delete-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" wire:click="$set('showDeleteModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200 dark:border-slate-700 p-6">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 rounded-2xl shrink-0">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Delete Question?') }}</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                {{ __('Are you sure you want to delete this question? It will be removed from the question bank.') }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <button
                            type="button"
                            wire:click="$set('showDeleteModal', false)"
                            class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-xl transition-colors"
                        >
                            {{ __('Cancel') }}
                        </button>
                        <button
                            type="button"
                            wire:click="deleteQuestion"
                            class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors"
                        >
                            {{ __('Delete') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
