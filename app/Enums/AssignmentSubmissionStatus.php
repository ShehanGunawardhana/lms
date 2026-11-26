<?php

declare(strict_types=1);

namespace App\Enums;

enum AssignmentSubmissionStatus: string
{
    case NotSubmitted = 'not_submitted';
    case Submitted = 'submitted';
    case Late = 'late';
    case Returned = 'returned';
    case Graded = 'graded';

    public function label(): string
    {
        return match ($this) {
            self::NotSubmitted => __('Not Submitted'),
            self::Submitted => __('Submitted'),
            self::Late => __('Submitted Late'),
            self::Returned => __('Returned for Revision'),
            self::Graded => __('Graded'),
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::NotSubmitted => 'bg-slate-100 text-slate-600 border-slate-200',
            self::Submitted => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::Late => 'bg-amber-50 text-amber-700 border-amber-200',
            self::Returned => 'bg-orange-50 text-orange-700 border-orange-200',
            self::Graded => 'bg-brand-50 text-brand-700 border-brand-200',
        };
    }
}
