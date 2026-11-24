<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\OtpResendThrottledException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyPhoneRequest;
use App\Services\PhoneVerificationService;
use Illuminate\Validation\ValidationException;

class PhoneVerificationController extends Controller
{
    public function create()
    {
        $user = auth()->user();

        if ($user->isPhoneVerified()) {
            return redirect()->route('dashboard');
        }

        return view('auth.verify-otp', [
            'phone' => $user->phone,
        ]);
    }

    public function store(VerifyPhoneRequest $request, PhoneVerificationService $verification)
    {
        $user = $request->user();

        if ($user->isPhoneVerified()) {
            return redirect()->route('dashboard');
        }

        if (! $verification->verify($user->phone, $request->string('code')->toString())) {
            throw ValidationException::withMessages([
                'code' => __('The verification code is invalid or has expired.'),
            ]);
        }

        $user->forceFill(['phone_verified_at' => now()])->save();

        $request->session()->regenerateToken();

        return redirect()->intended(route('dashboard', absolute: false))
            ->with('status', 'Phone number verified.');
    }

    public function resend(PhoneVerificationService $verification)
    {
        $user = auth()->user();

        if ($user->isPhoneVerified()) {
            return redirect()->route('dashboard');
        }

        try {
            $verification->send($user->phone);
        } catch (OtpResendThrottledException $exception) {
            throw ValidationException::withMessages([
                'code' => __('Please wait :seconds seconds before requesting another code.', [
                    'seconds' => $exception->availableIn,
                ]),
            ]);
        }

        return back()->with('status', 'A new verification code has been sent.');
    }
}
