<?php

namespace App\Notifications;

use App\Mailbox;
use App\Notifications\Channels\AlternativeMailChannel;

/**
 * Extends Laravel's ResetPassword notification to switch the notification driver
 * to our alternative mail notification channel if the $notifiable is of type Mailbox
 * and has an alternative_email.
 *
 * @package App\Notifications
 */
class ResetPassword extends \Illuminate\Auth\Notifications\ResetPassword
{
    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        if ($notifiable instanceof Mailbox && $notifiable->alternative_email) {
            return [AlternativeMailChannel::class];
        }
        return ['mail'];
    }
}
