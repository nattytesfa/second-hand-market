<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSuperAdmin
{
    /**
     * Separate, stricter guard for future superadmin-only routes. Authorization
     * is delegated to the "access-superadmin" Gate so there is one source of
     * truth for privileged access.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('login'))
                ->with('status', __('auth.login_required'));
        }

        if (! $user instanceof User || ! $user->isActive()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('status', __('auth.inactive_account'));
        }

        if (! Gate::forUser($user)->allows('access-superadmin')) {
            abort(403, __('auth.forbidden'));
        }

        return $next($request);
    }
}
