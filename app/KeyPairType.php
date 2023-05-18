<?php

namespace App;

enum KeyPairType: string
{
    case Ed25519 = 'ed25519';
    case Rsa = 'rsa';
}
