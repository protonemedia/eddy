<?php

namespace App\Policies;

use App\Models\DatabaseUser;
use App\Models\User;

class DatabaseUserPolicy
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
    public function view(User $user, DatabaseUser $databaseUser): bool
    {
        return $user->currentTeam->id === $databaseUser->server->team_id;
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
    public function update(User $user, DatabaseUser $databaseUser): bool
    {
        return $user->currentTeam->id === $databaseUser->server->team_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DatabaseUser $databaseUser): bool
    {
        return $user->currentTeam->id === $databaseUser->server->team_id;
    }
}
