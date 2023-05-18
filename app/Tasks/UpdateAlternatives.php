<?php

namespace App\Tasks;

class UpdateAlternatives extends Task
{
    public function __construct(public string $link, public string $path)
    {

    }

    /**
     * The command to run.
     */
    public function render(): string
    {
        return "update-alternatives --set {$this->link} {$this->path}";
    }
}
