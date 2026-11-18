<?php

namespace App\Enums;

enum RoleName: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Teacher = 'teacher';
    case Parent = 'parent';
    case Student = 'student';
    case PrintingStaff = 'printing_staff';
    case Cashier = 'cashier';
    case AttendanceMarshal = 'attendance_marshal';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => __('Super Admin'),
            self::Admin => __('Admin / Office Staff'),
            self::Teacher => __('Teacher / Lecturer'),
            self::Parent => __('Parent / Guardian'),
            self::Student => __('Student'),
            self::PrintingStaff => __('Printing Staff'),
            self::Cashier => __('Cashier'),
            self::AttendanceMarshal => __('Attendance Marshal'),
        };
    }
}
