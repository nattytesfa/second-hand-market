<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\OtpResendThrottledException;
use App\Exceptions\SmsDeliveryException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\PhoneVerificationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request, PhoneVerificationService $verification)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'password' => $validated['password'],
        ]);

        event(new Registered($user));

        $status = __('registration.status_code_sent');

        try {
            $verification->send($user->phone);
        } catch (OtpResendThrottledException) {
            $status = __('registration.status_code_already_sent');
        } catch (SmsDeliveryException $exception) {
            Auth::logout();
            $user->delete();

            report($exception);

            throw ValidationException::withMessages([
                'phone' => __('registration.error_sms_failed'),
            ]);
        }

        Auth::login($user);

        return redirect()->route('otp.create')->with('status', $status);
    }
}
