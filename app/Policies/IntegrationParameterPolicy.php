<?php

namespace App\Policies;

use App\Integration;
use App\Mailbox;
use App\IntegrationParameter;
use Illuminate\Auth\Access\HandlesAuthorization;

class IntegrationParameterPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view all integration parameters.
     *
     * @param  \App\Mailbox $user
     * @param Integration   $integration
     * @return mixed
     */
    public function index(Mailbox $user, Integration $integration)
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can view the integration parameter.
     *
     * @param  \App\Mailbox              $user
     * @param  \App\IntegrationParameter $integrationParameter
     * @param Integration                $integration
     * @return mixed
     */
    public function view(Mailbox $user, IntegrationParameter $integrationParameter, Integration $integration)
    {
        return $user->isSuperAdmin() && $integrationParameter->integration_id === $integration->id;
    }

    /**
     * Determine whether the user can create integration parameters.
     *
     * @param  \App\Mailbox $user
     * @param Integration   $integration
     * @return mixed
     */
    public function create(Mailbox $user, Integration $integration)
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can update the integration parameter.
     *
     * @param  \App\Mailbox              $user
     * @param  \App\IntegrationParameter $integrationParameter
     * @param Integration                $integration
     * @return mixed
     */
    public function update(Mailbox $user, IntegrationParameter $integrationParameter, Integration $integration)
    {
        return $user->isSuperAdmin() && $integrationParameter->integration_id === $integration->id;
    }

    /**
     * Determine whether the user can delete the integration parameter.
     *
     * @param  \App\Mailbox              $user
     * @param  \App\IntegrationParameter $integrationParameter
     * @param Integration                $integration
     * @return mixed
     */
    public function delete(Mailbox $user, IntegrationParameter $integrationParameter, Integration $integration)
    {
        return $user->isSuperAdmin() && $integrationParameter->integration_id === $integration->id;
    }
}
