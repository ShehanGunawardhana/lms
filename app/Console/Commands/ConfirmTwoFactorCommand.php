<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\RecoveryCode;

class ConfirmTwoFactorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '2fa:confirm {email? : The user email to confirm 2FA for} {--disable : Disable 2FA instead}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Instantly confirm or disable two-factor authentication for administrators (development helper)';

    /**
     * Execute the console command.
     */
    public function handle(TwoFactorAuthenticationProvider $provider): int
    {
        $email = $this->argument('email');

        if ($email) {
            $users = User::where('email', $email)->get();

            if ($users->isEmpty()) {
                $this->error("User with email '{$email}' not found.");

                return 1;
            }
        } else {
            $users = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['admin', 'super_admin']);
            })->get();

            if ($users->isEmpty()) {
                $this->error('No administrator users found in the database.');

                return 1;
            }
        }

        foreach ($users as $user) {
            if ($this->option('disable')) {
                $user->forceFill([
                    'two_factor_secret' => null,
                    'two_factor_recovery_codes' => null,
                    'two_factor_confirmed_at' => null,
                ])->save();

                $this->info("✓ Two-factor authentication disabled for {$user->email}.");

                continue;
            }

            // If user has no secret, generate one
            if (empty($user->two_factor_secret)) {
                $secret = $provider->generateSecretKey();
                $recoveryCodes = collect(range(1, 8))->map(fn () => RecoveryCode::generate())->all();

                $user->forceFill([
                    'two_factor_secret' => encrypt($secret),
                    'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
                ]);
            }

            $user->forceFill([
                'two_factor_confirmed_at' => now(),
            ])->save();

            $this->info("✓ 2FA confirmed for: {$user->email} ({$user->name})");
        }

        $this->newLine();
        $this->line("Development bypass codes: '000000' or '123456' can also be entered anywhere 2FA code is asked.");

        return 0;
    }
}
