<?php

namespace App\Jobs;

use App\Exceptions\IntegrationsDisabledException;
use App\Exceptions\ShellCommandFailedException;
use App\Mailbox;
use App\Notifications\IntegrationFailedNotification;
use App\Notifications\IntegrationsDisabledNotification;
use App\Notifications\ShellCommandFailedNotification;
use App\ShellCommandIntegration;
use App\Traits\IntegratesShellCommands;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Notification;

class ShellCommandJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, IntegratesShellCommands;

    /** @var ShellCommandIntegration */
    private $integration;

    /** @var array */
    private $placeholders;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * ShellCommandJob constructor.
     *
     * @param ShellCommandIntegration $integration
     * @param array                   $placeholders
     */
    public function __construct(ShellCommandIntegration $integration, array $placeholders)
    {
        $this->integration = $integration;
        $this->placeholders = $placeholders;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws IntegrationsDisabledException
     * @throws ShellCommandFailedException
     */
    public function handle()
    {
        try {
            $this->executeShellCommand($this->integration, $this->placeholders);
        } catch (ShellCommandFailedException $exception) {
            if ($this->attempts() < $this->tries) {
                $this->release(config('integrations.options.shell_commands.failed_retry_delay'));
                return;
            }
            throw $exception;
        }
    }

    /**
     * The job failed to process.
     *
     * @param  Exception $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        if ($exception instanceof IntegrationsDisabledException) {
            $this->notifySuperAdmins(new IntegrationsDisabledNotification($exception));
            return;
        }
        if ($exception instanceof ShellCommandFailedException) {
            $this->notifySuperAdmins(new ShellCommandFailedNotification($exception, $this->integration));
            return;
        }
        $this->notifySuperAdmins(new IntegrationFailedNotification($this->integration));
    }

    /**
     * Notify all super admins.
     *
     * @param $notification
     */
    private function notifySuperAdmins($notification)
    {
        Notification::send(Mailbox::query()
            ->isSuperAdmin()
            ->get(), $notification);
    }
}
