<?php

namespace App;

class Csr
{
    public function __construct(
        public readonly string $csr,
        public readonly KeyPair $keyPair
    ) {
    }
}
