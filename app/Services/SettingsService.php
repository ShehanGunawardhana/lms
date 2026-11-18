<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Arr;

class SettingsService
{
    public function __construct(
        protected InstitutionContext $context
    ) {}

    /**
     * Retrieve a setting value for the current institution, falling back to config/portal.php.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $institutionId = $this->context->id();

        if ($institutionId !== null) {
            $record = Setting::query()
                ->where('institution_id', $institutionId)
                ->where('key', $key)
                ->first();

            if ($record !== null) {
                return $record->getTypedValue();
            }
        }

        return config("portal.{$key}", $default);
    }

    /**
     * Save or update a setting for the current institution.
     */
    public function set(string $key, mixed $value, ?string $type = null): Setting
    {
        $institutionId = $this->context->id();

        if ($institutionId === null) {
            throw new \RuntimeException('Cannot save setting without an active institution context.');
        }

        if ($type === null) {
            $type = match (gettype($value)) {
                'boolean' => 'boolean',
                'integer' => 'integer',
                'double' => 'float',
                'array' => 'json',
                default => 'string',
            };
        }

        $storedValue = is_array($value) ? json_encode($value) : (string) $value;
        if (is_bool($value)) {
            $storedValue = $value ? '1' : '0';
        }

        return Setting::updateOrCreate(
            [
                'institution_id' => $institutionId,
                'key' => $key,
            ],
            [
                'value' => $storedValue,
                'type' => $type,
            ]
        );
    }

    /**
     * Check if a specific portal module is enabled.
     */
    public function isModuleEnabled(string $module): bool
    {
        return (bool) $this->get("modules.{$module}", false);
    }

    /**
     * Get all settings merged with configuration defaults.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        $defaults = config('portal', []);
        $institutionId = $this->context->id();

        if ($institutionId === null) {
            return $defaults;
        }

        $records = Setting::query()
            ->where('institution_id', $institutionId)
            ->get();

        foreach ($records as $record) {
            Arr::set($defaults, $record->key, $record->getTypedValue());
        }

        return $defaults;
    }
}
