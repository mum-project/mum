<?php

namespace App\Notifications;

use App\Exceptions\IntegrationsDisabledException;
use function compact;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class IntegrationsDisabledNotification extends Notification
{
    use Queueable;

    protected $integrationsDisabledException;

    /**
     * Create a new notification instance.
     *
     * @param IntegrationsDisabledException $exception
     */
    public function __construct(IntegrationsDisabledException $exception)
    {
        $this->integrationsDisabledException = $exception;
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
            ->subject('Integrations Disabled')
            ->greeting('Attention: Integrations are Disabled!')
            ->line('We tried to run an integration that was configured, but one of the necessary options is disabled.')
            ->line('Error message: "' . $this->integrationsDisabledException->getMessage() . '"');

        if ($integration = $this->integrationsDisabledException->getIntegration()) {
            $message->action('View Failed Integration', route('integrations.show', compact('integration')));
        }

        $message->line('Please check your configuration in the .env file.');

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
