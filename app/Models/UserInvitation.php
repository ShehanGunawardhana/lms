<?php

namespace App\Models;

use App\Support\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $institution_id
 * @property int $user_id
 * @property string $token
 * @property string $channel
 * @property string|null $sent_to
 * @property Carbon $expires_at
 * @property Carbon|null $accepted_at
 * @property int|null $created_by
 * @property-read User|null $user
 * @property-read Institution|null $institution
 */
class UserInvitation extends Model
{
    use BelongsToInstitution;
    use HasFactory;

    protected $fillable = [
        'institution_id',
        'user_id',
        'token',
        'channel',
        'sent_to',
        'expires_at',
        'accepted_at',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    public function isValid(): bool
    {
        return ! $this->isExpired() && ! $this->isAccepted();
    }

    public function getAcceptUrl(): string
    {
        return route('invitation.accept', ['token' => $this->token]);
    }
}
