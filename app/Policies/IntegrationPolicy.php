<?php

namespace App\Policies;

use App\Integration;
use App\Mailbox;
use Illuminate\Auth\Access\HandlesAuthorization;

class IntegrationPolicy
{
    use HandlesAuthorization;

    public function before(Mailbox $user, $ability)
    {
        if (!config('integrations.enabled.generally')) {
            return false;
        }

        if (isUserSuperAdmin($user)) {
            return true;
        }
    }

    /**
     * Determine whether the user can view a list of all integrations.
     *
     * @param Mailbox $user
     * @return bool
     */
    public function index(Mailbox $user)
    {
        return false;
    }

    /**
     * Determine whether the user can view the integration.
     *
     * @param Mailbox $user
     * @param Integration $integration
     * @return bool
     */
    public function view(Mailbox $user, Integration $integration)
    {
        return false;
    }

    /**
     * Determine whether the user can create integrations.
     *
     * @param Mailbox $user
     * @return bool
     */
    public function create(Mailbox $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the integration.
     *
     * @param Mailbox $user
     * @param Integration $integration
     * @return bool
     */
    public function update(Mailbox $user, Integration $integration)
    {
        return false;
    }

    /**
     * Determine whether the user can delete the integration.
     *
     * @param Mailbox $user
     * @param Integration $integration
     * @return bool
     */
    public function delete(Mailbox $user, Integration $integration)
    {
        return false;
    }
}
