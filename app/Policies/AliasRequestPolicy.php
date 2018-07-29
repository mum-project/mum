<?php

namespace App\Policies;

use App\AliasRequest;
use App\Mailbox;
use Illuminate\Auth\Access\HandlesAuthorization;
use function isUserSuperAdmin;

class AliasRequestPolicy
{
    use HandlesAuthorization;

    public function before(Mailbox $user, $ability)
    {
        if (isUserSuperAdmin($user) && $ability !== 'update' && $ability !== 'updateStatus' && $ability !== 'create') {
            return true;
        }
    }

    /**
     * Determine whether the user can view the alias requests.
     *
     * @param Mailbox      $user
     * @param AliasRequest $aliasRequest
     * @return bool
     */
    public function view(Mailbox $user, AliasRequest $aliasRequest)
    {
        return $user->aliasRequests()
            ->get()
            ->contains($aliasRequest);
    }

    /**
     * Determine whether the user can create the alias requests.
     *
     * @param Mailbox $user
     * @return bool
     */
    public function create(Mailbox $user)
    {
        return !isUserSuperAdmin($user);
    }

    /**
     * Determine whether the user can update the alias request.
     *
     * @param Mailbox      $user
     * @param AliasRequest $aliasRequest
     * @return bool
     */
    public function update(Mailbox $user, AliasRequest $aliasRequest)
    {
        if ($aliasRequest->status === 'approved') {
            return false;
        }

        if (!isUserSuperAdmin() && $aliasRequest->mailbox->id !== $user->id) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can update the status of the alias request.
     *
     * @param Mailbox      $user
     * @param AliasRequest $aliasRequest
     * @return bool
     */
    public function updateStatus(Mailbox $user, AliasRequest $aliasRequest)
    {
        if ($aliasRequest->status === 'approved') {
            return false;
        }

        return isUserSuperAdmin($user);
    }

    /**
     * Determine whether the user can delete the alias request.
     *
     * @param Mailbox      $user
     * @param AliasRequest $aliasRequest
     * @return bool
     */
    public function delete(Mailbox $user, AliasRequest $aliasRequest)
    {
        if ($aliasRequest->mailbox->id === $user->id && $aliasRequest->status === 'open') {
            return true;
        }

        return false;
    }
}
