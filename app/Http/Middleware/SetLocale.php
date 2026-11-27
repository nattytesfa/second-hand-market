<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Apply the session-selected locale to every web request. Unknown values
     * are discarded and replaced by the configured default (English).
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var list<string> $supported */
        $supported = (array) config('app.supported_locales', ['en', 'am', 'om']);

        $default = (string) config('app.locale', 'en');
        $default = in_array($default, $supported, true) ? $default : 'en';

        $locale = $request->session()->get('locale');

        if (! is_string($locale) || ! in_array($locale, $supported, true)) {
            $locale = $default;
            $request->session()->put('locale', $locale);
        }

        App::setLocale($locale);

        return $next($request);
    }
}
