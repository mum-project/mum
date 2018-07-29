<?php

namespace App\Http\Middleware;

use Closure;
use function config;

class EnvWritableWarning
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (config('app.env') === 'production' && is_writable(base_path() . '/.env')) {
            flash('warning', 'Your .env file is writable for the webserver. That is very dangerous! ' .
                'Since the file defines integration commands that are executed in the shell, ' .
                'a potential attacker could theoretically manipulate it to execute arbitrary code.');
        }
        return $next($request);
    }
}
