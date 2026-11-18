<?php

use App\Models\Institution;
use App\Services\InstitutionContext;
use App\Services\SettingsService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Contracts\Activity;

if (! function_exists('current_institution')) {
    /**
     * Get the current active institution model.
     */
    function current_institution(): ?Institution
    {
        return app(InstitutionContext::class)->get();
    }
}

if (! function_exists('current_institution_id')) {
    /**
     * Get the ID of the current active institution.
     */
    function current_institution_id(): ?int
    {
        return app(InstitutionContext::class)->id();
    }
}

if (! function_exists('portal_setting')) {
    /**
     * Get or check a portal setting.
     */
    function portal_setting(string $key, mixed $default = null): mixed
    {
        return app(SettingsService::class)->get($key, $default);
    }
}

if (! function_exists('audit')) {
    /**
     * Log a sensitive action to the audit log.
     *
     * @param  string  $event  Action/event name (e.g., 'parent.approved', 'attendance.manual')
     * @param  string  $description  Human-readable description
     * @param  Model|null  $subject  The affected model
     * @param  array<string, mixed>  $properties  Additional context data
     */
    function audit(string $event, string $description, ?Model $subject = null, array $properties = []): Activity
    {
        $properties['institution_id'] = current_institution_id();

        $logger = activity()
            ->event($event)
            ->withProperties($properties);

        if ($subject !== null) {
            $logger->performedOn($subject);
        }

        if (auth()->check()) {
            $logger->causedBy(auth()->user());
        }

        return $logger->log($description);
    }
}

if (! function_exists('current_timezone')) {
    /**
     * Get the active institution timezone (or fallback to UTC).
     */
    function current_timezone(): string
    {
        $institution = current_institution();
        if ($institution && ! empty($institution->timezone)) {
            return (string) $institution->timezone;
        }

        return (string) config('portal.timezone', config('app.timezone', 'UTC'));
    }
}

if (! function_exists('to_institution_tz')) {
    /**
     * Convert a UTC datetime into the institution's timezone.
     */
    function to_institution_tz(Carbon|string|null $datetime, ?string $format = null): Carbon|string|null
    {
        if ($datetime === null) {
            return null;
        }

        $carbon = is_string($datetime) ? Carbon::parse($datetime, 'UTC') : $datetime->copy()->setTimezone('UTC');
        $converted = $carbon->setTimezone(current_timezone());

        return $format ? $converted->format($format) : $converted;
    }
}

if (! function_exists('to_utc')) {
    /**
     * Convert an institution-local datetime into UTC for database persistence.
     */
    function to_utc(Carbon|string|null $datetime, ?string $fromTimezone = null): ?Carbon
    {
        if ($datetime === null) {
            return null;
        }

        $tz = $fromTimezone ?? current_timezone();

        $carbon = is_string($datetime) ? Carbon::parse($datetime, $tz) : $datetime->copy()->setTimezone($tz);

        return $carbon->setTimezone('UTC');
    }
}

if (! function_exists('format_portal_datetime')) {
    /**
     * Format a UTC datetime for display in the institution timezone.
     */
    function format_portal_datetime(Carbon|string|null $datetime, string $format = 'Y-m-d H:i'): string
    {
        if ($datetime === null) {
            return '—';
        }

        $converted = to_institution_tz($datetime);

        return $converted instanceof Carbon ? $converted->format($format) : (string) $converted;
    }
}
