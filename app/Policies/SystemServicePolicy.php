<?php

namespace App\Policies;

use App\Mailbox;
use App\SystemService;
use Illuminate\Auth\Access\HandlesAuthorization;
use function isUserSuperAdmin;

class SystemServicePolicy
{
    use HandlesAuthorization;

    public function before(Mailbox $user, $ability)
    {
        if (!config('mum.system_health.check_services')) {
            return false;
        }
    }

    /**
     * Determine whether the user can view all system services.
     *
     * @param  \App\Mailbox       $user
     * @return mixed
     */
    public function index(Mailbox $user)
    {
        return isUserSuperAdmin($user);
    }

    /**
     * Determine whether the user can view the system service.
     *
     * @param  \App\Mailbox       $user
     * @param  \App\SystemService $systemService
     * @return mixed
     */
    public function view(Mailbox $user, SystemService $systemService)
    {
        return isUserSuperAdmin($user);
    }

    /**
     * Determine whether the user can create system services.
     *
     * @param  \App\Mailbox $user
     * @return mixed
     */
    public function create(Mailbox $user)
    {
        return isUserSuperAdmin($user);
    }

    /**
     * Determine whether the user can update the system service.
     *
     * @param  \App\Mailbox       $user
     * @param  \App\SystemService $systemService
     * @return mixed
     */
    public function update(Mailbox $user, SystemService $systemService)
    {
        return isUserSuperAdmin($user);
    }

    /**
     * Determine whether the user can delete the system service.
     *
     * @param  \App\Mailbox       $user
     * @param  \App\SystemService $systemService
     * @return mixed
     */
    public function delete(Mailbox $user, SystemService $systemService)
    {
        return isUserSuperAdmin($user);
    }
}
