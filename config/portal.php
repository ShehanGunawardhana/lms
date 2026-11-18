<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Active Modules
    |--------------------------------------------------------------------------
    |
    | Institutions can toggle features on or off depending on their type.
    |
    */
    'modules' => [
        'nursery' => (bool) env('PORTAL_MODULE_NURSERY', true),
        'institute' => (bool) env('PORTAL_MODULE_INSTITUTE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Student Configuration
    |--------------------------------------------------------------------------
    */
    'student' => [
        'id_format' => env('PORTAL_STUDENT_ID_FORMAT', 'INS-{YY}-{SEQ5}'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Attendance Rules
    |--------------------------------------------------------------------------
    */
    'attendance' => [
        'window_before_min' => (int) env('PORTAL_ATTENDANCE_WINDOW_BEFORE_MIN', 30),
        'window_after_min' => (int) env('PORTAL_ATTENDANCE_WINDOW_AFTER_MIN', 30),
        'late_after_min' => (int) env('PORTAL_ATTENDANCE_LATE_AFTER_MIN', 15),
        'block_if_unpaid' => (bool) env('PORTAL_ATTENDANCE_BLOCK_IF_UNPAID', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fee Configuration
    |--------------------------------------------------------------------------
    */
    'fees' => [
        'grace_days' => (int) env('PORTAL_FEES_GRACE_DAYS', 7),
        'reminder_days_before' => (int) env('PORTAL_FEES_REMINDER_DAYS_BEFORE', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Printing Limits
    |--------------------------------------------------------------------------
    */
    'printing' => [
        'max_per_material_per_month' => (int) env('PORTAL_PRINTING_MAX_PER_MATERIAL_PER_MONTH', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Policies & Limits
    |--------------------------------------------------------------------------
    */
    'uploads' => [
        'max_size_mb' => (int) env('PORTAL_UPLOADS_MAX_SIZE_MB', 50),
        'chunk_size_mb' => (int) env('PORTAL_UPLOADS_CHUNK_SIZE_MB', 2),
        'allowed_types' => explode(',', env('PORTAL_UPLOADS_ALLOWED_TYPES', 'mp4,mov,webm,pdf,jpg,png,zip')),
        'zip_max_entries' => (int) env('PORTAL_UPLOADS_ZIP_MAX_ENTRIES', 200),
        'zip_max_uncompressed_mb' => (int) env('PORTAL_UPLOADS_ZIP_MAX_UNCOMPRESSED_MB', 200),
    ],

    /*
    |--------------------------------------------------------------------------
    | Submissions Rules
    |--------------------------------------------------------------------------
    */
    'submissions' => [
        'allow_late' => (bool) env('PORTAL_SUBMISSIONS_ALLOW_LATE', true),
        'lock_after_review' => (bool) env('PORTAL_SUBMISSIONS_LOCK_AFTER_REVIEW', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Quiz Engine
    |--------------------------------------------------------------------------
    */
    'quiz' => [
        'live' => [
            'require_checkin' => (bool) env('PORTAL_QUIZ_LIVE_REQUIRE_CHECKIN', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Private Chat
    |--------------------------------------------------------------------------
    */
    'chat' => [
        'enabled' => (bool) env('PORTAL_CHAT_ENABLED', true),
        'hours' => env('PORTAL_CHAT_HOURS', null),
        'retention_days' => (int) env('PORTAL_CHAT_RETENTION_DAYS', 365),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications Channels
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'channels' => explode(',', env('PORTAL_NOTIFICATIONS_CHANNELS', 'mail,database')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Passwords for Seeders
    |--------------------------------------------------------------------------
    */
    'passwords' => [
        'super_admin' => env('SUPER_ADMIN_PASSWORD', 'password'),
        'admin' => env('ADMIN_PASSWORD', 'password'),
        'teacher' => env('TEACHER_PASSWORD', 'password'),
        'parent' => env('PARENT_PASSWORD', 'password'),
        'student' => env('STUDENT_PASSWORD', 'password'),
        'printing_staff' => env('PRINT_STAFF_PASSWORD', 'password'),
        'cashier' => env('CASHIER_PASSWORD', 'password'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Localization & Timezone
    |--------------------------------------------------------------------------
    */
    'locale' => [
        'default' => 'en',
    ],

    'timezone' => 'UTC',
];
