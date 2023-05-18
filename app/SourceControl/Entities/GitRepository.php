<?php

namespace App\SourceControl\Entities;

class GitRepository
{
    public function __construct(
        public readonly string $name,
        public readonly string $url,
    ) {
    }
}
