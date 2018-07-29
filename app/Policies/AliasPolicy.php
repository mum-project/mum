<?php

namespace App\Policies;

use App\Mailbox;
use App\Alias;
use Illuminate\Auth\Access\HandlesAuthorization;
use function isUserSuperAdmin;

class AliasPolicy
{
    use HandlesAuthorization;

    public function before(Mailbox $user, $ability)
    {
        if (isUserSuperAdmin($user)) {
            return true;
        }
    }

    /**
     * Determine whether the user can view a list of aliases.
     *
     * @param  \App\Mailbox $user
     * @return mixed
     */
    public function index(Mailbox $user)
    {
        return $user->administratedDomains()
                ->count() > 0 || $user->sendingAliases()
                ->count() > 0 || $user->receivingAliases()
                ->count() > 0;
    }

    /**
     * Determine whether the user can view the alias.
     *
     * @param  \App\Mailbox $user
     * @param  \App\Alias   $alias
     * @return mixed
     */
    public function view(Mailbox $user, Alias $alias)
    {
        return Alias::whereAuthorized($user)
            ->get()
            ->contains($alias);
    }

    /**
     * Determine whether the user can create aliases.
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
     * Determine whether the user can update the alias.
     *
     * @param  \App\Mailbox $user
     * @param  \App\Alias   $alias
     * @return mixed
     */
    public function update(Mailbox $user, Alias $alias)
    {
        return $user->administratedDomains()
            ->get()
            ->contains($alias->domain()
                ->get());
    }

    /**
     * Determine whether the user can delete the alias.
     *
     * @param  \App\Mailbox $user
     * @param  \App\Alias   $alias
     * @return mixed
     */
    public function delete(Mailbox $user, Alias $alias)
    {
        return $user->administratedDomains()
            ->get()
            ->contains($alias->domain()
                ->get());
    }
}
