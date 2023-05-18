<?php

namespace App\Server\Database;

class UserHost
{
    public function __construct(public readonly string $user, public readonly string $host)
    {
    }
}
