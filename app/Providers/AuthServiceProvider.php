<?php

namespace App\Providers;

use App\Alias;
use App\AliasRequest;
use App\Domain;
use App\Integration;
use App\IntegrationParameter;
use App\Mailbox;
use App\Policies\AliasPolicy;
use App\Policies\AliasRequestPolicy;
use App\Policies\DomainPolicy;
use App\Policies\IntegrationParameterPolicy;
use App\Policies\IntegrationPolicy;
use App\Policies\MailboxPolicy;
use App\Policies\SystemServicePolicy;
use App\Policies\TlsPolicyPolicy;
use App\SystemService;
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
