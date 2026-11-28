<div class="py-6 sm:py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"
     x-data="quizRunner({
         remainingSeconds: {{ $remainingSeconds !== null ? (int) $remainingSeconds : 'null' }},
         isFinished: {{ ($attempt && $attempt->isFinished()) ? 'true' : 'false' }}
     })">

    {{-- Error / Success Alerts --}}
    @if ($errorMessage)
        <div class="mb-6 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span class="text-sm font-medium">{{ $errorMessage }}</span>
            </div>
            <button wire:click="$set('errorMessage', '')" class="text-rose-500 hover:text-rose-700">&times;</button>
        </div>
    @endif

    @if ($successMessage)
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm font-medium">{{ $successMessage }}</span>
            </div>
            <button wire:click="$set('successMessage', '')" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
    @endif

    {{-- VIEW 1: INTRO / PRE-ATTEMPT OVERVIEW SCREEN --}}
    @if (! $attempt || (! $attempt->isInProgress() && ! $attempt->isFinished()))
        <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden">
            <div class="p-6 sm:p-8 border-b border-zinc-200 dark:border-zinc-700 bg-gradient-to-r from-primary-500/10 via-transparent to-primary-500/5">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-primary-100 text-primary-800 dark:bg-primary-900/50 dark:text-primary-300">
                                {{ $quiz->mode->label() }}
                            </span>
                            @if ($quiz->code)
                                <span class="text-xs font-mono text-zinc-500 dark:text-zinc-400">
                                    {{ $quiz->code }}
                                </span>
                            @endif
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-zinc-900 dark:text-zinc-100">
                            {{ $quiz->title }}
                        </h1>
                    </div>
                </div>

                @if ($quiz->description)
                    <p class="mt-4 text-zinc-600 dark:text-zinc-300 text-sm sm:text-base max-w-4xl leading-relaxed">
                        {{ $quiz->description }}
                    </p>
                @endif
            </div>

            {{-- Quiz Rules & Parameters Grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-6 sm:p-8 bg-zinc-50/50 dark:bg-zinc-800/50 border-b border-zinc-200 dark:border-zinc-700">
                <div class="p-4 rounded-xl bg-white dark:bg-zinc-700/50 border border-zinc-200/80 dark:border-zinc-700">
                    <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                        {{ __('Time Limit') }}
                    </div>
                    <div class="mt-1 text-lg font-bold text-zinc-900 dark:text-zinc-100">
                        {{ $quiz->time_limit_minutes ? __(':min minutes', ['min' => $quiz->time_limit_minutes]) : __('No limit') }}
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-white dark:bg-zinc-700/50 border border-zinc-200/80 dark:border-zinc-700">
                    <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                        {{ __('Questions') }}
                    </div>
                    <div class="mt-1 text-lg font-bold text-zinc-900 dark:text-zinc-100">
                        {{ $quiz->quizQuestions()->count() }}
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-white dark:bg-zinc-700/50 border border-zinc-200/80 dark:border-zinc-700">
                    <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                        {{ __('Total Marks') }}
                    </div>
                    <div class="mt-1 text-lg font-bold text-zinc-900 dark:text-zinc-100">
                        {{ $quiz->total_marks }} <span class="text-xs font-normal text-zinc-500">({{ __('Pass: :pass%', ['pass' => $quiz->pass_percentage]) }})</span>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-white dark:bg-zinc-700/50 border border-zinc-200/80 dark:border-zinc-700">
                    <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                        {{ __('Attempts Allowed') }}
                    </div>
                    <div class="mt-1 text-lg font-bold text-zinc-900 dark:text-zinc-100">
                        @php
                            $attemptsTaken = $quiz->getAttemptsCountForUser(auth()->id());
                        @endphp
                        {{ $attemptsTaken }} / {{ $quiz->max_attempts ? $quiz->max_attempts : '∞' }}
                    </div>
                </div>
            </div>

            {{-- Instructions & Start Button --}}
            <div class="p-6 sm:p-8 space-y-6">
                @if ($quiz->instructions)
                    <div class="prose dark:prose-invert max-w-none text-sm text-zinc-600 dark:text-zinc-300">
                        <h4 class="font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Instructions for Takers') }}</h4>
                        {!! nl2br(e($quiz->instructions)) !!}
                    </div>
                @endif

                {{-- Availability details --}}
                <div class="rounded-xl p-4 bg-zinc-50 dark:bg-zinc-700/30 border border-zinc-200 dark:border-zinc-700 text-xs text-zinc-600 dark:text-zinc-300 space-y-1">
                    @if ($quiz->starts_at)
                        <div><strong>{{ __('Available from:') }}</strong> {{ $quiz->starts_at->translatedFormat('d M Y, h:i A') }}</div>
                    @endif
                    @if ($quiz->ends_at)
                        <div><strong>{{ __('Deadline:') }}</strong> {{ $quiz->ends_at->translatedFormat('d M Y, h:i A') }}</div>
                    @endif
                    <div><strong>{{ __('Negative Marking:') }}</strong> {{ $quiz->allow_negative_marking ? __('Enabled (deductions apply to wrong answers)') : __('Disabled') }}</div>
                </div>

                {{-- Action button --}}
                <div class="pt-4 flex flex-wrap items-center justify-between gap-4">
                    @if ($quiz->isAvailable() && $quiz->canUserAttempt(auth()->id()))
                        <button type="button"
                                wire:click="startAttempt"
                                class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-semibold shadow-sm transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ $attemptsTaken > 0 ? __('Retake Quiz') : __('Start Quiz') }}</span>
                        </button>
                    @elseif (! $quiz->isAvailable())
                        <div class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-zinc-100 dark:bg-zinc-700 text-zinc-500 dark:text-zinc-400 text-sm font-medium">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <span>{{ __('This quiz is currently closed.') }}</span>
                        </div>
                    @else
                        <div class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-zinc-100 dark:bg-zinc-700 text-zinc-500 dark:text-zinc-400 text-sm font-medium">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ __('Maximum attempts reached (:max).', ['max' => $quiz->max_attempts]) }}</span>
                        </div>
                    @endif

                    <a href="{{ url()->previous() }}" class="text-sm font-medium text-zinc-600 dark:text-zinc-400 hover:underline">
                        &larr; {{ __('Back to Coursework') }}
                    </a>
                </div>
            </div>

            {{-- Previous Attempts Table --}}
            @if ($previousAttempts->isNotEmpty())
                <div class="p-6 sm:p-8 border-t border-zinc-200 dark:border-zinc-700 bg-zinc-50/30 dark:bg-zinc-900/30">
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                        {{ __('Your Previous Attempts') }}
                    </h3>

                    <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700 text-sm">
                            <thead class="bg-zinc-50 dark:bg-zinc-800/80 text-zinc-500 dark:text-zinc-400">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium">{{ __('Attempt #') }}</th>
                                    <th class="px-4 py-3 text-left font-medium">{{ __('Date') }}</th>
                                    <th class="px-4 py-3 text-left font-medium">{{ __('Duration') }}</th>
                                    <th class="px-4 py-3 text-left font-medium">{{ __('Score') }}</th>
                                    <th class="px-4 py-3 text-left font-medium">{{ __('Result') }}</th>
                                    <th class="px-4 py-3 text-right font-medium">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach ($previousAttempts as $prev)
                                    <tr>
                                        <td class="px-4 py-3 font-semibold text-zinc-900 dark:text-zinc-100">
                                            #{{ $prev->attempt_number }}
                                        </td>
                                        <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">
                                            {{ $prev->started_at->translatedFormat('d M Y, h:i A') }}
                                        </td>
                                        <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">
                                            {{ gmdate('H:i:s', $prev->time_spent_seconds) }}
                                        </td>
                                        <td class="px-4 py-3 font-semibold text-zinc-900 dark:text-zinc-100">
                                            {{ $prev->score }} / {{ $prev->total_marks }}
                                            <span class="text-xs text-zinc-500 font-normal">({{ $prev->percentage }}%)</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($prev->passed)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                                                    {{ __('Passed') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-300">
                                                    {{ __('Failed') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <button type="button"
                                                    wire:click="$set('attempt', {{ $prev->id }})"
                                                    class="text-xs font-semibold text-primary-600 dark:text-primary-400 hover:underline">
                                                {{ __('View Summary') }}
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

    {{-- VIEW 2: ACTIVE ATTEMPT RUNNER (IN PROGRESS) --}}
    @elseif ($attempt->isInProgress())
        <div class="space-y-6">
            {{-- Top Sticky Control Bar --}}
            <div class="sticky top-16 z-20 bg-white/95 dark:bg-zinc-800/95 backdrop-blur-md border border-zinc-200 dark:border-zinc-700 rounded-2xl shadow-sm p-4 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 text-xs font-bold rounded-lg bg-primary-100 text-primary-800 dark:bg-primary-900/50 dark:text-primary-300">
                        {{ __('Question :current of :total', [
                            'current' => $activeQuestionIndex + 1,
                            'total' => $navigatorData['total'],
                        ]) }}
                    </span>
                    <h2 class="text-base sm:text-lg font-bold text-zinc-900 dark:text-zinc-100 truncate max-w-xs sm:max-w-md">
                        {{ $quiz->title }}
                    </h2>
                </div>

                {{-- Countdown Timer (Alpine.js) --}}
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl border font-mono text-sm font-semibold transition"
                         :class="{
                             'bg-rose-50 text-rose-700 border-rose-300 dark:bg-rose-950/50 dark:border-rose-700 dark:text-rose-300 animate-pulse': isCritical,
                             'bg-amber-50 text-amber-700 border-amber-300 dark:bg-amber-950/50 dark:border-amber-700 dark:text-amber-300': isWarning && !isCritical,
                             'bg-zinc-100 text-zinc-800 border-zinc-200 dark:bg-zinc-700/60 dark:border-zinc-600 dark:text-zinc-200': !isWarning && !isCritical
                         }">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span x-text="formattedTime">{{ __('No limit') }}</span>
                    </div>

                    {{-- Finish & Submit Trigger Button --}}
                    <button type="button"
                            wire:click="$set('showFinishModal', true)"
                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-semibold shadow-sm transition">
                        {{ __('Review & Finish') }}
                    </button>
                </div>
            </div>

            {{-- Main Layout: Question Area + Question Navigator Sidebar --}}
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
                {{-- Question Card (Cols 1-3) --}}
                <div class="lg:col-span-3 space-y-6">
                    @if ($currentQuestion)
                        <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-6 sm:p-8 space-y-6">
                            {{-- Question Header --}}
                            <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-zinc-100 dark:border-zinc-700/60">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-md bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300">
                                        {{ $currentQuestion->type->label() }}
                                    </span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ __('Marks: :marks', ['marks' => $currentQuestion->default_marks]) }}
                                    </span>
                                    @if ($quiz->allow_negative_marking && $currentQuestion->default_penalty_marks > 0)
                                        <span class="text-xs text-rose-500 dark:text-rose-400">
                                            (-{{ $currentQuestion->default_penalty_marks }} penalty)
                                        </span>
                                    @endif
                                </div>

                                {{-- Flag for Review Toggle --}}
                                <button type="button"
                                        wire:click="toggleFlag"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border transition {{ $isFlagged ? 'bg-amber-50 dark:bg-amber-950/50 border-amber-300 text-amber-700 dark:text-amber-300' : 'bg-zinc-50 dark:bg-zinc-700/50 border-zinc-200 dark:border-zinc-600 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100' }}">
                                    <svg class="w-4 h-4 {{ $isFlagged ? 'fill-current text-amber-500' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                                    </svg>
                                    <span>{{ $isFlagged ? __('Flagged for Review') : __('Flag for Review') }}</span>
                                </button>
                            </div>

                            {{-- Question Prompt --}}
                            <div class="text-base sm:text-lg text-zinc-900 dark:text-zinc-100 font-medium leading-relaxed">
                                {!! nl2br(e($currentQuestion->question_text)) !!}
                            </div>

                            {{-- Question Media (Image / Audio) --}}
                            @if ($currentQuestion->image_path)
                                <div class="rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700 max-w-lg bg-zinc-50 dark:bg-zinc-900/50">
                                    <img src="{{ Storage::url($currentQuestion->image_path) }}"
                                         alt="Question Diagram"
                                         class="w-full h-auto max-h-80 object-contain mx-auto" />
                                </div>
                            @endif

                            @if ($currentQuestion->audio_path)
                                <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-900/40 border border-zinc-200 dark:border-zinc-700 max-w-md">
                                    <div class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-2 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                                        </svg>
                                        {{ __('Audio Prompt (Listen carefully)') }}
                                    </div>
                                    <audio controls class="w-full">
                                        <source src="{{ Storage::url($currentQuestion->audio_path) }}">
                                        {{ __('Your browser does not support audio playback.') }}
                                    </audio>
                                </div>
                            @endif

                            {{-- DEDICATED INPUT CONTROLS BY QUESTION TYPE --}}
                            <div class="pt-4 border-t border-zinc-100 dark:border-zinc-700/60">
                                {{-- 1. Single Choice --}}
                                @if ($currentQuestion->type === \App\Enums\QuestionType::SingleChoice)
                                    <div class="space-y-3">
                                        @foreach ($options as $opt)
                                            <label wire:click="selectSingleOption({{ $opt->id }})"
                                                   class="flex items-start gap-3 p-4 rounded-xl border cursor-pointer transition {{ $selectedOptionId === (int) $opt->id ? 'border-primary-500 bg-primary-50/60 dark:bg-primary-950/30 ring-2 ring-primary-500/30' : 'border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700/40' }}">
                                                <input type="radio"
                                                       name="active_single_option"
                                                       value="{{ $opt->id }}"
                                                       {{ $selectedOptionId === (int) $opt->id ? 'checked' : '' }}
                                                       class="mt-1 h-4 w-4 text-primary-600 focus:ring-primary-500 border-zinc-300 dark:border-zinc-600" />
                                                <div class="flex-1">
                                                    <span class="text-sm sm:text-base font-medium text-zinc-900 dark:text-zinc-100">
                                                        {{ $opt->option_text }}
                                                    </span>
                                                    @if ($opt->image_path)
                                                        <div class="mt-2 max-w-xs">
                                                            <img src="{{ Storage::url($opt->image_path) }}" alt="Option image" class="rounded-lg max-h-32 object-contain" />
                                                        </div>
                                                    @endif
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>

                                {{-- 2. Multiple Choice --}}
                                @elseif ($currentQuestion->type === \App\Enums\QuestionType::MultipleChoice)
                                    <div class="space-y-3">
                                        <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-2">
                                            {{ __('Select all that apply:') }}
                                        </p>
                                        @foreach ($options as $opt)
                                            @php
                                                $isSelected = in_array((int) $opt->id, array_map('intval', $selectedOptionIds), true);
                                            @endphp
                                            <label wire:click="toggleMultipleOption({{ $opt->id }})"
                                                   class="flex items-start gap-3 p-4 rounded-xl border cursor-pointer transition {{ $isSelected ? 'border-primary-500 bg-primary-50/60 dark:bg-primary-950/30 ring-2 ring-primary-500/30' : 'border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700/40' }}">
                                                <input type="checkbox"
                                                       value="{{ $opt->id }}"
                                                       {{ $isSelected ? 'checked' : '' }}
                                                       class="mt-1 h-4 w-4 rounded text-primary-600 focus:ring-primary-500 border-zinc-300 dark:border-zinc-600" />
                                                <div class="flex-1">
                                                    <span class="text-sm sm:text-base font-medium text-zinc-900 dark:text-zinc-100">
                                                        {{ $opt->option_text }}
                                                    </span>
                                                    @if ($opt->image_path)
                                                        <div class="mt-2 max-w-xs">
                                                            <img src="{{ Storage::url($opt->image_path) }}" alt="Option image" class="rounded-lg max-h-32 object-contain" />
                                                        </div>
                                                    @endif
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>

                                {{-- 3. True / False --}}
                                @elseif ($currentQuestion->type === \App\Enums\QuestionType::TrueFalse)
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        @foreach ($options as $opt)
                                            <button type="button"
                                                    wire:click="selectSingleOption({{ $opt->id }})"
                                                    class="p-6 rounded-2xl border-2 text-center transition flex flex-col items-center justify-center gap-2 {{ $selectedOptionId === (int) $opt->id ? 'border-primary-500 bg-primary-50 dark:bg-primary-950/40 ring-2 ring-primary-500/30 text-primary-800 dark:text-primary-200 font-bold' : 'border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 text-zinc-700 dark:text-zinc-300 font-semibold' }}">
                                                <span class="text-xl sm:text-2xl">
                                                    {{ $opt->option_text }}
                                                </span>
                                            </button>
                                        @endforeach
                                    </div>

                                {{-- 4. Picture Choice --}}
                                @elseif ($currentQuestion->type === \App\Enums\QuestionType::PictureChoice)
                                    <div>
                                        <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-3">
                                            {{ __('Click on the picture that best answers the question:') }}
                                        </p>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                            @foreach ($options as $opt)
                                                <div wire:click="selectSingleOption({{ $opt->id }})"
                                                     class="cursor-pointer rounded-2xl border-2 overflow-hidden transition flex flex-col {{ $selectedOptionId === (int) $opt->id ? 'border-primary-500 ring-4 ring-primary-500/20 shadow-md' : 'border-zinc-200 dark:border-zinc-700 hover:border-zinc-300' }}">
                                                    <div class="aspect-video sm:aspect-square bg-zinc-100 dark:bg-zinc-900 flex items-center justify-center p-2">
                                                        @if ($opt->image_path)
                                                            <img src="{{ Storage::url($opt->image_path) }}" alt="{{ $opt->option_text }}" class="w-full h-full object-contain" />
                                                        @else
                                                            <svg class="w-10 h-10 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                            </svg>
                                                        @endif
                                                    </div>
                                                    <div class="p-3 bg-white dark:bg-zinc-800 flex items-center gap-2 border-t border-zinc-100 dark:border-zinc-700">
                                                        <input type="radio"
                                                               name="picture_choice_opt"
                                                               {{ $selectedOptionId === (int) $opt->id ? 'checked' : '' }}
                                                               class="h-4 w-4 text-primary-600 focus:ring-primary-500" />
                                                        <span class="text-xs sm:text-sm font-medium text-zinc-800 dark:text-zinc-200 truncate">
                                                            {{ $opt->option_text ?: __('Option') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                {{-- 5. Fill in the Blank --}}
                                @elseif ($currentQuestion->type === \App\Enums\QuestionType::FillInTheBlank)
                                    <div class="space-y-3">
                                        <label for="fitb-input" class="block text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                                            {{ __('Type your exact answer:') }}
                                        </label>
                                        <div class="flex items-center gap-3">
                                            <input id="fitb-input"
                                                   type="text"
                                                   wire:model.blur="textAnswer"
                                                   wire:keydown.enter="saveTextAnswer"
                                                   placeholder="{{ __('Enter answer here...') }}"
                                                   class="block w-full max-w-lg rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 px-4 py-2.5 text-zinc-900 dark:text-zinc-100 text-base shadow-sm focus:border-primary-500 focus:ring-primary-500" />
                                            <button type="button"
                                                    wire:click="saveTextAnswer"
                                                    class="px-4 py-2.5 rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-xs font-semibold text-zinc-800 dark:text-zinc-200">
                                                {{ __('Save') }}
                                            </button>
                                        </div>
                                    </div>

                                {{-- 6. Ordering --}}
                                @elseif ($currentQuestion->type === \App\Enums\QuestionType::Ordering)
                                    <div class="space-y-3">
                                        <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                                            {{ __('Use the Up and Down arrows to arrange the items in the correct order:') }}
                                        </p>

                                        @php
                                            $optionsMap = $currentQuestion->options->keyBy('id');
                                        @endphp

                                        <div class="space-y-2 max-w-xl">
                                            @foreach ($orderingAnswer as $posIndex => $optId)
                                                @php
                                                    $opt = $optionsMap->get($optId);
                                                @endphp
                                                @if ($opt)
                                                    <div class="flex items-center justify-between p-3.5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 shadow-sm">
                                                        <div class="flex items-center gap-3">
                                                            <span class="w-6 h-6 flex items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-700 text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                                                {{ $posIndex + 1 }}
                                                            </span>
                                                            <span class="text-sm sm:text-base font-medium text-zinc-800 dark:text-zinc-200">
                                                                {{ $opt->option_text }}
                                                            </span>
                                                        </div>

                                                        <div class="flex items-center gap-1">
                                                            <button type="button"
                                                                    wire:click="moveOrderItemUp({{ $posIndex }})"
                                                                    {{ $posIndex === 0 ? 'disabled' : '' }}
                                                                    class="p-1.5 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700 disabled:opacity-30 disabled:cursor-not-allowed">
                                                                <svg class="w-4 h-4 text-zinc-600 dark:text-zinc-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                                                </svg>
                                                            </button>
                                                            <button type="button"
                                                                    wire:click="moveOrderItemDown({{ $posIndex }})"
                                                                    {{ $posIndex === count($orderingAnswer) - 1 ? 'disabled' : '' }}
                                                                    class="p-1.5 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700 disabled:opacity-30 disabled:cursor-not-allowed">
                                                                <svg class="w-4 h-4 text-zinc-600 dark:text-zinc-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>

                                {{-- 7. Matching (Join Pairs) --}}
                                @elseif ($currentQuestion->type === \App\Enums\QuestionType::Matching)
                                    <div class="space-y-4"
                                         x-data="matchingRunner()"
                                         x-ref="matchingContainer"
                                         @pointermove.window="onDragMove($event)"
                                         @pointerup.window="endDrag($event)">

                                        <div class="flex flex-wrap items-center justify-between gap-3 pb-2">
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                                <strong>{{ __('How to match:') }}</strong>
                                                <span>{{ __('Tap a left item then tap its right match, or drag a line between the dots.') }}</span>
                                            </div>

                                            <div class="flex items-center gap-3">
                                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300">
                                                    {{ count($matchingAnswer) }} / {{ count($matchingPairs['left']) }} {{ __('Paired') }}
                                                </span>
                                                @if (count($matchingAnswer) > 0)
                                                    <button type="button"
                                                            wire:click="resetMatching"
                                                            class="text-xs font-medium text-rose-600 hover:text-rose-700 dark:text-rose-400 underline">
                                                        {{ __('Clear All Pairs') }}
                                                    </button>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Interactive Matching Grid with SVG Connectors --}}
                                        <div class="relative min-h-[260px] p-2 sm:p-4 rounded-2xl bg-zinc-50/60 dark:bg-zinc-900/40 border border-zinc-200 dark:border-zinc-700 select-none">
                                            {{-- SVG Overlay for Connection Lines --}}
                                            <svg class="absolute inset-0 w-full h-full pointer-events-none z-10">
                                                {{-- Matched Pairs Lines --}}
                                                <template x-for="(line, idx) in lines" :key="line.leftId + '-' + line.rightId">
                                                    <path :d="line.path"
                                                          :stroke="line.color"
                                                          stroke-width="3"
                                                          stroke-linecap="round"
                                                          fill="none" />
                                                </template>

                                                {{-- Active Dragging Line --}}
                                                <template x-if="isDragging">
                                                    <line :x1="dragStart.x"
                                                          :y1="dragStart.y"
                                                          :x2="dragCurrent.x"
                                                          :y2="dragCurrent.y"
                                                          stroke="#3b82f6"
                                                          stroke-width="2.5"
                                                          stroke-dasharray="5 5"
                                                          stroke-linecap="round" />
                                                </template>
                                            </svg>

                                            <div class="grid grid-cols-2 gap-8 sm:gap-16 relative z-20">
                                                {{-- Left Column (Premises) --}}
                                                <div class="space-y-4">
                                                    <div class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 pb-1">
                                                        {{ __('Premises (Left)') }}
                                                    </div>

                                                    @foreach ($matchingPairs['left'] as $leftItem)
                                                        @php
                                                            $isPaired = isset($matchingAnswer[$leftItem['id']]);
                                                            $isSelected = ($selectedMatchingLeftId === $leftItem['id']);
                                                        @endphp
                                                        <div data-matching-left="{{ $leftItem['id'] }}"
                                                             data-paired-right="{{ $isPaired ? $matchingAnswer[$leftItem['id']] : '' }}"
                                                             wire:click="selectMatchingLeft({{ $leftItem['id'] }})"
                                                             class="relative p-3 sm:p-4 rounded-xl border-2 transition cursor-pointer flex items-center justify-between gap-3 {{ $isSelected ? 'border-primary-500 bg-primary-50 dark:bg-primary-950/40 ring-2 ring-primary-500/40 shadow-sm' : ($isPaired ? 'border-emerald-300 dark:border-emerald-700 bg-emerald-50/40 dark:bg-emerald-950/20' : 'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:border-zinc-300') }}">
                                                            <div class="flex items-center gap-3 overflow-hidden">
                                                                @if ($leftItem['image_url'])
                                                                    <div class="w-12 h-12 rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 flex-shrink-0 bg-white dark:bg-zinc-900">
                                                                        <img src="{{ $leftItem['image_url'] }}" alt="Premise image" class="w-full h-full object-cover" />
                                                                    </div>
                                                                @endif
                                                                <span class="text-xs sm:text-sm font-semibold text-zinc-800 dark:text-zinc-200 truncate">
                                                                    {{ $leftItem['text'] ?: __('Item') }}
                                                                </span>
                                                            </div>

                                                            <div class="flex items-center gap-2">
                                                                @if ($isPaired)
                                                                    <button type="button"
                                                                            wire:click.stop="unpairMatching({{ $leftItem['id'] }})"
                                                                            title="{{ __('Unpair') }}"
                                                                            class="p-1 rounded-full text-zinc-400 hover:text-rose-600 hover:bg-zinc-100 dark:hover:bg-zinc-700">
                                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                                        </svg>
                                                                    </button>
                                                                @endif

                                                                {{-- Left Node Anchor Dot for Dragging/Touch --}}
                                                                <div class="node-dot w-4 h-4 rounded-full border-2 transition flex-shrink-0 cursor-crosshair {{ $isPaired ? 'bg-emerald-500 border-emerald-600' : ($isSelected ? 'bg-primary-500 border-primary-600 animate-pulse' : 'bg-zinc-200 dark:bg-zinc-600 border-zinc-400 dark:border-zinc-500') }}"
                                                                     @pointerdown.stop="startDrag($event, {{ $leftItem['id'] }})"></div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                {{-- Right Column (Targets) --}}
                                                <div class="space-y-4">
                                                    <div class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 pb-1 text-right">
                                                        {{ __('Targets (Right)') }}
                                                    </div>

                                                    @foreach ($matchingPairs['right'] as $rightItem)
                                                        @php
                                                            $isPairedToLeft = in_array($rightItem['id'], $matchingAnswer, true);
                                                        @endphp
                                                        <div data-matching-right="{{ $rightItem['id'] }}"
                                                             wire:click="selectMatchingRight({{ $rightItem['id'] }})"
                                                             class="relative p-3 sm:p-4 rounded-xl border-2 transition cursor-pointer flex items-center justify-between gap-3 {{ $isPairedToLeft ? 'border-emerald-300 dark:border-emerald-700 bg-emerald-50/40 dark:bg-emerald-950/20' : 'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:border-zinc-300' }}">
                                                            {{-- Right Node Anchor Dot --}}
                                                            <div class="node-dot w-4 h-4 rounded-full border-2 transition flex-shrink-0 {{ $isPairedToLeft ? 'bg-emerald-500 border-emerald-600' : 'bg-zinc-200 dark:bg-zinc-600 border-zinc-400 dark:border-zinc-500' }}"></div>

                                                            <div class="flex items-center gap-3 overflow-hidden text-right justify-end flex-1">
                                                                <span class="text-xs sm:text-sm font-semibold text-zinc-800 dark:text-zinc-200 truncate">
                                                                    {{ $rightItem['text'] ?: __('Target') }}
                                                                </span>
                                                                @if ($rightItem['image_url'])
                                                                    <div class="w-12 h-12 rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 flex-shrink-0 bg-white dark:bg-zinc-900">
                                                                        <img src="{{ $rightItem['image_url'] }}" alt="Target image" class="w-full h-full object-cover" />
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                {{-- 8. Short Answer --}}
                                @elseif ($currentQuestion->type === \App\Enums\QuestionType::ShortAnswer)
                                    <div class="space-y-3">
                                        <label for="short-answer-input" class="block text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                                            {{ __('Your written response (evaluated manually by your teacher):') }}
                                        </label>
                                        <textarea id="short-answer-input"
                                                  rows="5"
                                                  wire:model.blur="textAnswer"
                                                  placeholder="{{ __('Type your comprehensive response here...') }}"
                                                  class="block w-full rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-700 px-4 py-3 text-zinc-900 dark:text-zinc-100 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                                        <div class="flex justify-end">
                                            <button type="button"
                                                    wire:click="saveTextAnswer"
                                                    class="px-4 py-2 rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-xs font-semibold text-zinc-800 dark:text-zinc-200">
                                                {{ __('Save Response') }}
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Navigation Buttons Bar --}}
                            <div class="pt-6 border-t border-zinc-100 dark:border-zinc-700/60 flex items-center justify-between gap-4">
                                <button type="button"
                                        wire:click="previousQuestion"
                                        {{ $activeQuestionIndex === 0 ? 'disabled' : '' }}
                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 text-xs sm:text-sm font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700 disabled:opacity-40 disabled:cursor-not-allowed">
                                    &larr; {{ __('Previous') }}
                                </button>

                                <div class="flex items-center gap-3">
                                    @if ($activeQuestionIndex < $navigatorData['total'] - 1)
                                        <button type="button"
                                                wire:click="nextQuestion"
                                                class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs sm:text-sm font-semibold shadow-sm transition">
                                            <span>{{ __('Next') }}</span> &rarr;
                                        </button>
                                    @else
                                        <button type="button"
                                                wire:click="$set('showFinishModal', true)"
                                                class="inline-flex items-center gap-2 px-6 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-semibold shadow-sm transition">
                                            <span>{{ __('Finish Quiz') }}</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Question Navigator Sidebar (Col 4) --}}
                <div class="lg:col-span-1 space-y-4">
                    <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-700 p-5 space-y-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Question Navigator') }}
                        </h3>

                        {{-- Progress Counts --}}
                        <div class="flex items-center justify-between text-xs text-zinc-600 dark:text-zinc-300 pb-3 border-b border-zinc-100 dark:border-zinc-700">
                            <span>{{ __('Answered:') }} <strong>{{ $navigatorData['answered'] }}/{{ $navigatorData['total'] }}</strong></span>
                            @if ($navigatorData['flagged'] > 0)
                                <span class="text-amber-600 dark:text-amber-400 font-semibold">{{ __('Flagged:') }} {{ $navigatorData['flagged'] }}</span>
                            @endif
                        </div>

                        {{-- Question Grid --}}
                        <div class="grid grid-cols-5 gap-2">
                            @foreach ($navigatorData['items'] as $item)
                                <button type="button"
                                        wire:click="goToQuestion({{ $item['index'] }})"
                                        class="h-9 w-full rounded-xl text-xs font-bold relative flex items-center justify-center transition border {{ $item['is_active'] ? 'ring-2 ring-primary-500 border-primary-500 font-extrabold' : '' }} {{ $item['is_answered'] ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border-emerald-300 dark:border-emerald-700' : 'bg-zinc-50 dark:bg-zinc-700/40 text-zinc-700 dark:text-zinc-300 border-zinc-200 dark:border-zinc-600 hover:bg-zinc-100' }}">
                                    <span>{{ $item['index'] + 1 }}</span>
                                    @if ($item['is_flagged'])
                                        <span class="absolute -top-1 -right-1 w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                    @endif
                                </button>
                            @endforeach
                        </div>

                        {{-- Legend --}}
                        <div class="pt-3 border-t border-zinc-100 dark:border-zinc-700 text-2xs space-y-1.5 text-zinc-500 dark:text-zinc-400">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded bg-emerald-100 dark:bg-emerald-900 border border-emerald-300"></span>
                                <span>{{ __('Answered') }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded bg-zinc-100 dark:bg-zinc-700 border border-zinc-300"></span>
                                <span>{{ __('Unanswered') }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                <span>{{ __('Flagged for Review') }}</span>
                            </div>
                        </div>

                        <button type="button"
                                wire:click="$set('showFinishModal', true)"
                                class="w-full mt-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition">
                            {{ __('Submit Quiz') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Finish & Submit Confirmation Modal --}}
        @if ($showFinishModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-zinc-900/60 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-xl max-w-md w-full p-6 space-y-5 border border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center text-emerald-600 flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">{{ __('Ready to Submit?') }}</h3>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Please confirm that you want to finish your attempt.') }}</p>
                        </div>
                    </div>

                    {{-- Stats Summary --}}
                    <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-700/40 border border-zinc-200 dark:border-zinc-700 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-zinc-600 dark:text-zinc-400">{{ __('Total Questions:') }}</span>
                            <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $navigatorData['total'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-600 dark:text-zinc-400">{{ __('Answered Questions:') }}</span>
                            <span class="font-bold text-emerald-600">{{ $navigatorData['answered'] }}</span>
                        </div>
                        @if ($navigatorData['unanswered'] > 0)
                            <div class="flex justify-between text-rose-600 font-semibold">
                                <span>{{ __('Unanswered Questions:') }}</span>
                                <span>{{ $navigatorData['unanswered'] }}</span>
                            </div>
                        @endif
                        @if ($navigatorData['flagged'] > 0)
                            <div class="flex justify-between text-amber-600 font-semibold">
                                <span>{{ __('Flagged for Review:') }}</span>
                                <span>{{ $navigatorData['flagged'] }}</span>
                            </div>
                        @endif
                    </div>

                    @if ($navigatorData['unanswered'] > 0)
                        <p class="text-xs text-rose-600 dark:text-rose-400 font-medium">
                            {{ __('Warning: You still have :count unanswered questions. Unanswered questions will receive 0 marks.', ['count' => $navigatorData['unanswered']]) }}
                        </p>
                    @endif

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button"
                                wire:click="$set('showFinishModal', false)"
                                class="px-4 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-700">
                            {{ __('Return to Questions') }}
                        </button>
                        <button type="button"
                                wire:click="submitAttempt"
                                class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition">
                            {{ __('Confirm & Submit') }}
                        </button>
                    </div>
                </div>
            </div>
        @endif

    {{-- VIEW 3: ATTEMPT RESULTS SCREEN (COMPLETED / TIMED OUT) --}}
    @elseif ($attempt->isFinished())
        <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-700 overflow-hidden space-y-6">
            {{-- Results Banner --}}
            <div class="p-6 sm:p-8 border-b border-zinc-200 dark:border-zinc-700 {{ $attempt->passed ? 'bg-gradient-to-r from-emerald-500/10 via-transparent to-emerald-500/5' : 'bg-gradient-to-r from-rose-500/10 via-transparent to-rose-500/5' }}">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $attempt->passed ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-300' }}">
                                {{ $attempt->passed ? __('Passed') : __('Did Not Pass') }}
                            </span>
                            @if ($attempt->isTimedOut())
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">
                                    {{ __('Time Expired') }}
                                </span>
                            @endif
                            @if (! $attempt->is_fully_graded)
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                                    {{ __('Pending Instructor Grading') }}
                                </span>
                            @endif
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-zinc-900 dark:text-zinc-100">
                            {{ __('Attempt Summary: :quiz', ['quiz' => $quiz->title]) }}
                        </h1>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                            {{ __('Attempt #:number • Completed on :date', [
                                'number' => $attempt->attempt_number,
                                'date' => $attempt->completed_at ? $attempt->completed_at->translatedFormat('d M Y, h:i A') : '',
                            ]) }}
                        </p>
                    </div>

                    {{-- Score Badge --}}
                    <div class="text-center sm:text-right p-4 rounded-2xl bg-white dark:bg-zinc-700/60 border border-zinc-200 dark:border-zinc-600 shadow-sm">
                        <div class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            {{ __('Your Score') }}
                        </div>
                        <div class="text-3xl sm:text-4xl font-black {{ $attempt->passed ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $attempt->score }} <span class="text-lg font-normal text-zinc-400">/ {{ $attempt->total_marks }}</span>
                        </div>
                        <div class="text-sm font-bold text-zinc-700 dark:text-zinc-300 mt-0.5">
                            {{ $attempt->percentage }}% <span class="text-xs font-normal text-zinc-500">({{ __('Passing: :p%', ['p' => $quiz->pass_percentage]) }})</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Metrics Row --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 px-6 sm:px-8">
                <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-700/40 border border-zinc-200 dark:border-zinc-700">
                    <div class="text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wider font-semibold">{{ __('Time Spent') }}</div>
                    <div class="text-lg font-bold text-zinc-900 dark:text-zinc-100 mt-1">{{ gmdate('H:i:s', $attempt->time_spent_seconds) }}</div>
                </div>

                <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-700/40 border border-zinc-200 dark:border-zinc-700">
                    <div class="text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wider font-semibold">{{ __('Attempt Number') }}</div>
                    <div class="text-lg font-bold text-zinc-900 dark:text-zinc-100 mt-1">#{{ $attempt->attempt_number }}</div>
                </div>

                <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-700/40 border border-zinc-200 dark:border-zinc-700">
                    <div class="text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wider font-semibold">{{ __('Status') }}</div>
                    <div class="text-lg font-bold text-zinc-900 dark:text-zinc-100 mt-1">{{ $attempt->status->label() }}</div>
                </div>

                <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-700/40 border border-zinc-200 dark:border-zinc-700">
                    <div class="text-xs text-zinc-500 dark:text-zinc-400 uppercase tracking-wider font-semibold">{{ __('Remaining Attempts') }}</div>
                    <div class="text-lg font-bold text-zinc-900 dark:text-zinc-100 mt-1">
                        @php
                            $rem = $quiz->getRemainingAttemptsForUser(auth()->id());
                        @endphp
                        {{ $rem === null ? '∞' : $rem }}
                    </div>
                </div>
            </div>

            {{-- DETAILED QUESTION BREAKDOWN (If permitted by policy) --}}
            @if ($showAnswers)
                <div class="p-6 sm:p-8 space-y-6 border-t border-zinc-200 dark:border-zinc-700">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">
                        {{ __('Detailed Question Review') }}
                    </h2>

                    <div class="space-y-6">
                        @foreach ($attempt->getQuestionsSequence() as $idx => $q)
                            @php
                                $ans = $attempt->answers->firstWhere('question_id', $q->id);
                            @endphp
                            <div class="p-5 rounded-2xl border {{ $ans && $ans->is_correct === true ? 'border-emerald-200 dark:border-emerald-800 bg-emerald-50/30 dark:bg-emerald-950/20' : ($ans && $ans->is_correct === false ? 'border-rose-200 dark:border-rose-800 bg-rose-50/30 dark:bg-rose-950/20' : 'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800') }} space-y-3">
                                <div class="flex items-center justify-between gap-3 text-xs">
                                    <span class="font-bold text-zinc-500 dark:text-zinc-400">
                                        {{ __('Question :num', ['num' => $idx + 1]) }} • {{ $q->type->label() }}
                                    </span>
                                    <div>
                                        @if ($ans && $ans->is_correct === true)
                                            <span class="px-2 py-0.5 rounded-full font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">
                                                +{{ $ans->marks_awarded }} {{ __('Marks') }}
                                            </span>
                                        @elseif ($ans && $ans->is_correct === false)
                                            <span class="px-2 py-0.5 rounded-full font-bold bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-300">
                                                {{ $ans->marks_awarded }} {{ __('Marks') }}
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full font-bold bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">
                                                {{ __('Pending Manual Review') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="text-sm sm:text-base font-medium text-zinc-900 dark:text-zinc-100">
                                    {!! nl2br(e($q->question_text)) !!}
                                </div>

                                {{-- Display Answer details --}}
                                <div class="text-xs space-y-1.5 pt-2 border-t border-zinc-100 dark:border-zinc-700">
                                    @if ($ans && $ans->selected_option_id)
                                        @php
                                            $selectedOpt = $q->options->firstWhere('id', $ans->selected_option_id);
                                        @endphp
                                        <div>
                                            <strong class="text-zinc-600 dark:text-zinc-400">{{ __('Your Answer:') }}</strong>
                                            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $selectedOpt?->option_text ?? 'N/A' }}</span>
                                        </div>
                                    @elseif ($ans && !empty($ans->selected_option_ids))
                                        @php
                                            $selectedOpts = $q->options->whereIn('id', $ans->selected_option_ids)->pluck('option_text')->implode(', ');
                                        @endphp
                                        <div>
                                            <strong class="text-zinc-600 dark:text-zinc-400">{{ __('Your Selection(s):') }}</strong>
                                            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $selectedOpts }}</span>
                                        </div>
                                    @elseif ($ans && $ans->text_answer)
                                        <div>
                                            <strong class="text-zinc-600 dark:text-zinc-400">{{ __('Your Answer:') }}</strong>
                                            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $ans->text_answer }}</span>
                                        </div>
                                    @else
                                        <div class="text-zinc-400 italic">
                                            {{ __('No answer provided.') }}
                                        </div>
                                    @endif

                                    {{-- Correct Answer details for objective questions --}}
                                    @if ($q->type === \App\Enums\QuestionType::SingleChoice || $q->type === \App\Enums\QuestionType::TrueFalse || $q->type === \App\Enums\QuestionType::PictureChoice)
                                        @php
                                            $correctOpt = $q->options->firstWhere('is_correct', true);
                                        @endphp
                                        <div class="text-emerald-700 dark:text-emerald-400">
                                            <strong>{{ __('Correct Answer:') }}</strong>
                                            <span>{{ $correctOpt?->option_text ?? 'N/A' }}</span>
                                        </div>
                                    @elseif ($q->type === \App\Enums\QuestionType::MultipleChoice)
                                        @php
                                            $correctOpts = $q->options->where('is_correct', true)->pluck('option_text')->implode(', ');
                                        @endphp
                                        <div class="text-emerald-700 dark:text-emerald-400">
                                            <strong>{{ __('Correct Answers:') }}</strong>
                                            <span>{{ $correctOpts }}</span>
                                        </div>
                                    @elseif ($q->type === \App\Enums\QuestionType::Matching)
                                        <div class="mt-2 space-y-2">
                                            <strong class="text-zinc-600 dark:text-zinc-400">{{ __('Matching Pairs Review:') }}</strong>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-1">
                                                @foreach ($q->options as $opt)
                                                    @php
                                                        $matchingAnsArray = (array) ($ans?->matching_answer ?? []);
                                                        $matchedRightId = $matchingAnsArray[$opt->id] ?? $matchingAnsArray[(string) $opt->id] ?? null;
                                                        $matchedOption = $matchedRightId ? $q->options->firstWhere('id', (int) $matchedRightId) : null;
                                                        $isPairCorrect = ($matchedRightId !== null && (int) $matchedRightId === (int) $opt->id);
                                                    @endphp
                                                    <div class="p-2.5 rounded-lg border text-xs flex items-center justify-between {{ $isPairCorrect ? 'border-emerald-200 dark:border-emerald-800 bg-emerald-50/50 dark:bg-emerald-950/30' : 'border-rose-200 dark:border-rose-800 bg-rose-50/50 dark:bg-rose-950/30' }}">
                                                        <div class="space-y-1">
                                                            <div>
                                                                <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $opt->option_text ?: __('Premise') }}</span>
                                                                <span class="text-zinc-400">&rarr;</span>
                                                                <span class="font-medium {{ $isPairCorrect ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300 line-through' }}">
                                                                    {{ $matchedOption ? ($matchedOption->match_text ?: __('Target')) : __('Not matched') }}
                                                                </span>
                                                            </div>
                                                            @if (! $isPairCorrect)
                                                                <div class="text-emerald-700 dark:text-emerald-400 font-medium">
                                                                    {{ __('Correct match:') }} {{ $opt->match_text }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            @if ($isPairCorrect)
                                                                <span class="text-emerald-600 font-bold">&check;</span>
                                                            @else
                                                                <span class="text-rose-600 font-bold">&cross;</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @if ($ans && $ans->feedback)
                                        <div class="mt-2 p-2.5 rounded-lg bg-primary-50 dark:bg-primary-950/40 border border-primary-200 dark:border-primary-800 text-primary-900 dark:text-primary-100">
                                            <strong>{{ __('Instructor Feedback:') }}</strong> {{ $ans->feedback }}
                                        </div>
                                    @endif

                                    @if ($q->explanation)
                                        <div class="mt-2 p-2.5 rounded-lg bg-zinc-100 dark:bg-zinc-700/60 text-zinc-700 dark:text-zinc-300">
                                            <strong>{{ __('Explanation:') }}</strong> {{ $q->explanation }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="p-6 sm:p-8 border-t border-zinc-200 dark:border-zinc-700">
                    <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-700/30 border border-zinc-200 dark:border-zinc-700 text-xs text-zinc-600 dark:text-zinc-400 flex items-center gap-3">
                        <svg class="w-5 h-5 text-zinc-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ __('Detailed answer explanations will be made available according to your institution\'s quiz policy.') }}</span>
                    </div>
                </div>
            @endif

            {{-- Footer Actions --}}
            <div class="p-6 sm:p-8 border-t border-zinc-200 dark:border-zinc-700 flex flex-wrap items-center justify-between gap-4">
                @if ($quiz->canUserAttempt(auth()->id()))
                    <button type="button"
                            wire:click="startAttempt"
                            class="px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-semibold text-sm shadow-sm transition">
                        {{ __('Retake Quiz') }}
                    </button>
                @endif

                <a href="{{ route('quizzes.index') }}" class="text-sm font-semibold text-zinc-600 dark:text-zinc-400 hover:underline">
                    &larr; {{ __('Back to Quizzes') }}
                </a>
            </div>
        </div>
    @endif
</div>

{{-- Alpine.js Timer Script --}}
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('quizRunner', (config) => ({
        remainingSeconds: config.remainingSeconds,
        isFinished: config.isFinished,
        timerInterval: null,

        init() {
            if (this.remainingSeconds !== null && !this.isFinished) {
                this.timerInterval = setInterval(() => {
                    if (this.remainingSeconds > 0) {
                        this.remainingSeconds--;
                    } else {
                        clearInterval(this.timerInterval);
                        @this.autoSubmitTimedOut();
                    }
                }, 1000);
            }
        },

        get formattedTime() {
            if (this.remainingSeconds === null) {
                return '{{ __('No limit') }}';
            }
            if (this.remainingSeconds <= 0) {
                return '00:00';
            }
            const hours = Math.floor(this.remainingSeconds / 3600);
            const minutes = Math.floor((this.remainingSeconds % 3600) / 60);
            const seconds = this.remainingSeconds % 60;

            if (hours > 0) {
                return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            }
            return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        },

        get isWarning() {
            return this.remainingSeconds !== null && this.remainingSeconds <= 300 && this.remainingSeconds > 60;
        },

        get isCritical() {
            return this.remainingSeconds !== null && this.remainingSeconds <= 60;
        }
    }));

    Alpine.data('matchingRunner', () => ({
        isDragging: false,
        dragLeftId: null,
        dragStart: { x: 0, y: 0 },
        dragCurrent: { x: 0, y: 0 },
        colors: ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b', '#ec4899', '#06b6d4', '#14b8a6', '#f97316'],
        lines: [],

        init() {
            this.$nextTick(() => this.updateLines());
            window.addEventListener('resize', () => this.updateLines());
            if (window.Livewire) {
                Livewire.hook('morph.updated', () => {
                    this.$nextTick(() => this.updateLines());
                });
            }
        },

        updateLines() {
            const container = this.$refs.matchingContainer;
            if (!container) return;
            const rect = container.getBoundingClientRect();
            const newLines = [];
            let colorIdx = 0;

            const leftNodes = container.querySelectorAll('[data-matching-left]');
            leftNodes.forEach(leftEl => {
                const leftId = leftEl.getAttribute('data-matching-left');
                const rightId = leftEl.getAttribute('data-paired-right');
                if (rightId) {
                    const rightEl = container.querySelector(`[data-matching-right="${rightId}"]`);
                    if (rightEl) {
                        const lDot = leftEl.querySelector('.node-dot') || leftEl;
                        const rDot = rightEl.querySelector('.node-dot') || rightEl;
                        const lRect = lDot.getBoundingClientRect();
                        const rRect = rDot.getBoundingClientRect();

                        const x1 = lRect.left + lRect.width / 2 - rect.left;
                        const y1 = lRect.top + lRect.height / 2 - rect.top;
                        const x2 = rRect.left + rRect.width / 2 - rect.left;
                        const y2 = rRect.top + rRect.height / 2 - rect.top;

                        const color = this.colors[colorIdx % this.colors.length];
                        colorIdx++;

                        const midX = (x1 + x2) / 2;
                        const path = `M ${x1} ${y1} C ${midX} ${y1}, ${midX} ${y2}, ${x2} ${y2}`;

                        newLines.push({ leftId, rightId, path, color, x1, y1, x2, y2 });
                    }
                }
            });

            this.lines = newLines;
        },

        startDrag(e, leftId) {
            const container = this.$refs.matchingContainer;
            if (!container) return;
            const rect = container.getBoundingClientRect();
            const clientX = e.clientX || (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
            const clientY = e.clientY || (e.touches && e.touches[0] ? e.touches[0].clientY : 0);

            this.isDragging = true;
            this.dragLeftId = leftId;
            this.dragStart = { x: clientX - rect.left, y: clientY - rect.top };
            this.dragCurrent = { x: clientX - rect.left, y: clientY - rect.top };
        },

        onDragMove(e) {
            if (!this.isDragging) return;
            const container = this.$refs.matchingContainer;
            if (!container) return;
            const rect = container.getBoundingClientRect();
            const clientX = e.clientX || (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
            const clientY = e.clientY || (e.touches && e.touches[0] ? e.touches[0].clientY : 0);

            this.dragCurrent = { x: clientX - rect.left, y: clientY - rect.top };
        },

        endDrag(e) {
            if (!this.isDragging) return;
            this.isDragging = false;

            const clientX = e.clientX || (e.changedTouches && e.changedTouches[0] ? e.changedTouches[0].clientX : 0);
            const clientY = e.clientY || (e.changedTouches && e.changedTouches[0] ? e.changedTouches[0].clientY : 0);

            if (clientX && clientY) {
                const targetEl = document.elementFromPoint(clientX, clientY);
                const rightCard = targetEl ? targetEl.closest('[data-matching-right]') : null;
                if (rightCard) {
                    const rightId = rightCard.getAttribute('data-matching-right');
                    if (rightId && this.dragLeftId) {
                        @this.pairMatching(parseInt(this.dragLeftId), parseInt(rightId));
                    }
                }
            }

            this.dragLeftId = null;
        }
    }));
});
</script>
