<?php

use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Available to guests and authenticated users; session-backed and CSRF protected.
Route::post('locale', [LocaleController::class, 'update'])->name('locale.update');

Route::middleware(['auth', 'account.active'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware('phone.verified')->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::match(['put', 'patch'], '/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// Future admin and superadmin routes belong in groups using the "admin" and
// "superadmin" middleware aliases. No dashboards are part of Phase 1.
require __DIR__.'/auth.php';
