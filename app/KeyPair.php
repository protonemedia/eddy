<?php

namespace App;

class KeyPair
{
    public function __construct(
        public readonly string $privateKey,
        public readonly string $publicKey,
        public readonly KeyPairType $type
    ) {
    }
}
