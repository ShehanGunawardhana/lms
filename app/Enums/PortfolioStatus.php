<?php

namespace App\Enums;

enum PortfolioStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Published = 'published';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Pending => __('Pending Approval'),
            self::Published => __('Published (Live)'),
            self::Rejected => __('Revision Required'),
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Draft => 'amber',
            self::Pending => 'brand',
            self::Published => 'emerald',
            self::Rejected => 'rose',
        };
    }
}
