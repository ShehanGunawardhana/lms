<?php

namespace App\Models;

use App\Support\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $institution_id
 * @property int|null $user_id
 * @property string|null $address
 * @property string $relationship
 * @property-read User|null $user
 * @property-read Institution|null $institution
 * @property-read Collection<int, Student> $students
 * @property-read Pivot|null $pivot
 * @property-read string $name
 */
class Guardian extends Model
{
    use BelongsToInstitution;
    use HasFactory;

    protected $fillable = [
        'institution_id',
        'user_id',
        'address',
        'relationship',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToMany<Student, $this>
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'guardian_student')
            ->withPivot(['relationship', 'is_primary'])
            ->withTimestamps();
    }

    public function getNameAttribute(): string
    {
        $user = $this->user;

        return $user !== null ? $user->name : (string) __('Guardian');
    }
}
