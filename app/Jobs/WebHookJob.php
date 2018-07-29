<?php

namespace App\Jobs;

use App\Exceptions\HttpRequestFailedException;
use App\Exceptions\IntegrationsDisabledException;
use App\Mailbox;
use App\Notifications\IntegrationFailedNotification;
use App\Notifications\IntegrationsDisabledNotification;
use App\Notifications\WebHookFailedNotification;
use App\Traits\IntegratesWebHooks;
use App\WebHookIntegration;
use function config;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Notification;
use function now;

class WebHookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, IntegratesWebHooks;

    /** @var WebHookIntegration */
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
     * Create a new job instance.
     *
     * @param WebHookIntegration $integration
     * @param array              $placeholders
     */
    public function __construct(WebHookIntegration $integration, array $placeholders)
    {
        $this->integration = $integration;
        $this->placeholders = $placeholders;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws IntegrationsDisabledException
     * @throws HttpRequestFailedException
     */
    public function handle()
    {
        try {
            $this->callWebHook($this->integration, $this->placeholders);
        } catch (HttpRequestFailedException $exception) {
            if ($this->attempts() < $this->tries) {
                $this->release(config('integrations.options.web_hooks.failed_retry_delay'));
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
        if ($exception instanceof HttpRequestFailedException) {
            $this->notifySuperAdmins(new WebHookFailedNotification($exception, $this->integration));
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
