<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PhoneVerificationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    private const SESSION_KEY = 'password_reset_phone';

    public function create()
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request, PhoneVerificationService $service)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'exists:users,phone'],
        ]);

        $service->sendPasswordResetCode($validated['phone']);

        $request->session()->put(self::SESSION_KEY, $validated['phone']);

        return redirect()->route('password.reset.create')
            ->with('status', 'We sent a verification code to your phone.');
    }

    public function showResetForm(PhoneVerificationService $service)
    {
        $phone = session(self::SESSION_KEY);

        if (! $phone) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password', [
            'phone' => $phone,
            'debugCode' => $service->debugPasswordResetCode($phone),
        ]);
    }

    public function reset(Request $request, PhoneVerificationService $service)
    {
        $phone = $request->session()->get(self::SESSION_KEY);

        if (! $phone) {
            return redirect()->route('password.request');
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'size:'.config('otp.length')],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (! $service->verifyPasswordResetCode($phone, $validated['code'])) {
            throw ValidationException::withMessages([
                'code' => __('The verification code is invalid or has expired.'),
            ]);
        }

        User::wherePhone($phone)->firstOrFail()->update([
            'password' => $validated['password'],
        ]);

        $service->forgetPasswordResetCode($phone);
        $request->session()->forget(self::SESSION_KEY);

        return redirect()->route('login')
            ->with('status', 'Password reset successfully. Please log in.');
    }

    public function resend(Request $request, PhoneVerificationService $service)
    {
        $phone = $request->session()->get(self::SESSION_KEY);

        if (! $phone) {
            return redirect()->route('password.request');
        }

        $service->sendPasswordResetCode($phone);

        return back()->with('status', 'A new verification code has been sent.');
    }
}
