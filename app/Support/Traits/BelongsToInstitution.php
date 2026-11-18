<?php

namespace App\Support\Traits;

use App\Models\Institution;
use App\Services\InstitutionContext;
use App\Support\Scopes\InstitutionScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToInstitution
{
    /**
     * Boot the BelongsToInstitution trait for a model.
     */
    public static function bootBelongsToInstitution(): void
    {
        static::addGlobalScope(new InstitutionScope);

        static::creating(function (Model $model): void {
            if (empty($model->getAttribute('institution_id'))) {
                $context = app(InstitutionContext::class);
                $model->setAttribute('institution_id', $context->id());
            }
        });
    }

    /**
     * Relationship to the institution.
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Scope a query to ignore the institution global scope.
     *
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public function scopeWithoutInstitution(Builder $query): Builder
    {
        return $query->withoutGlobalScope(InstitutionScope::class);
    }
}
