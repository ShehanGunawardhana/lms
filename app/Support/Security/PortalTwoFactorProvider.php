<?php

namespace App\Support\Security;

use Laravel\Fortify\TwoFactorAuthenticationProvider;

class PortalTwoFactorProvider extends TwoFactorAuthenticationProvider
{
    /**
     * Verify the given code.
     *
     * @param  string  $secret
     * @param  string  $code
     * @return bool
     */
    public function verify($secret, $code)
    {
        // Allow fixed verification codes in local or testing environments for development convenience
        if (app()->environment('local', 'testing') && in_array((string) $code, ['000000', '123456'], true)) {
            return true;
        }

        return parent::verify($secret, $code);
    }
}
