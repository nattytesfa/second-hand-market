<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\OtpResendThrottledException;
use App\Exceptions\SmsDeliveryException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\PhoneVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request, PhoneVerificationService $verification)
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        if (! $user->isPhoneVerified()) {
            $status = 'Your phone is not verified. We sent a new code.';

            try {
                $verification->send($user->phone);
            } catch (OtpResendThrottledException) {
                $status = 'A verification code was already sent. Please check your messages.';
            } catch (SmsDeliveryException $exception) {
                report($exception);

                $status = 'We could not send the verification code. Check the SMS configuration and try again.';
            }

            return redirect()->route('otp.create')->with('status', $status);
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
