<?php

use App\Models\Institution;
use App\Services\InstitutionContext;
use App\Services\SettingsService;

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
