<?php

namespace App\Providers;

use App\Managers\HashManager;
use Illuminate\Hashing\HashServiceProvider as CoreHashServiceProvider;

class HashServiceProvider extends CoreHashServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('hash', function ($app) {
            return new HashManager($app);
        });

        $this->app->singleton('hash.driver', function ($app) {
            return $app['hash']->driver();
        });
    }
}
