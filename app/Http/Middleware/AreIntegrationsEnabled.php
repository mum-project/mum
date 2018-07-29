<?php

namespace App\Http\Middleware;

use function abort_if;
use Closure;
use function config;

class AreIntegrationsEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        abort_if(!config('integrations.enabled.generally'), 404);
        return $next($request);
    }
}
