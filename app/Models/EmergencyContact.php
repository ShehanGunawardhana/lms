<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $institution_id
 * @property int $student_id
 * @property string $name
 * @property string $relationship
 * @property string $phone_primary
 * @property string|null $phone_secondary
 * @property int $priority
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Institution|null $institution
 * @property-read Student|null $student
 */
class EmergencyContact extends Model
{
    use BelongsToInstitution;
    use HasFactory;

    protected $fillable = [
        'institution_id',
        'student_id',
        'name',
        'relationship',
        'phone_primary',
        'phone_secondary',
        'priority',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'priority' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Check if this is the primary emergency contact.
     */
    public function isPrimary(): bool
    {
        return $this->priority === 1;
    }
}
