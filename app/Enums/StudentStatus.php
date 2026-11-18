<?php

namespace App\Enums;

enum StudentStatus: string
{
    case Applied = 'applied';
    case LecturerApproved = 'lecturer_approved';
    case Registered = 'registered';
    case Active = 'active';
    case Rejected = 'rejected';
    case Suspended = 'suspended';
    case Left = 'left';

    public function label(): string
    {
        return match ($this) {
            self::Applied => __('Applied'),
            self::LecturerApproved => __('Lecturer Approved'),
            self::Registered => __('Registered'),
            self::Active => __('Active'),
            self::Rejected => __('Rejected'),
            self::Suspended => __('Suspended'),
            self::Left => __('Left'),
        };
    }
}
