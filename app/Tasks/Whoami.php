<?php

namespace App\Tasks;

class Whoami extends Task
{
    protected int $timeout = 15;

    /**
     * The command to run.
     */
    public function render(): string
    {
        return 'whoami';
    }
}
