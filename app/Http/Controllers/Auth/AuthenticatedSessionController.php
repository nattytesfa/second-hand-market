<?php

namespace App\Http\Controllers\Auth;

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

        if (! Auth::user()->phone_verified_at) {
            $verification->send(Auth::user()->phone);

            return redirect()->route('otp.create')
                ->with('status', 'Your phone is not verified. We sent a new code.');
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
