<?php

namespace App\Policies;

use App\Mailbox;
use App\TlsPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;
use function isUserSuperAdmin;

class TlsPolicyPolicy
{
    use HandlesAuthorization;

    public function before(Mailbox $user, $ability)
    {
        if (isUserSuperAdmin($user)) {
            return true;
        }
    }

    /**
     * Determine whether the user can view all tls policies.
     *
     * @param  \App\Mailbox  $user
     * @return mixed
     */
    public function index(Mailbox $user)
    {
        return false;
    }

    /**
     * Determine whether the user can view the tls policy.
     *
     * @param  \App\Mailbox  $user
     * @param  \App\TlsPolicy  $tlsPolicy
     * @return mixed
     */
    public function view(Mailbox $user, TlsPolicy $tlsPolicy)
    {
        return false;
    }

    /**
     * Determine whether the user can create tls policies.
     *
     * @param  \App\Mailbox  $user
     * @return mixed
     */
    public function create(Mailbox $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the tls policy.
     *
     * @param  \App\Mailbox  $user
     * @param  \App\TlsPolicy  $tlsPolicy
     * @return mixed
     */
    public function update(Mailbox $user, TlsPolicy $tlsPolicy)
    {
        return false;
    }

    /**
     * Determine whether the user can delete the tls policy.
     *
     * @param  \App\Mailbox  $user
     * @param  \App\TlsPolicy  $tlsPolicy
     * @return mixed
     */
    public function delete(Mailbox $user, TlsPolicy $tlsPolicy)
    {
        return false;
    }
}
