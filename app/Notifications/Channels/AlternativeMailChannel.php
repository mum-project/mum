<?php

namespace App\Notifications\Channels;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Channels\MailChannel;

/**
 * Extends Laravel's MailChannel to switch the email address to the
 * alternative_email. Everything else is exactly the same as with
 * the normal MailChannel.
 *
 * @package App\Notifications\Channels
 */
class AlternativeMailChannel extends MailChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed                                  $notifiable
     * @param  \Illuminate\Notifications\Notification $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toMail($notifiable);

        if (!$notifiable->routeNotificationFor('alternative_mail', $notification) && !$message instanceof Mailable) {
            return;
        }

        if ($message instanceof Mailable) {
            return $message->send($this->mailer);
        }

        $this->mailer->send(
            $this->buildView($message),
            $message->data(),
            $this->messageBuilder($notifiable, $notification, $message)
        );
    }

    /**
     * Get the recipients of the given message.
     *
     * @param  mixed                                          $notifiable
     * @param  \Illuminate\Notifications\Notification         $notification
     * @param  \Illuminate\Notifications\Messages\MailMessage $message
     * @return mixed
     */
    protected function getRecipients($notifiable, $notification, $message)
    {
        if (is_string($recipients = $notifiable->routeNotificationFor('alternative_mail', $notification))) {
            $recipients = [$recipients];
        }

        return collect($recipients)
            ->map(function ($recipient) {
                return is_string($recipient) ? $recipient : $recipient->email;
            })
            ->all();
    }
}
