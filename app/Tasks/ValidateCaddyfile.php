<?php

namespace App\Tasks;

use Illuminate\Support\Str;

class ValidateCaddyfile extends Task
{
    public string $path;

    public function __construct(public string $caddyfile)
    {
        $id = Str::random();

        $this->path = "/tmp/caddyfile-{$id}.caddyfile";
    }
}
