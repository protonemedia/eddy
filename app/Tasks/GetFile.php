<?php

namespace App\Tasks;

class GetFile extends Task
{
    protected int $timeout = 30;

    public function __construct(private string $path, private ?int $lines = null)
    {
    }

    /**
     * The command to run.
     */
    public function render(): string
    {
        if ($this->lines) {
            return "tail -n {$this->lines} {$this->path}";
        }

        return "tail -c 1M {$this->path}";
    }
}
