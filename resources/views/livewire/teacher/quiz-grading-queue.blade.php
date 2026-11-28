<div class="py-6 sm:py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('teacher.quizzes.index') }}" class="text-xs font-semibold text-primary-600 dark:text-primary-400 hover:underline">
                    &larr; {{ __('Back to Quizzes') }}
                </a>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-zinc-900 dark:text-zinc-100">
                {{ __('Quiz Manual Marking Queue') }}
            </h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                {{ __('Review, evaluate, and provide written feedback for subjective open-ended student answers.') }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('teacher.quizzes.index') }}" class="px-4 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700">
                {{ __('Quiz Builder') }}
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if ($successMessage)
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm font-medium">{{ $successMessage }}</span>
            </div>
            <button wire:click="$set('successMessage', '')" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
    @endif

    {{-- Metrics Bar --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400">
                    {{ __('Pending Evaluation') }}
                </span>
                <div class="text-2xl sm:text-3xl font-black text-zinc-900 dark:text-zinc-100 mt-1">
                    {{ $pendingCount }}
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center text-amber-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                    {{ __('Graded Answers') }}
                </span>
                <div class="text-2xl sm:text-3xl font-black text-zinc-900 dark:text-zinc-100 mt-1">
                    {{ $gradedCount }}
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center text-emerald-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                    {{ __('Total Responses') }}
                </span>
                <div class="text-2xl sm:text-3xl font-black text-zinc-900 dark:text-zinc-100 mt-1">
                    {{ $totalCount }}
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-zinc-100 dark:bg-zinc-700 flex items-center justify-center text-zinc-500 dark:text-zinc-400">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
        </div>
    </div>

    {{-- Filters & Controls Bar --}}
    <div class="bg-white dark:bg-zinc-800 p-4 sm:p-5 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm space-y-4">
        {{-- Status Tabs --}}
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-zinc-100 dark:border-zinc-700/60 pb-4">
            <div class="flex items-center gap-2">
                <button type="button"
                        wire:click="$set('statusFilter', 'pending')"
                        class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition {{ $statusFilter === 'pending' ? 'bg-primary-600 text-white shadow-sm' : 'bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200' }}">
                    {{ __('Needs Review') }} ({{ $pendingCount }})
                </button>
                <button type="button"
                        wire:click="$set('statusFilter', 'graded')"
                        class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition {{ $statusFilter === 'graded' ? 'bg-primary-600 text-white shadow-sm' : 'bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200' }}">
                    {{ __('Graded') }} ({{ $gradedCount }})
                </button>
                <button type="button"
                        wire:click="$set('statusFilter', 'all')"
                        class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition {{ $statusFilter === 'all' ? 'bg-primary-600 text-white shadow-sm' : 'bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200' }}">
                    {{ __('All') }} ({{ $totalCount }})
                </button>
            </div>

            {{-- Search Bar --}}
            <div class="w-full sm:w-72">
                <div class="relative">
                    <input type="text"
                           wire:model.live.debounce.300ms="search"
                           placeholder="{{ __('Search student, quiz, text...') }}"
                           class="w-full pl-9 pr-4 py-2 rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-xs sm:text-sm text-zinc-900 dark:text-zinc-100 shadow-sm focus:border-primary-500 focus:ring-primary-500" />
                    <svg class="w-4 h-4 text-zinc-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Dropdowns Row --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="filter-quiz" class="block text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1">
                    {{ __('Filter by Quiz:') }}
                </label>
                <select id="filter-quiz"
                        wire:model.live="selectedQuizId"
                        class="w-full rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-xs sm:text-sm text-zinc-900 dark:text-zinc-100 py-2 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">{{ __('All Quizzes') }}</option>
                    @foreach ($quizzes as $q)
                        <option value="{{ $q->id }}">{{ $q->title }} ({{ $q->code }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="filter-class" class="block text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1">
                    {{ __('Filter by Class:') }}
                </label>
                <select id="filter-class"
                        wire:model.live="selectedClassId"
                        class="w-full rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 text-xs sm:text-sm text-zinc-900 dark:text-zinc-100 py-2 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">{{ __('All Classes') }}</option>
                    @foreach ($classes as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Queue Items Table --}}
    <div class="bg-white dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700 text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-750 text-zinc-500 dark:text-zinc-400">
                    <tr>
                        <th class="px-5 py-3 text-left font-medium">{{ __('Student') }}</th>
                        <th class="px-5 py-3 text-left font-medium">{{ __('Quiz') }}</th>
                        <th class="px-5 py-3 text-left font-medium">{{ __('Question & Response') }}</th>
                        <th class="px-5 py-3 text-left font-medium">{{ __('Submitted') }}</th>
                        <th class="px-5 py-3 text-left font-medium">{{ __('Score') }}</th>
                        <th class="px-5 py-3 text-right font-medium">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($answers as $ans)
                        <tr class="hover:bg-zinc-50/60 dark:hover:bg-zinc-700/30 transition">
                            {{-- Student --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $ans->attempt?->user?->name ?? __('Unknown Student') }}
                                </div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $ans->attempt?->user?->email }}
                                </div>
                            </td>

                            {{-- Quiz --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="font-medium text-zinc-900 dark:text-zinc-100 max-w-xs truncate">
                                    {{ $ans->attempt?->quiz?->title }}
                                </div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ __('Attempt #:num', ['num' => $ans->attempt?->attempt_number]) }}
                                </div>
                            </td>

                            {{-- Question & Response Preview --}}
                            <td class="px-5 py-4 max-w-md">
                                <div class="font-semibold text-xs text-zinc-800 dark:text-zinc-200 truncate">
                                    {{ $ans->question?->title ?: __('Question') }}
                                </div>
                                <p class="text-xs text-zinc-600 dark:text-zinc-300 line-clamp-2 mt-0.5">
                                    {{ $ans->text_answer ?: __('No response text provided.') }}
                                </p>
                            </td>

                            {{-- Submitted Date --}}
                            <td class="px-5 py-4 whitespace-nowrap text-xs text-zinc-600 dark:text-zinc-300">
                                {{ $ans->attempt?->completed_at ? $ans->attempt->completed_at->translatedFormat('d M Y, h:i A') : __('In Progress') }}
                            </td>

                            {{-- Score / Status --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if ($ans->marks_awarded !== null)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                                        {{ $ans->marks_awarded }} {{ __('Marks') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">
                                        {{ __('Needs Grading') }}
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-5 py-4 whitespace-nowrap text-right">
                                <button type="button"
                                        wire:click="openGradeModal({{ $ans->id }})"
                                        class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ $ans->marks_awarded === null ? 'bg-primary-600 hover:bg-primary-700 text-white shadow-sm' : 'border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700' }}">
                                    {{ $ans->marks_awarded === null ? __('Grade Answer') : __('Edit Grade') }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-zinc-500 dark:text-zinc-400">
                                <svg class="w-12 h-12 mx-auto text-zinc-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="text-base font-semibold text-zinc-800 dark:text-zinc-200">
                                    {{ __('Queue is all caught up!') }}
                                </div>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                    {{ __('There are no pending subjective student responses to grade.') }}
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($answers->hasPages())
            <div class="p-4 border-t border-zinc-200 dark:border-zinc-700">
                {{ $answers->links() }}
            </div>
        @endif
    </div>

    {{-- Grading Modal --}}
    @if ($showGradeModal && $activeAnswer)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-zinc-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl max-w-2xl w-full p-6 space-y-5 border border-zinc-200 dark:border-zinc-700 my-8 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-700 pb-3">
                    <div>
                        <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">
                            {{ __('Grade Short Answer Response') }}
                        </h3>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                            {{ $activeAnswer->attempt?->user?->name }} • {{ $activeAnswer->attempt?->quiz?->title }}
                        </p>
                    </div>
                    <button type="button" wire:click="$set('showGradeModal', false)" class="text-zinc-400 hover:text-zinc-600 text-xl font-bold">&times;</button>
                </div>

                {{-- Question Card --}}
                <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-700/40 border border-zinc-200 dark:border-zinc-700 space-y-3">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            {{ $activeAnswer->question?->title ?: __('Question') }}
                        </span>
                        <span class="font-semibold text-primary-600 dark:text-primary-400">
                            {{ __('Max Marks: :marks', ['marks' => $maxMarks]) }}
                        </span>
                    </div>

                    <div class="text-sm sm:text-base font-medium text-zinc-900 dark:text-zinc-100 leading-relaxed">
                        {!! nl2br(e($activeAnswer->question?->question_text)) !!}
                    </div>

                    @if ($activeAnswer->question?->explanation)
                        <div class="p-3 rounded-lg bg-zinc-100 dark:bg-zinc-700/60 text-xs text-zinc-700 dark:text-zinc-300">
                            <strong>{{ __('Instructor Rubric / Reference:') }}</strong>
                            <p class="mt-0.5">{{ $activeAnswer->question->explanation }}</p>
                        </div>
                    @endif
                </div>

                {{-- Student Response Display --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        {{ __('Student Response:') }}
                    </label>
                    <div class="p-4 rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-sm text-zinc-900 dark:text-zinc-100 leading-relaxed whitespace-pre-wrap font-sans min-h-[90px]">
                        {{ $activeAnswer->text_answer ?: __('No response text submitted.') }}
                    </div>
                </div>

                {{-- Marks & Feedback Inputs --}}
                <div class="space-y-4 pt-2 border-t border-zinc-100 dark:border-zinc-700">
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="marks-input" class="text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                {{ __('Marks Awarded (out of :max):', ['max' => $maxMarks]) }}
                            </label>
                            {{-- Quick set buttons --}}
                            <div class="flex items-center gap-1.5">
                                <button type="button"
                                        wire:click="setScoreQuick(1.0)"
                                        class="px-2 py-0.5 rounded-lg border border-zinc-200 dark:border-zinc-600 bg-zinc-50 dark:bg-zinc-700 text-2xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100">
                                    {{ __('Full (:max)', ['max' => $maxMarks]) }}
                                </button>
                                <button type="button"
                                        wire:click="setScoreQuick(0.5)"
                                        class="px-2 py-0.5 rounded-lg border border-zinc-200 dark:border-zinc-600 bg-zinc-50 dark:bg-zinc-700 text-2xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100">
                                    {{ __('Half (:half)', ['half' => round($maxMarks / 2, 2)]) }}
                                </button>
                                <button type="button"
                                        wire:click="setScoreQuick(0.0)"
                                        class="px-2 py-0.5 rounded-lg border border-zinc-200 dark:border-zinc-600 bg-zinc-50 dark:bg-zinc-700 text-2xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100">
                                    {{ __('Zero (0)') }}
                                </button>
                            </div>
                        </div>

                        <input id="marks-input"
                               type="number"
                               step="0.25"
                               min="0"
                               max="{{ $maxMarks }}"
                               wire:model="marksAwarded"
                               class="w-full sm:w-44 rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 px-3.5 py-2 text-base font-bold text-zinc-900 dark:text-zinc-100 focus:border-primary-500 focus:ring-primary-500" />

                        @if ($gradeError)
                            <p class="text-xs text-rose-600 dark:text-rose-400 mt-1 font-medium">{{ $gradeError }}</p>
                        @endif
                    </div>

                    <div>
                        <label for="feedback-input" class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">
                            {{ __('Teacher Feedback / Remarks:') }}
                        </label>
                        <textarea id="feedback-input"
                                  rows="3"
                                  wire:model="teacherFeedback"
                                  placeholder="{{ __('Provide constructive notes or corrections for the student...') }}"
                                  class="w-full rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 px-3.5 py-2.5 text-sm text-zinc-900 dark:text-zinc-100 focus:border-primary-500 focus:ring-primary-500"></textarea>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-between gap-3 pt-3 border-t border-zinc-100 dark:border-zinc-700">
                    <button type="button"
                            wire:click="$set('showGradeModal', false)"
                            class="px-4 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700">
                        {{ __('Cancel') }}
                    </button>

                    <div class="flex items-center gap-2">
                        <button type="button"
                                wire:click="saveGrade(false)"
                                class="px-5 py-2 rounded-xl bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-100 dark:hover:bg-white text-white dark:text-zinc-900 text-xs font-semibold shadow-sm transition">
                            {{ __('Save Grade') }}
                        </button>
                        <button type="button"
                                wire:click="saveGrade(true)"
                                class="px-5 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold shadow-sm transition">
                            {{ __('Save & Grade Next') }} &rarr;
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
