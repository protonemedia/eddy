<?php

namespace App\Tasks;

class ReloadCaddy extends Task
{
    protected int $timeout = 30;

    /**
     * The command to run.
     */
    public function render(): string
    {
        return '/usr/sbin/service caddy reload';
    }
}
