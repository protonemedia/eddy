<?php

namespace App\Tasks;

abstract class RestartService extends Task
{
    protected int $timeout = 30;

    protected string $service;

    /**
     * The command to run.
     */
    public function render(): string
    {
        return "service {$this->service} restart";
    }
}
