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
            $status = __('login.status_unverified');

            try {
                $verification->send($user->phone);
            } catch (OtpResendThrottledException) {
                $status = __('login.status_code_already_sent');
            } catch (SmsDeliveryException $exception) {
                report($exception);

                $status = __('login.error_sms_failed');
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
