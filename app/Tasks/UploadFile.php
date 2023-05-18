<?php

namespace App\Tasks;

class UploadFile extends Task
{
    protected int $timeout = 20;

    public function __construct(
        public string $path,
        public string $contents,
    ) {
    }

    public function directory(): string
    {
        return dirname($this->path);
    }
}
