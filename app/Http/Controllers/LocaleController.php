<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    /**
     * Persist a valid language choice in the session and return to the page
     * the visitor was on, so switching language does not lose their place.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in(config('app.supported_locales', ['en', 'am', 'om']))],
        ]);

        $request->session()->put('locale', $validated['locale']);

        // Apply immediately as well, so the redirect response and any flash
        // messages use the language that was just chosen.
        App::setLocale($validated['locale']);

        return back()->with('status', __('common.success.locale_changed'));
    }
}
