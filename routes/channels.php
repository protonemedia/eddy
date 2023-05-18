<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('teams.{id}', function ($user, $id) {
    if ($user->currentTeam->id == $id) {
        return [
            'user_id' => $user->id,
            'team_id' => $user->currentTeam->id,
        ];
    }
});
