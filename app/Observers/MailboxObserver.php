<?php

namespace App\Observers;

use App\Mailbox;
use App\Traits\IntegrationObserverTrait;

class MailboxObserver
{
    use IntegrationObserverTrait;

    /**
     * Listen to the Mailbox creating event.
     *
     * @param Mailbox $mailbox
     * @return void
     */
    public function creating(Mailbox $mailbox)
    {
        if (config('mum.mailboxes.homedir')) {
            $mailbox->homedir = getHomedirForMailbox($mailbox->local_part, $mailbox->domain->domain);
        }
        if (config('mum.mailboxes.maildir')) {
            $mailbox->maildir = getMaildirForMailbox($mailbox->local_part, $mailbox->domain->domain);
        }
    }

    /**
     * Listen to the Mailbox created event.
     *
     * @param Mailbox $mailbox
     * @return void
     */
    public function created(Mailbox $mailbox)
    {
        $this->runShellCommandIntegrations('created', $mailbox);
        $this->runWebHookIntegrations('created', $mailbox);
    }

    /**
     * Listen to the Mailbox updated event.
     *
     * @param Mailbox $mailbox
     * @return void
     */
    public function updated(Mailbox $mailbox)
    {
        $this->runShellCommandIntegrations('updated', $mailbox);
        $this->runWebHookIntegrations('updated', $mailbox);
    }

    /**
     * Listen to the Mailbox deleted event.
     *
     * @param Mailbox $mailbox
     * @return void
     */
    public function deleted(Mailbox $mailbox)
    {
        $this->runShellCommandIntegrations('deleted', $mailbox);
        $this->runWebHookIntegrations('deleted', $mailbox);
    }
}
