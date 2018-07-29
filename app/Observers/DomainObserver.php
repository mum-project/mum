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

    /**
     * Listen to the Domain created event.
     *
     * @param Domain $domain
     * @return void
     */
    public function created(Domain $domain)
    {
        $this->runShellCommandIntegrations('created', $domain);
        $this->runWebHookIntegrations('created', $domain);
    }

    /**
     * Listen to the Domain updated event.
     *
     * @param Domain $domain
     * @return void
     */
    public function updated(Domain $domain)
    {
        $this->runShellCommandIntegrations('updated', $domain);
        $this->runWebHookIntegrations('updated', $domain);
    }

    /**
     * Listen to the Domain deleted event.
     *
     * @param Domain $domain
     * @return void
     */
    public function deleted(Domain $domain)
    {
        $this->runShellCommandIntegrations('deleted', $domain);
        $this->runWebHookIntegrations('deleted', $domain);
    }
}
