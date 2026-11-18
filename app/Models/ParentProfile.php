<?php

namespace App\Models;

use App\Contracts\SmsChannelInterface;
use App\Support\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $institution_id
 * @property int $user_id
 * @property string $relationship
 * @property string|null $secondary_phone
 * @property string|null $emergency_contact_name
 * @property string|null $emergency_contact_phone
 * @property bool $consent_terms
 * @property bool $consent_child_safety
 * @property bool $consent_photo
 * @property string|null $consent_ip
 * @property Carbon|null $consented_at
 * @property string|null $phone_verification_code
 * @property Carbon|null $phone_verification_expires_at
 * @property string|null $rejection_reason
 * @property int|null $reviewed_by
 * @property Carbon|null $reviewed_at
 * @property-read User|null $user
 * @property-read User|null $reviewer
 * @property-read Institution|null $institution
 * @property-read Collection<int, ChildRegistration> $children
 */
class ParentProfile extends Model
{
    use BelongsToInstitution;
    use HasFactory;

    protected $fillable = [
        'institution_id',
        'user_id',
        'relationship',
        'secondary_phone',
        'emergency_contact_name',
        'emergency_contact_phone',
        'consent_terms',
        'consent_child_safety',
        'consent_photo',
        'consent_ip',
        'consented_at',
        'phone_verification_code',
        'phone_verification_expires_at',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'consent_terms' => 'boolean',
            'consent_child_safety' => 'boolean',
            'consent_photo' => 'boolean',
            'consented_at' => 'datetime',
            'phone_verification_expires_at' => 'datetime',
            'reviewed_at' => 'datetime',
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
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * @return HasMany<ChildRegistration, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(ChildRegistration::class);
    }

    public function isReviewed(): bool
    {
        return $this->reviewed_at !== null;
    }

    public function isPending(): bool
    {
        return $this->user?->status === 'pending';
    }

    public function sendPhoneVerificationOtp(): string
    {
        $code = (string) random_int(100000, 999999);

        $this->update([
            'phone_verification_code' => $code,
            'phone_verification_expires_at' => now()->addMinutes(15),
        ]);

        if ($this->user && ! empty($this->user->phone)) {
            $sms = app(SmsChannelInterface::class);
            $inst = current_institution();
            $instName = $inst !== null ? $inst->name : 'EduPortal';
            $sms->send($this->user->phone, __(':inst: Your registration verification code is :code. Valid for 15 minutes.', [
                'inst' => $instName,
                'code' => $code,
            ]));
        }

        return $code;
    }

    public function verifyPhoneOtp(string $code): bool
    {
        if (
            empty($this->phone_verification_code) ||
            $this->phone_verification_code !== trim($code) ||
            $this->phone_verification_expires_at === null ||
            $this->phone_verification_expires_at->isPast()
        ) {
            return false;
        }

        $this->update([
            'phone_verification_code' => null,
            'phone_verification_expires_at' => null,
        ]);

        $this->user?->update([
            'phone_verified_at' => now(),
        ]);

        return true;
    }
}
