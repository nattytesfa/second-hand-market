<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\PhoneVerificationController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password', [PasswordResetController::class, 'showResetForm'])
        ->name('password.reset.create');

    Route::post('reset-password', [PasswordResetController::class, 'reset'])
        ->name('password.reset');

    Route::post('reset-password/resend', [PasswordResetController::class, 'resend'])
        ->middleware('throttle:3,1')
        ->name('password.resend');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-phone', [PhoneVerificationController::class, 'create'])
        ->name('otp.create');

    Route::post('verify-phone', [PhoneVerificationController::class, 'store'])
        ->name('otp.store');

    Route::post('verify-phone/resend', [PhoneVerificationController::class, 'resend'])
        ->middleware('throttle:3,1')
        ->name('otp.resend');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
