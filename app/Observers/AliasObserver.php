<?php

namespace App\Observers;

use App\Alias;
use App\Traits\IntegrationObserverTrait;

class AliasObserver
{
    use IntegrationObserverTrait;

    /**
     * Listen to the Alias created event.
     *
     * @param Alias $alias
     * @return void
     */
    public function created(Alias $alias)
    {
        $this->runShellCommandIntegrations('created', $alias);
        $this->runWebHookIntegrations('created', $alias);
    }

    /**
     * Listen to the Alias updated event.
     *
     * @param Alias $alias
     * @return void
     */
    public function updated(Alias $alias)
    {
        $this->runShellCommandIntegrations('updated', $alias);
        $this->runWebHookIntegrations('updated', $alias);
    }

    /**
     * Listen to the Alias deleted event.
     *
     * @param Alias $alias
     * @return void
     */
    public function deleted(Alias $alias)
    {
        $this->runShellCommandIntegrations('deleted', $alias);
        $this->runWebHookIntegrations('deleted', $alias);
    }
}
