<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\PhoneVerificationService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PhoneVerificationController extends Controller
{
    public function create(PhoneVerificationService $verification)
    {
        if (auth()->user()->phone_verified_at) {
            return redirect()->route('dashboard');
        }

        return view('auth.verify-otp', [
            'phone' => auth()->user()->phone,
            'debugCode' => $verification->debugCode(auth()->user()->phone),
        ]);
    }

    public function store(Request $request, PhoneVerificationService $verification)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:'.config('otp.length')],
        ]);

        $user = $request->user();

        if (! $verification->verify($user->phone, $request->string('code')->toString())) {
            throw ValidationException::withMessages([
                'code' => __('The verification code is invalid or has expired.'),
            ]);
        }

        $user->forceFill(['phone_verified_at' => now()])->save();

        return redirect()->intended(route('dashboard', absolute: false))
            ->with('status', 'Phone number verified.');
    }

    public function resend(PhoneVerificationService $verification)
    {
        $user = auth()->user();

        if ($user->phone_verified_at) {
            return redirect()->route('dashboard');
        }

        $verification->send($user->phone);

        return back()->with('status', 'A new verification code has been sent.');
    }
}
