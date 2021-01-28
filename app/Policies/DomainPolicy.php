<?php

namespace App\Policies;

use App\Domain;
use App\Mailbox;
use Illuminate\Auth\Access\HandlesAuthorization;
use function isUserSuperAdmin;

class DomainPolicy
{
    use HandlesAuthorization;

    public function before(Mailbox $user, $ability)
    {
        if (isUserSuperAdmin($user)) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the domain.
     *
     * @param  \App\Mailbox $user
     * @param  \App\Domain  $domain
     * @return mixed
     */
    public function view(Mailbox $user, Domain $domain)
    {
        return $domain->admins()
            ->get()
            ->contains($user);
    }

    /**
     * Determine whether the user can create domains.
     *
     * @param  \App\Mailbox $user
     * @return mixed
     */
    public function create(Mailbox $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the domain.
     *
     * @param  \App\Mailbox $user
     * @param  \App\Domain  $domain
     * @return mixed
     */
    public function update(Mailbox $user, Domain $domain)
    {
        return false;
    }

    /**
     * Determine whether the user can delete the domain.
     *
     * @param  \App\Mailbox $user
     * @param  \App\Domain  $domain
     * @return mixed
     */
    public function delete(Mailbox $user, Domain $domain)
    {
        return false;
    }
}
