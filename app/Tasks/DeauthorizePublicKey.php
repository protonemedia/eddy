<?php

namespace App\Tasks;

use App\Models\Server;

class DeauthorizePublicKey extends Task
{
    protected int $timeout = 15;

    public function __construct(public Server $server, public string $publicKey)
    {
    }
}
