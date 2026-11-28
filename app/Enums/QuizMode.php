<?php

declare(strict_types=1);

namespace App\Enums;

enum QuizMode: string
{
    case Standard = 'standard';
    case Nursery = 'nursery';
    case Live = 'live';
    case Paper = 'paper';

    public function label(): string
    {
        return match ($this) {
            self::Standard => 'Standard',
            self::Nursery => 'Nursery Mode',
            self::Live => 'Live (In-Class)',
            self::Paper => 'Online Paper',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Standard => 'Default interactive quiz with timers, shuffling and instant feedback.',
            self::Nursery => 'Child-friendly UI with large buttons, pictures, audio prompts, and no timer.',
            self::Live => 'Synchronous in-class quiz session coordinated live with the teacher.',
            self::Paper => 'Formal timed online examination paper with paginated navigation.',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Standard => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 border-blue-200 dark:border-blue-800',
            self::Nursery => 'bg-pink-100 text-pink-800 dark:bg-pink-900/40 dark:text-pink-300 border-pink-200 dark:border-pink-800',
            self::Live => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border-amber-200 dark:border-amber-800',
            self::Paper => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300 border-purple-200 dark:border-purple-800',
        };
    }
}
