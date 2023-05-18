<?php

namespace App\Infrastructure\Entities;

class SshKey
{
    public function __construct(
        public readonly string $id,
        public readonly string $publicKey,
    ) {
    }
}
