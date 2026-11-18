<?php

namespace App\Services;

use App\Models\Institution;

class InstitutionContext
{
    protected ?Institution $institution = null;

    /**
     * Set the current active institution.
     */
    public function set(?Institution $institution): void
    {
        $this->institution = $institution;
    }

    /**
     * Get the current active institution.
     */
    public function get(): ?Institution
    {
        if ($this->institution === null) {
            $this->institution = Institution::query()->where('status', 'active')->first();
        }

        return $this->institution;
    }

    /**
     * Get the ID of the current active institution.
     */
    public function id(): ?int
    {
        return $this->get()?->id;
    }
}
