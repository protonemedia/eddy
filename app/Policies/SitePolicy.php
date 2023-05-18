<?php

namespace App\Policies;

use App\Models\Site;
use App\Models\User;

class SitePolicy
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
    public function view(User $user, Site $site): bool
    {
        return $user->currentTeam->id === $site->server->team_id;
    }

    /**
     * Determine whether the user can manage the model.
     */
    public function manage(User $user, Site $site): bool
    {
        return $user->currentTeam->id === $site->server->team_id;
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
    public function update(User $user, Site $site): bool
    {
        return $user->currentTeam->id === $site->server->team_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Site $site): bool
    {
        return $user->currentTeam->id === $site->server->team_id;
    }
}
