<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Supported portal locales.
     *
     * @var list<string>
     */
    public const SUPPORTED_LOCALES = ['en', 'si', 'ta'];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = null;

        // 1. Session locale
        if ($request->hasSession() && $request->session()->has('locale')) {
            $sessionLocale = (string) $request->session()->get('locale');
            if (in_array($sessionLocale, self::SUPPORTED_LOCALES, true)) {
                $locale = $sessionLocale;
            }
        }

        // 2. Authenticated user's preferred locale
        if (! $locale && $request->user()?->locale) {
            $userLocale = (string) $request->user()->locale;
            if (in_array($userLocale, self::SUPPORTED_LOCALES, true)) {
                $locale = $userLocale;
            }
        }

        // 3. Institution default locale
        if (! $locale && function_exists('current_institution')) {
            $instLocale = current_institution()?->locale;
            if ($instLocale && in_array($instLocale, self::SUPPORTED_LOCALES, true)) {
                $locale = $instLocale;
            }
        }

        // 4. Default portal setting / config
        if (! $locale) {
            $locale = (string) config('app.locale', 'en');
        }

        App::setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
