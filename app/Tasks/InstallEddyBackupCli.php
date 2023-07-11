<?php

namespace App\Tasks;

use App\Models\Server;

class InstallEddyBackupCli extends Task
{
    protected int $timeout = 120;

    public function __construct(public Server $server)
    {
    }
}
