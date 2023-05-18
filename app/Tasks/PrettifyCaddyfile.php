<?php

namespace App\Tasks;

class PrettifyCaddyfile extends Task
{
    protected int $timeout = 15;

    public function __construct(public string $path)
    {
    }

    /**
     * The command to run.
     */
    public function render(): string
    {
        return "caddy fmt {$this->path} --overwrite";
    }
}
