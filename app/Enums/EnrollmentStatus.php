<?php

namespace App\Enums;

enum EnrollmentStatus: string
{
    case Active = 'active';
    case Waitlisted = 'waitlisted';
    case Dropped = 'dropped';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Active => __('Active'),
            self::Waitlisted => __('Waitlisted'),
            self::Dropped => __('Dropped'),
            self::Completed => __('Completed'),
        };
    }
}
