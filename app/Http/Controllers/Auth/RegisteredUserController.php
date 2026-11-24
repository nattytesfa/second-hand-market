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

        $status = 'We sent a verification code to your phone.';

        try {
            $verification->send($user->phone);
        } catch (OtpResendThrottledException) {
            $status = 'Your account is ready. A verification code was already sent; please check your messages.';
        } catch (SmsDeliveryException $exception) {
            Auth::logout();
            $user->delete();

            report($exception);

            throw ValidationException::withMessages([
                'phone' => 'We could not send the verification code. Please check the SMS configuration and try again.',
            ]);
        }

        Auth::login($user);

        return redirect()->route('otp.create')->with('status', $status);
    }
}
