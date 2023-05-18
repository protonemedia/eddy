<?php

namespace App\Tasks;

class GenerateEd25519KeyPair extends Task
{
    public function __construct(public string $privatePath)
    {
    }

    public function comment()
    {
        return config('eddy.server_defaults.ssh_comment');
    }
}
