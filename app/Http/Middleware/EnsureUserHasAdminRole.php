<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasAdminRole
{
    /**
     * Guard future admin routes. Superadmins inherit admin access, but normal
     * users do not. Superadmin-only routes use "superadmin" in addition.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('login'))
                ->with('status', __('auth.login_required'));
        }

        if (! $user->isActive()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('status', __('auth.inactive_account'));
        }

        if (! $user->hasAdminAccess()) {
            abort(403, __('auth.forbidden'));
        }

        return $next($request);
    }
}
