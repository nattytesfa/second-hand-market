<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\OtpResendThrottledException;
use App\Exceptions\SmsDeliveryException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RequestPasswordResetRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Services\PhoneVerificationService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    private const SESSION_KEY = 'password_reset_phone';

    public function create()
    {
        return view('auth.forgot-password');
    }

    public function store(RequestPasswordResetRequest $request, PhoneVerificationService $service)
    {
        $phone = $request->validated('phone');

        $request->session()->put(self::SESSION_KEY, $phone);

        if (User::where('phone', $phone)->exists()) {
            try {
                $service->sendPasswordResetCode($phone);
            } catch (OtpResendThrottledException) {
                // Same response as a successful request: do not reveal that an
                // OTP already exists for this number.
            } catch (SmsDeliveryException $exception) {
                // Keep the enumeration-safe response, but surface the failure
                // in logs so configuration issues can be diagnosed.
                report($exception);
            }
        }

        return redirect()->route('password.reset.create')->with('status', __('passwords.generic_status'));
    }

    public function showResetForm()
    {
        $phone = session(self::SESSION_KEY);

        if (! $phone) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password', [
            'phone' => $phone,
        ]);
    }

    public function reset(Request $request, ResetPasswordRequest $resetRequest, PhoneVerificationService $service)
    {
        $phone = $request->session()->get(self::SESSION_KEY);

        if (! $phone) {
            return redirect()->route('password.request');
        }

        $validated = $resetRequest->validated();

        $user = User::where('phone', $phone)->first();

        if (! $user || ! $service->verifyPasswordResetCode($phone, $validated['code'])) {
            throw ValidationException::withMessages([
                'code' => __('phone.invalid_or_expired'),
            ]);
        }

        $user->update(['password' => $validated['password']]);

        $service->forgetPasswordResetCode($phone);
        $request->session()->forget(self::SESSION_KEY);
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('status', __('passwords.status_success'));
    }

    public function resend(Request $request, PhoneVerificationService $service)
    {
        $phone = $request->session()->get(self::SESSION_KEY);

        if (! $phone) {
            return redirect()->route('password.request');
        }

        if (User::where('phone', $phone)->exists()) {
            try {
                $service->sendPasswordResetCode($phone);
            } catch (OtpResendThrottledException) {
                // Keep the response identical for throttled and fresh requests.
            } catch (SmsDeliveryException $exception) {
                report($exception);
            }
        }

        return back()->with('status', __('passwords.generic_status'));
    }
}
