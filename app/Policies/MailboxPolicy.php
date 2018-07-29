<?php

namespace App\Policies;

use App\Mailbox;
use Illuminate\Auth\Access\HandlesAuthorization;
use function isUserSuperAdmin;

class MailboxPolicy
{
    use HandlesAuthorization;

    public function before(Mailbox $user, $ability)
    {
        if (isUserSuperAdmin($user)) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the mailbox.
     *
     * @param  \App\Mailbox $user
     * @param  \App\Mailbox $mailbox
     * @return mixed
     */
    public function view(Mailbox $user, Mailbox $mailbox)
    {
        return Mailbox::whereAuthorized($user)
            ->get()
            ->contains($mailbox);
    }

    /**
     * Determine whether the user can create mailboxes.
     *
     * @param  \App\Mailbox $user
     * @return mixed
     */
    public function create(Mailbox $user)
    {
        return $user->administratedDomains()
                ->count() > 0;
    }

    /**
     * Determine whether the user can update the mailbox.
     *
     * @param  \App\Mailbox $user
     * @param  \App\Mailbox $mailbox
     * @return mixed
     */
    public function update(Mailbox $user, Mailbox $mailbox)
    {
        return Mailbox::whereAuthorized($user)
                ->get()
                ->contains($mailbox) && !$mailbox->isSuperAdmin();
    }

    /**
     * Determine whether the user can delete the mailbox.
     *
     * @param  \App\Mailbox $user
     * @param  \App\Mailbox $mailbox
     * @return mixed
     */
    public function delete(Mailbox $user, Mailbox $mailbox)
    {
        return Mailbox::whereAuthorized($user)
                ->get()
                ->contains($mailbox) && !$mailbox->isSuperAdmin();
    }
}
