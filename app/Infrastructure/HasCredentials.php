<?php

namespace App\Infrastructure;

interface HasCredentials
{
    public function canConnect(): bool;
}
