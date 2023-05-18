<?php

namespace Tests;

use App\Models\Server;
use App\Models\User;
use Database\Factories\UserFactory;

trait ServerTest
{
    protected User $user;

    protected Server $server;

    public function setUpServerTest()
    {
        /** @var User */
        $this->user = UserFactory::new()->withPersonalTeam(provisionedServer: true)->create();

        /** @var Server */
        $this->server = $this->user->currentTeam->servers->first();
    }
}
