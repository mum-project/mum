<?php

namespace App\Notifications;

use App\Exceptions\HttpRequestFailedException;
use App\WebHookIntegration;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class WebHookFailedNotification extends Notification
{
    use Queueable;

    /** @var HttpRequestFailedException */
    protected $exception;

    /** @var WebHookIntegration */
    protected $integration;

    /**
     * Create a new notification instance.
     *
     * @param HttpRequestFailedException $exception
     * @param WebHookIntegration         $integration
     */
    public function __construct(HttpRequestFailedException $exception, WebHookIntegration $integration)
    {
        $this->exception = $exception;
        $this->integration = $integration;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $message = (new MailMessage)->error()
            ->subject('Webhook Integration Failed');

        if ($this->exception->getHttpResponse() != null) {
            $message->line('We tried to run a webhook integration, but it failed with status code ' .
                $this->exception->getHttpResponse()
                    ->getStatusCode() . '.');
        } else {
            $message->line('We tried to run a webhook integration, but it failed unexpectedly.');
        }

        $message->action('View Failed Integration', route('integrations.show', ['integration' => $this->integration]))
            ->line('Please check the health of your integrated service and your integration configuration.');
        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
