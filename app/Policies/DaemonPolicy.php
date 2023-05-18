<?php

namespace App\Policies;

use App\Models\Daemon;
use App\Models\User;

class DaemonPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Daemon $daemon): bool
    {
        return $user->currentTeam->id === $daemon->server->team_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Daemon $daemon): bool
    {
        return $user->currentTeam->id === $daemon->server->team_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Daemon $daemon): bool
    {
        return $user->currentTeam->id === $daemon->server->team_id;
    }
}
