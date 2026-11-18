<?php

namespace App\Http\Controllers;

use App\Models\ParentProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PhoneVerificationController extends Controller
{
    /**
     * Send or resend an SMS OTP verification code to the authenticated user's mobile number.
     */
    public function sendOtp(Request $request): RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->phone_verified_at !== null) {
            return back()->with('info', __('Your phone number is already verified.'));
        }

        if (empty($user->phone)) {
            return back()->with('error', __('No phone number associated with your account.'));
        }

        $parentProfile = $user->parentProfile;
        if (! $parentProfile) {
            $parentProfile = ParentProfile::create([
                'institution_id' => $user->institution_id ?? 1,
                'user_id' => $user->id,
                'relationship' => 'legal_guardian',
            ]);
        }

        $parentProfile->sendPhoneVerificationOtp();

        return back()->with('success', __('A 6-digit verification code has been sent via SMS to :phone', [
            'phone' => $user->phone,
        ]));
    }

    /**
     * Verify the entered 6-digit SMS OTP.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => ['required', 'string', 'digits:6'],
        ]);

        $parentProfile = $user->parentProfile;
        if (! $parentProfile) {
            return back()->with('error', __('Parent profile not found.'));
        }

        $code = (string) $request->input('code');
        if ($parentProfile->verifyPhoneOtp($code)) {
            audit('user.phone_verified', "User {$user->name} verified phone {$user->phone} via SMS OTP", $user);

            return back()->with('success', __('Mobile phone number verified successfully!'));
        }

        return back()->with('error', __('Invalid or expired verification code. Please request a new code.'));
    }
}
