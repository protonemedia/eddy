<?php

namespace App\Policies;

use App\Models\Deployment;
use App\Models\User;

class DeploymentPolicy
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
    public function view(User $user, Deployment $deployment): bool
    {
        return $user->currentTeam->id === $deployment->site->server->team_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }
}
