<?php

namespace App\Notifications;

use App\Exceptions\ShellCommandFailedException;
use App\ShellCommandIntegration;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ShellCommandFailedNotification extends Notification
{
    use Queueable;

    /** @var ShellCommandFailedException */
    protected $exception;

    /** @var ShellCommandIntegration */
    protected $integration;

    /**
     * Create a new notification instance.
     *
     * @param ShellCommandFailedException $exception
     * @param ShellCommandIntegration     $integration
     */
    public function __construct(ShellCommandFailedException $exception, ShellCommandIntegration $integration)
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
        return (new MailMessage)->error()
            ->line('We tried to run a shell command integration, but it failed with exit code ' .
                $this->exception->getExitCode() . '.')
            ->line('`$ ' . $this->exception->getShellCommand() . '`')
            ->line('Error Output: `' . $this->exception->getErrorOutput() . '`')
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
