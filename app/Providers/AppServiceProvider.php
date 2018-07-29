<?php

namespace App\Providers;

use App\Alias;
use App\Domain;
use App\Mailbox;
use App\Observers\AliasObserver;
use App\Observers\DomainObserver;
use App\Observers\MailboxObserver;
use App\Observers\IntegrationObserver;
use App\ShellCommandIntegration;
use App\WebHookIntegration;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        Paginator::defaultView('pagination::default');
        Paginator::defaultSimpleView('pagination::simple-default');

        Alias::observe(AliasObserver::class);
        Domain::observe(DomainObserver::class);
        Mailbox::observe(MailboxObserver::class);

        ShellCommandIntegration::observe(IntegrationObserver::class);
        WebHookIntegration::observe(IntegrationObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
