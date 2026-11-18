<?php

namespace App\Enums;

enum StudentKind: string
{
    case Nursery = 'nursery';
    case Institute = 'institute';

    public function label(): string
    {
        return match ($this) {
            self::Nursery => __('Nursery'),
            self::Institute => __('Institute'),
        };
    }
}
