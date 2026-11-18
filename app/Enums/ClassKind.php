<?php

namespace App\Enums;

enum ClassKind: string
{
    case NurseryGroup = 'nursery_group';
    case InstituteClass = 'institute_class';

    public function label(): string
    {
        return match ($this) {
            self::NurseryGroup => __('Nursery Group'),
            self::InstituteClass => __('Institute Class'),
        };
    }
}
