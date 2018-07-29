<?php

namespace App\Notifications;

use App\AliasRequest;
use App\Http\Resources\AliasResource;
use function compact;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AliasRequestStatusNotification extends Notification implements ShouldQueue
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
        $mailMessage = new MailMessage;

        if ($this->aliasRequest->status === 'accepted') {
            $mailMessage->success()
                ->line('The request for ' . $aliasRequestAddress . ' was accepted!' .
                    ' An alias with that address was created.')
                ->action('Show Alias', route('aliases.show', ['alias' => $this->aliasRequest->alias]));
        } elseif ($this->aliasRequest->status === 'dismissed') {
            $mailMessage->error()
                ->line('Sorry, the request for ' . $aliasRequestAddress . ' was dismissed.')
                ->action('View Request', route('alias-requests.show', ['aliasRequest' => $this->aliasRequest]));
        } else {
            $mailMessage->line('The request for ' . $aliasRequestAddress . ' was reopened.')
                ->action('View Request', route('alias-requests.show', ['aliasRequest' => $this->aliasRequest]));
        }
        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [//
        ];
    }
}
