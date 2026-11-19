<?php

namespace App\Models;

use App\Enums\ClassKind;
use App\Support\Traits\BelongsToInstitution;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $institution_id
 * @property string $name
 * @property Carbon $date
 * @property string $affects
 * @property bool $is_recurring
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Institution|null $institution
 */
class Holiday extends Model
{
    use BelongsToInstitution;
    use HasFactory;

    protected $fillable = [
        'institution_id',
        'name',
        'date',
        'affects',
        'is_recurring',
        'description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_recurring' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Institution, $this>
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function affectsClass(SchoolClass $class): bool
    {
        if ($this->affects === 'all') {
            return true;
        }

        $classKind = $class->kind instanceof ClassKind ? $class->kind->value : (string) $class->kind;

        if ($this->affects === 'nursery' && $classKind === 'nursery_group') {
            return true;
        }

        if ($this->affects === 'institute' && $classKind === 'institute_class') {
            return true;
        }

        return false;
    }
}
