<?php

namespace App\Http\Middleware;

use App\Enums\RoleName;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorEnabled
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user && ($user->hasRole(RoleName::SuperAdmin->value) || $user->hasRole(RoleName::Admin->value))) {
            // Check if 2FA has been confirmed
            $has2FA = ! empty($user->two_factor_secret) && ! empty($user->two_factor_confirmed_at);

            if (! $has2FA) {
                // Allow setup routes and logout to prevent redirect loops
                if ($request->routeIs('two-factor.*') || $request->routeIs('logout')) {
                    return $next($request);
                }

                return redirect()->route('two-factor.setup')
                    ->with('warning', __('Two-factor authentication is mandatory for administrator accounts. Please configure and verify your device to continue.'));
            }
        }

        return $next($request);
    }
}
