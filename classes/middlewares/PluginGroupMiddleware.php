<?php namespace Grch\Editor\Classes\Middlewares;

use Backend\Facades\BackendAuth;
use Illuminate\Http\Request;
use October\Rain\Support\Str;
use Grch\Editor\Classes\Exceptions\AccessDeniedException;
use Grch\Editor\Models\Settings;

/**
 * Class PluginGroupMiddleware
 * @package Grch\Editor\Classes\Middlewares
 */
class PluginGroupMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     * @throws AccessDeniedException
     */
    public function handle($request, \Closure $next)
    {
        if (!$this->checkReferer($request) || !$this->checkAuth()) {
            throw new AccessDeniedException;
        }

        return $next($request);
    }

    /**
     * @param Request $request
     * @return bool
     */
    private function checkReferer(Request $request): bool
    {
        if (Settings::get('disable_secure_endpoints')) {
            return true;
        }

        $refererHeader = $request->header('referer');
        if (empty($refererHeader)) {
            return false;
        }

        $checkDomain  = Str::startsWith($refererHeader, \Config::get('app.url'));
        $checkBackend = Str::startsWith(
            $refererHeader,
            $this->getBackendUri()
        );
        return $checkDomain && $checkBackend;
    }

    /**
     * @return bool
     */
    private function checkAuth(): bool
    {
        if (Settings::get('disable_secure_backendauth')) {
            return true;
        }

        return BackendAuth::check();
    }

    /**
     * @return string
     */
    private function getBackendUri(): string
    {
        return sprintf('%s/%s',
            rtrim(\Config::get('app.url'), '/'),
            ltrim(\Config::get('cms.backendUri'), '/')
        );
    }
}
