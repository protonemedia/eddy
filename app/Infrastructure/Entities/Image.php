<?php

namespace App\Infrastructure\Entities;

class Image
{
    public function __construct(
        public readonly string $id,
        public readonly Distribution $distribution,
        public readonly OperatingSystem $operatingSystem,
    ) {
    }
}
