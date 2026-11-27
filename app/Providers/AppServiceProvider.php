<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use App\Services\AfroMessageOtpProvider;
use App\Services\Contracts\OtpProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OtpProvider::class, AfroMessageOtpProvider::class);
    }

    public function boot(): void
    {
        // Authorization is explicit: only the seeded superadmin role passes.
        Gate::define('access-superadmin', fn (User $user): bool => $user->isSuperAdmin());

        Gate::policy(User::class, UserPolicy::class);
    }
}
