<?php

namespace App\Enums;

enum UserStatus: string
{
    case Active = 'active';
    case Pending = 'pending';
    case Suspended = 'suspended';
    case Rejected = 'rejected';
    case Deactivated = 'deactivated';

    public function label(): string
    {
        return match ($this) {
            self::Active => __('Active'),
            self::Pending => __('Pending'),
            self::Suspended => __('Suspended'),
            self::Rejected => __('Rejected'),
            self::Deactivated => __('Deactivated'),
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Pending => 'warning',
            self::Suspended => 'danger',
            self::Rejected => 'danger',
            self::Deactivated => 'slate',
        };
    }
}
