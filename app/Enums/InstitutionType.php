<?php

namespace App\Enums;

enum InstitutionType: string
{
    case Nursery = 'nursery';
    case Institute = 'institute';
    case University = 'university';
    case Mixed = 'mixed';

    public function label(): string
    {
        return match ($this) {
            self::Nursery => __('Nursery / Kindergarten'),
            self::Institute => __('Tuition / Vocational Institute'),
            self::University => __('Colleges & Universities'),
            self::Mixed => __('Comprehensive Campus'),
        };
    }
}
