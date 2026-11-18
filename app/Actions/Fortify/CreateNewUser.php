<?php

namespace App\Actions\Fortify;

use App\Enums\RoleName;
use App\Models\ChildRegistration;
use App\Models\ParentProfile;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => $this->passwordRules(),
            'relationship' => ['nullable', 'string', 'max:50'],
            'child_first_name' => ['sometimes', 'required', 'string', 'max:120'],
            'child_last_name' => ['sometimes', 'required', 'string', 'max:120'],
            'child_dob' => ['sometimes', 'required', 'date', 'before:today'],
            'child_gender' => ['nullable', 'string', 'in:male,female,other'],
            'nursery_group' => ['sometimes', 'required', 'string', 'max:50'],
            'medical_notes' => ['nullable', 'string', 'max:2000'],
            'dietary_needs' => ['nullable', 'string', 'max:1000'],
            'special_notes' => ['nullable', 'string', 'max:1000'],
            'consent_terms' => ['required', 'accepted'],
            'consent_child_safety' => ['required', 'accepted'],
            'consent_photo' => ['nullable', 'boolean'],
        ])->validate();

        $institutionId = current_institution_id() ?? 1;

        $user = User::create([
            'institution_id' => $institutionId,
            'name' => (string) $input['name'],
            'email' => (string) $input['email'],
            'phone' => isset($input['phone']) && ! empty($input['phone']) ? (string) $input['phone'] : null,
            'status' => 'pending',
            'password' => Hash::make((string) $input['password']),
        ]);

        $user->assignRole(RoleName::Parent->value);

        // Create ParentProfile with consent records (§5.2, §14)
        $parentProfile = ParentProfile::create([
            'institution_id' => $institutionId,
            'user_id' => $user->id,
            'relationship' => isset($input['relationship']) ? (string) $input['relationship'] : 'legal_guardian',
            'consent_terms' => ! empty($input['consent_terms']),
            'consent_child_safety' => ! empty($input['consent_child_safety']),
            'consent_photo' => ! empty($input['consent_photo']),
            'consent_ip' => request()->ip(),
            'consented_at' => now(),
        ]);

        // Create ChildRegistration if child details provided (§5.2, §6.5)
        $childFullName = null;
        if (! empty($input['child_first_name']) && ! empty($input['child_last_name'])) {
            $child = ChildRegistration::create([
                'institution_id' => $institutionId,
                'parent_profile_id' => $parentProfile->id,
                'first_name' => (string) $input['child_first_name'],
                'last_name' => (string) $input['child_last_name'],
                'dob' => (string) $input['child_dob'],
                'gender' => isset($input['child_gender']) ? (string) $input['child_gender'] : null,
                'nursery_group' => isset($input['nursery_group']) ? (string) $input['nursery_group'] : 'nursery',
                'medical_notes' => isset($input['medical_notes']) && ! empty($input['medical_notes']) ? (string) $input['medical_notes'] : null,
                'dietary_needs' => isset($input['dietary_needs']) && ! empty($input['dietary_needs']) ? (string) $input['dietary_needs'] : null,
                'special_notes' => isset($input['special_notes']) && ! empty($input['special_notes']) ? (string) $input['special_notes'] : null,
            ]);

            $childFullName = $child->full_name;
        }

        // Audit Log registration (§14)
        audit(
            'parent.registered',
            "Parent {$user->name} self-registered with child ".($childFullName ?? 'unspecified'),
            $user,
            [
                'parent_profile_id' => $parentProfile->id,
                'child_name' => $childFullName,
                'consent_photo' => $parentProfile->consent_photo,
            ],
            $user
        );

        return $user;
    }
}
