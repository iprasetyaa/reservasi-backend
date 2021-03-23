<?php

namespace App\Policies;

use App\Enums\UserRoleEnum;
use App\Models\CommandCenterCloseDate;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CCCloseDatePolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->hasRole(UserRoleEnum::admin_reservasi())) {
            return true;
        }
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasPermission('create-reservation');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\User  $user
     * @param  \App\CommandCenterCloseDate  $commandCenterCloseDate
     * @return mixed
     */
    public function update(User $user)
    {
        return $user->hasPermission('update-reservation');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\CommandCenterCloseDate  $commandCenterCloseDate
     * @return mixed
     */
    public function delete(User $user)
    {
        return $user->hasPermission('delete-reservation');
    }
}
