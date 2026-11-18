<?php

use App\Models\Institution;
use App\Services\InstitutionContext;
use App\Services\SettingsService;
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
