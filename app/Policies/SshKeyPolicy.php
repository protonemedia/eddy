<?php

namespace App\Policies;

use App\Models\SshKey;
use App\Models\User;

class SshKeyPolicy
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
    public function manage(User $user, SshKey $sshKey): bool
    {
        return $user->id === $sshKey->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SshKey $sshKey): bool
    {
        return $user->id === $sshKey->user_id;
    }
}
