<?php

namespace App\Enums;

enum ActivitySubmissionStatus: string
{
    case NotStarted = 'not_started';
    case Submitted = 'submitted';
    case Late = 'late';
    case Reviewed = 'reviewed';
    case Returned = 'returned';

    public function label(): string
    {
        return match ($this) {
            self::NotStarted => __('To Do'),
            self::Submitted => __('Submitted'),
            self::Late => __('Submitted Late'),
            self::Reviewed => __('Reviewed'),
            self::Returned => __('Returned for Correction'),
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::NotStarted => 'bg-slate-100 text-slate-700 border-slate-200',
            self::Submitted => 'bg-sky-50 text-sky-700 border-sky-200',
            self::Late => 'bg-amber-50 text-amber-700 border-amber-200',
            self::Reviewed => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::Returned => 'bg-rose-50 text-rose-700 border-rose-200',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::NotStarted => 'clock',
            self::Submitted => 'paper-airplane',
            self::Late => 'exclamation-triangle',
            self::Reviewed => 'check-circle',
            self::Returned => 'arrow-uturn-left',
        };
    }
}
