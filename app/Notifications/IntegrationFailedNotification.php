<?php

namespace App\Notifications;

use App\Integration;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class IntegrationFailedNotification extends Notification
{
    use Queueable;

    /** @var Integration */
    protected $integration;

    /**
     * Create a new notification instance.
     *
     * @param Integration $integration
     */
    public function __construct(Integration $integration)
    {
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
        return (new MailMessage)->error()
            ->line('An unexpected error occurred when running a configured integration.')
            ->action('View Failed Integration', route('integrations.show', ['integration' => $this->integration]))
            ->line('Please check the health of your integrated service and your integration configuration.');
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
