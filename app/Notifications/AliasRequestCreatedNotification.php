<?php

namespace App\Notifications;

use App\AliasRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AliasRequestCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var AliasRequest
     */
    protected $aliasRequest;

    /**
     * Create a new notification instance.
     *
     * @param AliasRequest $aliasRequest
     */
    public function __construct(AliasRequest $aliasRequest)
    {
        $this->aliasRequest = $aliasRequest;
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
        $aliasRequestAddress = $this->aliasRequest->address();
        $mailboxAddress = $this->aliasRequest->mailbox()
            ->address();

        return (new MailMessage)->line('A new request for an alias with the address ' . $aliasRequestAddress .
            ' was created by ' . $mailboxAddress . '.')
            ->action('Show Details', route('alias-requests.show', ['aliasRequest' => $this->aliasRequest]))
            ->line('Please have a look at the request and approve or dismiss it.');
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
