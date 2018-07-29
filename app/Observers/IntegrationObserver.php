<?php

namespace App\Observers;

use App\Exceptions\NotImplementedException;
use App\Integration;
use App\ShellCommandIntegration;
use App\WebHookIntegration;

class IntegrationObserver
{
    /**
     * Listen to the Integration creating event.
     *
     * @param Integration $integration
     * @return void
     */
    public function creating(Integration $integration)
    {
        if ($integration instanceof ShellCommandIntegration) {
            $integration->type = ShellCommandIntegration::class;
            return;
        }
        if ($integration instanceof WebHookIntegration) {
            $integration->type = WebHookIntegration::class;
            return;
        }
        throw new NotImplementedException('Integration type is not implemented yet.');
    }
}
