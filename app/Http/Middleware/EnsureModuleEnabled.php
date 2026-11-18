<?php

namespace App\Http\Middleware;

use App\Services\SettingsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleEnabled
{
    public function __construct(
        protected SettingsService $settings
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        if (! $this->settings->isModuleEnabled($module)) {
            abort(404, __('The :module module is not enabled for this institution.', ['module' => ucfirst($module)]));
        }

        return $next($request);
    }
}
