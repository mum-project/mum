<?php

namespace App\Providers;

use App\Alias;
use App\Domain;
use App\Mailbox;
use App\Policies\AliasPolicy;
use App\Policies\DomainPolicy;
use App\Policies\MailboxPolicy;
use App\Policies\TlsPolicyPolicy;
use App\TlsPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model'                 => 'App\Policies\ModelPolicy',
        Alias::class                => AliasPolicy::class,
        Domain::class               => DomainPolicy::class,
        Mailbox::class              => MailboxPolicy::class,
        TlsPolicy::class            => TlsPolicyPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
