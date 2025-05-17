<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{

    public function viewAny(User $user): bool
    {

        return true;
    }


    public function view(User $user, Task $task): bool
    {

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isManager()) {
            if ($task->created_by === $user->id) {
                return true;
            }

            $assignedUser = User::find($task->assigned_to);
            return $assignedUser && $assignedUser->isStaff();
        }


        return $task->assigned_to === $user->id || $task->created_by === $user->id;
    }


    public function create(User $user): bool
    {

        return true;
    }


    public function update(User $user, Task $task): bool
    {

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isManager()) {
            if ($task->created_by === $user->id) {
                return true;
            }

            $assignedUser = User::find($task->assigned_to);
            return $assignedUser && $assignedUser->isStaff();
        }


        return $task->assigned_to === $user->id || $task->created_by === $user->id;
    }


    public function delete(User $user, Task $task): bool
    {

        if ($user->isAdmin()) {
            return true;
        }


        return $task->created_by === $user->id;
    }


    public function assign(User $user, Task $task, User $assignee): bool
    {

        if ($user->isAdmin()) {
            return true;
        }


        if ($user->isManager() && $assignee->isStaff()) {
            return true;
        }


        return $user->isStaff() && $user->id === $assignee->id;
    }
}
