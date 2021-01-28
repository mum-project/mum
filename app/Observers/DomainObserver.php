<?php

namespace App\Observers;

use App\Domain;
use App\Traits\IntegrationObserverTrait;
use function getHomedirForDomain;

class DomainObserver
{
    use IntegrationObserverTrait;

    /**
     * Listen to the Domain creating event.
     *
     * @param Domain $domain
     * @return void
     */
    public function creating(Domain $domain)
    {
        $domain->homedir = getHomedirForDomain($domain->domain);
    }
}
