<?php

namespace App\Actions\Fortify;

use App\Enums\RoleName;
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
        ])->validate();

        $institutionId = current_institution_id() ?? 1;

        $user = User::create([
            'institution_id' => $institutionId,
            'name' => (string) $input['name'],
            'email' => (string) $input['email'],
            'phone' => isset($input['phone']) ? (string) $input['phone'] : null,
            'status' => 'pending',
            'password' => Hash::make((string) $input['password']),
        ]);

        $user->assignRole(RoleName::Parent->value);

        return $user;
    }
}
