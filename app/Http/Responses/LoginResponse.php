<?php

namespace App\Http\Responses;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;

class LoginResponse implements LoginResponseContract, RegisterResponseContract, TwoFactorLoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     */
    public function toResponse($request): RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user === null) {
            return redirect('/');
        }

        if ($user->isPending()) {
            return redirect()->route('awaiting-approval');
        }

        $user->updateQuietly(['last_login_at' => now()]);

        return redirect()->intended($this->getRedirectUrlForUser($user));
    }

    /**
     * Resolve the target dashboard URL according to the user's role.
     */
    public function getRedirectUrlForUser(User $user): string
    {
        if ($user->hasRole(RoleName::SuperAdmin->value) || $user->hasRole(RoleName::Admin->value)) {
            return route('admin.dashboard');
        }

        if ($user->hasRole(RoleName::Teacher->value)) {
            return route('teacher.dashboard');
        }

        if ($user->hasRole(RoleName::Parent->value)) {
            return route('parent.dashboard');
        }

        if ($user->hasRole(RoleName::Student->value)) {
            return route('student.dashboard');
        }

        if ($user->hasRole(RoleName::PrintingStaff->value)) {
            return route('printing.dashboard');
        }

        if ($user->hasRole(RoleName::Cashier->value)) {
            return route('cashier.dashboard');
        }

        if ($user->hasRole(RoleName::AttendanceMarshal->value)) {
            return route('attendance.dashboard');
        }

        return url('/dashboard');
    }
}
