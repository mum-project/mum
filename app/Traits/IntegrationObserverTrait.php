<?php

namespace App\Traits;

use App\Interfaces\Integratable;
use App\Jobs\ShellCommandJob;
use App\Jobs\WebHookJob;
use App\ShellCommandIntegration;
use App\WebHookIntegration;

trait IntegrationObserverTrait
{
    /**
     * Runs all shell command integrations that are registered to combination of
     * the provided model class and the event.
     *
     * @param string       $event
     * @param Integratable $integratable
     */
    protected function runShellCommandIntegrations(string $event, Integratable $integratable)
    {
        ShellCommandIntegration::forEvent($integratable->getIntegratableClassName(), $event)
            ->get()
            ->each(function (ShellCommandIntegration $integration) use ($integratable) {
                ShellCommandJob::dispatch($integration, $integratable->getIntegratablePlaceholders());
            });
    }

    /**
     * Runs all web hook integrations that are registered to combination of
     * the provided model class and the event.
     *
     * @param string       $event
     * @param Integratable $integratable
     */
    protected function runWebHookIntegrations(string $event, Integratable $integratable)
    {
        WebHookIntegration::forEvent($integratable->getIntegratableClassName(), $event)
            ->get()
            ->each(function (WebHookIntegration $integration) use ($integratable) {
                WebHookJob::dispatch($integration, $integratable->getIntegratablePlaceholders());
            });
    }
}
