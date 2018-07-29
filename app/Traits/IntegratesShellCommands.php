<?php

namespace App\Traits;

use App\Exceptions\IntegrationsDisabledException;
use App\Exceptions\ShellCommandFailedException;
use App\Exceptions\ShellCommandUndefinedException;
use App\IntegrationParameter;
use App\ShellCommandIntegration;
use function array_push;
use function config;
use function escapeshellarg;
use function escapeshellcmd;
use function implode;
use function str_replace;
use Facades\App\Process;

trait IntegratesShellCommands
{
    /**
     * Creates a shell command from the given integration model and executes it.
     *
     * @param ShellCommandIntegration $integration
     * @param array|null              $placeholders Example: ['placeholder' => $value]
     * @throws IntegrationsDisabledException
     * @throws ShellCommandFailedException
     */
    public function executeShellCommand(ShellCommandIntegration $integration, array $placeholders = null)
    {
        $this->checkShellCommandIntegrationsEnabled();

        $command = $this->buildEscapedShellCommand($integration, $placeholders);

        /** @var \Symfony\Component\Process\Process $process */
        $process = Process::run($command);
        if (!$process->isSuccessful()) {
            throw new ShellCommandFailedException($command, $process->getErrorOutput(), $process->getExitCode());
        }
    }

    /**
     * Throws an IntegrationsDisabledException if integrations are disabled generally
     * or shell command integrations are disabled.
     * Throws an UnexpectedValueException if the integration is not not of type 'shell_command'.
     *
     * @throws IntegrationsDisabledException
     */
    private function checkShellCommandIntegrationsEnabled()
    {
        if (!config('integrations.enabled.generally')) {
            throw new IntegrationsDisabledException();
        }
        if (!config('integrations.enabled.shell_commands')) {
            throw new IntegrationsDisabledException("Shell command integrations are disabled.");
        }
    }

    /**
     * Builds an escaped shell command from the given integration model.
     * Any given placeholders will be replaced by their corresponding value
     * from the $placeholders array.
     *
     * @param ShellCommandIntegration $integration
     * @param array|null              $placeholders
     * @return string
     */
    private function buildEscapedShellCommand(ShellCommandIntegration $integration, array $placeholders = null)
    {
        if (config('integrations.options.shell_commands.allow_parameters') && $integration->parameters()
                ->exists()) {
            return escapeshellcmd($this->getShellCommandString($integration) . ' ' .
                $this->getShellParameterString($integration, $placeholders));
        }
        return escapeshellcmd($this->getShellCommandString($integration));
    }

    /**
     * Gets the shell command string from the config.
     *
     * @param ShellCommandIntegration $integration
     * @return string
     */
    private function getShellCommandString(ShellCommandIntegration $integration)
    {
        $command = config('integrations.shell_commands.' . $integration->getCommandId());

        if ($command == null) {
            throw new ShellCommandUndefinedException($integration->getCommandId());
        }

        return (string)$command;
    }

    /**
     * Returns a string with all parameters that belong to the given integration.
     * Any given placeholders will be replaced by their corresponding value
     * from the $placeholders array.
     *
     * @param ShellCommandIntegration $integration
     * @param array|null              $placeholders
     * @return string
     */
    private function getShellParameterString(ShellCommandIntegration $integration, array $placeholders = null)
    {
        $parameters = [];

        $integration->parameters->each(function (IntegrationParameter $p) use (&$parameters, $placeholders) {
            $arguments = $p->value;

            if ($placeholders) {
                foreach ($placeholders as $placeholder => $value) {
                    $arguments = str_replace('%{' . $placeholder . '}', $value, $arguments);
                }
            }

            $divider = $p->use_equal_sign ? '=' : ' ';

            array_push($parameters, ($p->option ? $p->option . $divider : '') . escapeshellarg($arguments));
        });

        return implode(' ', $parameters);
    }
}
