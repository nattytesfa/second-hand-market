<?php

namespace App\Providers;

use App\Services\AfroMessageOtpProvider;
use App\Services\Contracts\OtpProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OtpProvider::class, AfroMessageOtpProvider::class);
    }

    public function boot(): void
    {
        //
    }
}
