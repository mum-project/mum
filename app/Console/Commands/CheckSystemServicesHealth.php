<?php

namespace App\Console\Commands;

use App\Exceptions\ShellCommandFailedException;
use App\ServiceHealthCheck;
use App\SystemService;
use function config;
use function escapeshellarg;
use function escapeshellcmd;
use Illuminate\Console\Command;
use Facades\App\Process;
use function str_replace;

class CheckSystemServicesHealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system-services:health';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if all system services are running';

    /**
     * The property that should be checked with systemctl.
     *
     * @var string
     */
    protected $systemctlProperty = 'SubState';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            foreach (SystemService::all() as $systemService) {
                $this->createServiceHealthCheck($systemService);
            }
        } catch (ShellCommandFailedException $exception) {
            $this->error($exception->getMessage());
            $this->error($exception->getErrorOutput());
            return 1;
        }
        return 0;
    }

    /**
     * Checks the current state of the provided system service
     * and creates a service health check record.
     *
     * @param SystemService $systemService
     * @throws ShellCommandFailedException
     */
    protected function createServiceHealthCheck(SystemService $systemService)
    {
        $output = $this->checkSystemctl($systemService->service);
        $output = str_replace($this->getSystemctlProperty() . '=', '', trim($output));

        $healthCheck = new ServiceHealthCheck(['output' => $output]);

        if ($healthCheck->wasRunning()) {
            $this->deleteOldRunningHealthChecks($systemService);
        }

        $systemService->serviceHealthChecks()
            ->save($healthCheck);

        $this->deleteOldNotRunningHealthChecks($systemService);
    }

    /**
     * Checks the current state of the provided system service
     * with systemctl.
     *
     * @param string $service
     * @return string
     * @throws ShellCommandFailedException
     */
    protected function checkSystemctl(string $service)
    {
        $command = $this->buildShellCommand($service);
        /** @var \Symfony\Component\Process\Process $process */
        $process = Process::run($command);
        if (!$process->isSuccessful()) {
            throw new ShellCommandFailedException($command, $process->getErrorOutput(), $process->getExitCode());
        }
        return $process->getOutput();
    }

    /**
     * Provides an escaped shell command that calls systemctl
     * to check a system service's status.
     *
     * @param string $service
     * @return string
     */
    protected function buildShellCommand(string $service)
    {
        return escapeshellcmd('systemctl show -p ' . $this->getSystemctlProperty() . ' ' . escapeshellarg($service));
    }

    /**
     * Returns the systemctl property that should be checked.
     * See systemctl's man page for options.
     *
     * @return string
     */
    protected function getSystemctlProperty()
    {
        return $systemctlProperty ?? 'SubState';
    }

    /**
     * Deletes all old health checks for the provided system service where the
     * output was 'running'.
     *
     * @param SystemService $systemService
     */
    protected function deleteOldRunningHealthChecks(SystemService $systemService)
    {
        $systemService->serviceHealthChecks()
            ->whereRunning()
            ->delete();
    }

    /**
     * Deletes old health check reports if the config has a maximum number of
     * entries in the incident history. Does nothing, if the config value is null.
     *
     * @param SystemService $systemService
     */
    protected function deleteOldNotRunningHealthChecks(SystemService $systemService)
    {
        $maxEntries = config('mum.system_health.max_entries_incident_history');
        $historyCount = $systemService->serviceHealthChecks()
            ->whereNotRunning()
            ->count();
        if ($maxEntries && $historyCount > $maxEntries) {
            $systemService->serviceHealthChecks()
                ->oldest()
                ->limit($maxEntries - 1)
                ->delete();
        }
    }
}
