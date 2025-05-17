<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{

    public function viewAny(User $user): bool
    {

        return $user->isAdmin() || $user->isManager();
    }


    public function view(User $user, User $model): bool
    {

        if ($user->isAdmin()) {
            return true;
        }


        if ($user->isManager() && $model->isStaff()) {
            return true;
        }


        return $user->id === $model->id;
    }


    public function create(User $user): bool
    {

        return $user->isAdmin();
    }


    public function update(User $user, User $model): bool
    {

        if ($user->isAdmin()) {
            return true;
        }


        if ($user->isManager() && $model->isStaff()) {
            return true;
        }


        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {

        return $user->isAdmin() && $user->id !== $model->id;
    }

    public function manage(User $user, User $model): bool
    {

        if ($user->isAdmin()) {
            return true;
        }


        if ($user->isManager() && $model->isStaff()) {
            return true;
        }

        return false;
    }
}
