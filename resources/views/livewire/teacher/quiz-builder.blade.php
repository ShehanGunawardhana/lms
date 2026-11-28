<div class="space-y-6">
    <!-- Top Action / Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                <svg class="h-7 w-7 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <span>{{ __('Quiz Builder & Assessments') }}</span>
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                {{ __('Build standard quizzes, nursery games, live in-class sessions, and exam papers attachable to classes, sessions, or standalone.') }}
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <!-- Link to Question Bank -->
            <a
                href="{{ route('teacher.questions.index') }}"
                class="inline-flex items-center px-4 py-2.5 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/80 text-slate-700 dark:text-slate-200 text-sm font-semibold rounded-2xl border border-slate-300 dark:border-slate-700 shadow-sm transition-colors"
            >
                <svg class="h-4 w-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span>{{ __('Question Bank') }}</span>
            </a>

            <!-- Link to Manual Marking Queue (§9, T-6.6) -->
            <a
                href="{{ route('teacher.quizzes.grading') }}"
                class="inline-flex items-center px-4 py-2.5 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/80 text-slate-700 dark:text-slate-200 text-sm font-semibold rounded-2xl border border-slate-300 dark:border-slate-700 shadow-sm transition-colors"
            >
                <svg class="h-4 w-4 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>{{ __('Marking Queue') }}</span>
            </a>

            <!-- Create Quiz Button -->
            <button
                type="button"
                wire:click="openCreateModal"
                class="inline-flex items-center px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-2xl shadow-sm shadow-brand-600/30 transition-colors"
            >
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>{{ __('Create Quiz') }}</span>
            </button>
        </div>
    </div>

    <!-- Feedback messages -->
    @if (session()->has('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-sm text-emerald-800 dark:text-emerald-300 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span>✓</span>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 dark:text-emerald-400 font-bold">&times;</button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-sm text-rose-800 dark:text-rose-300 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span>⚠️</span>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 dark:text-rose-400 font-bold">&times;</button>
        </div>
    @endif

    <!-- Metrics Cards Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ __('Total Quizzes') }}</span>
            <p class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ $metrics['total'] }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">{{ __('Published') }}</span>
            <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ $metrics['published'] }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ __('Drafts') }}</span>
            <p class="text-2xl font-black text-slate-700 dark:text-slate-300 mt-1">{{ $metrics['draft'] }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-pink-600 dark:text-pink-400">{{ __('Nursery Mode') }}</span>
            <p class="text-2xl font-black text-pink-600 dark:text-pink-400 mt-1">{{ $metrics['nursery'] }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm col-span-2 sm:col-span-1">
            <span class="text-xs font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400">{{ __('Standalone') }}</span>
            <p class="text-2xl font-black text-blue-600 dark:text-blue-400 mt-1">{{ $metrics['standalone'] }}</p>
        </div>
    </div>

    <!-- Search & Filters Toolbar -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
            <!-- Search Input -->
            <div class="md:col-span-4 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('Search quizzes by title, description...') }}"
                    class="block w-full pl-9 pr-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500"
                />
            </div>

            <!-- Status Filter -->
            <div class="md:col-span-2">
                <select
                    wire:model.live="statusFilter"
                    class="block w-full py-2 px-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                >
                    <option value="all">{{ __('Status: All') }}</option>
                    @foreach ($statuses as $st)
                        <option value="{{ $st->value }}">{{ $st->label() }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Mode Filter -->
            <div class="md:col-span-2">
                <select
                    wire:model.live="modeFilter"
                    class="block w-full py-2 px-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                >
                    <option value="all">{{ __('Mode: All') }}</option>
                    @foreach ($modes as $m)
                        <option value="{{ $m->value }}">{{ $m->label() }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Attachment Filter -->
            <div class="md:col-span-2">
                <select
                    wire:model.live="attachmentFilter"
                    class="block w-full py-2 px-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                >
                    <option value="all">{{ __('Attachment: All') }}</option>
                    <option value="standalone">{{ __('Standalone') }}</option>
                    <option value="class">{{ __('Class Link') }}</option>
                    <option value="activity">{{ __('Activity Link') }}</option>
                    <option value="session">{{ __('Session Link') }}</option>
                </select>
            </div>

            <!-- Class Filter -->
            <div class="md:col-span-2">
                <select
                    wire:model.live="classFilter"
                    class="block w-full py-2 px-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                >
                    <option value="all">{{ __('Class: All') }}</option>
                    @foreach ($classes as $c)
                        <option value="{{ $c->id }}">{{ $c->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Quizzes List Cards -->
    <div class="space-y-3.5">
        @forelse ($quizzes as $quiz)
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 p-5 sm:p-6 shadow-sm hover:border-brand-300 dark:hover:border-brand-700 transition-colors">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                    <!-- Left info -->
                    <div class="space-y-3 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Mode Badge -->
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $quiz->mode->badgeClass() }}">
                                {{ $quiz->mode->label() }}
                            </span>

                            <!-- Status Toggle Badge -->
                            <button
                                type="button"
                                wire:click="toggleStatus({{ $quiz->id }})"
                                title="{{ __('Toggle published / draft status') }}"
                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold border transition-colors {{ $quiz->status->badgeClass() }}"
                            >
                                <span class="h-1.5 w-1.5 rounded-full {{ $quiz->isPublished() ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                {{ $quiz->status->label() }}
                            </button>

                            <!-- Attachment Target Pill -->
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                📎 {{ $quiz->getAttachmentTargetLabel() }}
                            </span>

                            <!-- Availability Window Pill if active -->
                            @if ($quiz->starts_at || $quiz->ends_at)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium {{ $quiz->isAvailable() ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300' }}">
                                    @if ($quiz->isAvailable())
                                        {{ __('Open now') }}
                                    @elseif ($quiz->ends_at && now()->gt($quiz->ends_at))
                                        {{ __('Closed') }}
                                    @else
                                        {{ __('Scheduled') }}
                                    @endif
                                </span>
                            @endif
                        </div>

                        <!-- Title and Description -->
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                                {{ $quiz->title }}
                            </h3>
                            @if ($quiz->description)
                                <p class="text-sm text-slate-600 dark:text-slate-300 mt-1 line-clamp-2">
                                    {{ $quiz->description }}
                                </p>
                            @endif
                        </div>

                        <!-- Quick metrics footer -->
                        <div class="flex flex-wrap items-center gap-x-5 gap-y-1.5 text-xs text-slate-500 dark:text-slate-400 pt-1">
                            <span class="font-medium text-slate-700 dark:text-slate-300">
                                📝 {{ $quiz->quiz_questions_count }} {{ __('questions') }}
                            </span>
                            <span class="font-medium text-slate-700 dark:text-slate-300">
                                🎯 {{ number_format($quiz->total_marks, 1) }} {{ __('pts total') }}
                            </span>
                            <span>
                                ⏱ {{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes.' '.__('mins') : __('No time limit') }}
                            </span>
                            <span>
                                🔄 {{ $quiz->max_attempts ? $quiz->max_attempts.' '.__('attempts') : __('Unlimited attempts') }}
                            </span>
                            <span>
                                🏅 {{ number_format($quiz->pass_percentage, 0) }}% {{ __('pass mark') }}
                            </span>
                            <span>
                                👁 {{ $quiz->show_answers_policy->label() }}
                            </span>
                            @if ($quiz->creator)
                                <span>{{ __('By') }} {{ $quiz->creator->name }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Right action buttons -->
                    <div class="flex items-center gap-1.5 self-end lg:self-center shrink-0">
                        <!-- Preview -->
                        <button
                            type="button"
                            wire:click="openPreview({{ $quiz->id }})"
                            title="{{ __('Preview Quiz') }}"
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
                            wire:click="editQuiz({{ $quiz->id }})"
                            title="{{ __('Edit Quiz Settings & Questions') }}"
                            class="p-2 text-slate-500 hover:text-brand-600 dark:text-slate-400 dark:hover:text-brand-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 rounded-xl transition-colors"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>

                        <!-- Duplicate -->
                        <button
                            type="button"
                            wire:click="duplicateQuiz({{ $quiz->id }})"
                            title="{{ __('Duplicate Quiz') }}"
                            class="p-2 text-slate-500 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-700/50 rounded-xl transition-colors"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </button>

                        <!-- Delete -->
                        <button
                            type="button"
                            wire:click="confirmDelete({{ $quiz->id }})"
                            title="{{ __('Delete Quiz') }}"
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
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <h3 class="mt-3 text-base font-semibold text-slate-900 dark:text-white">{{ __('No quizzes found') }}</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ __('Create your first quiz or adjust your search filter criteria.') }}
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
                        <span>{{ __('Create Quiz') }}</span>
                    </button>
                </div>
            </div>
        @endforelse

        <!-- Pagination -->
        @if ($quizzes->hasPages())
            <div class="pt-4">
                {{ $quizzes->links() }}
            </div>
        @endif
    </div>

    <!-- Quiz Create / Edit Builder Modal -->
    @if ($showQuizModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" wire:click="$set('showQuizModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-slate-200 dark:border-slate-700">
                    <form wire:submit.prevent="saveQuiz">
                        <!-- Modal Header -->
                        <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="p-2.5 bg-brand-50 dark:bg-brand-950/40 text-brand-600 dark:text-brand-400 rounded-2xl">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                                        {{ $isEditing ? __('Edit Quiz: :title', ['title' => $title]) : __('Create New Quiz') }}
                                    </h2>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ __('Configure quiz rules, attach target, and manage questions sequence.') }}
                                    </p>
                                </div>
                            </div>
                            <button
                                type="button"
                                wire:click="$set('showQuizModal', false)"
                                class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Modal Tabs Navigation -->
                        <div class="px-6 pt-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30 flex items-center gap-2">
                            <button
                                type="button"
                                wire:click="$set('activeModalTab', 'settings')"
                                class="px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-t-xl transition-colors border-b-2 {{ $activeModalTab === 'settings' ? 'border-brand-600 text-brand-600 dark:text-brand-400 bg-white dark:bg-slate-800' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400' }}"
                            >
                                ⚙️ {{ __('1. Settings & Policy') }}
                            </button>
                            <button
                                type="button"
                                wire:click="$set('activeModalTab', 'attachment')"
                                class="px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-t-xl transition-colors border-b-2 {{ $activeModalTab === 'attachment' ? 'border-brand-600 text-brand-600 dark:text-brand-400 bg-white dark:bg-slate-800' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400' }}"
                            >
                                📎 {{ __('2. Attachment Target') }}
                            </button>
                            <button
                                type="button"
                                wire:click="$set('activeModalTab', 'questions')"
                                class="px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-t-xl transition-colors border-b-2 {{ $activeModalTab === 'questions' ? 'border-brand-600 text-brand-600 dark:text-brand-400 bg-white dark:bg-slate-800' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400' }}"
                            >
                                📝 {{ __('3. Questions (:count)', ['count' => count($selectedQuestions)]) }}
                            </button>
                        </div>

                        <!-- Modal Body -->
                        <div class="px-6 py-6 max-h-[72vh] overflow-y-auto space-y-6">
                            <!-- TAB 1: Settings & Policies -->
                            <div class="{{ $activeModalTab === 'settings' ? 'space-y-5' : 'hidden' }}">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Title -->
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">
                                            {{ __('Quiz Title') }} <span class="text-rose-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            wire:model="title"
                                            placeholder="{{ __('e.g., Photosynthesis & Plant Biology Mid-Term Quiz') }}"
                                            class="block w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                                        />
                                        @error('title') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Mode Selector -->
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">
                                            {{ __('Quiz Mode') }} <span class="text-rose-500">*</span>
                                        </label>
                                        <select
                                            wire:model="mode"
                                            class="block w-full py-2 px-3 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-brand-500"
                                        >
                                            @foreach ($modes as $m)
                                                <option value="{{ $m->value }}">{{ $m->label() }} — {{ $m->description() }}</option>
                                            @endforeach
                                        </select>
                                        @error('mode') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Status Selector -->
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">
                                            {{ __('Publication Status') }} <span class="text-rose-500">*</span>
                                        </label>
                                        <select
                                            wire:model="status"
                                            class="block w-full py-2 px-3 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-brand-500"
                                        >
                                            @foreach ($statuses as $st)
                                                <option value="{{ $st->value }}">{{ $st->label() }}</option>
                                            @endforeach
                                        </select>
                                        @error('status') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Description -->
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">
                                            {{ __('Description / Overview') }}
                                        </label>
                                        <textarea
                                            wire:model="description"
                                            rows="2"
                                            placeholder="{{ __('Short summary of quiz topics and goals...') }}"
                                            class="block w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                                        ></textarea>
                                        @error('description') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Instructions -->
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">
                                            {{ __('Student Instructions') }}
                                        </label>
                                        <textarea
                                            wire:model="instructions"
                                            rows="2"
                                            placeholder="{{ __('Instructions displayed on quiz start...') }}"
                                            class="block w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                                        ></textarea>
                                        @error('instructions') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Time Limit & Max Attempts -->
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">
                                            {{ __('Time Limit (Minutes)') }}
                                        </label>
                                        <input
                                            type="number"
                                            min="0"
                                            wire:model="timeLimitMinutes"
                                            placeholder="{{ __('0 or empty = No time limit') }}"
                                            class="block w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                        />
                                        <p class="text-[11px] text-slate-400 mt-1">{{ __('Leave blank or set to 0 for untimed quizzes.') }}</p>
                                        @error('timeLimitMinutes') <span class="text-xs text-rose-500 block">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">
                                            {{ __('Max Attempts Allowed') }}
                                        </label>
                                        <input
                                            type="number"
                                            min="1"
                                            wire:model="maxAttempts"
                                            placeholder="{{ __('1') }}"
                                            class="block w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                        />
                                        <p class="text-[11px] text-slate-400 mt-1">{{ __('Number of allowed student submissions.') }}</p>
                                        @error('maxAttempts') <span class="text-xs text-rose-500 block">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Pass Percentage & Show Answers Policy -->
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">
                                            {{ __('Passing Percentage (%)') }} <span class="text-rose-500">*</span>
                                        </label>
                                        <input
                                            type="number"
                                            step="1"
                                            min="0"
                                            max="100"
                                            wire:model="passPercentage"
                                            class="block w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                        />
                                        @error('passPercentage') <span class="text-xs text-rose-500 block">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">
                                            {{ __('Show Answers Policy') }} <span class="text-rose-500">*</span>
                                        </label>
                                        <select
                                            wire:model="showAnswersPolicy"
                                            class="block w-full py-2 px-3 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                        >
                                            @foreach ($policies as $pol)
                                                <option value="{{ $pol->value }}">{{ $pol->label() }}</option>
                                            @endforeach
                                        </select>
                                        @error('showAnswersPolicy') <span class="text-xs text-rose-500 block">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Starts At / Ends At -->
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">
                                            {{ __('Availability Window: Starts At') }}
                                        </label>
                                        <input
                                            type="datetime-local"
                                            wire:model="startsAt"
                                            class="block w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                        />
                                        @error('startsAt') <span class="text-xs text-rose-500 block">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">
                                            {{ __('Availability Window: Ends At') }}
                                        </label>
                                        <input
                                            type="datetime-local"
                                            wire:model="endsAt"
                                            class="block w-full px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                        />
                                        @error('endsAt') <span class="text-xs text-rose-500 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- Toggle checkboxes -->
                                <div class="border-t border-slate-200 dark:border-slate-700 pt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <label class="flex items-center gap-2 p-3 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 cursor-pointer">
                                        <input type="checkbox" wire:model="shuffleQuestions" class="rounded text-brand-600 focus:ring-brand-500 border-slate-300" />
                                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ __('Shuffle Questions') }}</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-3 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 cursor-pointer">
                                        <input type="checkbox" wire:model="shuffleOptions" class="rounded text-brand-600 focus:ring-brand-500 border-slate-300" />
                                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ __('Shuffle Options') }}</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-3 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 cursor-pointer">
                                        <input type="checkbox" wire:model="allowNegativeMarking" class="rounded text-brand-600 focus:ring-brand-500 border-slate-300" />
                                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ __('Allow Negative Marking') }}</span>
                                    </label>
                                </div>
                            </div>

                            <!-- TAB 2: Attachment Target -->
                            <div class="{{ $activeModalTab === 'attachment' ? 'space-y-4' : 'hidden' }}">
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ __('Choose whether this quiz is standalone (open assessment) or attach it to an academic class, session, or nursery activity:') }}
                                </p>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <!-- Option 1: Standalone -->
                                    <label class="p-4 rounded-2xl border cursor-pointer transition-colors flex items-start gap-3 {{ $attachmentType === 'standalone' ? 'border-brand-500 bg-brand-50/50 dark:bg-brand-950/30 ring-1 ring-brand-500' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800' }}">
                                        <input type="radio" name="attachment_type_radio" value="standalone" wire:model.live="attachmentType" class="mt-1 text-brand-600 focus:ring-brand-500 border-slate-300" />
                                        <div>
                                            <span class="block text-sm font-bold text-slate-900 dark:text-white">{{ __('Standalone Quiz') }}</span>
                                            <span class="block text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Not linked to any specific class schedule; open to all authorized learners.') }}</span>
                                        </div>
                                    </label>

                                    <!-- Option 2: Class -->
                                    <label class="p-4 rounded-2xl border cursor-pointer transition-colors flex items-start gap-3 {{ $attachmentType === 'class' ? 'border-brand-500 bg-brand-50/50 dark:bg-brand-950/30 ring-1 ring-brand-500' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800' }}">
                                        <input type="radio" name="attachment_type_radio" value="class" wire:model.live="attachmentType" class="mt-1 text-brand-600 focus:ring-brand-500 border-slate-300" />
                                        <div>
                                            <span class="block text-sm font-bold text-slate-900 dark:text-white">{{ __('Attach to Class') }}</span>
                                            <span class="block text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Enrolled students in this specific class will see the quiz.') }}</span>
                                        </div>
                                    </label>

                                    <!-- Option 3: Class Session -->
                                    <label class="p-4 rounded-2xl border cursor-pointer transition-colors flex items-start gap-3 {{ $attachmentType === 'session' ? 'border-brand-500 bg-brand-50/50 dark:bg-brand-950/30 ring-1 ring-brand-500' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800' }}">
                                        <input type="radio" name="attachment_type_radio" value="session" wire:model.live="attachmentType" class="mt-1 text-brand-600 focus:ring-brand-500 border-slate-300" />
                                        <div>
                                            <span class="block text-sm font-bold text-slate-900 dark:text-white">{{ __('Attach to Class Session') }}</span>
                                            <span class="block text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Perfect for in-class live tests or post-session revision.') }}</span>
                                        </div>
                                    </label>

                                    <!-- Option 4: Activity -->
                                    <label class="p-4 rounded-2xl border cursor-pointer transition-colors flex items-start gap-3 {{ $attachmentType === 'activity' ? 'border-brand-500 bg-brand-50/50 dark:bg-brand-950/30 ring-1 ring-brand-500' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800' }}">
                                        <input type="radio" name="attachment_type_radio" value="activity" wire:model.live="attachmentType" class="mt-1 text-brand-600 focus:ring-brand-500 border-slate-300" />
                                        <div>
                                            <span class="block text-sm font-bold text-slate-900 dark:text-white">{{ __('Attach to Activity / Nursery') }}</span>
                                            <span class="block text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Links quiz as an interactive checkpoint for an existing activity.') }}</span>
                                        </div>
                                    </label>
                                </div>

                                <!-- Dynamic Dropdown Selectors based on chosen attachmentType -->
                                <div class="pt-3">
                                    @if ($attachmentType === 'class')
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">
                                            {{ __('Select Target Class') }} <span class="text-rose-500">*</span>
                                        </label>
                                        <select
                                            wire:model="selectedClassId"
                                            class="block w-full py-2.5 px-3 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                        >
                                            <option value="">{{ __('-- Choose a Class --') }}</option>
                                            @foreach ($classes as $c)
                                                <option value="{{ $c->id }}">{{ $c->title }} ({{ $c->subject ?? __('General') }})</option>
                                            @endforeach
                                        </select>
                                        @error('selectedClassId') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror

                                    @elseif ($attachmentType === 'session')
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">
                                            {{ __('Select Target Class Session') }} <span class="text-rose-500">*</span>
                                        </label>
                                        <select
                                            wire:model="selectedSessionId"
                                            class="block w-full py-2.5 px-3 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                        >
                                            <option value="">{{ __('-- Choose a Session --') }}</option>
                                            @foreach ($sessions as $s)
                                                <option value="{{ $s->id }}">
                                                    {{ $s->schoolClass?->title ?? __('Class') }} — {{ $s->title ?? __('Session') }} ({{ $s->date->format('M j, Y') }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('selectedSessionId') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror

                                    @elseif ($attachmentType === 'activity')
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">
                                            {{ __('Select Target Activity') }} <span class="text-rose-500">*</span>
                                        </label>
                                        <select
                                            wire:model="selectedActivityId"
                                            class="block w-full py-2.5 px-3 text-sm rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                                        >
                                            <option value="">{{ __('-- Choose an Activity --') }}</option>
                                            @foreach ($activities as $a)
                                                <option value="{{ $a->id }}">{{ $a->title }}</option>
                                            @endforeach
                                        </select>
                                        @error('selectedActivityId') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                    @endif
                                </div>
                            </div>

                            <!-- TAB 3: Questions Management -->
                            <div class="{{ $activeModalTab === 'questions' ? 'space-y-4' : 'hidden' }}">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-200 dark:border-slate-700">
                                    <div>
                                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">{{ __('Quiz Score Summary') }}</span>
                                        <div class="flex items-center gap-4 mt-0.5">
                                            <span class="text-base font-extrabold text-slate-900 dark:text-white">
                                                {{ count($selectedQuestions) }} {{ __('Questions') }}
                                            </span>
                                            <span class="text-base font-extrabold text-brand-600 dark:text-brand-400">
                                                {{ number_format($this->totalMarks, 1) }} {{ __('Total Points') }}
                                            </span>
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        wire:click="openQuestionBankPicker"
                                        class="inline-flex items-center px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-sm transition-colors"
                                    >
                                        <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        <span>{{ __('Add From Question Bank') }}</span>
                                    </button>
                                </div>

                                @error('status')
                                    <p class="text-xs text-rose-500 font-semibold">{{ $message }}</p>
                                @enderror

                                <!-- Attached questions sequence list -->
                                @if (empty($selectedQuestions))
                                    <div class="py-12 text-center border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-2xl">
                                        <svg class="mx-auto h-10 w-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p class="mt-2 text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('No questions added to this quiz yet.') }}</p>
                                        <p class="text-xs text-slate-400 mt-0.5">{{ __('Click "Add From Question Bank" above to select questions.') }}</p>
                                    </div>
                                @else
                                    <div class="space-y-2.5">
                                        @foreach ($selectedQuestions as $index => $sq)
                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 bg-white dark:bg-slate-700/50 rounded-2xl border border-slate-200 dark:border-slate-600">
                                                <div class="flex items-start sm:items-center gap-3 flex-1">
                                                    <!-- Order position & arrows -->
                                                    <div class="flex items-center gap-1 shrink-0">
                                                        <span class="h-6 w-6 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold flex items-center justify-center">
                                                            {{ $index + 1 }}
                                                        </span>
                                                        <div class="flex flex-col">
                                                            <button
                                                                type="button"
                                                                wire:click="moveQuestionUp({{ $index }})"
                                                                @disabled($index === 0)
                                                                class="text-[10px] text-slate-400 hover:text-slate-700 disabled:opacity-20"
                                                            >
                                                                ▲
                                                            </button>
                                                            <button
                                                                type="button"
                                                                wire:click="moveQuestionDown({{ $index }})"
                                                                @disabled($index === count($selectedQuestions) - 1)
                                                                class="text-[10px] text-slate-400 hover:text-slate-700 disabled:opacity-20"
                                                            >
                                                                ▼
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <!-- Question preview info -->
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex flex-wrap items-center gap-1.5 mb-1">
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border {{ $sq['type_badge'] ?? '' }}">
                                                                {{ $sq['type_label'] ?? $sq['type'] }}
                                                            </span>
                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium border {{ $sq['difficulty_badge'] ?? '' }}">
                                                                {{ ucfirst($sq['difficulty']) }}
                                                            </span>
                                                            @if (!empty($sq['has_image']))
                                                                <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium">📷 {{ __('Image') }}</span>
                                                            @endif
                                                            @if (!empty($sq['has_audio']))
                                                                <span class="text-[10px] text-purple-600 dark:text-purple-400 font-medium">🎵 {{ __('Audio') }}</span>
                                                            @endif
                                                        </div>
                                                        <h4 class="text-sm font-bold text-slate-900 dark:text-white truncate">
                                                            {{ $sq['title'] }}
                                                        </h4>
                                                    </div>
                                                </div>

                                                <!-- Score overrides & Remove -->
                                                <div class="flex items-center gap-3 self-end sm:self-center shrink-0">
                                                    <div class="flex items-center gap-1.5 text-xs">
                                                        <label class="text-slate-500 font-medium">{{ __('Marks:') }}</label>
                                                        <input
                                                            type="number"
                                                            step="0.5"
                                                            min="0.5"
                                                            wire:model.live.debounce.300ms="selectedQuestions.{{ $index }}.marks"
                                                            class="w-16 px-2 py-1 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-center font-bold"
                                                        />
                                                    </div>

                                                    @if ($allowNegativeMarking)
                                                        <div class="flex items-center gap-1.5 text-xs">
                                                            <label class="text-rose-500 font-medium">{{ __('Penalty:') }}</label>
                                                            <input
                                                                type="number"
                                                                step="0.25"
                                                                min="0"
                                                                wire:model.live.debounce.300ms="selectedQuestions.{{ $index }}.penalty_marks"
                                                                class="w-16 px-2 py-1 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-center font-bold"
                                                            />
                                                        </div>
                                                    @endif

                                                    <button
                                                        type="button"
                                                        wire:click="removeQuestionFromQuiz({{ $index }})"
                                                        title="{{ __('Remove from Quiz') }}"
                                                        class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg transition-colors"
                                                    >
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                <span>{{ count($selectedQuestions) }} {{ __('questions attached') }}</span> &bull;
                                <span class="font-bold text-slate-700 dark:text-slate-300">{{ number_format($this->totalMarks, 1) }} {{ __('total pts') }}</span>
                            </div>

                            <div class="flex items-center gap-3">
                                <button
                                    type="button"
                                    wire:click="$set('showQuizModal', false)"
                                    class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors"
                                >
                                    {{ __('Cancel') }}
                                </button>
                                <button
                                    type="submit"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow-sm shadow-brand-600/30 transition-colors"
                                >
                                    <span wire:loading.remove>{{ $isEditing ? __('Update Quiz') : __('Save Quiz') }}</span>
                                    <span wire:loading>{{ __('Saving...') }}</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Question Bank Picker Modal (Step 3 Addition) -->
    @if ($showQuestionBankPicker)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="picker-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" wire:click="$set('showQuestionBankPicker', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-slate-200 dark:border-slate-700">
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <span>📚</span>
                                <span>{{ __('Select Questions from Bank') }}</span>
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                {{ __('Check the questions you want to include in this quiz.') }}
                            </p>
                        </div>
                        <button
                            type="button"
                            wire:click="$set('showQuestionBankPicker', false)"
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Picker Search and Filters -->
                    <div class="p-4 bg-slate-50 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-700 grid grid-cols-1 sm:grid-cols-4 gap-2.5">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="pickerSearch"
                            placeholder="{{ __('Search questions...') }}"
                            class="sm:col-span-2 px-3 py-1.5 text-xs rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                        />
                        <select
                            wire:model.live="pickerType"
                            class="px-2 py-1.5 text-xs rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                        >
                            <option value="all">{{ __('Type: All') }}</option>
                            @foreach ($questionTypes as $qt)
                                <option value="{{ $qt->value }}">{{ $qt->label() }}</option>
                            @endforeach
                        </select>
                        <select
                            wire:model.live="pickerDifficulty"
                            class="px-2 py-1.5 text-xs rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white"
                        >
                            <option value="all">{{ __('Difficulty: All') }}</option>
                            @foreach ($difficulties as $d)
                                <option value="{{ $d->value }}">{{ $d->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Questions List in Picker -->
                    <div class="p-6 max-h-[55vh] overflow-y-auto space-y-2.5">
                        @forelse ($availableQuestions as $availQ)
                            <label class="flex items-start gap-3 p-3 rounded-2xl border transition-colors cursor-pointer {{ in_array($availQ->id, $pickerCheckedQuestionIds) ? 'bg-brand-50/50 dark:bg-brand-950/30 border-brand-500 ring-1 ring-brand-500' : 'bg-white dark:bg-slate-700/40 border-slate-200 dark:border-slate-600 hover:border-brand-300' }}">
                                <input
                                    type="checkbox"
                                    value="{{ $availQ->id }}"
                                    wire:model="pickerCheckedQuestionIds"
                                    class="mt-1 h-4 w-4 rounded text-brand-600 focus:ring-brand-500 border-slate-300"
                                />
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-1.5 mb-1">
                                        <span class="inline-flex items-center px-2 py-0.2 rounded text-[10px] font-semibold border {{ $availQ->type->badgeClass() }}">
                                            {{ $availQ->type->label() }}
                                        </span>
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-medium border {{ $availQ->difficulty->badgeClass() }}">
                                            {{ $availQ->difficulty->label() }}
                                        </span>
                                        <span class="text-[10px] text-slate-500 font-medium">
                                            {{ number_format($availQ->default_marks, 1) }} {{ __('pts') }}
                                        </span>
                                        @if ($availQ->hasImage())
                                            <span class="text-[10px] text-emerald-600">📷</span>
                                        @endif
                                        @if ($availQ->hasAudio())
                                            <span class="text-[10px] text-purple-600">🎵</span>
                                        @endif
                                    </div>
                                    <h4 class="text-xs font-bold text-slate-900 dark:text-white">
                                        {{ $availQ->title }}
                                    </h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-1 mt-0.5">
                                        {{ $availQ->question_text }}
                                    </p>
                                </div>
                            </label>
                        @empty
                            <div class="py-8 text-center text-slate-400 text-xs">
                                {{ __('No matching unselected questions found in the Question Bank.') }}
                            </div>
                        @endforelse
                    </div>

                    <!-- Picker Footer -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
                        <span class="text-xs text-slate-500 dark:text-slate-400">
                            {{ count($pickerCheckedQuestionIds) }} {{ __('selected') }}
                        </span>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                wire:click="$set('showQuestionBankPicker', false)"
                                class="px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 rounded-xl"
                            >
                                {{ __('Cancel') }}
                            </button>
                            <button
                                type="button"
                                wire:click="addSelectedQuestionsToQuiz"
                                class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold rounded-xl shadow-sm"
                            >
                                {{ __('Add Selected (:count)', ['count' => count($pickerCheckedQuestionIds)]) }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Preview Quiz Modal -->
    @if ($showPreviewModal && $previewQuiz)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="preview-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" wire:click="$set('showPreviewModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-slate-200 dark:border-slate-700">
                    <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold border {{ $previewQuiz->mode->badgeClass() }}">
                                    {{ $previewQuiz->mode->label() }}
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold border {{ $previewQuiz->status->badgeClass() }}">
                                    {{ $previewQuiz->status->label() }}
                                </span>
                                <span class="text-xs text-slate-500">📎 {{ $previewQuiz->getAttachmentTargetLabel() }}</span>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                                {{ $previewQuiz->title }}
                            </h3>
                        </div>
                        <button
                            type="button"
                            wire:click="$set('showPreviewModal', false)"
                            class="text-slate-400 hover:text-slate-600 p-1 rounded-lg"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="p-6 max-h-[70vh] overflow-y-auto space-y-6">
                        @if ($previewQuiz->instructions)
                            <div class="p-3.5 bg-blue-50 dark:bg-blue-950/40 rounded-2xl border border-blue-200 dark:border-blue-800 text-xs text-blue-900 dark:text-blue-200">
                                <span class="font-bold">{{ __('Instructions:') }}</span> {{ $previewQuiz->instructions }}
                            </div>
                        @endif

                        <!-- Quiz Questions Preview -->
                        <div class="space-y-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                {{ __('Questions Sequence (:count questions &bull; :pts pts total)', ['count' => $previewQuiz->quizQuestions->count(), 'pts' => number_format($previewQuiz->total_marks, 1)]) }}
                            </h4>

                            @foreach ($previewQuiz->quizQuestions as $index => $qq)
                                @php $q = $qq->question; @endphp
                                @if ($q)
                                    <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/40 space-y-3">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <span class="h-6 w-6 rounded-md bg-brand-100 text-brand-800 font-bold flex items-center justify-center text-xs">
                                                    {{ $index + 1 }}
                                                </span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border {{ $q->type->badgeClass() }}">
                                                    {{ $q->type->label() }}
                                                </span>
                                            </div>
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                                {{ number_format($qq->getEffectiveMarks(), 1) }} {{ __('pts') }}
                                                @if ($qq->getEffectivePenalty() > 0)
                                                    <span class="text-rose-500">(-{{ number_format($qq->getEffectivePenalty(), 1) }})</span>
                                                @endif
                                            </span>
                                        </div>

                                        <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                            {{ $q->question_text }}
                                        </p>

                                        @if ($q->hasImage())
                                            <div class="rounded-xl overflow-hidden max-h-48 flex items-center justify-center bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                                <img src="{{ $q->getImageUrl() }}" alt="Image" class="max-h-48 object-contain" />
                                            </div>
                                        @endif

                                        @if ($q->hasAudio())
                                            <audio controls src="{{ $q->getAudioUrl() }}" class="w-full mt-2"></audio>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Quiz Confirmation Modal -->
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
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Delete Quiz?') }}</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                {{ __('Are you sure you want to delete this quiz? It will be soft-deleted.') }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <button
                            type="button"
                            wire:click="$set('showDeleteModal', false)"
                            class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 rounded-xl"
                        >
                            {{ __('Cancel') }}
                        </button>
                        <button
                            type="button"
                            wire:click="deleteQuiz"
                            class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-xl shadow-sm"
                        >
                            {{ __('Delete') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
