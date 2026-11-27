<?php

declare(strict_types=1);

namespace App\Enums;

enum QuestionType: string
{
    case SingleChoice = 'single_choice';
    case MultipleChoice = 'multiple_choice';
    case TrueFalse = 'true_false';
    case Matching = 'matching';
    case PictureChoice = 'picture_choice';
    case FillInTheBlank = 'fill_in_the_blank';
    case Ordering = 'ordering';
    case ShortAnswer = 'short_answer';

    public function label(): string
    {
        return match ($this) {
            self::SingleChoice => 'Single Choice',
            self::MultipleChoice => 'Multiple Choice',
            self::TrueFalse => 'True / False',
            self::Matching => 'Matching (Join)',
            self::PictureChoice => 'Picture Answers',
            self::FillInTheBlank => 'Fill in the Blank',
            self::Ordering => 'Ordering',
            self::ShortAnswer => 'Short Answer',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::SingleChoice => 'Select one correct answer from a list of options.',
            self::MultipleChoice => 'Select one or more correct options from a list.',
            self::TrueFalse => 'Decide whether a statement is true or false.',
            self::Matching => 'Match related pairs of text or pictures.',
            self::PictureChoice => 'Identify and choose the correct picture from multiple visual options.',
            self::FillInTheBlank => 'Type in the missing word(s), auto-marked against accepted answers.',
            self::Ordering => 'Arrange a list of items into the correct sequence.',
            self::ShortAnswer => 'Write an open-ended response marked manually by a teacher.',
        };
    }

    public function isAutoMarked(): bool
    {
        return $this !== self::ShortAnswer;
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::SingleChoice => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 border-blue-200 dark:border-blue-800',
            self::MultipleChoice => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800',
            self::TrueFalse => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300 border-purple-200 dark:border-purple-800',
            self::Matching => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
            self::PictureChoice => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border-amber-200 dark:border-amber-800',
            self::FillInTheBlank => 'bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-300 border-teal-200 dark:border-teal-800',
            self::Ordering => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/40 dark:text-cyan-300 border-cyan-200 dark:border-cyan-800',
            self::ShortAnswer => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 border-rose-200 dark:border-rose-800',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::SingleChoice => 'radio-button-checked',
            self::MultipleChoice => 'check-box',
            self::TrueFalse => 'toggle-on',
            self::Matching => 'sync-alt',
            self::PictureChoice => 'image',
            self::FillInTheBlank => 'edit-note',
            self::Ordering => 'sort',
            self::ShortAnswer => 'notes',
        };
    }
}
